# KBUMPC Inventory Request Monitoring System Specification

Version: 0.1

Status: Draft

Last Updated: August 4, 2026

Owner: Jairus Gian Ardiente

---

## 1. Overview

The KBUMPC Inventory Request Monitoring System will monitor tools, equipment,
materials, and consumables requested by foremen from the warehouse.

Warehouse personnel currently receive requests on paper and encode the details
in Excel. The system will centralize these records, monitor available inventory,
track released items, record additional requests, monitor returned tools, and
produce printable reports.

The system focuses on inventory and request monitoring. It will not manage the
complete Material Entry Pass process of the power plant.

---

## 2. Main Users

### Super Admin

The Super Admin primarily monitors:

- Dashboard summaries
- Inventory results
- Request results
- Reports
- Historical records

The Super Admin is not responsible for daily warehouse operations.

### Admin

The Admin manages warehouse operations and may:

- Approve requests
- Manage inventory items
- Mobilize requested items
- Record returns
- Record damaged or missing tools
- Manage staff accounts
- Generate and print reports

### Staff

The Staff user may:

- Encode paper requests
- Add requested items and quantities
- Add additional items
- View available inventory
- Record item returns
- Prepare printable request copies

Staff cannot approve requests.

---

## 3. Inventory Classification

### Consumable Items

Consumable items are normally used during work and are not expected to return
to the warehouse.

Examples:

- Gloves
- Plastic bags
- Blue sheets
- Other materials and supplies

### Non-Consumable Items

Non-consumable items are reusable tools and equipment that should return after
the work is completed.

Examples:

- Wrench
- Ball hammer
- Wheelbarrow

Returned non-consumable items may be recorded as:

- Good
- Damaged
- Missing

---

## 4. Request Workflow

1. A foreman submits a paper request to the warehouse.
2. Warehouse staff encode the request into the system.
3. A newly encoded request automatically receives the `Pending` status.
4. An Admin reviews and approves the request.
5. Approved items remain inside the warehouse until physically released.
6. Once the items leave the warehouse, the request becomes `Mobilized`.
7. Inventory quantities are deducted when the request becomes `Mobilized`.
8. The request remains `In Use` while non-consumable tools or equipment have
   not yet returned.
9. Additional items may be added to the same request while work is ongoing.
10. Returned tools and equipment are recorded with their condition.
11. The request becomes `Completed` after work is finished and all returns,
    damaged items, and missing items have been accounted for.
12. Request history remains available for printing, reporting, and auditing.

---

## 5. Request Statuses

### Pending

The request has been encoded but has not yet been approved. Items remain inside
the warehouse and inventory is not deducted.

### Approved

The Admin has approved the request. Items may be prepared, but inventory is not
yet deducted.

### Mobilized

The items have physically left the warehouse. Inventory is deducted at this
stage.

### In Use

Work is ongoing, and one or more non-consumable items have not yet returned.

### Completed

Work is finished and all non-consumable items have been returned or properly
recorded as damaged or missing.

### Cancelled

The request was cancelled before mobilization.

---

## 6. Essential Features

### Dashboard

- Overall inventory stock
- Total requests
- Pending requests
- Approved requests
- Mobilized or in-use requests
- Completed requests
- Low-stock items
- Damaged tools
- Missing tools
- Recent activity

### Item Management

- Add, edit, view, and archive items
- Item name
- Item code
- Category
- Consumable or non-consumable type
- Current overall stock
- Available quantity
- Unit of measurement
- Storage location
- Minimum stock level
- Remarks

### Request Management

- Request number
- Foreman name
- Project, work area, or location
- Date and time
- Request status
- Multiple requested items
- Requested quantity
- Released quantity
- Remarks

### Additional Items

- Add extra items to an existing request
- Quantity
- Date and time added
- Reason or remarks
- User who encoded the additional item

### Stock Monitoring

- Add inventory stock
- Deduct inventory upon mobilization
- Preserve the overall stock record
- Prevent release beyond available quantity
- Allow partial release when stock is insufficient
- Record all stock movements

### Return Monitoring

For non-consumable items:

- Quantity released
- Quantity returned
- Return date and time
- Good quantity
- Damaged quantity
- Missing quantity
- Remarks

### Reports

- Printable copy of every request
- Request report by date
- Request report by foreman
- Request report by project or work area
- Consumable usage report
- Non-consumable release and return report
- Damaged and missing item report
- Additional-item report
- Excel or CSV export

### Activity History

The system records:

- Request creation
- Request approval
- Mobilization
- Additional items
- Stock changes
- Returns
- Damaged or missing items
- User responsible
- Date and time

---

## 7. Initial Data

Development will initially use dummy inventory data.

The client's existing Excel inventory file will be imported after the database
structure and import format have been verified.

---

## 8. Excluded Features

Version 1 will not include:

- Barcode scanning
- QR code scanning
- Sales processing
- Accounting
- Payroll
- Supplier purchasing
- Mobile application
- Full Material Entry Pass processing