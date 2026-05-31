---
name: espocrm-schema
scope: Entity overview, stage/status enums, LOB values, key identifiers
priority: high
token_cost: ~600
---

# RSG EspoCRM — Entity Schema

Entity purposes, key enums, and immutable identifiers. For full field tables see `field_dictionary.md`.

---

## Account

Central hub record for a client or prospect household/business.

**accountType** (Enum): `Prospect` · `Commercial Lines` · `Personal Lines` · `Group Benefits` · `Medicare` · `Life Insurance` · `Carrier` · `MGA`

**accountStatus** (Enum): `Active` · `Urgent` · `Renewing` · `At Risk` · `Inactive`

**scoreTier** (Enum): `Strong` · `Good` · `At Risk` · `Critical`

**retentionRisk** (Enum): `Low` · `Medium` · `High`

**renewalOutreachStage** (Enum): `Not Started` · `Day 60 Sent` · `Day 30 Sent` · `Day 14 Sent` · `Confirmed` · `Shopped` · `Lost`

**renewalDecision** (Enum): `Renewing` · `Re-marketed` · `Lost — Price` · `Lost — Service` · `Lost — Carrier` · `Non-renewed by Carrier`

**lob** (Multi-Enum): `Commercial Auto` · `GL` · `Workers Comp` · `Cargo` · `Home` · `Auto` · `Life` · `Medicare` · `BOP` · `Umbrella` · `Professional Liability` · `Builders Risk` · `Transportation`

**Immutable external IDs:** `momentumClientId`, `momentumLastSynced`

---

## Contact

Individual person linked to one or more Accounts.

**householdRole** (Enum): `Primary` · `Spouse` · `Dependent` · `Co-insured`

**clientType** (Enum): `Personal` · `Commercial`

**medicarePlanType** (Enum): `Supplement` · `Advantage` · `Part D`

**lifeHealthClass** (Enum): `Preferred Plus` · `Preferred` · `Standard Plus` · `Standard` · `Substandard`

**opportunityRole** (Enum): `Decision Maker` · `Evaluator` · `Influencer`

**Immutable external IDs:** `momentumClientId`

---

## Lead

Unqualified prospect. Converts to Account + Contact + Opportunity.

**status** (Enum, pipeline order): `New / Uncontacted` → `Attempting Contact` → `Connected` → `Gathering Info` → `Qualified` · `Nurture` · `DNC` · `Converted`

**priority** (Enum): `Hot` · `Warm` · `Cold`

**source** (Enum): `Call` · `Email` · `Existing Customer` · `Client Referral` · `Partner Referral` · `Public Relations` · `Web Site` · `Campaign` · `Other`

> Once `status = Converted`, do not edit the Lead — edit the resulting Account, Contact, and Opportunity instead.

---

## Opportunity

Sales pipeline item for new business or renewal.

**stage** (Enum, pipeline order): `Discovery` → `Quoting` → `Markets Out / Shopping` → `Proposal Presented` → `Negotiation` → `Closed Won` · `Closed Lost`

**businessType** (Enum): `New Business` · `Renewal` · `Rewrite`

**priority** (Enum): `Hot` · `Warm` · `Cold`

**lineOfBusiness** (Enum): `Commercial Auto` · `Transportation / Trucking` · `General Liability` · `Workers Comp` · `Commercial Property` · `BOP` · `Professional Liability` · `Umbrella` · `Builders Risk` · `Inland Marine` · `Personal Auto` · `Homeowners` · `Renters` · `Condo` · `Dwelling Fire` · `Motorcycle` · `Boat` · `RV` · `Life` · `Health` · `Medicare` · `Group Benefits` · `Garagekeepers` · `Commercial Package` · `Other`

**lostReason** (Enum): `Price` · `Coverage` · `Service` · `Competitor Stole` · `Business Closed` · `Carrier Non-Renewed` · `Client Moved` · `Unknown` · `N/A`

---

## Policy

Bound insurance policy. AMS (Momentum) is source of truth — CRM is read-mostly.

**status** (Enum): `Active` · `Up for Renewal` · `Renewing` · `Renewed` · `Expired` · `Cancelled` · `Flat Cancel` · `Pending Cancel` · `Non-Renewed` · `Lapsed`

**businessType** (Enum): `New Business` · `Renewal` · `Rewrite`

**urgency** (Enum): `Low` · `Medium` · `High` · `Critical`

**syncStatus** (Enum, computed by sync engine): `Synced` · `Pending` · `Error` · `Skipped`

**amsLockState** (Enum): `Unlocked` · `Pending AMS` · `Locked by AMS` · `Rejected by AMS`

**Immutable external IDs:** `momentumPolicyId`, `insuredMomentumId`, `momentumLastSynced`

> Never edit core fields when `amsLockState = Locked by AMS`. See `guardrails.md` §Policy.

---

## Renewal

Tracks the window between renewal notice and re-bind or loss.

**stage** (Enum): `Identified` · `Outreach Sent` · `Quote Requested` · `Proposal Sent` · `Negotiating` · `Renewed - Won` · `Lost`

**urgency** (Enum): `Critical` · `High` · `Medium` · `Low`

**lastContactMethod** (Enum): `Email` · `Call` · `Text` · `In person`

**lostReason** (Enum): `Price` · `Coverage` · `Unresponsive` · `Moved carrier` · `Other`

---

## Commission

Commission transaction tied to an Opportunity, Policy, or Renewal.

**commissionType** (Enum): `New Business` · `Renewal` · `Endorsement`

**status** (Enum): `Estimated` · `Posted` · `Overdue`

**ledgerSyncStatus** (Enum, owned by n8n): `Pending` · `Synced` · `Error`

> Ledger sync fields are managed by n8n — do not manually edit. See `guardrails.md` §Commission.

---

## Task

Actionable to-do item. Most task types fire outbound webhooks on status changes — see `guardrails.md` §Task webhooks.

**status** (Enum): `Inbox` · `In Progress` · `Waiting on Client` · `Waiting on Carrier` · `Completed` · `Cancelled`

**priority** (Enum): `Low` · `Normal` · `High` · `Urgent`

**taskType** (Enum): `Client Service` · `Policy Change` · `Renewal` · `New Business` · `Follow Up` · `Onboarding` · `Claims` · `Commission` · `Admin` · `Other`

**syncSource** (Enum): `Manual` · `Gmail` · `Slack` · `Momentum` · `n8n` · `Hermes`

> Status changes on most task types send real client-facing notifications. Update one at a time with user confirmation. See `guardrails.md` §Task webhooks.

---

## ActivityLog

Immutable audit trail created by hooks and sync processes. **Never create or edit manually.**

Each record captures: `eventType`, linked `account`, linked `task`, `eventData` (JSON), timestamps.
