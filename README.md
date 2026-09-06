# QR Code-Based IT Asset Tracker

A web-based IT Asset Management System developed to improve the tracking, borrowing, returning, maintenance, and auditing of IT assets using QR code technology.

## 📌 Project Overview

Managing IT assets manually can make it difficult to keep accurate records of asset ownership, borrowing history, returns, maintenance activities, and asset status.

This project provides a centralized system for managing IT assets throughout their lifecycle. It allows administrators to register and manage assets, generate unique QR codes, monitor borrowing and returns, manage users, track penalties, and generate reports.

Users can scan QR codes to quickly identify assets and facilitate the borrowing and returning process.

## 🎯 Objectives

- Automate the management of IT assets.
- Improve the accuracy and accessibility of asset records.
- Simplify the borrowing and returning of IT assets.
- Use QR codes for quick asset identification.
- Track asset maintenance and lifecycle information.
- Improve accountability through user and transaction records.
- Provide administrators with reports and useful asset information.
- Send email notifications for borrowing, returns, and overdue assets.

## ✨ Key Features

### Asset Management
- Register new IT assets.
- Generate unique asset IDs.
- Generate QR codes for assets.
- View and update asset information.
- Track asset status and availability.
- Record maintenance activities.
- Mark assets as disposed.

### QR Code Scanning
- Generate unique QR codes for registered assets.
- Scan asset QR codes using a device camera.
- Retrieve asset information through QR codes.
- Support faster asset identification during borrowing and returning.

### Borrowing & Returning
- Allow users to request available assets.
- Record asset borrowing transactions.
- Track expected return dates.
- Process asset returns.
- Maintain borrowing history.

### User Management
- User registration and authentication.
- Role-based access for administrators and users.
- Manage user information.
- Manage user departments.

### Penalty Management
- Track overdue assets.
- Record applicable penalties.
- Manage penalty information.

### Email Notifications
The system supports email notifications for:
- Borrowing confirmations
- Return confirmations
- Upcoming return reminders
- Overdue assets
- Penalty notifications

### Reporting
Administrators can access information relating to:
- Assets
- Borrowing transactions
- Returns
- Users
- Penalties
- Asset status

## 🛠️ Technologies Used

| Technology | Purpose |
|------------|---------|
| PHP | Backend development |
| MySQL/MariaDB | Database management |
| HTML5 | Web page structure |
| CSS3 | Interface styling |
| JavaScript | Client-side functionality |
| XAMPP | Local development environment |
| PHPMailer | Email notifications |
| html5-qrcode | QR code scanning |
| Git & GitHub | Version control and source code management |

## 🏗️ System Structure

```text
asset_tracker_system/
│
├── config/
│   ├── Database configuration
│   ├── Email configuration
│   ├── QR code functionality
│   └── Asset image handling
│
├── cron/
│   └── Automated notification processes
│
├── phpmailer/
│   └── PHPMailer library
│
├── public/
│   ├── admin/
│   │   ├── Asset management
│   │   ├── User management
│   │   ├── Borrow management
│   │   ├── Penalty management
│   │   └── Reports
│   │
│   └── user/
│       ├── Dashboard
│       ├── Asset borrowing
│       ├── Asset returns
│       └── Penalties
│
├── services/
│   ├── Asset borrowing service
│   ├── Asset return service
│   ├── Email service
│   └── Event service
│
├── sql/
│   └── Database schema
│
├── templates/
│   └── Email templates
│
└── regenerate_qr.php


🔄 System Workflow
User/Admin Login
       ↓
Authentication
       ↓
   Dashboard
       ↓
 ┌───────────────┐
 │               │
Asset Management  Borrow/Return
 │               │
 ↓               ↓
QR Code        Transaction
Generation      Records
 │               │
 └───────┬───────┘
         ↓
   Asset Tracking
         ↓
 Notifications
         ↓
    Reporting
🔐 Security Considerations

The system incorporates several security measures, including:

User authentication.
Role-based access control.
Password protection.
Restricted access to administrative functionality.
Input validation.
Database queries using MySQLi.
Separation of configuration and application functionality.
Sensitive environment variables excluded from version control.

Note: Sensitive configuration files such as .env are intentionally excluded from this repository.

💻 Local Installation
Prerequisites

Before running the project, install:

XAMPP
PHP 8.2 or later
MySQL/MariaDB
A modern web browser
Git (optional)
Setup
Clone the repository:
git clone https://github.com/Msngugi/asset-tracker-system.git
Move the project into your XAMPP htdocs directory:
C:\xampp\htdocs\asset_tracker_system
Start Apache and MySQL using XAMPP.
Create a database using phpMyAdmin.
Import the database schema located at:
sql/asset_tracker_schema.sql
Configure the database connection using your local environment configuration.
Configure email settings if email notifications are required.
Open the application through your local XAMPP server.
📂 Database

The database contains tables responsible for managing:

Users
Departments
Assets
Asset categories
QR codes
Borrowing transactions
Penalties
Asset images
Email logs

The database schema is available in:

sql/asset_tracker_schema.sql
🚀 Future Improvements

Possible future improvements include:

Deploying the system to a cloud server.
Developing a mobile application.
Adding advanced analytics dashboards.
Implementing more detailed audit trails.
Adding automated database backups.
Improving notification and reminder automation.
Adding more granular user permissions.
👩‍💻 Author

Wairimu Ngugi

Bachelor of Business Information Technology (BBIT)

Developed as an IT Asset Management System project using PHP, MySQL/MariaDB, JavaScript, HTML, CSS, and QR code technology.

📄 License

This project was developed for academic and portfolio purposes.


### But don't paste it yet

I want us to make this **accurate to the system you actually built**, rather than putting impressive-sounding things in your README that your code doesn't actually do.

In particular, we'll eventually verify things like:

- whether `html5-qrcode` is actually loaded externally or stored locally
- exactly how authentication works
- the database name/setup
- whether maintenance is actually implemented
- what reports are available
- which security controls are actually present
- how the email/cron functionality works

That will make the README much stronger in an interview because you can confidently explain everything in it.

**For this step, create the empty `README.md` file in your project folder first.** Once you've created it, tell me, and we'll put the content in and then commit/push it to GitHub.