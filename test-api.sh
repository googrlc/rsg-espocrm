#!/bin/bash
# test-api.sh — Test EspoCRM API connection

CRM_URL="https://rrespocrm-rsg-u69864.vm.elestio.app"
API_KEY="YOUR_API_KEY_HERE"

# Test with API key
curl -s -X GET "$CRM_URL/api/v1/App/user" \
    -H "X-Api-Key: $API_KEY" \
    -H "Accept: application/json" | jq .

# OR test with username/password (uncomment and fill in):
# curl -s -X POST "$CRM_URL/api/v1/App/user" \
#     -u "USERNAME:PASSWORD" \
#     -H "Accept: application/json" | jq .
