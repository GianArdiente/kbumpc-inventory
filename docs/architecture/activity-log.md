# Activity Log Architecture

Version: 0.1

Status: Draft

Last Updated: August 4, 2026

Owner: Jairus Gian Ardiente

---

## 1. Definition

An Activity Log is a protected historical record of an important action performed inside the system.

It provides accountability by showing who performed an action, what changed, and when it happened.

---

## 2. Purpose

Activity Logs help the organization:

- Review request history
- Identify who changed inventory data
- Investigate incorrect stock movements
- Confirm approvals and mobilizations
- Review returns, damaged items, and missing items
- Produce reliable reports

---

## 3. Logged Actions

Initial logged actions include:

- User login
- Inventory item created
- Inventory item edited
- Inventory stock added
- Request created
- Request edited
- Request approved
- Request cancelled
- Request mobilized
- Additional item added
- Return recorded
- Damaged item recorded
- Missing item recorded
- Request completed
- Record archived

---

## 4. Business Rules

- Every important operational action must create an Activity Log.
- Activity Logs identify the responsible user.
- Activity Logs record the affected record and action performed.
- Activity Logs must include the date and time.
- Activity Logs cannot be edited through normal application usage.
- Activity Logs cannot be deleted through normal application usage.
- Historical logs remain available even when related records are archived.
- Sensitive information such as passwords must never be stored in activity details.

---

## 5. Initial Attributes

| Attribute | Purpose |
|---|---|
| `id` | Unique activity identifier |
| `user_id` | User responsible for the action |
| `action` | Type of action performed |
| `subject_type` | Type of affected record |
| `subject_id` | Identifier of affected record |
| `description` | Human-readable activity description |
| `properties` | Structured before-and-after or contextual details |
| `created_at` | Action timestamp |

---

## 6. Example Activities

```text
Admin Maria approved Request REQ-2026-001.