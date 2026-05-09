![StayEase](assets/images/logo.png)

A modern hotel discovery and booking platform. Search hotels, explore rooms, and manage your stays.

---

## Overview

StayEase is a full-stack web application built with plain HTML, CSS, and PHP. Users can register, search hotels by city, browse rooms, make reservations, and leave reviews. Built as a university team project by 6 members, covering real-world concepts: authentication, sessions, CRUD operations, relational databases, and multi-table JOINs.

---

## Features

- User registration and login with secure password hashing
- Search hotels by name/city/location with dynamic paginated results, and filtering/sorting features
- Hotel detail pages with room listings and average review rating
- Room detail pages with full description and pricing
- Booking system with date validation and overlap checks
- User dashboard with booking history and cancellation
- Hotel reviews with rating system (one review per user per hotel)

---

## Tech Stack

| Layer    | Technology          |
|----------|---------------------|
| Frontend | HTML5, CSS3         |
| Backend  | PHP (no frameworks) |
| Database | MySQL (PDO)         |
| Icons    | Font Awesome 6      |
| Font     | Plus Jakarta Sans   |

---

## Project Structure

```
stayease/
├── assets/images/        # Hotel and room images
├── css/                  # One CSS file per member
├── database/
│   └── schema.sql        # Database schema
├── js/                   # JavaScript files
├── php/                  # PHP logic and shared config
│   └── config.php        # DB connection (host, name, user, password)
└── *.html / *.php        # Page files
```

---

## Getting Started

### Requirements

- [XAMPP](https://www.apachefriends.org/) (includes Apache, MySQL, PHP)

### Setup

1. **Clone the repo** into your XAMPP `htdocs` folder:
   ```
   git clone <repo-url> C:\xampp\htdocs\StayEase-Web
   ```

2. **Start XAMPP** and turn on the **Apache** and **MySQL** modules.

3. **Create the database:**
   - Open [phpMyAdmin](http://localhost/phpmyadmin)
   - Create a new database named `stayease_db`
   - Select it, go to the **SQL** tab, paste the contents of `database/schema.sql`, and run it

4. **Check the DB credentials** in [php/config.php](php/config.php):
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'stayease_db');
   define('DB_USER', 'root');
   define('DB_PASS', '');  // default XAMPP has no password
   ```
   Update these if your MySQL setup differs.

5. **Open the app** in your browser:
   ```
   http://localhost/StayEase-Web/
   ```

---

## Team

| Name            | Responsibility              |
|-----------------|-----------------------------|
| Anas Mohamed    | Authentication and DB setup |
| Mohamed Gamil   | Homepage and hotel search   |
| Tarek Elsayed   | Hotel detail and room pages |
| Mohsen Mohamed  | Booking flow                |
| Yassin Abdullah | User dashboard              |
| Ahmed Tarig     | Reviews and about page      |

---

<p align="center">Built with care by the StayEase team</p>
