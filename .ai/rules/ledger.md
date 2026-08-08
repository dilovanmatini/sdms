---
paths:
  - 'app/Models/**'
  - 'app/Services/**'
  - 'database/migrations/**'
---

# Ledger and inventory transactions

Never store product stock or customer/AR balances as updatable columns. Always calculate from append-only tables: `inventory_transactions` (quantity_in − quantity_out), `customer_ledger_entries`, and `accounts_receivable_entries` (debit − credit).

Posted documents (purchases, sales invoices, payment receipts) are immutable. Do not edit or hard-delete posted records; reverse with new compensating transactions. Use `DocumentNumberGenerator` for PUR/INV/REC numbers under row locks so numbers are never reused.
