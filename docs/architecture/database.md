# Database Architecture

Version: 0.1

Status: Draft

Last Updated: August 4, 2026

Owner: Jairus Gian Ardiente

---

## 1. Core Entities

The initial system contains the following core entities:

- Users
- Items
- Inventory Requests
- Request Items
- Returns
- Activity Logs

---

## 2. Relationship Overview

```text
User
├── creates many Inventory Requests
├── approves many Inventory Requests
├── mobilizes many Inventory Requests
├── records many Returns
└── creates many Activity Logs

Inventory Request
├── belongs to one encoded-by User
├── may belong to one approved-by User
├── may belong to one mobilized-by User
└── has many Request Items

Request Item
├── belongs to one Inventory Request
├── belongs to one Item
└── has many Returns

Item
└── has many Request Items

Return
├── belongs to one Request Item
└── belongs to one recorded-by User

Activity Log
├── belongs to one User
└── may reference any supported system record


Entity Relationship Diagram
USERS
  │
  ├───────────────┐
  │               │
  ▼               ▼
INVENTORY_REQUESTS       ACTIVITY_LOGS
  │
  ▼
REQUEST_ITEMS
  │           │
  ▼           ▼
ITEMS       RETURNS
               │
               ▼
             USERS


             
The next step is not yet Laravel. Read this document once and check whether the flow matches the client’s process.

One thing to notice: I kept `project_name` and `work_area` as fields inside the request instead of creating separate Project and Work Area tables. For this client’s current scope, that is simpler and avoids unnecessary administration.