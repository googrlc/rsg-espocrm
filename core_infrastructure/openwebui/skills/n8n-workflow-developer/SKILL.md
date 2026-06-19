---
name: n8n-workflow-developer
description: RSG skill for building and debugging n8n workflows on the Elestio-hosted n8n instance. Use when authoring workflows, wiring webhooks (including the OpenWebUI bridge), calling EspoCRM/Supabase/Slack/OpenWebUI from n8n, or debugging executions.
---

# n8n Workflow Developer (RSG)

For building and debugging workflows on RSG's n8n instance.

## Service context

- Host: `https://n8n-9uiaa-u69864.vm.elestio.app` (Elestio-hosted, not the old local Hermes docker setup).
- Production webhook base: `https://n8n-9uiaa-u69864.vm.elestio.app/webhook/<path>`
- Test webhook base: `https://n8n-9uiaa-u69864.vm.elestio.app/webhook-test/<path>` (only fires while editing the workflow)
- A workflow must be **ACTIVE** for its production `/webhook/<path>` to fire. Test URLs never fire in production.
- Timezone: `America/New_York`.

## RSG integration endpoints (call from n8n HTTP Request nodes)

**EspoCRM** — `https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/{Entity}` (or `/{Entity}/{id}`)
- Header: `X-Api-Key: <key>`. The API key is frequently stale (returns 401) — verify/rotate before relying on it.
- List/filter uses bracket params: `where[0][type]=equals&where[0][attribute]=<field>&where[0][value]=<val>&maxSize=<n>`.

**Supabase** — `https://wibscqhkvpijzqbhjphg.supabase.co/rest/v1/{table}`
- Headers: `apikey: <service-role-key>` and `Authorization: Bearer <service-role-key>`. Service key bypasses RLS.
- PostgREST query params: `select=*`, `<col>=eq.<val>` (filters), `order=<col>.desc`, `limit=<n>`.
- Writes: add `Prefer: return=representation` to get the created/updated row back.

**Slack** — use the Slack node (preferred) or an incoming webhook.
- Channels: `#the-boss` (C0ANQUENX4P, Lamar alerts), `#rsg-gretchen` (C0AMWAZBBJP), `#systems-check` (C0ANSEP6SSD), `#rsg-wins` (C0ANFKMDRUH).

**OpenWebUI** — `https://openwebui-l8ola-u69864.vm.elestio.app/api/v1/...`
- Header: `Authorization: Bearer <openwebui-api-key>`.
- Knowledge base: `GET /api/v1/knowledge/`, files `GET /api/v1/knowledge/{id}/files`, upload `POST /api/v1/files/`, attach `POST /api/v1/knowledge/{id}/file/add` body `{"file_id": ...}`. WebUI blocks `.md` uploads — name files `.txt`.
- Tools: `GET /api/v1/tools/`, create `POST /api/v1/tools/create`, update `POST /api/v1/tools/id/{id}/update`.

## OpenWebUI bridge (chat triggers n8n)

The **RSG n8n Bridge** tool in OpenWebUI calls `POST {n8n_webhook_base}/<workflow>` with a JSON body. To expose an agency action to chat:

1. Create a workflow with a **Webhook** node, path = the slug chat will call (e.g. `client-lookup`), HTTP method POST, Response mode "Using Respond to Webhook Node".
2. Process the input, call EspoCRM/Supabase as needed, then end with a **Respond to Webhook** node returning JSON (`application/json`).
3. Activate the workflow. Test via the production URL.
4. In chat: the RSG n8n Bridge tool's `trigger_workflow("client-lookup", "<json>")` hits it.

## Building workflows (conventions)

1. **Credentials, not secrets in nodes.** Store EspoCRM API key, Supabase service key, and Slack token as n8n Credentials; reference them in nodes. Never paste secrets into node parameters or commit them.
2. **Webhook node** — set path, method, response mode. For chat-triggered actions the path is the slug the WebUI tool calls.
3. **HTTP Request nodes** — use the credential for auth; use expressions (`={{ $json.field }}`) for dynamic params.
4. **Name workflows clearly** (e.g. `CRM Service Lifecycle -> Notifications`) and tag them.
5. **Export for version control** — n8n UI → workflow → ⋯ → Download. Commit the JSON under the repo `n8n/` directory. Strip credential IDs before committing; creds live in n8n, not in the JSON.
6. **Return JSON** from webhooks chat calls (Respond to Webhook, content-type application/json).

## Debugging

1. **Executions tab** — filter by workflow and status (error/success). Open a run to see item data at each node and the exact failing node + error message.
2. **Webhook not firing?** Workflow must be ACTIVE; the production URL is `/webhook/<path>`. The `/webhook-test/...` URL only works while editing. If you changed the path, every external caller (and the WebUI tool slug) must be updated.
3. **Step-through** — use Execute Workflow / execute node on the canvas with sample/pinned input data to isolate logic.
4. **Error workflow** — set a dedicated error workflow (Workflow Settings → Error Workflow) that posts the failure to `#systems-check` so you see node errors without watching the UI.
5. **Data shape** — n8n items are arrays; inspect with the table/JSON view. Use Set / Item Lists / Loop nodes to shape data. Watch binary vs JSON data.
6. **Retry** — set node "Retry On Fail" + "Continue On Fail" for resilient runs over flaky external APIs.
7. **Supabase 400** — check PostgREST filter syntax (`<col>=eq.<val>`), that the `select` columns exist, and that you're using the service key if RLS blocks reads.
8. **EspoCRM 401** — API key is stale/rotated; re-issue from EspoCRM and update the n8n credential.
9. **WebUI upload 400 "file type not allowed"** — rename the file to `.txt` (WebUI blocks `.md`/`.skill`).

## Pitfalls

- Using `localhost` in HTTP nodes — n8n is Elestio-hosted and not on the same network as EspoCRM; use the full public URL.
- Committing secrets in exported workflow JSON.
- Changing a webhook path without updating the WebUI n8n Bridge slug / external callers.
- Confusing test (`/webhook-test/`) vs production (`/webhook/`) URLs.
- Editing a production workflow without exporting first — the only durable backup is the n8n storage; export to the repo `n8n/` dir before big changes.

## References

- n8n docs: https://docs.n8n.io/
- RSG n8n exports: `n8n/` (this repo)
- RSG stack/credentials: AGENTS.md (hosts, 1Password items, Slack channel IDs)
- OpenWebUI API: see `core_infrastructure/openwebui/` scripts in this repo
