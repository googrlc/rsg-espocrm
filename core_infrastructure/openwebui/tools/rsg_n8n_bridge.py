"""
title: RSG n8n Bridge
author: RSG
version: 0.1.0
description: Trigger RSG n8n workflows from chat via webhooks.
"""
import json
import urllib.request
import urllib.error
from pydantic import BaseModel, Field


class Tools:
    class Valves(BaseModel):
        N8N_WEBHOOK_BASE: str = Field(
            default="https://n8n-9uiaa-u69864.vm.elestio.app:443/webhook",
            description="n8n production webhook base URL (no trailing slash).",
        )
        N8N_TOKEN: str = Field(default="", description="Optional bearer token if webhooks require auth.")

    def __init__(self):
        self.valves = self.Valves()

    def trigger_workflow(self, workflow: str, payload: str = "{}") -> str:
        """
        Trigger an RSG n8n workflow via its webhook path.
        :param workflow: Webhook path/slug, e.g. 'client-lookup' or 'create-renewal-task'.
        :param payload: JSON string with the workflow input (default "{}").
        :return: JSON response from n8n.
        """
        url = f"{self.valves.N8N_WEBHOOK_BASE}/{workflow.lstrip('/')}"
        headers = {"Content-Type": "application/json"}
        if self.valves.N8N_TOKEN:
            headers["Authorization"] = f"Bearer {self.valves.N8N_TOKEN}"
        try:
            data = json.loads(payload) if isinstance(payload, str) else payload
        except Exception:
            return json.dumps({"error": "payload must be valid JSON"})
        req = urllib.request.Request(
            url, data=json.dumps(data).encode("utf-8"), headers=headers, method="POST"
        )
        try:
            with urllib.request.urlopen(req, timeout=60) as r:
                body = r.read().decode("utf-8", "replace")
                try:
                    return json.dumps(json.loads(body))
                except Exception:
                    return body[:1000]
        except urllib.error.HTTPError as e:
            return json.dumps({"error": f"HTTP {e.code}", "detail": e.read().decode("utf-8", "replace")[:300]})
        except Exception as e:  # noqa
            return json.dumps({"error": str(e)})
