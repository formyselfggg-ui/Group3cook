# DASURECO Integrated Field Operations and Work Order Management System

## Purpose

Build a centralized **Field Operations and Work Order Management System for DASURECO**. It manages field activities, work orders, electrical assets, materials, maintenance records, and operational monitoring.

- Do **NOT** include GIS mapping or any map-based features.
- Build one connected system, not separate modules. Work orders, assets, materials, field updates, and maintenance records must be linked through relationships.
- Enforce **Role-Based Access Control (RBAC)**. Each role has its own dashboard, permissions, and functions.

---

## Core Workflow

```
Issue / Maintenance Request
  → Create Work Order
  → Set Priority
  → Assign Field Crew
  → Perform Field Work
  → Record Work & Materials Used
  → Submit Completion
  → Supervisor Review
  → Approve OR Return for Correction
  → Close Work Order
  → Update Maintenance Records
```

When a work order is **closed**, automatically update the related maintenance history and asset records.

A completed work order must contain: work order number, maintenance/field activity type, assigned personnel, priority, date assigned, status, asset involved, materials used, work performed, field notes, photos/documentation, completion date, and supervisor approval.

---

## Roles and Permissions (RBAC)

| Role | Scope |
|---|---|
| Admin | System administration (highest access) |
| Operations / Engineering Staff | Operational and asset management |
| Supervisor / Dispatcher | Work order and field crew management |
| Field Personnel | Assigned field work and updates only |

Enforce permissions on both the UI and the backend/API. Never rely on hiding UI elements alone.

### Admin
- Manage users: create, edit, deactivate accounts
- Assign roles and manage permissions
- Manage system configuration and master records
- View activity logs
- Dashboard: total users, active users, users by role, recent system activity

### Operations / Engineering Staff
- Register work requirements
- View work orders
- Manage asset records, view asset condition and maintenance history
- Review field-operation records and view material usage
- Generate operational reports
- Dashboard: total registered assets, assets under maintenance, recent maintenance activities, work orders by category, material usage

### Supervisor / Dispatcher
- Create work orders, set priorities, assign field personnel, schedule work
- Monitor task progress
- Review field submissions; return incomplete work; approve completed work; close work orders
- Dashboard: pending, assigned, in-progress, waiting-for-review, completed, and urgent work orders; field personnel workload

### Field Personnel
- View assigned tasks and instructions
- Start work, update status, record work performed, record materials used, add notes, upload photos
- Submit work for supervisor review
- Dashboard (simple, focused): new assignments, current tasks, in-progress tasks, tasks waiting for review, completed tasks
- Can access **only work orders assigned to them**, unless permission is explicitly granted
- Must have **no access** to administrative or management functions

---

## Modules

### 1. Work Order Management

**Fields:** Work Order ID, Title, Description, Work Category, Priority, Assigned Personnel/Team, Related Asset, Required Materials, Date Created, Scheduled Date, Status, Field Notes, Completion Information.

**Statuses:** `Pending → Assigned → In Progress → For Review → Completed`

**Priorities:** `Low → Normal → High → Urgent`

Supervisors must be able to monitor all active work orders and identify delayed, pending, ongoing, and completed tasks.

### 2. Field Operations Management

Field workflow:

```
Assigned Task → Start Work → Perform Work → Record Activity → Record Materials → Submit Completion
```

Dashboard flow: `My Tasks → Task Details → Start → Update → Document → Submit`

### 3. Asset Management

Centralized records for electrical assets and equipment: transformers, electric meters, distribution equipment, cables, maintenance equipment, other registered electrical assets.

**Fields:** Asset ID, Asset Type, Name/Description, Serial Number (if applicable), Current Status, Condition, Installation/Acquisition Date, Maintenance History, Related Work Orders.

**Statuses:** `Active → Under Maintenance → Damaged → Retired`

Link assets to work orders so users can view an asset's full maintenance and repair history.

### 4. Inventory and Material Usage

Track materials used in field operations: cables, connectors, electrical components, replacement parts, maintenance materials.

Material transaction flow (always tied to a specific work order):

```
Work Order → Material Selected → Quantity Used → Inventory Updated → Usage Recorded
```

Keep a usage history with: material, quantity, work order, personnel/team, date used.

---

## Maintenance History

Every completed maintenance-related work order must feed the maintenance history:

```
Asset → Work Orders → Maintenance Activities → Materials Used → Completion Records
```

Each history entry shows: asset, maintenance type, date, work performed, personnel involved, materials used, work order reference, completion status.

Do not store maintenance information separately from work orders.

---

## Dashboards and Reporting

Management dashboards should summarize:

- Total, pending, in-progress, completed, and urgent work orders
- Work orders by category and by status
- Asset condition and assets under maintenance
- Material usage
- Field personnel workload
- Recent maintenance activities

Reports must be filterable by: date range, status, priority, work category, asset, assigned personnel.

---

## Notifications

Provide in-system notifications that respect user roles, for:

- New work order assigned
- Work order priority changed
- Work order submitted for review
- Work order returned for correction
- Work order approved
- Work order completed
- Low inventory level

---

## Activity Logs

Record important actions for accountability using the format:

```
User → Action → Record Affected → Date/Time
```

Log at minimum:

- Work order creation
- Personnel assignment by supervisor
- Work status changes by field personnel
- Materials recorded as used
- Supervisor approval of a work order
- Asset information changes
- User account or role modifications

---

## Data Relationships

```
Users → Roles & Permissions
Work Orders → Assigned Personnel
Work Orders → Assets
Work Orders → Materials Used
Work Orders → Field Updates
Work Orders → Completion Records
Assets → Maintenance History
Completed Work → Dashboard & Reports
```

Design the database schema and APIs so these relationships are enforced with foreign keys and referential integrity.

---

## Implementation Guidelines for AI

- Follow the workflow, statuses, and priority levels exactly as defined above.
- Allow only valid status transitions. Field personnel move work through `Assigned → In Progress → For Review`. Only supervisors can approve, return, or close.
- Returning a work order for correction sends it back to the field personnel with a notification.
- Deduct inventory only through work-order-linked material usage records.
- Write an activity log entry for every action listed under Activity Logs.
- Keep the Field Personnel interface simple and task-focused.
- Do not add GIS or mapping features, and do not add modules beyond those described unless asked.

## Overall Concept

```
Work Request → Work Order → Supervisor Assignment → Field Operation
  → Asset & Material Usage → Completion & Approval
  → Maintenance History → Operational Reports
```

The system gives DASURECO a structured way to coordinate field activities, monitor work-order progress, maintain asset and maintenance records, track materials used, and provide role-specific dashboards.
