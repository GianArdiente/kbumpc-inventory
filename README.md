# KBUMPC Inventory Management System

Web-based inventory and warehouse management system developed for KBUMPC.

The system handles inventory records, material and tool requests, stock movement, returns, item costs, reports, activity logs, and user accounts.

The main goal of the project is to keep warehouse records in one system and maintain a history of what happens to each inventory item.

## Project Description

The KBUMPC Inventory Management System was developed to manage the day-to-day inventory operations of KBUMPC.

The system keeps track of materials and tools stored in the warehouse. It records stock coming in, items being requested, approved requests, released items, returned tools, damaged items, missing items, and inventory adjustments.

Instead of only storing the current quantity of an item, inventory movements are also saved as transactions. This allows the system to keep a history of stock changes and the user responsible for the action.

The project also includes cost tracking. The unit cost used during a transaction is saved so that older transactions keep their original cost even when the current price of an item changes.

Reports can be viewed for a selected date range and exported to an Excel workbook with separate sheets for different types of warehouse records.

## Main Features

### Dashboard

The dashboard provides a quick view of the current warehouse and inventory status.

It displays important information from the inventory and request modules so users do not need to open each section individually.

### Inventory Management

The inventory module stores the main information for materials and tools.

Each item can contain:

- Item code
- Item name
- Category
- Consumption type
- Unit
- Current unit cost
- Overall stock
- Available quantity
- Reserved quantity
- Minimum stock level
- Storage location
- Remarks
- Archive status

Items can be archived instead of permanently deleted so their previous transactions can still be referenced.

### Stock Transactions

Changes to inventory are recorded in the inventory transaction table.

Transactions can store:

- Item
- Transaction type
- Stock quantity change
- Available quantity change
- Quantity after the transaction
- Unit cost at the time of the transaction
- Total cost
- User responsible for the transaction
- Source record
- Remarks
- Date and time

This is used by the system to maintain the history of inventory movement.

### Cost Tracking

The system saves a copy of the item's unit cost when certain inventory transactions are created.

For example:

```text
Current item cost: ₱125.50
Stock In: 10 pieces
Saved transaction cost: ₱125.50 each

Item cost later changes to: ₱140.00
New Stock In: 5 pieces
Saved transaction cost: ₱140.00 each
```

The first transaction remains at ₱125.50 even after the item's current cost changes.

This is used so reports from previous periods do not use the latest item price for older transactions.

## Inventory Requests

Users can create requests for materials and tools needed for operations.

The request process follows this general flow:

```text
Request Created
      ↓
Items Added
      ↓
Request Approved
      ↓
Items Reserved
      ↓
Request Mobilized
      ↓
Items Used / Returned
```

### Approval

A request must be approved before its inventory can be released.

The system uses reserved quantities to prevent stock assigned to an approved request from being treated as freely available inventory.

### Mobilization

Mobilization records the actual release of approved materials and tools.

The inventory quantities are updated and corresponding inventory transactions are created.

## Consumable Materials and Reusable Tools

Inventory items have a consumption type so the system can handle materials and tools differently.

### Consumable

Consumable items are materials that are expected to be used after they are released.

Examples may include construction materials or supplies.

### Reusable

Reusable items are tools or equipment that are expected to return to the warehouse after use.

These items can go through the return process.

## Returns

The Returns module records reusable items coming back to the warehouse.

Returned quantities can be classified based on their condition.

The system currently handles records for:

- Good returns
- Damaged items
- Missing items

Good returned quantities can be made available in inventory again.

Damaged and missing quantities are recorded separately so they can appear correctly in reports and inventory history.

## Reports

The Reports module allows warehouse records to be viewed for a selected date range.

This can be used for weekly, monthly, or custom reports.

The report uses inventory transactions and related warehouse records to show what happened during the selected period.

## Excel Export

Reports can be exported as an `.xlsx` workbook.

The export uses multiple worksheets instead of placing all records in one large sheet.

Current workbook sheets:

1. Summary
2. Materials Used
3. Tools Mobilized
4. Stock In
5. Good Returns
6. Damaged Items
7. Missing Items
8. Adjustments
9. Completed Requests
10. Inventory Snapshot
11. Transaction Ledger

This keeps different report types separated while still exporting them in one Excel file.

## Activity Logs

Important system actions are recorded in the Activity Logs.

A log can contain:

- User
- Action
- Related record
- Description
- Previous values
- New values
- Additional properties
- Date and time

Examples of logged actions include inventory changes, request processing, returns, and user account changes.

Passwords are not included in activity log data.

## User Management

Admin users can manage system accounts.

Available functions include:

- Create user
- Edit user
- Assign role
- Change account status
- Change user password

The current roles are:

```text
Admin
Staff
```

Admin-only sections such as User Management and Activity Logs are not available to staff accounts.

## Account Status

User accounts can be set to:

```text
Active
Inactive
```

Inactive accounts cannot log in.

If an account is changed to inactive while the user is already logged in, the account is blocked on the user's next request and the existing session is ended.

The system also prevents an administrator from accidentally deactivating their own account or removing their own Admin role.

## Technology Used

- PHP
- Laravel
- Blade
- Tailwind CSS
- Alpine.js
- JavaScript
- SQLite
- Laravel Breeze
- Maatwebsite Laravel Excel
- PhpSpreadsheet
- Composer
- NPM
- Vite
- Git
- GitHub

## Project Structure

Some of the main application directories are:

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

## Local Setup

### Requirements

- PHP
- Composer
- Node.js and NPM
- SQLite
- Git

Clone the repository:

```bash
git clone <repository-url>
cd kbumpc-inventory
```

Install PHP packages:

```bash
composer install
```

Install frontend packages:

```bash
npm install
```

Create the environment file:

```bash
cp .env.example .env
```

For PowerShell:

```powershell
Copy-Item .env.example .env
```

Generate the Laravel application key:

```bash
php artisan key:generate
```

Configure the database in `.env`.

Run the migrations:

```bash
php artisan migrate
```

Build the frontend:

```bash
npm run build
```

For development:

```bash
npm run dev
```

Run Laravel:

```bash
php artisan serve
```

## Files Not Included in Git

Development and sensitive files are excluded from the repository through `.gitignore`.

This includes files such as:

```text
.env
database/database.sqlite
vendor/
node_modules/
storage logs
```

A local database must be created when setting up a new copy of the project.

## Current Status

Core system development is complete.

Completed modules:

- Dashboard
- Inventory
- Stock adjustments
- Inventory requests
- Request approval
- Request mobilization
- Returns
- Item cost tracking
- Reports
- Excel export
- Activity logs
- User management
- Account status checking
- Admin and staff access control

The system is currently being prepared for final UI cleanup, testing, deployment, and user acceptance testing.

## Planned Final Work

Before production deployment:

- Final UI cleanup
- Activity Log details modal
- Responsive layout checks
- Final workflow testing
- Production configuration
- Database backup procedure
- Restore testing
- User acceptance testing
- Client deployment

## License

Copyright © 2026 Jairus Gian S. Ardiente.

All Rights Reserved.

This project is proprietary software. The source code may not be used, copied, modified, distributed, sold, or deployed without permission from the copyright holder.

See [LICENSE](LICENSE) for the full terms.

## Developer

**Jairus Gian S. Ardiente**

BS Information Technology