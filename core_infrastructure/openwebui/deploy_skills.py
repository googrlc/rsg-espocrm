#!/usr/bin/env python3
"""
One-shot deploy: ingest the RSG skills (from the Obsidian vault) into an
OpenWebUI Knowledge collection named "RSG Skills".

Runs on Lamar's Mac (has network). No pip deps - stdlib only.

  OPENWEBUI_API_KEY='sk-...' \
  python3 core_infrastructure/openwebui/deploy_skills.py

Optional env:
  OPENWEBUI_URL   default https://openwebui-l8ola-u69864.vm.elestio.app
  SKILLS_DIR      default <vault>/AI_Knowledge/Skills
  COLLECTION      default "RSG Skills"
  DRY_RUN=1       list files + verify auth, do not upload
"""
import os, sys, json, mimetypes, uuid, urllib.request, urllib.error

BASE = os.environ.get("OPENWEBUI_URL", "https://openwebui-l8ola-u69864.vm.elestio.app").rstrip("/")
KEY  = os.environ.get("OPENWEBUI_API_KEY")
if not KEY:
    sys.exit("ERROR: set OPENWEBUI_API_KEY env var (get it from WebUI > Settings > Account > API Keys).")
SKILLS_DIR = os.environ.get("SKILLS_DIR", os.path.expanduser(
    "~/Documents/GitHub/rsg-obsidian-vault/AI_Knowledge/Skills"))
COLLECTION = os.environ.get("COLLECTION", "RSG Skills")
DRY_RUN    = os.environ.get("DRY_RUN") == "1"
AUTH       = {"Authorization": f"Bearer {KEY}"}

def http(method, path, headers=None, body=None, ctype=None):
    url = f"{BASE}{path}" if path.startswith("/") else f"{BASE}/{path}"
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

def jget(s, default=None):
    try: return json.loads(s)
    except Exception: return default

def multipart(field, files):
    """files: list of (filename, bytes). Returns (body_bytes, content_type)."""
    boundary = "----rsgdeploy" + uuid.uuid4().hex
    out = b""
    for fname, data in files:
        out += f"--{boundary}\r\n".encode()
        ctype = mimetypes.guess_type(fname)[0] or "application/octet-stream"
        out += f'Content-Disposition: form-data; name="{field}"; filename="{fname}"\r\n'.encode()
        out += f"Content-Type: {ctype}\r\n\r\n".encode()
        out += data + b"\r\n"
    out += f"--{boundary}--\r\n".encode()
    return out, f"multipart/form-data; boundary={boundary}"

def gather_skills(root):
    exts = (".md", ".skill", ".json")
    found = []
    for dirpath, _, fnames in os.walk(root):
        for fn in sorted(fnames):
            if fn.lower().endswith(exts):
                fp = os.path.join(dirpath, fn)
                # flatten subdirs into the stored filename to keep names unique
                rel = os.path.relpath(fp, root)
                flat = rel.replace(os.sep, "_")
                found.append((flat, fp))
    return sorted(found)

print(f"WebUI: {BASE}")
print(f"Skills dir: {SKILLS_DIR}")
if not os.path.isdir(SKILLS_DIR):
    sys.exit(f"ERROR: skills dir not found: {SKILLS_DIR}")

# 1) verify auth
st, body = http("GET", "/api/v1/auths/")
if st != 200:
    sys.exit(f"AUTH FAILED ({st}): {body[:300]}\nLikely a stale/invalid API key.")
me = jget(body, {})
print(f"Auth OK -> user: {me.get('email') or me.get('name') or me.get('id') or '?'}")

# 2) find or create the knowledge collection
st, body = http("GET", "/api/v1/knowledge/")
items = jget(body, []) if st == 200 else []
# normalize: endpoints may return a bare list, {"data":[...]}, or a dict keyed by id
if isinstance(items, dict):
    items = items.get("data") or list(items.values())
kid = ""
for k in (items or []):
    if not isinstance(k, dict):
        continue
    if (k.get("name") or "").strip() == COLLECTION:
        kid = k.get("id"); break
if kid:
    print(f"Reusing existing collection '{COLLECTION}' id={kid}")
elif DRY_RUN:
    print(f"[dry-run] would create collection '{COLLECTION}'")
else:
    st, body = http("POST", "/api/v1/knowledge/create",
                    body=json.dumps({"name": COLLECTION,
                                     "description": "RSG agency skills synced from the Obsidian vault (AI_Knowledge/Skills)."}),
                    ctype="application/json")
    if st not in (200, 201):
        sys.exit(f"CREATE FAILED ({st}): {body[:500]}")
    kid = (jget(body) or {}).get("id")
    if not kid:
        sys.exit(f"CREATE OK but no id in response: {body[:300]}")
    print(f"Created collection '{COLLECTION}' id={kid}")

# 3) gather + upload skill files
skills = gather_skills(SKILLS_DIR)
print(f"Found {len(skills)} skill file(s).")
if DRY_RUN:
    for flat, fp in skills: print(f"  [dry-run] {flat}")
    sys.exit("dry-run complete, nothing uploaded.")

ok = bad = 0
for i, (flat, fp) in enumerate(skills, 1):
    with open(fp, "rb") as f:
        data = f.read()
    # WebUI blocks .md/.skill by default; ship as .txt (content stays markdown)
    upload_name = os.path.splitext(flat)[0] + ".txt"

    # 1) upload to /api/v1/files/ (field name "file")
    body, ct = multipart("file", [(upload_name, data)])
    st, resp = http("POST", "/api/v1/files/",
                    headers={"Content-Length": str(len(body))},
                    body=body, ctype=ct)
    if st not in (200, 201):
        bad += 1
        print(f"  [{i:>2}/{len(skills)}] {flat}  UPLOAD FAIL ({st}): {resp[:160]}")
        continue
    fid = (jget(resp) or {}).get("id")
    if not fid:
        bad += 1
        print(f"  [{i:>2}/{len(skills)}] {flat}  NO FILE ID: {resp[:160]}")
        continue

    # 2) attach to the knowledge collection (also triggers embedding/RAG)
    st2, resp2 = http("POST", f"/api/v1/knowledge/{kid}/file/add",
                      body=json.dumps({"file_id": fid}), ctype="application/json")
    if st2 in (200, 201):
        ok += 1
        print(f"  [{i:>2}/{len(skills)}] {flat}  OK")
    else:
        bad += 1
        print(f"  [{i:>2}/{len(skills)}] {flat}  ATTACH FAIL ({st2}): {resp2[:200]}")

print(f"\nDone. uploaded={ok} failed={bad} collection_id={kid}")
print("Verify in WebUI: Workspace > Knowledge > 'RSG Skills'.")
