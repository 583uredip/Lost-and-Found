<div align="center">

<img src="https://img.shields.io/badge/AIUB-Lost%20%26%20Found-blue?style=for-the-badge&logo=searchengin&logoColor=white" alt="AIUB Lost & Found" />

# 🔍 AIUB Lost & Found

### A smart web platform for American International University–Bangladesh
### that automatically reunites students & staff with their lost belongings.

<br/>

[![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?style=flat-square&logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-5.7+-4479A1?style=flat-square&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5-7952B3?style=flat-square&logo=bootstrap&logoColor=white)](https://getbootstrap.com/)
[![PHPMailer](https://img.shields.io/badge/PHPMailer-Included-red?style=flat-square&logo=gmail&logoColor=white)](https://github.com/PHPMailer/PHPMailer)
[![License](https://img.shields.io/badge/License-MIT-green?style=flat-square)](LICENSE)

</div>

---

## 📖 About the Project

**AIUB Lost & Found** is a full-stack PHP + MySQL web application designed specifically for the AIUB campus community. Students and staff can report lost or found items, and the system's built-in **smart matching engine** automatically cross-compares new reports against existing ones using multiple criteria — category, keywords, color, brand, location, and date proximity — scoring similarity from **0 to 100%**.

When a match crosses the threshold, **both parties are instantly notified** via email and an in-app notification, maximising the chance of reuniting people with their belongings.

---

## ✨ Features

| Feature | Description |
|---|---|
| 🔐 **Secure Auth** | User registration & login with `password_hash` / `password_verify` |
| 📋 **Report Lost** | Submit a lost item with photo, category, color, brand, location & date |
| 📋 **Report Found** | Submit a found item with the same rich details |
| 🤖 **Auto Matching** | Scores similarity 0–100% and auto-creates a match above the threshold |
| 📧 **Email Alerts** | Instant email notification via PHPMailer when a match is found |
| 🔔 **In-App Notifications** | Real-time notification bell with unread count |
| 🔎 **Browse & Search** | Filter lost/found listings by keyword or category |
| 📊 **Personal Dashboard** | Track your own reports and mark items as resolved |
| 🛡️ **Admin Panel** | Manage users, items, and matches from a dedicated admin area |
| 📱 **Responsive UI** | Mobile-friendly design built with Bootstrap 5 |

---

## 🧠 How the Matching Algorithm Works

The core of the platform is `includes/matching.php` → `calculate_match_score()`.
Every time a new lost or found item is submitted, the engine scores it against **all existing open reports** using this weighted rubric:

```
┌─────────────────────────────────────┬────────┐
│ Criterion                           │ Points │
├─────────────────────────────────────┼────────┤
│ Category match                      │  30 pt │
│ Item name / description overlap     │  30 pt │
│ Color match                         │  10 pt │
│ Brand match                         │  10 pt │
│ Location similarity (word overlap)  │  10 pt │
│ Date logic (found >= lost, <=30 d)  │  10 pt │
└─────────────────────────────────────┴────────┘
                              Total → 100 pt
```

A match record is created and notifications are sent when the score >= **MATCH_THRESHOLD** (default: **55%**, configurable in `config/database.php`).

---

## 🗂️ Project Structure

```
aiub-lost-and-found/
│
├── 📁 admin/
│   ├── dashboard.php          # Admin dashboard (users, items, matches)
│   ├── add-item.php           # Admin: manually add an item
│   ├── add-user.php           # Admin: manually add a user
│   ├── login.php              # Admin login
│   └── auth.php               # Admin session guard
│
├── 📁 auth/
│   ├── login.php              # User login
│   ├── register.php           # User registration
│   └── logout.php             # Session destroy
│
├── 📁 config/
│   └── database.php           # DB connection, SITE_URL, MATCH_THRESHOLD
│
├── 📁 includes/
│   ├── header.php             # Global nav/header
│   ├── footer.php             # Global footer
│   ├── functions.php          # Helper functions + email sender
│   ├── matching.php           # Smart matching engine
│   └── PHPMailer/             # PHPMailer library (bundled)
│
├── 📁 assets/
│   ├── css/style.css          # Site-wide styles
│   └── js/script.js           # Image preview + form validation
│
├── 📁 uploads/
│   ├── lost/                  # Uploaded photos for lost items
│   └── found/                 # Uploaded photos for found items
│
├── index.php                  # Homepage
├── report-lost.php            # Report a lost item
├── report-found.php           # Report a found item
├── browse-lost.php            # Browse lost item listings
├── browse-found.php           # Browse found item listings
├── item-details.php           # Item detail + matches + contact info
├── dashboard.php              # User's personal dashboard
├── notifications.php          # Full notification history
├── database.sql               # Database schema (import this first!)
└── test-email.php             # Email config tester
```

---

## 🗃️ Database Schema

The application uses **4 tables**:

```
users           → id, full_name, email, phone, student_id, password
lost_items      → id, user_id, item_name, category, description, color, brand, location, date_lost, image, status
found_items     → id, user_id, item_name, category, description, color, brand, location, date_found, image, status
matches         → id, lost_item_id, found_item_id, match_score, status
notifications   → id, user_id, title, message, link, is_read
```

---

## 🚀 Installation & Setup

### Prerequisites

- **PHP** 8.0+ with PDO MySQL extension
- **MySQL** 5.7+ or MariaDB
- A local server stack: [XAMPP](https://www.apachefriends.org/), [WAMP](https://www.wampserver.com/), [Laragon](https://laragon.org/), or any LAMP/LEMP hosting

### Step-by-Step

**1. Clone or download the repository**

```bash
git clone https://github.com/your-username/aiub-lost-and-found.git
```

Copy the project folder into your server's web root (e.g. `htdocs/aiub-lost-and-found`).

**2. Import the database**

Open **phpMyAdmin** and import `database.sql`. This creates the `aiub_lost_found` database with all required tables.

```bash
# Or via MySQL CLI:
mysql -u root -p < database.sql
```

**3. Configure the connection**

Open `config/database.php` and update with your credentials:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'aiub_lost_found');
define('DB_USER', 'root');
define('DB_PASS', '');                                         // your MySQL password
define('SITE_URL', 'http://localhost/aiub-lost-and-found');    // no trailing slash

define('MATCH_THRESHOLD', 55);                                 // matching sensitivity (0-100)
```

**4. Make the uploads folder writable** *(Linux / macOS)*

```bash
chmod -R 755 uploads/
```

On Windows with XAMPP the folder is writable by default.

**5. Visit the site**

```
http://localhost/aiub-lost-and-found/index.php
```

**6. Register & start using!**

Create an account, report a lost or found item, and let the matching engine do the rest.

---

## 📧 Email Configuration

By default the app uses PHP's built-in `mail()` function *(works on most cPanel / shared hosting)*.

For **Gmail / SMTP** delivery (recommended for local testing), PHPMailer is already **bundled** in `includes/PHPMailer/`. Configure your SMTP credentials inside `includes/functions.php` → `send_email_notification()`:

```php
$mail->isSMTP();
$mail->Host       = 'smtp.gmail.com';
$mail->SMTPAuth   = true;
$mail->Username   = 'your-email@gmail.com';
$mail->Password   = 'your-app-password';   // Gmail App Password (not your login password)
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port       = 587;
```

> **Tip:** Use a [Gmail App Password](https://myaccount.google.com/apppasswords) instead of your real password. Run `test-email.php` to verify SMTP settings before going live.

---

## ⚙️ Tuning the Matching Engine

Open `includes/matching.php` and adjust the point weights inside `calculate_match_score()` to suit your needs. Then update the trigger threshold in `config/database.php`:

```php
define('MATCH_THRESHOLD', 55); // lower = more matches, higher = stricter
```

---

## 🤝 Contributing

Contributions, issues, and feature requests are welcome!

1. Fork the repository
2. Create a new branch: `git checkout -b feature/your-feature-name`
3. Commit your changes: `git commit -m "Add: your feature description"`
4. Push to the branch: `git push origin feature/your-feature-name`
5. Open a **Pull Request**

---

## 👨‍💻 Authors

Built with ❤️ by AIUB students for the AIUB campus community.

---

## 📄 License

This project is licensed under the **MIT License** — feel free to use, modify, and distribute it with attribution.

---

<div align="center">

Made for 🎓 **American International University–Bangladesh (AIUB)**

</div>
