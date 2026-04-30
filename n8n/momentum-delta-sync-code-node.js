// n8n Code Node: Momentum-to-EspoCRM Delta Sync
// ------------------------------------------------
// Reads Momentum OData policy objects (PascalCase), maps them to the
// EspoCRM snake_case schema, computes a high-water mark for the next
// OData $filter, and tags every record with sync_status = "Synced".
//
// Inputs
//   $input.all()       – Array of Momentum OData items (PascalCase keys).
//   static_last_sync   – ISO 8601 timestamp of the previous successful run
//                         (passed as an n8n workflow variable).
//
// Output (single item)
//   { processed_records, new_high_water_mark }

const items = $input.all();
const staticLastSync = $('Schedule Trigger').first().json.static_last_sync
  ?? items[0]?.json?.static_last_sync
  ?? new Date(0).toISOString();

// ── helpers ──────────────────────────────────────────────────────────

function toDateOnly(raw) {
  if (!raw) return null;
  const d = new Date(raw);
  if (Number.isNaN(d.getTime())) return null;
  return d.toISOString().slice(0, 10); // YYYY-MM-DD
}

function toIso(raw) {
  if (!raw) return null;
  const d = new Date(raw);
  if (Number.isNaN(d.getTime())) return null;
  return d.toISOString();
}

function toFloat(raw) {
  if (raw === null || raw === undefined || raw === '') return null;
  const n = parseFloat(raw);
  return Number.isNaN(n) ? null : n;
}

// ── mapping ──────────────────────────────────────────────────────────

const processedRecords = [];
let maxLastModified = null;

for (const item of items) {
  const src = item.json;
  try {
    const mapped = {
      momentum_policy_id:   String(src.DatabaseId ?? ''),
      momentum_client_id:   String(src.InsuredId ?? ''),
      policy_number:        src.PolicyNumber ?? null,
      premium_amount:       toFloat(src.TotalPremium),
      effective_date:       toDateOnly(src.EffectiveDate),
      expiration_date:      toDateOnly(src.ExpirationDate),
      line_of_business:     src.LineOfBusiness ?? null,
      status:               src.Status ?? null,
      momentum_last_synced: toIso(src.LastModifiedDate),
      sync_status:          'Synced',
      // ams_lock_state is intentionally omitted — governed by CRM-side hooks
    };

    processedRecords.push(mapped);

    // track high-water mark
    const ts = toIso(src.LastModifiedDate);
    if (ts && (!maxLastModified || ts > maxLastModified)) {
      maxLastModified = ts;
    }
  } catch (err) {
    console.error(
      `[MomentumSync] Skipping malformed record `
      + `(DatabaseId=${src?.DatabaseId ?? 'unknown'}): ${err.message}`
    );
  }
}

// ── high-water mark ──────────────────────────────────────────────────

const newHighWaterMark = maxLastModified ?? staticLastSync;

// ── output ───────────────────────────────────────────────────────────

return [{
  json: {
    processed_records:   processedRecords,
    new_high_water_mark: newHighWaterMark,
  },
}];
