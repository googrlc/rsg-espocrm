#!/usr/bin/env python3
"""
Seed RSG agency launchpad links from a 1Password-style CSV (Title, Url, Archived)
into Supabase agency_links. URLs are reduced to origin (strips SAML/OAuth/reset tokens).
Idempotent: updates existing (name,url) rows in place (preserves favorites), inserts new.

  SUPABASE_SERVICE_KEY='eyJ...' python3 seed.py ["path/to/agency links.csv"]
"""
import csv, os, sys, json, urllib.parse, urllib.request, urllib.error

SUPA = os.environ.get("SUPABASE_URL", "https://wibscqhkvpijzqbhjphg.supabase.co").rstrip("/")
KEY  = os.environ.get("SUPABASE_SERVICE_KEY")
if not KEY:
    sys.exit("ERROR: set SUPABASE_SERVICE_KEY env var (service-role key).")
CSV = sys.argv[1] if len(sys.argv) > 1 else os.path.expanduser("~/Desktop/agency links.csv")

# best-effort categorization (agents can recategorize in-app)
CATS = {
  "Carrier": ["aetna","aflac","chubb","cna","clearcover","geico","libertymutual","liberty mutual",
              "nationwide","stateauto","state auto","towerhill","thig","usassure","trexis","natgen",
              "gainsco","amig","orchidinsurance","ncci","amwins","safeco","allstate","travelers",
              "progressive","hartford","metlife","mutual of omaha","mutualofomaha","erie ","erieinsurance",
              "kemper","mapfre","mercury","safeco","tokiomarine","wrangler","citizens","foremost",
              "national general","natgen","grange","leavitt","bristol west","olympic","sequoia"],
  "MGA": ["amwins","amwinsconnect","afglink","btisinc","btis","bondramp","coverforce","semsee",
          "coverbadger","attuneinsurance","auiagents","amgida","usli","usli.com","auiagents.com",
          "amwinsconnect","cover badger","bigi","bold","jencap","apriso","sageSure","sagesure",
          "k2 insurance","k2","burns & wilcox","burns","whins","rsui","rsg","ascot","amrisc"],
  "GA": ["smartchoiceagents","smart choice","chenango","simplybusiness","simply business","auiagents",
         "usli","siaa","big "],
  "AMS/Agency Mgmt": ["ascend","brokersnapshot","broker snapshot","m-files","mfiles","suitedash",
                     "nowcerts","momentum","ams","apex","qqsolutions","qq solutions","powerbroker",
                     "agencyzoom","eclipse","xdate","insurancexdate","insurance x date","betterment of agency"],
  "Utilities": ["xfinity","comcast","georgia power","southern company","southernco","cobb emc","cobbemc",
                "spectrum","att ","at&t","verizon","tmobile","t-mobile","google fiber","googlefiber",
                "clayton water","city water","atlanta watershed","waste management","wastemanagement"],
  "Banking/Finance": ["bluevine","bill.com","bill","fidelity","vanguard","chase","bank of america",
                      "glass city fcu","glasscityfcu","wells fargo","wellsfargo","xero","quickbooks",
                      "paypal","stripe"," Mercury ","capital one","capitalone","brex"],
  "Tools": ["airtable","aws","azure","canva","codecademy","github","slack","openai","otter","docusign",
            "zoom","notion","google","1password","lastpass","tailwind","vercel","netlify","cloudflare",
            "datadog","postman","getpostman","supabase","linear","clickup","todoist","trello","asana",
            "monday.com","hubspot","salesforce","calendly","loom","dropbox","box ","onedrive","google drive",
            "whatsapp","telegram","signal","discord","giphy","heygen","lindy","chatgpt","chat gpt","claude",
            "anthropic","limit ","rocket lawyer","rocketlawyer","docusign","ipostal","ipostal1","usps",
            "enterprise","hertz","costco","amazon","apple","appleid","facebook","instagram","linkedin",
            "twitter","x.com","meetleo","zoominfo"],
}

def categorize(name, host):
    s = (name + " " + host).lower()
    for cat, kws in CATS.items():
        for kw in kws:
            if kw in s:
                return cat
    return "Other"

def origin(url):
    u = (url or "").strip()
    if not u or u.lower() in ("none","(none)"):
        return None
    if "://" not in u:
        u = "https://" + u
    try:
        from urllib.parse import urlsplit, urlunsplit
        sp = urlsplit(u)
        if not sp.netloc:
            return None
        return urlunsplit((sp.scheme or "https", sp.netloc, "", "", ""))
    except Exception:
        return None

def rest(method, path, params=None, body=None):
    url = f"{SUPA}/rest/v1/{path}"
    if params:
        url += "?" + urllib.parse.urlencode(params)
    headers = {"apikey": KEY, "Authorization": f"Bearer {KEY}",
               "Content-Type": "application/json", "Accept": "application/json",
               "Prefer": "return=representation"}
    data = json.dumps(body).encode("utf-8") if body is not None else None
    req = urllib.request.Request(url, data=data, headers=headers, method=method)
    try:
        with urllib.request.urlopen(req, timeout=60) as r:
            return json.loads(r.read().decode("utf-8","replace") or "[]")
    except urllib.error.HTTPError as e:
        sys.exit(f"{method} {path} -> HTTP {e.code}: {e.read().decode('utf-8','replace')[:300]}")
    except Exception as e:
        sys.exit(f"{method} {path} -> {e}")

if not os.path.isfile(CSV):
    sys.exit(f"CSV not found: {CSV}")

# existing links keyed by (name,url)
existing = rest("GET", "agency_links", {"select": "id,name,url,category"})
emap = {(r["name"], r["url"]): r for r in existing}

rows = list(csv.DictReader(open(CSV, encoding="utf-8-sig")))
added = updated = skipped = 0
seen = set()
for r in rows:
    if str(r.get("Archived","")).strip().lower() in ("true","yes","1"):
        skipped += 1; continue
    name = (r.get("Title","") or "").strip()
    url = origin(r.get("Url",""))
    if not name or not url:
        skipped += 1; continue
    key = (name, url)
    if key in seen:
        skipped += 1; continue
    seen.add(key)
    cat = categorize(name, url.split("//",1)[-1].split("/")[0])
    if key in emap:
        rid = emap[key]["id"]
        if emap[key].get("category") != cat:
            rest("PATCH", "agency_links", params={"id": f"eq.{rid}"}, body={"category": cat})
            updated += 1
        else:
            skipped += 1
    else:
        rest("POST", "agency_links", body={"name": name, "url": url, "category": cat, "created_by": "seed"})
        added += 1

print(f"Seed done. added={added} updated={updated} skipped={skipped} (total in table now: {len(existing)+added})")
