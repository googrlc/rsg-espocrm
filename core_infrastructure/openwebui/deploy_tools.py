#!/usr/bin/env python3
"""
Create OpenWebUI Tools (Workspace > Tools) from the .py files in tools/.

  OPENWEBUI_API_KEY='sk-...' python3 core_infrastructure/openwebui/deploy_tools.py

Re-running with the same tool id updates (re-creates) the tool content.
"""
import os, sys, json, re, urllib.request, urllib.error

BASE = os.environ.get("OPENWEBUI_URL", "https://openwebui-l8ola-u69864.vm.elestio.app").rstrip("/")
KEY  = os.environ.get("OPENWEBUI_API_KEY")
if not KEY:
    sys.exit("ERROR: set OPENWEBUI_API_KEY env var.")
TOOLS_DIR = os.path.join(os.path.dirname(os.path.abspath(__file__)), "tools")
AUTH = {"Authorization": f"Bearer {KEY}"}

def http(method, path, headers=None, body=None, ctype=None):
    url = f"{BASE}{path}"
    h = dict(AUTH); h.update(headers or {})
    if body is not None and ctype: h["Content-Type"] = ctype
    if isinstance(body, str): body = body.encode("utf-8")
    req = urllib.request.Request(url, data=body, headers=h, method=method)
    try:
        with urllib.request.urlopen(req, timeout=60) as r:
            return r.status, r.read().decode("utf-8", "replace")
    except urllib.error.HTTPError as e:
        return e.code, e.read().decode("utf-8", "replace")
    except Exception as e:  # noqa
        return 0, str(e)

def parse_header(code):
    m = re.search(r'^"""\s*\n(.*?)\n"""', code, re.S)
    block = m.group(1) if m else ""
    title = ""; desc = ""
    for line in block.splitlines():
        if line.lower().startswith("title:"): title = line.split(":", 1)[1].strip()
        if line.lower().startswith("description:"): desc = line.split(":", 1)[1].strip()
    return title, desc

def main():
    files = sorted(f for f in os.listdir(TOOLS_DIR) if f.endswith(".py"))
    if not files:
        sys.exit(f"No tool files in {TOOLS_DIR}")
    # list existing tools (idempotency check)
    st, body = http("GET", "/api/v1/tools/")
    existing = set()
    if st == 200:
        try:
            d = json.loads(body)
            items = d.get("items") or d.get("data") or (d if isinstance(d, list) else [])
            existing = {t.get("id") for t in items if isinstance(t, dict)}
        except Exception:
            pass
    for fn in files:
        path = os.path.join(TOOLS_DIR, fn)
        code = open(path, "r", encoding="utf-8").read()
        title, desc = parse_header(code)
        tool_id = os.path.splitext(fn)[0]
        name = title or tool_id
        payload = {"id": tool_id, "name": name, "content": code, "meta": {"description": desc}}
        if tool_id in existing:
            st, body = http("POST", f"/api/v1/tools/id/{tool_id}/update",
                            body=json.dumps(payload), ctype="application/json")
            action = "UPDATE"
        else:
            st, body = http("POST", "/api/v1/tools/create",
                            body=json.dumps(payload), ctype="application/json")
            action = "CREATE"
        if st in (200, 201):
            print(f"  OK   {tool_id}  ({name}) [{action}]")
        else:
            print(f"  FAIL {tool_id} ({st}) [{action}]: {body[:400]}")
    print("\nNext: in WebUI > Workspace > Tools, open each tool > Valves/Settings,")
    print("paste the Supabase service key / n8n token, save, then enable it.")

if __name__ == "__main__":
    main()
