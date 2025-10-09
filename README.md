# 💰 FinovateX - Personal Finance Tracker

**License:** MIT  
**Technologies:** PHP, MySQL, HTML, CSS, JavaScript, Chart.js  
**UI:** Fully Responsive (Light/Dark Mode)

---

## 📋 Project Overview

**FinovateX** is a full-stack web application for **personal finance management**.  
It allows users to track income and expenses, view analytics, upload receipts, generate financial reports, and calculate savings schemes.  
The app includes secure authentication, modern UI, and dynamic charts for visual insights.

---

## ✨ Key Features

- **🔐 Authentication:** Secure signup/login with password hashing and OTP placeholder for “Forgot Password”.
- **📊 Dashboard:** Real-time income, expense, and balance display.
- **💵 Transaction Tracking:** Add, edit, and view categorized income and expenses.
- **📈 History & Analytics:** Interactive tables and pie charts (Chart.js) for category-wise insights.
- **📤 Report Downloads:** Export data as **CSV**, **JSON**, or **PDF** (filtered by date).
- **🧾 Receipt Management:** Drag-and-drop upload for receipts (stored as BLOBs).
- **👤 Profile Management:** Update user details and profile picture.
- **💰 Savings Calculator:** Compute Simple/Compound Interest for FD, Mutual Funds, and Policies.
- **🎨 UI/UX:** Sidebar navigation, responsive layout, and theme toggle.
- **🛡️ Security:** SQL injection prevention with prepared statements and session control.

---

## 🛠️ Tech Stack

| Layer | Technologies |
|-------|---------------|
| **Backend** | PHP 7+ (MySQLi) |
| **Frontend** | HTML5, CSS3, Vanilla JavaScript |
| **Database** | MySQL (`BACKEND/schema.sql`) |
| **Libraries** | Chart.js (CDN) |
| **Hosting** | InfinityFree (Free Tier) |
| **Version Control** | Git |

---

## 📁 Project Structure
FinovateX/
│
├── BACKEND/
│   ├── config.php                # Database connection & helper functions
│   ├── handle_login.php          # Handles user login
│   ├── handle_signup.php         # Handles user registration
│   ├── handle_dashboard.php      # Fetches dashboard data
│   ├── handle_add.php            # Adds new income/expense entry
│   ├── handle_download.php       # Exports CSV/JSON/PDF
│   ├── handle_receipt.php        # Uploads and manages receipts
│   ├── handle_profile.php        # Fetches user details
│   ├── handle_profileupdate.php  # Updates user profile
│   └── schema.sql                # MySQL schema for all tables
│
├── FRONTEND/
│   ├── IMAGES/
│   │   ├── logo.png
│   │   ├── google.png
│   │   ├── apple.png
│   │   ├── facebook.png
│   │   └── uploads/              # Stores uploaded profile pictures
│   │
│   └── PAGES/
│       ├── index.html               # Landing Page
│       ├── 2login.html              # Login Page
│       ├── 3signup.html             # Signup Page
│       ├── 4dashboard.html          # Dashboard Overview
│       ├── 5Add.html                # Add Transaction Menu
│       ├── 6expense.html            # Add Expense
│       ├── 7income.html             # Add Income
│       ├── 8password.html           # Change Password
│       ├── 9fpassword.html          # Forgot Password
│       ├── 10profile.html           # User Profile
│       ├── 11profileupdate.html     # Update Profile Info
│       ├── 12DownloadExpenses.html  # Export Data Page
│       ├── 13histroy.html           # Transaction History with Charts
│       ├── 14RecieptScanner.html    # Receipt Upload/Viewer
│       └── 15Savingschemes.html     # Savings Calculator
│
├── style.css                     # Default Light Mode Styles
├── darkmode.css                  # Dark Mode Styles
├── script.js                     # JS for navigation, theme, and charts
└── README.md                     # Project Documentation

---

## 🚀 Quick Start (Local Setup)

### Prerequisites
- PHP 7+ (XAMPP/WAMP/MAMP)
- MySQL Server
- Modern browser (Chrome/Firefox)

### Setup Steps

1. **Clone the Repository**
   ```bash
   git clone https://github.com/Talaviya-Sarthak/Expense_Tracker.git
   cd Expense_Tracker
Database Setup

Create a MySQL database, e.g. finovatex_db

Update credentials in BACKEND/config.php:

php
Copy code
$host = "localhost";
$user = "root";
$pass = "";
$db   = "finovatex_db";
Import BACKEND/schema.sql via phpMyAdmin or MySQL CLI.

File Permissions

bash
Copy code
mkdir -p FRONTEND/IMAGES/uploads
chmod 755 FRONTEND/IMAGES/uploads
Run Locally

Start XAMPP/WAMP server.

Open in browser:

bash
Copy code
http://localhost/Expense_Tracker/FRONTEND/PAGES/index.html
🧭 Usage Guide
Feature	Page
Signup / Login	3signup.html → 8password.html
Dashboard	4dashboard.html
Add Transactions	5Add.html → 6expense.html or 7income.html
History & Charts	13histroy.html
Download Reports	12DownloadExpenses.html
Receipt Uploads	14RecieptScanner.html
Profile	10profile.html, 11profileupdate.html
Savings Calculator	15Savingschemes.html
Theme Toggle	Header switch (Light/Dark)

⚠️ Known Issues & Limitations
OTP in “Forgot Password” is placeholder (123456)

PDF exports are minimal

Receipts stored as BLOBs (not ideal for large uploads)

Limited client-side validation

Chart.js requires internet access (via CDN)

Further security hardening advised for production

🤝 Contributing
Fork the repository

Create a feature branch

bash
Copy code
git checkout -b feature/awesome-feature
Commit changes

bash
Copy code
git commit -m "Add awesome feature"
Push and create a Pull Request

📄 License
MIT License © Sarthak Talaviya

📞 Contact
Developer: Talaviya Sarthak
Email: support@finovatex.com
GitHub: Expense_Tracker
Demo: FinovateX Live (coming soon)


