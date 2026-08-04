# Item Architecture

Version: 0.1

Status: Draft

Last Updated: August 4, 2026

Owner: Jairus Gian Ardiente

---

## 1. Definition

An Item is a tool, equipment, material, or consumable stored and monitored by the warehouse.

Items may be requested by foremen, released by warehouse personnel, returned when reusable, or recorded as damaged or missing.

---

## 2. Classification

### Category

An item belongs to one of the following initial categories:

- Tool
- Equipment
- Material
- Consumable

### Consumption Type

An item must also be classified as:

- Consumable
- Non-consumable

The category and consumption type serve different purposes.

Example:

- Wrench — Tool, Non-consumable
- Gloves — Material, Consumable

---

## 3. Stock Meaning

Each item keeps an overall stock quantity and an available quantity.

### Overall Stock

The total number of recorded units owned by the warehouse, including units currently released or in use.

### Available Quantity

The number of units currently available inside the warehouse for release.

Example:

```text
Overall Stock: 20
Available Quantity: 15
In Use: 5


The key rule here is important:

> For non-consumables, mobilization reduces **available quantity**, not necessarily **overall stock**.

That matches the client’s statement that the overall stock must remain visible while some tools are outside the warehouse.

After you paste and review both files, we proceed to `inventory-request.md`, where we define Pending, Approved, Mobilized, In Use, Completed, and Cancelled precisely.