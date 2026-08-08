# Store & Distribution Management System (SDMS)
## Functional Scope & Business Specification
**Version:** 1.0  
**Language:** Arabic  
**Platform:** Web Application

---

# 1. Project Overview

Develop a modern web-based Store & Distribution Management System for managing inventory and sales of alcoholic beverages distributed to customers (distributors).

The application will be used internally by the company and must support the complete workflow from purchasing products until collecting customer payments.

The application interface shall be **100% Arabic (RTL)**.

The database design should follow **ERP/Accounting standards**, meaning every inventory and financial transaction must be stored as immutable transactional records rather than modifying balances directly.

---

# 2. General Requirements

## Application

- Web application
- Arabic language only
- Right-to-left (RTL)
- Responsive UI
- Multi-user
- Role-based permissions

---

# 3. Business Workflow

The complete workflow is:

Supplier
↓

Purchase
↓

Warehouse Inventory
↓

Distributor
↓

Sales Invoice
↓

Payment Receipt
↓

Customer Statement

---

# 4. Modules

---

# Module 1 — Authentication

## Features

- Login
- Logout
- Change Password

No self-registration.

Only administrators can create users.

---

# Module 2 — User Management

Users contain

- Full Name
- Username
- Password
- Role
- Status (Active/Inactive)

Roles

- Administrator
- Warehouse
- Sales
- Accountant
- Manager

Permissions should be role-based.

---

# Module 3 — Product Categories

Fields

- ID
- Category Name
- Description
- Status

Operations

- Create
- Edit
- Delete (only if unused)
- Search

---

# Module 4 — Products

Each product contains

- Product Code
- Barcode (optional)
- Arabic Name
- Category
- Unit
- Status
- Notes

No selling price.

No purchase price.

No cost.

The inventory quantity must never be stored directly.

Current stock must always be calculated from inventory transactions.

---

# Module 5 — Suppliers

Fields

- Supplier Name
- Contact Person
- Phone
- Address
- Notes
- Status

Operations

- CRUD

---

# Module 6 — Purchases

Purpose

Increase inventory.

Purchase Header

- Purchase Number
- Purchase Date
- Supplier
- Notes

Purchase Details

- Product
- Quantity

No prices.

No totals.

Posting Purchase

Creates Inventory Transactions.

No inventory quantity is updated directly.

---

# Module 7 — Inventory

Inventory page should display

- Product
- Available Quantity

Quantity =

Total Purchased

minus

Total Sold

The inventory must be calculated from transaction tables.

Never maintain stock using update statements.

---

# Module 8 — Distributors

Fields

- Distributor Name
- Contact Person
- Phone
- Address
- Credit Limit (optional)
- Notes
- Active

Operations

CRUD

---

# Module 9 — Sales Invoices

Header

- Invoice Number
- Invoice Date
- Distributor
- Notes

Details

- Product
- Quantity
- Unit Price
- Line Total

Invoice Total

Subtotal

Discount (optional)

Grand Total

Posting Invoice

Creates

Inventory Transactions

Accounts Receivable Transactions

Customer Ledger Transactions

---

# Module 10 — Payment Receipts

Receipt Header

- Receipt Number
- Receipt Date
- Distributor
- Payment Method
- Notes

Receipt Details

- Amount
- Allocated Invoice(s)

Posting Receipt

Creates

Customer Ledger

Accounts Receivable Ledger

Payments may be

- Full
- Partial

One receipt can pay multiple invoices.

One invoice can be paid by multiple receipts.

---

# Module 11 — Customer Statement

Statement should display

Chronologically

Invoice

Payment

Balance

Running Balance

Remaining Balance

Date Range

Customer Information

Printable

PDF

---

# Module 12 — Dashboard

Display

Current Inventory

Today's Sales

This Month Sales

Outstanding Receivables

Total Customers

Total Products

Recent Sales

Recent Payments

---

# Module 13 — Reports

Inventory Report

Shows

Product

Current Stock

Supplier Report

Purchases Report

Sales Report

Customer Statement

Outstanding Customers

Payment Report

Daily Sales

Monthly Sales

All reports should support

Printing

PDF

Excel Export

---

# 5. Database Design Standards

This project **must follow accounting and ERP database standards**.

## Important Principles

### Master Tables

Store static information only.

Examples

Products

Customers

Suppliers

Users

Categories

---

### Transaction Tables

Store business events.

Examples

Purchases

Purchase Details

Sales Invoices

Invoice Details

Receipts

Receipt Allocations

Inventory Transactions

Customer Ledger

---

### Ledger Tables

Never update balances directly.

Balances must be calculated from ledger entries.

---

# Inventory Design

Do NOT store

Product.Quantity

Instead

InventoryTransaction

contains

- Transaction ID
- Product
- Date
- Reference Type

Purchase

Sale

Adjustment (future)

- Reference ID
- Quantity In
- Quantity Out

Current Stock

SUM(QuantityIn)

-

SUM(QuantityOut)

---

# Customer Ledger

CustomerLedger

contains

Invoice

Debit

Receipt

Credit

Running Balance

Every invoice creates

Debit

Every payment creates

Credit

Customer balance is calculated.

Never updated.

---

# Accounts Receivable Ledger

Each invoice creates

AR Debit

Each payment creates

AR Credit

Outstanding Balance

Calculated

---

# Numbering

Automatically generated

Examples

PUR-000001

INV-000001

REC-000001

Numbers must never be reused.

---

# Soft Delete

Use

is_active

or

deleted_at

Never hard delete transactional records.

---

# Audit Fields

Every table should contain

Created By

Created At

Updated By

Updated At

Deleted By

Deleted At (optional)

---

# 6. Validation Rules

Cannot sell more than available stock.

Cannot delete posted transactions.

Cannot edit posted transactions.

Posted transactions must be reversed instead of modified.

Invoice totals must equal detail totals.

Receipt allocations cannot exceed invoice balance.

---

# 7. Future Expansion

Database must be designed to easily support

- Purchase prices
- Costing
- FIFO
- Average Cost
- Multiple Warehouses
- Multiple Branches
- Barcode Scanner
- POS
- Accounting Module
- General Ledger
- Journal Entries
- VAT
- Mobile Application

No database redesign should be required.

---

# 8. Non-Functional Requirements

- Clean Architecture
- Layered Architecture
- Stateless backend
- Optimized SQL queries
- ACID database transactions
- Proper indexing
- Foreign key constraints
- Optimistic concurrency where appropriate
- Secure authentication
- Audit logging
- Daily backup support

---

# 9. Acceptance Criteria

The system will be accepted when:

- Purchases correctly increase inventory.
- Sales correctly decrease inventory.
- Selling unavailable quantities is prevented.
- Customer balances are calculated correctly.
- Partial payments work correctly.
- Customer statements show accurate running balances.
- Reports match transactional data.
- All screens are fully functional in Arabic (RTL).
- The database follows transactional accounting principles with no direct balance storage.
