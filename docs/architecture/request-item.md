# Request Item Architecture

Version: 0.1

Status: Draft

Last Updated: August 4, 2026

Owner: Jairus Gian Ardiente

---

## 1. Definition

A Request Item represents an inventory item requested within an Inventory Request.

Each Inventory Request may contain one or more Request Items.

A Request Item records the requested quantity, released quantity, returned quantity, and the current operational state of that specific item.

---

## 2. Purpose

Request Items connect warehouse inventory to operational requests.

Instead of storing all requested items as plain text, every requested item becomes an individual record that can be monitored throughout its lifecycle.

This allows the system to accurately monitor releases, returns, damages, shortages, and additional requested items.

---

## 3. Item Lifecycle

A Request Item may experience the following events.

Requested

↓

Approved

↓

Released

↓

In Use

↓

Returned

or

Damaged

or

Missing

---

## 4. Original and Additional Items

The client's workflow allows workers to request additional items while work is ongoing.

Instead of creating a completely separate request, additional items remain part of the original Inventory Request.

Every Request Item therefore belongs to one of two types.

### Original

The item was included in the initial warehouse request.

### Additional

The item was requested after work had already started.

Both remain under the same Inventory Request.

---

## 5. Business Rules

- Every Request Item belongs to one Inventory Request.
- Every Request Item references one inventory Item.
- Requested quantity must be greater than zero.
- Released quantity must never exceed requested quantity unless approved by an Admin.
- Returned quantity cannot exceed released quantity.
- Consumable items normally do not require returns.
- Non-consumable items may be returned, damaged, or marked missing.
- Additional items remain attached to the original request.
- Every quantity adjustment must be recorded in the activity history.

---

## 6. Initial Attributes

| Attribute | Purpose |
|------------|----------|
| id | Unique identifier |
| inventory_request_id | Parent request |
| item_id | Requested inventory item |
| request_type | Original or Additional |
| requested_quantity | Quantity requested |
| released_quantity | Quantity actually released |
| returned_quantity | Quantity returned |
| damaged_quantity | Quantity returned damaged |
| missing_quantity | Quantity not returned |
| remarks | Additional notes |
| created_at | Creation timestamp |
| updated_at | Last update timestamp |

---

## 7. Quantity Rules

Requested Quantity

The quantity requested by the foreman.

Released Quantity

The quantity physically released from the warehouse.

Returned Quantity

The quantity returned after work.

Damaged Quantity

Items returned but no longer usable.

Missing Quantity

Items not returned.

Example

Requested: 10 Wrenches

Released: 10

Returned: 8

Damaged: 1

Missing: 1

Inventory reconciliation:

10 Released

8 Returned

1 Damaged

1 Missing

---

## 8. Relationships

Inventory Request

└── Request Items

├── Original

└── Additional

Each Request Item references exactly one Inventory Item.

---

## 9. Deferred Features

The following are postponed for future versions.

- Partial approval of individual items
- Replacement items
- Item reservations
- Warehouse transfer requests
- Batch or serial number tracking
- Automatic procurement requests