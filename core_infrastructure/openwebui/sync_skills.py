#!/usr/bin/env python3
"""
Idempotent sync of RSG skills -> OpenWebUI 'RSG Skills' knowledge.

Sources (SKILLS_DIRS, colon-separated):
  - file skills: a .md/.skill/.json directly in a source root (skill name = file stem)
  - dir skills:  a subdir containing SKILL.md (skill name = subdir; only SKILL.md is ingested)
First source wins on name collisions (so the vault beats rsg-hermes for shared skills).
EXCLUDE (comma-separated skill names) skips matching skills.

- Adds NEW skill files (skips ones already in the collection by upload name).
- --update re-ingests files whose content hash changed (remove old + add new), if WebUI
  exposes a file hash on collection files.
- --prune removes collection files no longer present in any source.

Reads OPENWEBUI_API_KEY from env or ~/.config/rsg/openwebui.env (chmod 600).

  python3 core_infrastructure/openwebui/sync_skills.py [--update] [--prune]
"""
import os, sys, json, hashlib, mimetypes, uuid, urllib.request, urllib.error

BASE = os.environ.get("OPENWEBUI_URL", "https://openwebui-l8ola-u69864.vm.elestio.app").rstrip("/")
ENV_FILE = os.path.expanduser("~/.config/rsg/openwebui.env")
KEY = os.environ.get("OPENWEBUI_API_KEY")
if not KEY and os.path.isfile(ENV_FILE):
    for line in open(ENV_FILE):
        line = line.strip()
        if line.startswith("OPENWEBUI_API_KEY") and "=" in line:
            KEY = line.split("=", 1)[1].strip().strip('"').strip("'")
if not KEY:
    sys.exit(f"ERROR: OPENWEBUI_API_KEY not in env and not found in {ENV_FILE}")

_default_dir = os.path.expanduser("~/Documents/GitHub/rsg-obsidian-vault/AI_Knowledge/Skills")
SKILLS_DIRS = [os.path.expanduser(d) for d in os.environ.get("SKILLS_DIRS", _default_dir).split(":") if d]
EXCLUDE = {s.strip() for s in os.environ.get("EXCLUDE", "").split(",") if s.strip()}
COLLECTION = os.environ.get("COLLECTION", "RSG Skills")
DO_UPDATE = "--update" in sys.argv
DO_PRUNE = "--prune" in sys.argv
AUTH = {"Authorization": f"Bearer {KEY}"}


def http(method, path, headers=None, body=None, ctype=None):
    url = f"{BASE}{path}"
    h = dict(AUTH); h.update(headers or {})
    if body is not None and ctype:
        h["Content-Type"] = ctype
    if isinstance(body, str):
        body = body.encode("utf-8")
    req = urllib.request.Request(url, data=body, headers=h, method=method)
    try:
        with urllib.request.urlopen(req, timeout=120) as r:
            return r.status, r.read().decode("utf-8", "replace")
    except urllib.error.HTTPError as e:
        return e.code, e.read().decode("utf-8", "replace")
    except Exception as e:  # noqa
        return 0, str(e)


def jget(s, d=None):
    try:
        return json.loads(s)
    except Exception:
        return d


def multipart(field, files):
    boundary = "----rsgsync" + uuid.uuid4().hex
    out = b""
    for fname, data in files:
        out += f"--{boundary}\r\n".encode()
        ct = mimetypes.guess_type(fname)[0] or "application/octet-stream"
        out += f'Content-Disposition: form-data; name="{field}"; filename="{fname}"\r\n'.encode()
        out += f"Content-Type: {ct}\r\n\r\n".encode()
        out += data + b"\r\n"
    out += f"--{boundary}--\r\n".encode()
    return out, f"multipart/form-data; boundary={boundary}"


def gather(root):
    """Return list of (skill_name, flat_name, fp) for canonical skill files in root."""
    out = []
    if not os.path.isdir(root):
        return out
    for entry in sorted(os.listdir(root)):
        fp = os.path.join(root, entry)
        if os.path.isfile(fp) and entry.lower().endswith((".md", ".skill", ".json")):
            out.append((os.path.splitext(entry)[0], entry, fp))
        elif os.path.isdir(fp):
            sm = os.path.join(fp, "SKILL.md")
            if os.path.isfile(sm):
                out.append((entry, f"{entry}_SKILL.md", sm))
    return out


def find_collection():
    st, body = http("GET", "/api/v1/knowledge/")
    items = jget(body, []) if st == 200 else []
    if isinstance(items, dict):
        items = items.get("items") or items.get("data") or list(items.values())
    for k in (items or []):
        if isinstance(k, dict) and (k.get("name") or "").strip() == COLLECTION:
            return k.get("id")
    return None


def list_files(kid):
    st, body = http("GET", f"/api/v1/knowledge/{kid}/files?limit=1000")
    items = jget(body, []) if st == 200 else []
    if isinstance(items, dict):
        items = items.get("items") or items.get("data") or list(items.values())
    return items or []


def file_hash(path):
    h = hashlib.sha256()
    with open(path, "rb") as f:
        for chunk in iter(lambda: f.read(65536), b""):
            h.update(chunk)
    return h.hexdigest()


def remote_hash(item):
    m = item.get("meta") or {}
    for k in ("file_hash", "hash", "content_hash", "sha256"):
        if m.get(k):
            return m.get(k)
        if item.get(k):
            return item.get(k)
    return None


def upload_and_attach(kid, flat, fp):
    data = open(fp, "rb").read()
    name = os.path.splitext(flat)[0] + ".txt"
    body, ct = multipart("file", [(name, data)])
    st, resp = http("POST", "/api/v1/files/", headers={"Content-Length": str(len(body))},
                    body=body, ctype=ct)
    if st not in (200, 201):
        return False, f"upload {st}: {resp[:160]}"
    fid = (jget(resp) or {}).get("id")
    if not fid:
        return False, f"no file id: {resp[:160]}"
    st2, resp2 = http("POST", f"/api/v1/knowledge/{kid}/file/add",
                      body=json.dumps({"file_id": fid}), ctype="application/json")
    if st2 not in (200, 201):
        return False, f"attach {st2}: {resp2[:160]}"
    return True, fid


def remove(kid, fid):
    st, _ = http("POST", f"/api/v1/knowledge/{kid}/file/remove",
                 body=json.dumps({"file_id": fid}), ctype="application/json")
    return st in (200, 201)


def collect_sources():
    skills = {}  # name -> (flat, fp); first source wins
    for root in SKILLS_DIRS:
        for name, flat, fp in gather(root):
            if name in EXCLUDE:
                continue
            if name not in skills:
                skills[name] = (flat, fp)
    return skills


def main():
    for root in SKILLS_DIRS:
        if not os.path.isdir(root):
            print(f"  WARN: source dir not found: {root}")
    kid = find_collection()
    if not kid:
        sys.exit(f"ERROR: collection '{COLLECTION}' not found. Run deploy_skills.py first.")
    rfiles = list_files(kid)
    rmap = {}
    for it in rfiles:
        if not isinstance(it, dict):
            continue
        fn = (it.get("filename") or (it.get("meta") or {}).get("name") or "")
        rmap[fn] = (it.get("id"), remote_hash(it))
    skills = collect_sources()
    added = updated = skipped = 0
    for name, (flat, fp) in sorted(skills.items()):
        upload_name = os.path.splitext(flat)[0] + ".txt"
        h = file_hash(fp)
        if upload_name in rmap:
            rid, rh = rmap[upload_name]
            if DO_UPDATE and rh and rh.lower() != h.lower():
                ok, msg = upload_and_attach(kid, flat, fp)
                if ok and rid:
                    remove(kid, rid)
                if ok:
                    updated += 1; print(f"  UPDATED {name}")
                else:
                    print(f"  UPDATE FAIL {name}: {msg}")
            else:
                skipped += 1
        else:
            ok, msg = upload_and_attach(kid, flat, fp)
            if ok:
                added += 1; print(f"  ADDED {name}")
            else:
                print(f"  ADD FAIL {name}: {msg}")
    pruned = 0
    if DO_PRUNE:
        local_names = {os.path.splitext(f)[0] + ".txt" for f, _ in skills.values()}
        for fn, (rid, _rh) in rmap.items():
            if fn not in local_names and rid and remove(kid, rid):
                pruned += 1; print(f"  PRUNED {fn}")
    print(f"\nSync done. added={added} updated={updated} skipped={skipped} pruned={pruned} collection={kid}")


if __name__ == "__main__":
    main()
