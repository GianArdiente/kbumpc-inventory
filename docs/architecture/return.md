# Return Architecture

Version: 0.1

Status: Draft

Last Updated: August 4, 2026

Owner: Jairus Gian Ardiente

---

## 1. Definition

A Return records the result of bringing a non-consumable item back to the warehouse after it was released for work.

Returns apply mainly to tools and equipment.

---

## 2. Purpose

The Return record allows the warehouse to determine:

- What item came back
- How many units came back
- When the item returned
- Whether the returned item is good or damaged
- Whether any released quantity is still missing
- Who recorded the return

---

## 3. Return Conditions

### Good

The item returned in usable condition and may become available again.

### Damaged

The item returned but is no longer fully usable.

Damaged items remain part of the warehouse's overall stock record but should not automatically return to available quantity.

### Missing

The item was released but did not return.

Missing items remain recorded for accountability and reporting.

---

## 4. Business Rules

- Returns apply only to released non-consumable items.
- A Return belongs to one Request Item.
- Returned, damaged, and missing quantities must not exceed the released quantity.
- Good returned quantities increase available stock.
- Damaged returned quantities do not automatically increase available stock.
- Missing quantities do not increase available stock.
- A request cannot become Completed while released non-consumable quantities remain unaccounted for.
- Every return must record the responsible user and date and time.
- Return records must remain available for audit and reporting.

---

## 5. Initial Attributes

| Attribute | Purpose |
|---|---|
| `id` | Unique return identifier |
| `request_item_id` | Related released request item |
| `recorded_by` | User who recorded the return |
| `good_quantity` | Quantity returned in usable condition |
| `damaged_quantity` | Quantity returned damaged |
| `missing_quantity` | Quantity confirmed missing |
| `returned_at` | Return date and time |
| `remarks` | Notes about the return |
| `created_at` | Creation timestamp |
| `updated_at` | Last update timestamp |

---

## 6. Quantity Reconciliation

For every released non-consumable item:

```text
Released Quantity
=
Good Returned
+
Damaged
+
Missing
+
Still In Use

Released: 10

Good Returned: 6

Damaged: 1

Missing: 1

Still In Use: 2