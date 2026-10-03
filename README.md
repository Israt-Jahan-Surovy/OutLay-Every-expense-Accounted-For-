# Outlay – Every Expense, Accounted For

**Outlay** is a web-based expense tracking and budget management system developed as an academic project for **CSC 3215: Web Technologies**. The system provides a centralized platform for managing work-related expenses, hierarchical budget allocations, post-expense approvals, system-wide category reporting graphs, and real-time notifications across an organization.

---

##  Project Overview

Outlay is designed around a structured **Admin – Manager – Employee** workflow. It allows employees to submit work-related expenses, managers to review team requests, and administrators to oversee system users, categories, budgets, reports, and manager personal expenses.

The system features dynamic notifications to keep users updated on expense request submissions, approval decisions, rejection reasons, and budget assignments/allocations. It also provides visual category reports (pie chart & percentage breakdowns) for expense tracking.

---

##  Objectives

- Manage work-related expenses digitally within a centralized web platform.
- Provide strict Role-Based Access Control (RBAC) for Admin, Manager, and Employee roles.
- Allow employees to submit, edit, and track personal work expenses.
- Enable managers to review, approve, or reject employee expenses with rejection feedback.
- Enable administrators to review, approve, or reject manager personal expenses.
- Implement hierarchical budget management (Admin → Manager → Employee).
- Provide visual category expense reports and pie chart analytics across custom date ranges.
- Provide role-specific notifications for expense requests, approvals, rejections, budget assignments, and allocations.
- Track spending against pre-assigned budgets and monitor remaining balances in real time.

---

##  User Roles & Features

###  Admin
The Admin has system-wide oversight and management privileges.
* **User Management:** Create, update, view, activate/deactivate, and delete Manager and Employee accounts.
* **Category Management:** Create, edit, and delete expense categories.
* **Expense Management:** View system-wide expenses, search/filter submissions, and monitor expense statuses.
* **System-Wide Reports & Analytics:** Generate category breakdown reports with pie charts for all users across the entire system using custom date range filters.
* **Manager Expense Approvals:** Review, approve, or reject personal expenses submitted by Managers (with rejection reasons).
* **Budget Assignment:** Assign monthly/team budgets to Managers and modify allocations when necessary.
* **Notifications:**
  * Receive alerts when Managers submit personal expense requests.
  * Receive system-level budget alerts and updates.
* **Account Control:** Manage personal profile, change password, and handle password resets.

###  Manager
The Manager oversees team spending, employee approvals, and team budget allocations.
* **Expense Review:** Review submitted employee expense requests, inspect details, and approve or reject them with feedback.
* **Team Budget Allocation:** Allocate portions of the team budget to assigned employees and adjust allocations as needed.
* **Team Category Reports:** View category percentage breakdowns and spending pie charts specifically for team expenses.
* **Personal Expense Submission:** Submit personal work-related expenses (reviewed and approved directly by Admin).
* **Manage Personal Expenses:** View personal expense submission history and edit or delete pending personal expense requests.
* **Spending Oversight:** Track team spending, monitor employee budget usage, and view remaining balances.
* **Notifications:**
  * Receive notifications when Employees submit new expense requests for review.
  * Receive notifications when Admin assigns or updates their team budget.
  * Receive notifications when Admin approves or rejects their personal expenses (with rejection reasons).
* **Account Control:** Manage profile, change password, and reset password.

###  Employee
The Employee manages personal work-related expense submissions and tracks assigned budgets.
* **Submit Expenses:** Submit work-related expenses with title, category, date, amount, and description.
* **Track Expenses:** View personal expense history and monitor request statuses (`Pending`, `Approved`, `Rejected`).
* **Manage Pending Requests:** Edit or delete pending expense submissions before manager review.
* **Budget Tracking:** View assigned monthly budget, monitor total spent, and check remaining available balance.
* **Notifications:**
  * Receive notifications when a Manager allocates or updates their monthly budget.
  * Receive notifications when a Manager approves or rejects their submitted expense request (including rejection reasons).
* **Account Control:** Manage profile, change password, and reset password.

---

##  Category Expense Reports & Analytics

The system features integrated reporting modules:

* **System-Wide Scope (Admin):** Aggregates approved expenses for all users in the system.
* **Date Filtering:** Select start date (`From`) and end date (`To`) to generate filtered analytics.
* **Pie Chart & Percentage Breakdown:** Dynamically visualizes expense proportions across categories (e.g., Food: 30%, Transport: 40%, Office: 30%).

---

##  Budget & Expense Flow

```text
                                  BUDGET FLOW

                               ┌────────────────┐
                               │     Admin      │
                               └───────┬────────┘
                                       │
                                Assigns Budget
                                       │
                                       ▼
                               ┌────────────────┐
                               │    Manager     │
                               └───────┬────────┘
                                       │
                               Allocates Budget
                                       │
                                       ▼
                               ┌────────────────┐
                               │    Employee    │
                               └────────────────┘

                                 EXPENSE FLOW

  1. Employee Personal Expense               2. Manager Personal Expense

        ┌────────────────┐                          ┌────────────────┐
        │    Employee    │                          │    Manager     │
        └───────┬────────┘                          └───────┬────────┘
                │                                           │
         Submits Expense                             Submits Expense
                │                                           │
                ▼                                           ▼
        ┌────────────────┐                          ┌────────────────┐
        │    Manager     │                          │     Admin      │
        └───────┬────────┘                          └───────┬────────┘
                │                                           │
             Reviews                                     Reviews
                │                                           │
   ┌────────────┴────────────┐                 ┌─────────────┴─────────────┐
   ▼                         ▼                 ▼                           ▼
Approve                   Reject            Approve                     Reject
   │                         │                 │                           │
   ▼                         ▼                 ▼                           ▼
Status Updated       Rejection Reason   Status Updated             Rejection Reason
                         Saved                                         Saved
```
---
##  System Architecture

The project follows an **MVC (Model-View-Controller)** architectural pattern:

```text
┌────────────────────────────────────────────────────────┐
│                        VIEW                            │
│           HTML5 / CSS3 / JavaScript (UI)               │
└───────────────────────────┬────────────────────────────┘
                            │
                            ▼
┌────────────────────────────────────────────────────────┐
│                     CONTROLLER                         │
│       Handles HTTP Requests & Business Logic           │
└───────────────────────────┬────────────────────────────┘
                            │
                            ▼
┌────────────────────────────────────────────────────────┐
│                        MODEL                           │
│        Database Operations & SQL Prepared Stmts        │
└───────────────────────────┬────────────────────────────┘
                            │
                            ▼
┌────────────────────────────────────────────────────────┐
│                   MySQL DATABASE                       │
└────────────────────────────────────────────────────────┘
```
---
### MVC Responsibilities

| Component | Responsibility |
| --- | --- |
| **View** | Displays the user interface |
| **Controller** | Handles requests and application flow |
| **Model** | Handles database operations |
| **Database** | Stores users, expenses, budgets, approvals, categories, etc. |

---
## Authentication & Security

- **Session Management:** Enforces role-based route protection across all user sessions.
- **SQL Injection Prevention:** Uses Prepared SQL Statements (`mysqli_prepare`) for database transactions.
- **XSS Prevention:** Escapes HTML outputs using `htmlspecialchars()`.
- **Password Hashing:** Uses `password_hash()` with `PASSWORD_DEFAULT` for secure password storage and `password_verify()` for login validation.

---

##  Technologies Used

- **Frontend:** HTML5, CSS3, JavaScript (ES6 / Fetch API / Chart.js)
- **Backend:** PHP 8.x (Procedural / MVC approach, MySQLi)
- **Database:** MySQL / MariaDB
- **Development Environment:** XAMPP / WampServer, Apache, phpMyAdmin, VS Code
---

## Installation & Setup

1. **Install XAMPP / WampServer:** Start the **Apache** and **MySQL** modules.

2. **Copy Project Directory:** Place the project folder into your web server root:
   - XAMPP: `xampp/htdocs/Outlay-Expense-Tracker/`
   - WampServer: `wamp64/www/Outlay-Expense-Tracker/`

3. **Import Database:**
   - Open **phpMyAdmin** (`http://localhost/phpmyadmin`).
   - Create a new database named `expense`.
   - Click **Import** and select the database export file: `database/expense.sql`.

4. **Verify Database Connection:** Open `models/dbConnect.php` and verify your credentials:

   ```php
   $serverName = "localhost";
   $userName   = "root";
   $password   = "";
   $db         = "expense";
   ```
5. **Before running make sure to start Apache and MySQL from Apache server.**
6. **Run the Application:** Open your web browser and navigate to:

   ```text
   http://localhost/Outlay-Expense-Tracker/
   ```

---

##  Academic Project Details

- **Course:** CSC 3215 – Web Technologies
- **Department:** Computer Science and Engineering
- **Institution:** American International University-Bangladesh (AIUB)
- **Faculty:** Md. Khairul Alam Mazumder
- **Semester:** Summer 2025–26 

### Team Members

| # | Name | ID | Role |
| --- | --- | --- | --- |
| 1 | **Maisha Mahjabin** | 23-54978-3 | Group Leader |
| 2 | **Israt Jahan Surovy** | 23-54972-3 | Member |
| 3 | **H.M. Taiyeb Ahsan Tuhin** | 23-51066-1 | Member |
| 4 | **Md. Muhaiminul Islam** | 24-56611-1 | Member |

---

##  License & Usage

This software was developed strictly for academic evaluation in the CSC 3215 course at AIUB.


