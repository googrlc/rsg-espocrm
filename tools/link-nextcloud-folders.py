#!/usr/bin/env python3
"""
RSG — bulk-link EspoCRM Accounts to their Nextcloud client folders.

One-off: walks the Nextcloud "Commercial Lines" and "Personal Lines" top-level
client folders via WebDAV, matches each EspoCRM Account by (normalised) name,
and writes the Nextcloud folder share URL into the Account.nextcloud_folder_url
field so the "Nextcloud Docs" side panel is populated for every client.

Runs on the EspoCRM host (needs docker access to app-espocrm-1 / app-mysql-1).
Nextcloud + DB credentials are read from the app's data/config-internal.php —
nothing is hardcoded.

Usage:
    python3 tools/link-nextcloud-folders.py            # match + dry-run report
    python3 tools/link-nextcloud-folders.py --apply     # actually write to DB

Safe by default: prints a report only. Pass --apply to write.
"""
import base64
import json
import re
import subprocess
import sys
import urllib.parse
import urllib.request
from xml.etree import ElementTree as ET

DAV = "{DAV:}"
TOP_CLIENT_FOLDERS = ["Commercial Lines", "Personal Lines"]


def sh(cmd):
    r = subprocess.run(cmd, shell=True, capture_output=True, text=True)
    if r.returncode != 0 and r.stderr:
        sys.stderr.write(r.stderr[:500] + "\n")
    return r.stdout


def load_config():
    """Read Nextcloud + DB creds from the app container config."""
    php = (
        "docker exec app-espocrm-1 php -r '"
        "$ci=include \"/var/www/html/data/config-internal.php\";"
        "echo json_encode(["
        "\"ncUrl\"=>$ci[\"nextcloudUrl\"],"
        "\"ncUser\"=>$ci[\"nextcloudUser\"],"
        "\"ncPass\"=>$ci[\"nextcloudAppPassword\"],"
        "\"dbUser\"=>$ci[\"database\"][\"user\"],"
        "\"dbPass\"=>$ci[\"database\"][\"password\"],"
        "\"dbName\"=>$ci[\"database\"][\"dbname\"]"
        "]);'"
    )
    return json.loads(sh(php).strip())


def propfind(url, user, pwd, depth=1):
    req = urllib.request.Request(url, method="PROPFIND")
    req.add_header("Depth", str(depth))
    req.add_header("Content-Type", "application/xml")
    req.add_header(
        "Authorization",
        "Basic " + base64.b64encode(f"{user}:{pwd}".encode()).decode(),
    )
    with urllib.request.urlopen(req, timeout=25) as r:
        return r.getcode(), r.read()


def list_folder(base, user, pwd, path):
    """Return list of (name, is_dir) for immediate children of a Nextcloud path."""
    enc_segments = [urllib.parse.quote(seg, safe="") for seg in path.strip("/").split("/") if seg]
    dav_path = "/remote.php/dav/files/" + urllib.parse.quote(user, safe="") + "/" + "/".join(enc_segments)
    url = base.rstrip("/") + dav_path
    code, body = propfind(url, user, pwd)
    if code != 207:
        return [], f"PROPFIND {path} returned HTTP {code}"
    try:
        root = ET.fromstring(body)
    except ET.ParseError as e:
        return [], f"XML parse error for {path}: {e}"

    # The folder's own href (decoded) so we can skip it.
    self_decoded = "/remote.php/dav/files/" + user + "/" + path.strip("/")
    items = []
    for resp in root.iter(f"{DAV}response"):
        href_el = resp.find(f"{DAV}href")
        if href_el is None or not href_el.text:
            continue
        href = urllib.parse.unquote(href_el.text).rstrip("/")
        if href == self_decoded.rstrip("/"):
            continue  # the folder itself
        name = href.rstrip("/").split("/")[-1]
        if not name:
            continue
        rt = resp.find(f".//{DAV}resourcetype")
        is_dir = rt is not None and rt.find(f"{DAV}collection") is not None
        items.append((name, is_dir))
    return items, None


def norm(name):
    """Normalise a name for matching: lowercase, collapse whitespace, strip."""
    return re.sub(r"\s+", " ", (name or "").strip()).lower()


def swap_lastfirst(name):
    """'Harris, Arthur' -> 'Arthur Harris'. Only single-comma, two-part names."""
    n = (name or "").strip()
    if n.count(",") != 1:
        return None
    a, b = [x.strip() for x in n.split(",", 1)]
    if not a or not b:
        return None
    return b + " " + a


def name_variants(name):
    """Normalised forms to try when matching an account name to a folder."""
    vs = [norm(name)]
    sw = swap_lastfirst(name)
    if sw:
        vs.append(norm(sw))
    # also a no-suffix variant? keep it conservative — only exact + swap
    return vs


def folder_url(base, path):
    """Build the Nextcloud Files-app share URL for a folder path."""
    return base.rstrip("/") + "/index.php/apps/files?dir=" + urllib.parse.quote("/" + path.strip("/"), safe="")


def mysql(cfg, sql, raw=False):
    cmd = (
        f"docker exec app-mysql-1 mysql -u{cfg['dbUser']} -p{cfg['dbPass']} "
        f"{cfg['dbName']} -sN -e {shell_quote(sql)}"
    )
    out = sh(cmd)
    return out if raw else [line for line in out.splitlines() if line.strip()]


def shell_quote(s):
    return "'" + s.replace("'", "'\\''") + "'"


def esc_sql(s):
    return s.replace("\\", "\\\\").replace("'", "''")


def main():
    apply = "--apply" in sys.argv

    cfg = load_config()
    base, user, pwd = cfg["ncUrl"], cfg["ncUser"], cfg["ncPass"]
    print(f"Nextcloud: {base}  (user {user})")

    # 1. gather client folders from the two top-level client dirs
    folders = {}          # norm_name -> (parent, full_path)
    collisions = {}       # norm_name -> [parents]
    for parent in TOP_CLIENT_FOLDERS:
        items, err = list_folder(base, user, pwd, parent)
        if err:
            print("  ! " + err)
            continue
        dir_count = sum(1 for _, d in items if d)
        print(f"  {parent}: {dir_count} subfolders")
        for name, is_dir in items:
            if not is_dir:
                continue  # skip stray files (e.g. *-info.json)
            n = norm(name)
            full = f"{parent}/{name}".strip("/")
            if n in folders and folders[n][0] != parent:
                collisions.setdefault(n, []).append(folders[n][0])
                collisions.setdefault(n, []).append(parent)
            folders[n] = (parent, full)

    print(f"Total unique client folders: {len(folders)}")
    if collisions:
        print(f"  name collisions across parents: {len(collisions)} (will resolve by account_type)")

    # 2. read accounts from DB
    rows = mysql(cfg, "SELECT id, name, account_type FROM account WHERE deleted=0")
    print(f"Accounts in CRM: {len(rows)}")

    matched = []
    unmatched_accounts = []
    for line in rows:
        parts = line.split("\t")
        aid = parts[0]
        aname = parts[1] if len(parts) > 1 else ""
        atype = parts[2] if len(parts) > 2 else ""
        if not norm(aname):
            continue
        matched_variant = None
        for n in name_variants(aname):
            if n in folders:
                matched_variant = n
                break
        if matched_variant:
            parent, full = folders[matched_variant]
            # resolve collisions: prefer the parent matching account_type
            if matched_variant in collisions and atype and atype in collisions[matched_variant]:
                # find the path under the matching parent
                # rebuild by searching again (kept simple: only 2 parents)
                for p in TOP_CLIENT_FOLDERS:
                    if p == atype:
                        full = f"{p}/{aname}".strip("/")
                        parent = p
                        break
            matched.append((aid, aname, atype, full))
        else:
            unmatched_accounts.append((aid, aname, atype))

    print(f"\nMatched: {len(matched)}")
    print(f"Unmatched accounts: {len(unmatched_accounts)}")

    # 3. write (or report)
    if not apply:
        print("\n[DRY RUN] sample matches (first 15):")
        for aid, aname, atype, full in matched[:15]:
            print(f"  {aname[:40]:40}  ->  {full}")
        if unmatched_accounts:
            print("\n[DRY RUN] unmatched accounts (first 20):")
            for aid, aname, atype in unmatched_accounts[:20]:
                print(f"  - {aname[:50]:50}  [{atype or '?'}]")
        print("\nRe-run with --apply to write nextcloud_folder_url for the matched accounts.")
        return

    # apply: build one batched UPDATE
    stmts = []
    for aid, aname, atype, full in matched:
        url = folder_url(base, full)
        stmts.append(
            f"UPDATE account SET nextcloud_folder_url='{esc_sql(url)}' WHERE id='{esc_sql(aid)}';"
        )
    if not stmts:
        print("Nothing to write.")
        return
    sql = "\n".join(stmts)
    # write via a piped mysql to avoid huge -e arg
    proc = subprocess.run(
        "docker exec -i app-mysql-1 mysql -u%s -p%s %s" % (cfg["dbUser"], cfg["dbPass"], cfg["dbName"]),
        shell=True, input=sql, capture_output=True, text=True,
    )
    if proc.returncode != 0:
        print("DB write FAILED:")
        print(proc.stderr[:1000])
        sys.exit(1)
    print(f"\nWROTE nextcloud_folder_url for {len(stmts)} accounts.")


if __name__ == "__main__":
    main()
