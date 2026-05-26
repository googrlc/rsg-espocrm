---
name: espocrm-relationships
scope: Load when you need to understand how CRM entities link together
priority: medium
token_cost: ~150
---

# RSG EspoCRM — Entity Relationships

```
Lead ──(convert)──► Account ◄─── Contact
                       │
            ┌──────────┼──────────┐
            ▼          ▼          ▼
       Opportunity   Policy     Task
            │          │          │
            ▼          ▼          ▼
       Commission   Renewal   ActivityLog
            │          │
            └──────────┘
               Commission
```

- An **Account** is the hub. Most entities link back to it.
- A **Lead** converts into Account + Contact + Opportunity.
- An **Opportunity** at stage `Won - Bound` generates a stub **Policy**.
- A **Policy** approaching expiration generates a **Renewal** record.
- A **Renewal** at stage `Renewed - Won` links to a new **Policy**.
- **Commission** records link to Opportunity, Policy, and/or Renewal.
- **Task** records link polymorphically to any parent entity.
- **ActivityLog** records are written automatically by hooks and sync — never create them manually.
