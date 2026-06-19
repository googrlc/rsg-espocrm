# RSG Agency Launchpad

Categorized agency login hub with per-agent favorites + add/remove/edit. Backed by
Supabase (`rsg-infrastructure`). Single static `index.html` (Supabase JS SDK via CDN).

## Setup

1. **Create tables** — Supabase dashboard → `rsg-infrastructure` → SQL Editor → paste
   `schema.sql` → Run. (Creates `agency_links` + `agency_link_favorites` with permissive
   RLS for the anon role — access is gated by private hosting + the in-app passphrase.)
2. **Seed links** from the agency CSV:
   ```
   SUPABASE_SERVICE_KEY='<service-role-key>' \
     python3 seed.py "/Users/lamarcoates/Desktop/agency links.csv"
   ```
   URLs are reduced to origin (strips SAML/OAuth/reset tokens). Re-runnable: updates
   existing rows in place (favorites preserved), inserts new ones.
3. **Set a passphrase** — in `index.html`, `CONFIG.PASSPHRASE` (default `rsg-launch`).
   Pick something from 1Password. This is a client-side gate only; real access control
   is private hosting.
4. **Test locally** — `open index.html` (it talks to Supabase directly; works from the
   file). Enter passphrase → pick Lamar/Gretchen → search, favorite, add/edit/remove.
5. **Host** — private static host (Elestio box or GitHub Pages). Then link it from
   OpenWebUI (add the URL to the RSG Assistant system prompt, or a Tool).

## Notes
- Anon key in `index.html` is public-safe (RLS protects data; the link list isn't
  secret). Service key is NEVER in the app — only `seed.py` uses it from env.
- Categories are best-effort seeded (keyword map); agents recategorize in-app via Edit.
- Favorites are per `agent_id` (Lamar / Gretchen), stored in `agency_link_favorites`.
