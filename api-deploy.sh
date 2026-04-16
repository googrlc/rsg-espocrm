#!/bin/bash
# api-deploy.sh — Deploy EspoCRM metadata via REST API
# Deploys: kanban layouts, clientDefs
# Note: CSS/JS/Template files must be deployed via SSH

CRM_URL="https://rrespocrm-rsg-u69864.vm.elestio.app"
API_KEY="e5df7c321b47427d24046bab814dbb58"

set -e

echo "🚀 Deploying to EspoCRM via API..."

# Deploy Lead kanban layout
echo "📋 Updating Lead kanban layout..."
curl -s -X PUT "$CRM_URL/api/v1/Layout/Lead/kanban" \
    -H "X-Api-Key: $API_KEY" \
    -H "Content-Type: application/json" \
    -d @custom/Espo/Custom/Resources/layouts/Lead/kanban.json \
    | jq -r '.name // .id // "OK"'

# Deploy Opportunity kanban layout
echo "📋 Updating Opportunity kanban layout..."
curl -s -X PUT "$CRM_URL/api/v1/Layout/Opportunity/kanban" \
    -H "X-Api-Key: $API_KEY" \
    -H "Content-Type: application/json" \
    -d @custom/Espo/Custom/Resources/layouts/Opportunity/kanban.json \
    | jq -r '.name // .id // "OK"'

# Deploy Lead clientDefs (metadata)
echo "🔧 Updating Lead clientDefs..."
curl -s -X PUT "$CRM_URL/api/v1/Metadata/clientDefs/Lead" \
    -H "X-Api-Key: $API_KEY" \
    -H "Content-Type: application/json" \
    -d @custom/Espo/Custom/Resources/metadata/clientDefs/Lead.json \
    | jq -r '.name // .id // "OK"'

# Deploy Opportunity clientDefs (metadata)
echo "🔧 Updating Opportunity clientDefs..."
curl -s -X PUT "$CRM_URL/api/v1/Metadata/clientDefs/Opportunity" \
    -H "X-Api-Key: $API_KEY" \
    -H "Content-Type: application/json" \
    -d @custom/Espo/Custom/Resources/metadata/clientDefs/Opportunity.json \
    | jq -r '.name // .id // "OK"'

echo ""
echo "✅ Metadata deployed!"
echo "⚠️  CSS/JS/template files still need SSH deployment (see deploy-ssh.sh)"
