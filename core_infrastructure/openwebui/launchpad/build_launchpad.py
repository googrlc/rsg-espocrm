#!/usr/bin/env python3
"""
Generate a searchable launchpad (login links) from a 1Password URL export.

Input CSV (default ~/Desktop/1PasswordExport.csv): columns Title, Url, Archived.
URLs are reduced to their ORIGIN (path/query dropped) to strip one-time SAML/OAuth
state, password-recovery, and invite tokens. Output is PERSONAL data - keep it local,
do NOT commit or host it publicly.

  python3 core_infrastructure/openwebui/launchpad/build_launchpad.py [path/to.csv]
"""
import csv, os, sys, html, json
from urllib.parse import urlsplit, urlunsplit

CSV = sys.argv[1] if len(sys.argv) > 1 else os.path.expanduser("~/Desktop/1PasswordExport.csv")
OUT = os.path.join(os.path.dirname(os.path.abspath(__file__)), "index.html")

# Agency-critical pinned links (canonical Elestio hosts we configured)
PINNED = [
    ("EspoCRM", "https://rrespocrm-rsg-u69864.vm.elestio.app", "Sales pipeline & renewals"),
    ("OpenWebUI", "https://openwebui-l8ola-u69864.vm.elestio.app", "RSG Assistant + knowledge base"),
    ("n8n", "https://n8n-9uiaa-u69864.vm.elestio.app", "Workflows & webhooks"),
    ("Momentum AMS", "https://portal.momentumamp.com", "AMS - system of record"),
    ("Supabase", "https://supabase.com/dashboard/project/wibscqhkvpijzqbhjphg", "rsg-infrastructure warehouse"),
    ("Slack", "https://app.slack.com", "RSG workspace"),
    ("Obsidian Vault", "https://github.com/googrlc/rsg-obsidian-vault", "Skills source (GitHub)"),
    ("OpenClaw", "https://openclaw-larau-u69864.vm.elestio.app", "Agent runtime"),
    ("1Password", "https://my.1password.com", "Credentials vault"),
]


def origin(url):
    u = (url or "").strip()
    if not u or u.lower() in ("none", "(none)"):
        return None
    if "://" not in u:
        u = "https://" + u
    try:
        s = urlsplit(u)
        if not s.netloc:
            return None
        return urlunsplit((s.scheme or "https", s.netloc, "", "", ""))
    except Exception:
        return None


pinned_hosts = {urlsplit(u).netloc for _, u, _ in PINNED}
merged = {}
with open(CSV, encoding="utf-8-sig") as f:
    for r in csv.DictReader(f):
        if str(r.get("Archived", "")).strip().lower() in ("true", "yes", "1"):
            continue
        o = origin(r.get("Url", ""))
        if not o or urlsplit(o).netloc in pinned_hosts:
            continue
        title = (r.get("Title", "") or "").strip() or urlsplit(o).netloc
        merged.setdefault(o, []).append(title)

items = sorted(((o, " / ".join(ts)) for o, ts in merged.items()), key=lambda x: x[1].lower())
data = [{"t": t, "u": o} for o, t in items]


def esc(s):
    return html.escape(s, quote=True)


pin_html = "\n".join(
    f'      <a class="tile pin" href="{esc(u)}" target="_blank" rel="noopener"><span class="t"><b>{esc(n)}</b><span>{esc(s)}</span></span></a>'
    for n, u, s in PINNED
)

page = f"""<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
<meta name="theme-color" content="#0f1419" />
<title>RSG Launchpad</title>
<style>
  :root{{--bg:#0f1419;--card:#161c24;--card2:#1b232d;--line:#26313d;--text:#e6edf3;--muted:#8b97a3;--accent:#3ba776}}
  *{{box-sizing:border-box}}
  html,body{{margin:0;background:var(--bg);color:var(--text);font:16px/1.4 -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif;-webkit-text-size-adjust:100%}}
  body{{padding:env(safe-area-inset-top) 14px calc(env(safe-area-inset-bottom) + 24px);max-width:680px;margin:0 auto}}
  header{{padding:16px 2px 10px;position:sticky;top:0;background:linear-gradient(var(--bg) 78%,transparent);z-index:5}}
  h1{{font-size:21px;margin:0}}
  header p{{margin:5px 0 10px;color:var(--muted);font-size:13px}}
  .search{{width:100%;padding:11px 13px;background:var(--card);border:1px solid var(--line);border-radius:10px;color:var(--text);font-size:15px;outline:none}}
  .search:focus{{border-color:#3a4756}}
  h2{{font-size:11px;text-transform:uppercase;letter-spacing:1.2px;color:var(--muted);margin:14px 4px 8px;font-weight:600}}
  .grid{{display:grid;grid-template-columns:1fr;gap:9px}}
  @media(min-width:520px){{.grid{{grid-template-columns:1fr 1fr}}}}
  a.tile{{display:flex;align-items:center;background:var(--card);border:1px solid var(--line);border-radius:10px;padding:12px 13px;text-decoration:none;color:var(--text);min-height:54px}}
  a.tile:active{{transform:scale(.99)}}
  a.tile:hover{{border-color:#3a4756;background:var(--card2)}}
  a.tile.pin{{border-left:3px solid var(--accent)}}
  .t{{display:flex;flex-direction:column;line-height:1.25}}
  .t b{{font-size:14.5px;font-weight:600}}
  .t span{{font-size:11.5px;color:var(--muted);margin-top:2px;word-break:break-all}}
  #count{{color:var(--muted);font-size:11.5px;margin:6px 4px 0}}
  .hide{{display:none}}
  footer{{margin-top:22px;color:var(--muted);font-size:11px;text-align:center}}
</style>
</head>
<body>
<header>
  <h1>RSG Launchpad</h1>
  <p>Search your logins — agency systems pinned up top</p>
  <input id="q" class="search" placeholder="Search {len(items)} logins…" autocomplete="off" />
</header>
<section>
  <h2>Pinned</h2>
  <div class="grid" id="pinned">
{pin_html}
  </div>
</section>
<section>
  <h2>All logins</h2>
  <div id="count"></div>
  <div class="grid" id="all"></div>
</section>
<footer>RSG · personal launchpad · keep local, do not host publicly</footer>
<script>
const DATA = {json.dumps(data)};
const all = document.getElementById('all');
const count = document.getElementById('count');
const host = u => {{ try {{ return new URL(u).host }} catch(e) {{ return u }} }};
function render(q) {{
  q = q.trim().toLowerCase();
  let n = 0;
  all.innerHTML = '';
  for (const it of DATA) {{
    const hay = (it.t + ' ' + host(it.u)).toLowerCase();
    if (q && !hay.includes(q)) continue;
    n++;
    const a = document.createElement('a');
    a.className = 'tile';
    a.href = it.u; a.target = '_blank'; a.rel = 'noopener';
    a.innerHTML = '<span class="t"><b>' + escape(it.t) + '</b><span>' + escape(host(it.u)) + '</span></span>';
    all.appendChild(a);
  }}
  count.textContent = q ? n + ' of ' + DATA.length + ' shown' : DATA.length + ' logins';
}}
const input = document.getElementById('q');
input.addEventListener('input', () => render(input.value));
render('');
</script>
</body>
</html>
"""
with open(OUT, "w", encoding="utf-8") as f:
    f.write(page)
print(f"wrote {OUT}")
print(f"pinned={len(PINNED)} all={len(items)} (csv rows read, archived/empty/schemeless skipped)")
