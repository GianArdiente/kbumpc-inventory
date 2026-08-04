# User Architecture

Version: 0.1

Status: Draft

Last Updated: August 4, 2026

Owner: Jairus Gian Ardiente

---

## 1. Definition

A User is an authorized person who accesses the inventory request monitoring system.

Users are responsible for encoding requests, approving requests, managing inventory records, recording returns, and monitoring reports depending on their assigned role.

---

## 2. Initial Roles

### Super Admin

The Super Admin primarily monitors:

- Dashboard summaries
- Inventory results
- Request results
- Reports
- Historical records

The Super Admin is not responsible for normal warehouse operations.

### Admin

The Admin manages warehouse operations and may:

- Approve requests
- Manage inventory items
- Mobilize requested items
- Record returns
- Record damaged or missing tools
- Manage Staff accounts
- Generate and print reports

### Staff

The Staff user may:

- Encode requests
- Add requested items
- Add additional items
- View available inventory
- Record item returns
- Prepare printable request copies

Staff cannot approve requests.

---

## 3. Business Rules

- Every user must have one active role.
- Only Admin users may approve requests.
- Staff users may encode requests but may not approve them.
- Super Admin users primarily have monitoring and reporting access.
- Every important user action must be recorded in the activity history.
- Inactive users must not be allowed to access the system.

---

## 4. Initial Attributes

| Attribute | Purpose |
|---|---|
| `id` | Unique user identifier |
| `name` | User's full name |
| `email` | Login email |
| `password` | Securely hashed login password |
| `role` | Super Admin, Admin, or Staff |
| `status` | Active or inactive |
| `created_at` | Creation timestamp |
| `updated_at` | Last update timestamp |

---

## 5. Deferred Features

- Custom permissions
- Multiple roles per user
- Foreman accounts
- Approval delegation
- Department-based access