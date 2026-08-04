# Inventory Request Architecture

Version: 0.1

Status: Draft

Last Updated: August 4, 2026

Owner: Jairus Gian Ardiente

---

## 1. Definition

An Inventory Request is a warehouse request submitted by a foreman and encoded into the system by warehouse personnel.

A request may contain tools, equipment, materials, and consumables needed for a specific project, work area, or task.

---

## 2. Purpose

The Inventory Request records:

- Who requested the items
- What items were requested
- How many items were requested
- When the request was encoded
- Where the items will be used
- Whether the request has been approved
- Whether the items have been released
- Whether non-consumable items remain in use
- Whether additional items were requested later

The request acts as the main record connecting warehouse inventory, requested items, releases, returns, and reports.

---

## 3. Request Workflow

1. A foreman prepares a paper request.
2. Warehouse Staff encode the request into the system.
3. The system automatically assigns the `Pending` status.
4. An Admin reviews the request.
5. The Admin may approve or cancel the request.
6. Approved items remain inside the warehouse until physically released.
7. Once the items leave the warehouse, the Admin marks the request as `Mobilized`.
8. Stock quantities are updated during mobilization.
9. A request containing unreturned non-consumable items remains `In Use`.
10. Additional items may be added while the request is Mobilized or In Use.
11. Returns are recorded for non-consumable items.
12. Damaged and missing items must be accounted for.
13. The request becomes `Completed` only after all required returns and item conditions have been recorded.

---

## 4. Request Statuses

### Pending

The request has been encoded and is waiting for Admin review.

Rules:

- This is the default status.
- Items remain inside the warehouse.
- Inventory quantities are not changed.
- Staff may still edit the request before approval.

### Approved

The request has been reviewed and accepted by an Admin.

Rules:

- Items may be prepared for release.
- Inventory quantities are not changed yet.
- Only an Admin may approve a request.
- Approved requests may proceed to Mobilized.

### Mobilized

The requested items have physically left the warehouse.

Rules:

- Inventory is updated at this stage.
- Consumable quantities reduce available and usable stock.
- Non-consumable quantities reduce available stock but remain part of overall stock.
- The request may receive additional items.
- The request may proceed to In Use or Completed depending on its contents.

### In Use

One or more non-consumable items remain outside the warehouse.

Rules:

- The request is still active.
- Additional items may still be added.
- Returns may be recorded.
- The request cannot be completed while unaccounted non-consumable items remain.

### Completed

The request has finished its operational lifecycle.

Rules:

- All non-consumable items have been returned or recorded as damaged or missing.
- All additional items have been accounted for.
- Completed requests remain available for printing and reporting.
- Completed records should not be silently modified.

### Cancelled

The request was cancelled before mobilization.

Rules:

- Inventory quantities remain unchanged.
- Cancelled requests remain available for historical reference.
- Mobilized or In Use requests cannot be cancelled through the normal workflow.

---

## 5. Allowed Status Transitions

```text
Pending
├── Approved
└── Cancelled

Approved
├── Mobilized
└── Cancelled

Mobilized
├── In Use
└── Completed

In Use
└── Completed


This document locks the most important rule:

> Inventory changes only when the items physically leave the warehouse and the request becomes `Mobilized`.

Next, we should create `request-item.md`, where we decide how original items and additional items are stored while still appearing separately to the client.