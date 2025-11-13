# FinovateX – Personal Finance Tracker

FinovateX is a full-stack web application that helps individuals understand and manage their personal finances.  
It combines secure authentication, rich analytics, and exportable reports in a modern, mobile-friendly interface.

---

## Table of Contents

1. [Features](#features)
2. [Architecture](#architecture)
3. [Project Structure](#project-structure)
4. [Getting Started](#getting-started)
5. [Usage Guide](#usage-guide)
6. [Tech Stack](#tech-stack)
7. [Known Limitations](#known-limitations)
8. [Contributing](#contributing)
9. [License](#license)
10. [Contact](#contact)

---

## Features

- **🔐 Authentication:** Secure signup/login with password hashing and session-based access control.  
- **📊 Dashboard:** Real-time overview of income, expenses, and current balance.  
- **💵 Transaction Management:** Add categorized income and expense records, including quantities and descriptions.  
- **📈 History & Analytics:** Interactive tables and Chart.js visualizations (pie, bar, line) for category and time-based insights.  
- **📂 Report Download:** Export transaction history as CSV (PDF export provided through a lightweight helper).  
- **🧾 Receipt Storage:** Drag-and-drop uploads saved to the database for quick reference.  
- **👤 Profile Center:** View and update personal details, including profile image support.  
- **💰 Savings Calculator:** Compare Simple and Compound interest for fixed deposits, mutual funds, or policy-based savings.  
- **🎨 Responsive UI:** Sidebar navigation, adaptive layouts, and (optional) light/dark themes.  
- **🛡️ Security Practices:** Prepared statements to prevent SQL injection, session validation, and defensive backend responses.

---

## Architecture

| Layer      | Responsibilities                                              |
| ---------- | ------------------------------------------------------------- |
| Frontend   | Static HTML pages, modular CSS, vanilla JavaScript for UX     |
| Backend    | PHP 7+ routes (`BACKEND`) handle auth, CRUD, exports, uploads |
| Database   | MySQL schema (`BACKEND/database/schema.sql`) for persistence  |

External libraries are intentionally minimal: Chart.js (via CDN) renders analytics, keeping deployment simple on shared hosting (e.g., InfinityFree).

---

## Project Structure

```
PROJECT/
├── BACKEND/
│   ├── config.php                 # Connection + helper utilities
│   ├── handle_login.php           # Authentication (login)
│   ├── handle_signup.php          # Authentication (signup)
│   ├── handle_dashboard.php       # Aggregate totals for dashboard
│   ├── handle_history.php         # Insert new income/expense entry
│   ├── handle_download.php        # CSV / PDF export logic
│   ├── handle_receipt_upload.php  # Receipt upload processing
│   ├── handle_profile.php         # Fetch profile details (JSON)
│   ├── handle_profile_update.php  # Persist profile edits
│   ├── handle_income.php          # Dedicated income insert endpoint
│   ├── handle_expense.php         # Dedicated expense insert endpoint
│   └── database/
│       └── schema.sql             # MySQL DDL for all tables
│
├── FRONTEND/
│   ├── IMAGES/                    # Logos, social icons, uploaded receipts
│   ├── PAGES/                     # All UI screens (HTML)
│   │   ├── 2login.html            # Login
│   │   ├── 3signup.html           # Signup
│   │   ├── 4dashboard.html        # Dashboard overview
│   │   ├── 5Add.html              # Entry type selector
│   │   ├── 6expense.html          # Expense form
│   │   ├── 7income.html           # Income form
│   │   ├── 8password.html         # Change password
│   │   ├── 9fpassword.html        # Forgot password (OTP placeholder)
│   │   ├── 10profile.html         # Profile view
│   │   ├── 11profileupdate.html   # Profile edit form
│   │   ├── 12DownloadExpenses.html# Export history
│   │   ├── 13histroy.html         # History table + charts
│   │   ├── 14RecieptScanner.html  # Receipt uploader
│   │   └── 15Savingschemes.html   # Savings calculator
│   ├── style.css                  # Base theme
│   ├── darkmode.css               # Optional dark theme
│   └── script.js                  # Shared JS (navigation, charts, fetchers)
│
└── README.md                      # Project documentation
```

---

## Getting Started

### Prerequisites

- PHP 7.4+ (XAMPP/WAMP/MAMP)
- MySQL Server
- Modern browser (Chrome, Edge, Firefox, Safari)

### Installation

```bash
# Clone the repository
git clone https://github.com/Talaviya-Sarthak/Expense_Tracker.git
cd Expense_Tracker
```

1. **Create the database** – e.g., `finovatex_db`.
2. **Configure credentials** – update `BACKEND/config.php`.
   ```php
   $DB_HOST = "localhost";
   $DB_USER = "root";
   $DB_PASS = "";
   $DB_NAME = "finovatex_db";
   ```
3. **Import schema** – run `BACKEND/database/schema.sql` in phpMyAdmin or MySQL CLI.  
4. **Ensure uploads directory exists** – `FRONTEND/IMAGES/uploads` should be writable.
5. **Run locally** – start Apache/MySQL (XAMPP, etc.) and open:
   ```
   http://localhost/Expense_Tracker/FRONTEND/PAGES/2login.html
   ```

---

## Usage Guide

| Workflow                | Pages Involved                                            |
| ----------------------- | --------------------------------------------------------- |
| Create account          | `3signup.html → 8password.html`                           |
| Sign in                 | `2login.html`                                             |
| View dashboard          | `4dashboard.html`                                         |
| Add transactions        | `5Add.html → 6expense.html / 7income.html`                |
| Review history          | `13histroy.html`                                          |
| Download reports        | `12DownloadExpenses.html`                                 |
| Upload receipts         | `14RecieptScanner.html`                                   |
| Manage profile          | `10profile.html`, `11profileupdate.html`                  |
| Calculate savings       | `15Savingschemes.html`                                    |

The `script.js` file coordinates navigation, lazy loads profile data, and calls backend endpoints using `fetch` with credentials.

---

## Tech Stack

- **Frontend:** HTML5, CSS3, Vanilla JavaScript, Chart.js
- **Backend:** PHP (MySQLi, sessions, prepared statements)
- **Database:** MySQL
- **Hosting-ready:** Tested with InfinityFree (free tier)
- **Version Control:** Git

---

## Known Limitations

- Forgot-password flow uses a static OTP placeholder (`123456`) – needs a proper mail/SMS provider.
- PDF export leverages a lightweight helper; richer formatting would require a library like TCPDF.
- Receipts stored as BLOBs – move to object storage/CDN for large-scale deployments.
- Client-side validation is minimal; consider adding form-level constraints and feedback.
- Chart.js is loaded via CDN; offline deployments should self-host assets.
- Additional hardening (rate limiting, CSRF protection, CSP headers) recommended for production.

---

## Contributing

1. Fork the repository
2. Create a feature branch:
   ```bash
   git checkout -b feature/awesome-feature
   ```
3. Commit your changes:
   ```bash
   git commit -m "Add awesome feature"
   ```
4. Push and open a pull request.

Please include screenshots or GIFs for UI changes and describe testing steps taken.

---

## License

This project is released under the [MIT License](LICENSE).

---

## Contact

- **Developer:** Sarthak Talaviya  
- **Email:** [support@finovatex.com](mailto:support@finovatex.com)  
- **GitHub:** [Talaviya-Sarthak](https://github.com/Talaviya-Sarthak)  
- **Project Repo:** [Expense_Tracker](https://github.com/Talaviya-Sarthak/Expense_Tracker)

Feel free to reach out for collaboration, bug reports, or feature requests!

#   E x p e n s e _ T r a c k e r  
 