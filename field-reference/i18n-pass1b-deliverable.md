# Pass 1b deliverable — Quote, OpportunityVehicle, OpportunityDriver (i18n only)

**Scope:** `custom/Espo/Custom/Resources/i18n/en_US/` only. No `entityDefs`, layouts, or PHP. **`bin/command rebuild` not run.**

## `Entity | Field API Name | Old Label | New Label`

### Quote

| Entity | Field API Name | Old Label | New Label |
|--------|----------------|-----------|-----------|
| Quote | premium | Premium | Premium Amount |

**Review, no further changes:** `quoteNumber`, `carrier`, `status`, `effectiveDate`, `expirationDate`, `lineOfBusiness`, `opportunity`, `account`, `notes`, and all `options.status` values were already fully spelled.

---

### OpportunityVehicle

| Entity | Field API Name | Old Label | New Label |
|--------|----------------|-----------|-----------|
| OpportunityVehicle | vin | VIN | Vehicle Identification Number |
| OpportunityVehicle | ownershipType | Own / Lease | Ownership Type |
| OpportunityVehicle | ownershipType (option key `Own`) | Own | Owned |
| OpportunityVehicle | ownershipType (option key `Lease`) | Lease | Leased |
| OpportunityVehicle | year | Year | Vehicle Model Year |
| OpportunityVehicle | make | Make | Vehicle Make |
| OpportunityVehicle | model | Model | Vehicle Model |

**Review, no further changes:** `mileageDriven`, `lineOfBusiness`, `opportunity`, `account` — already clear and consistent with canonical **Line of Business**.

---

### OpportunityDriver

| Entity | Field API Name | Old Label | New Label |
|--------|----------------|-----------|-----------|
| OpportunityDriver | stateIssued | State Issued | Driver License Issuing State |

**`lineOfBusiness`:** Already **Line of Business** — matches canonical label on other entities; **no change.**

**Review, no further changes:** `driverName`, `driverLicenseNumber`, `dateOfBirth`, `licenseExpirationDate`, `vehicle`, `driverSchedule`, `opportunity`, `account`.
