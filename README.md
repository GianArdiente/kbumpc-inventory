# KBUMPC Inventory Management System

A web-based inventory, material request, warehouse monitoring, reporting, and accountability system developed for **KBUMPC**.

The system is designed to provide a centralized platform for managing warehouse inventory and monitoring the complete movement of materials and tools—from stock entry and inventory requests to approval, mobilization, returns, damage or missing-item recording, cost tracking, reporting, and administrative auditing.

Instead of relying on scattered manual records, the system maintains a structured history of inventory transactions and warehouse activities while providing administrators with reporting and accountability tools.

---

## Project Overview

The **KBUMPC Inventory Management System** was developed to help organize and monitor warehouse operations through a centralized digital system.

The project focuses not only on recording how many items are currently available, but also on tracking **what happened to inventory over time**.

The system can record and monitor:

- Materials and tools currently stored in inventory
- Physical, available, and reserved quantities
- Stock received by the warehouse
- Inventory adjustments and corrections
- Material and tool requests
- Request approval
- Release or mobilization of requested items
- Consumable materials used
- Reusable tools currently in use
- Returned tools and materials
- Good-condition returns
- Damaged items
- Missing or unreturned items
- Historical unit costs
- Inventory transaction values
- User activity and administrative actions

This creates a traceable inventory history rather than simply maintaining a current stock count.

---

## Core Features

### Dashboard

Provides administrators and warehouse personnel with an overview of important inventory and operational information.

The dashboard is designed to surface relevant warehouse information without requiring users to manually inspect individual inventory records.

---

### Inventory Management

The inventory module maintains the central list of materials, equipment, and tools.

Each inventory item can contain information such as:

- Item code
- Item name
- Category
- Consumption type
- Unit of measurement
- Current unit cost
- Overall stock
- Available quantity
- Reserved quantity
- Minimum stock level
- Storage location
- Remarks
- Archive status

Items can also be archived without deleting their historical records.

---

### Stock Movement and Inventory Transactions

Inventory changes are recorded as transactions rather than simply overwriting stock values.

The system maintains transaction information including:

- Transaction type
- Overall stock change
- Available quantity change
- Stock balance after the transaction
- Historical unit cost
- Transaction cost
- User responsible
- Source of the transaction
- Remarks
- Transaction date and time

This provides a historical ledger of inventory movement.

---

### Historical Cost Tracking

The system preserves the unit cost associated with an inventory transaction at the time that transaction occurs.

For example, if an item previously cost:

`₱125.50`

and its current unit cost later becomes:

`₱140.00`

older inventory transactions can retain the original `₱125.50` value while new transactions use `₱140.00`.

This prevents historical reports from being incorrectly recalculated using only the item's latest price.

---

## Inventory Request Workflow

The system includes a structured request workflow for materials and tools.

A request can progress through operational stages such as:

**Request Creation → Item Assignment → Approval → Mobilization → Return / Completion**

This provides clearer accountability for inventory leaving the warehouse.

### Request Creation

Authorized users can create inventory requests and specify the required materials or tools.

### Approval

Requests can be reviewed before inventory is released.

Inventory quantities can be reserved during the request process to prevent the same available stock from being allocated incorrectly.

### Mobilization

Once an approved request is mobilized, the system records the release of the requested inventory.

The corresponding inventory quantities and transaction records are updated automatically.

---

## Materials and Tools

The system distinguishes between inventory based on how it is consumed or returned.

### Consumable Materials

Consumable materials can be recorded as used once released for operations.

These materials contribute to material usage and cost reporting.

### Reusable Tools

Reusable tools can be mobilized and later returned to the warehouse.

The system can therefore track equipment that is:

- Available
- Reserved
- In use
- Returned
- Damaged
- Missing

---

## Returns Management

Reusable inventory can be processed through the Returns module.

Return records allow the warehouse to distinguish between different outcomes instead of treating every returned item identically.

The system can track:

- Good returns
- Damaged returns
- Missing or unreturned quantities
- Returned quantities
- Inventory restored to availability
- Related historical costs
- User responsible for recording the return

This helps maintain accountability for equipment released from the warehouse.

---

## Reporting System

The project includes a dedicated reporting module for reviewing warehouse activity within selected reporting periods.

Reports can be filtered by date range so administrators can inspect activity for periods such as:

- A specific day
- A week
- A month
- A custom reporting period

The reporting system summarizes inventory movement, usage, costs, returns, damages, missing inventory, requests, and other relevant warehouse activity.

---

## Excel Report Export

Reports can be exported as an organized Microsoft Excel workbook.

Instead of placing every report into a single oversized worksheet, the exported workbook separates information into dedicated sheets.

The workbook can include:

1. **Summary**
2. **Materials Used**
3. **Tools Mobilized**
4. **Stock In**
5. **Good Returns**
6. **Damaged Items**
7. **Missing Items**
8. **Adjustments**
9. **Completed Requests**
10. **Inventory Snapshot**
11. **Transaction Ledger**

This structure makes exported reports easier to review, print, archive, and present.

### Summary Sheet

Provides an overview of activity and financial values for the selected reporting period.

### Materials Used

Shows consumable materials released or used during operations together with their quantities and historical costs.

### Tools Mobilized

Shows reusable equipment released for operational use.

### Stock In

Records inventory received by the warehouse during the reporting period.

### Good Returns

Shows equipment successfully returned in usable condition.

### Damaged Items

Provides records of inventory returned or recorded as damaged.

### Missing Items

Provides records of inventory that was not returned or was recorded as missing.

### Adjustments

Shows manual inventory corrections and adjustments.

### Completed Requests

Provides information about inventory requests completed during the selected period.

### Inventory Snapshot

Provides a structured view of inventory balances.

### Transaction Ledger

Provides detailed inventory transaction history for auditing and review.

---

## Activity Logs

The system maintains administrative activity logs to improve accountability.

Activities can record information such as:

- User performing the action
- Action type
- Related record
- Description
- Previous values
- Updated values
- Additional transaction properties
- Date and time

Examples include:

- Inventory adjustments
- Request approval
- Request mobilization
- Returns
- User creation
- User account changes

Sensitive information such as passwords is not stored in the activity log.

---

## User Management

Administrators can manage system accounts through the User Management module.

Administrators can:

- Create users
- Edit account information
- Assign roles
- Activate accounts
- Deactivate accounts
- Reset/change account passwords

The system currently supports administrative and staff roles.

---

## Account Security

Several safeguards are implemented around account management.

### Inactive Account Protection

Accounts marked as inactive cannot authenticate normally.

If an account is deactivated while that user already has an active session, the system checks the account status on subsequent protected requests and invalidates access.

### Administrator Protection

Administrative safeguards prevent actions that could leave the system without an active administrator.

A logged-in administrator is also protected from accidentally:

- Deactivating their own account
- Removing their own administrator role

### Password Protection

Passwords are hashed through Laravel's authentication system and are never written into activity logs.

---

## Roles and Permissions

### Admin

Administrative users can perform management functions including inventory administration, reports, activity monitoring, and user management.

### Staff

Staff accounts are intended for permitted warehouse operations while sensitive administrative areas such as user management and activity logs are restricted.

Backend authorization is used in addition to hiding restricted navigation elements.

---

## Inventory Accountability

One of the primary objectives of the system is maintaining traceability.

Rather than only answering:

> "How many items do we have?"

the system is designed to help answer:

> "What happened to the inventory?"

This includes questions such as:

- What entered the warehouse?
- What was requested?
- Who approved the request?
- What was released?
- What materials were consumed?
- What tools are still in use?
- What was returned?
- What was damaged?
- What is missing?
- What did those items cost at the time?
- Who performed the transaction?
- When did the activity occur?

---

## Technology Stack

The system is built using:

- **Laravel**
- **PHP**
- **Blade**
- **Tailwind CSS**
- **Alpine.js**
- **Vite**
- **SQLite / Laravel Database Layer**
- **Laravel Breeze**
- **Maatwebsite Laravel Excel**
- **PhpSpreadsheet**
- **Composer**
- **NPM**
- **Git / GitHub**

---

## Project Structure

Important application areas include:

```text
app/
├── Actions/
│   ├── Inventory/
│   └── InventoryRequests/
├── Exports/
│   └── Sheets/
├── Http/
│   ├── Controllers/
│   ├── Middleware/
│   └── Requests/
├── Models/
└── Services/

database/
└── migrations/

resources/
└── views/
    ├── activity-logs/
    ├── inventory/
    ├── reports/
    ├── requests/
    ├── returns/
    └── users/
```

The project separates major inventory operations into controllers, request validation, action classes, services, models, and reporting/export components.

---

## Development Installation

This repository is private and proprietary. These instructions are intended for authorized development environments.

### Requirements

Ensure the development machine has:

- PHP
- Composer
- Node.js / NPM
- Required PHP extensions
- SQLite or another configured database
- Git

### Setup

Clone the authorized repository:

```bash
git clone <authorized-repository-url>
cd kbumpc-inventory
```

Install PHP dependencies:

```bash
composer install
```

Install frontend dependencies:

```bash
npm install
```

Create the environment configuration:

```bash
cp .env.example .env
```

On Windows PowerShell, this can instead be:

```powershell
Copy-Item .env.example .env
```

Generate the application key:

```bash
php artisan key:generate
```

Configure the database in `.env`.

Run migrations:

```bash
php artisan migrate
```

Build frontend assets:

```bash
npm run build
```

For development:

```bash
npm run dev
```

Start Laravel:

```bash
php artisan serve
```

---

## Repository Security

Sensitive runtime information is intentionally excluded from version control.

Examples include:

```text
.env
database/database.sqlite
vendor/
node_modules/
storage logs
generated build/runtime files
```

Environment credentials, real operational databases, passwords, and production secrets must never be committed to this repository.

---

## Project Status

**Core Development: Completed**

Implemented major modules include:

- Inventory management
- Inventory stock transactions
- Unit cost tracking
- Material and tool requests
- Request approval
- Inventory mobilization
- Returns processing
- Damage and missing-item tracking
- Reporting
- Multi-sheet Excel exports
- Activity logs
- User management
- Role-based access
- Inactive account protection

The project may continue to receive user-interface polishing, quality assurance improvements, deployment configuration, and client-specific changes before production rollout.

---

## Future / Final Polishing

Planned final improvements may include:

- UI and responsive-layout refinements
- Improved Activity Log detail presentation
- Wide landscape-style Activity Log details modal
- Human-readable audit property formatting
- Additional usability improvements
- Production deployment configuration
- Backup and recovery procedures
- Client-specific configuration
- Final user acceptance testing

---

## Screenshots

Project screenshots can be added here to demonstrate:

- Dashboard
- Inventory
- Inventory details
- Requests
- Returns
- Reports
- Excel report output
- Activity Logs
- User Management

---

## Purpose

This project serves as both a functional warehouse inventory solution and a software development project demonstrating the implementation of:

- Business workflow modeling
- Inventory transaction management
- Database relationships
- Historical costing
- Audit trails
- Role-based authorization
- Authentication safeguards
- Administrative interfaces
- Report generation
- Excel workbook generation
- Laravel application architecture

---

## License

**Copyright © 2026 Jairus Gian S. Ardiente. All Rights Reserved.**

This project is proprietary software.

Access to this repository does not grant permission to use, copy, modify, redistribute, deploy, sublicense, sell, or commercially exploit the software.

Any use of the software requires explicit authorization from the copyright holder.

See the [`LICENSE`](LICENSE) file for the complete terms.

---

## Developer

**Jairus Gian S. Ardiente**

BS Information Technology

Developer of the KBUMPC Inventory Management System.