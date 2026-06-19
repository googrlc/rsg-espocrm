"""
title: RSG Supabase
author: RSG
version: 0.1.0
description: Query the RSG Supabase data warehouse (rsg-infrastructure) — CRM change proposals, commission parity, sync control.
"""
import json
import urllib.parse
import urllib.request
import urllib.error
from pydantic import BaseModel, Field


class Tools:
    class Valves(BaseModel):
        SUPABASE_URL: str = Field(
            default="https://wibscqhkvpijzqbhjphg.supabase.co",
            description="Supabase project REST base (no trailing slash).",
        )
        SUPABASE_SERVICE_KEY: str = Field(
            default="",
            description="Supabase service-role key (bypasses RLS). Keep private.",
        )
        ROW_LIMIT: int = Field(default=25, description="Max rows returned per query.")

    def __init__(self):
        self.valves = self.Valves()

    def _rest(self, table: str, params: dict | None = None) -> list:
        if not self.valves.SUPABASE_SERVICE_KEY:
            return [{"error": "SUPABASE_SERVICE_KEY not set in this tool's Valves."}]
        url = f"{self.valves.SUPABASE_URL}/rest/v1/{table}"
        if params:
            url += "?" + urllib.parse.urlencode(params)
        headers = {
            "apikey": self.valves.SUPABASE_SERVICE_KEY,
            "Authorization": f"Bearer {self.valves.SUPABASE_SERVICE_KEY}",
            "Accept": "application/json",
        }
        req = urllib.request.Request(url, headers=headers, method="GET")
        try:
            with urllib.request.urlopen(req, timeout=30) as r:
                return json.loads(r.read().decode("utf-8", "replace"))
        except urllib.error.HTTPError as e:
            return [{"error": f"HTTP {e.code}", "detail": e.read().decode("utf-8", "replace")[:300]}]
        except Exception as e:  # noqa
            return [{"error": str(e)}]

    def pending_crm_change_proposals(self) -> str:
        """
        List CRM change proposals not yet completed (queued data changes for the CRM).
        Use this to see what is pending review/approval.
        :return: JSON array of proposals (id, entity, status, created_at).
        """
        rows = self._rest(
            "crm_change_proposals",
            {"select": "*", "status": "neq.completed",
             "order": "created_at.desc", "limit": str(self.valves.ROW_LIMIT)},
        )
        return json.dumps(rows, default=str)

    def commission_parity_discrepancies(self) -> str:
        """
        Return recent commission parity report rows (commission reconciliation discrepancies).
        :return: JSON array of parity report rows.
        """
        rows = self._rest(
            "commission_parity_report",
            {"select": "*", "limit": str(self.valves.ROW_LIMIT)},
        )
        return json.dumps(rows, default=str)

    def sync_control_status(self) -> str:
        """
        Return the sync_control table (last sync times / health for CRM/AMS integrations).
        :return: JSON array of sync control rows.
        """
        rows = self._rest("sync_control", {"select": "*", "limit": str(self.valves.ROW_LIMIT)})
        return json.dumps(rows, default=str)

    def query_table(self, table: str, filter_column: str = "", filter_value: str = "", limit: int = 25) -> str:
        """
        Generic read-only query of a whitelisted RSG Supabase table.
        :param table: One of: crm_change_proposals, commission_ledger, commission_rules, commission_parity_report, sync_control.
        :param filter_column: Optional column to filter on (e.g. "status").
        :param filter_value: Optional value the column must equal (e.g. "approved").
        :param limit: Max rows (default 25).
        :return: JSON array of rows.
        """
        allowed = {"crm_change_proposals", "commission_ledger", "commission_rules",
                   "commission_parity_report", "sync_control"}
        if table not in allowed:
            return json.dumps({"error": f"table '{table}' not allowed", "allowed": sorted(allowed)})
        params = {"select": "*", "limit": str(limit)}
        if filter_column and filter_value:
            params[filter_column] = f"eq.{filter_value}"
        rows = self._rest(table, params)
        return json.dumps(rows, default=str)
