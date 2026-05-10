# Cibo Food Ordering System

Cibo is a PHP and MySQL based food ordering system designed for a XAMPP environment. The project provides a customer-facing food ordering experience, account and order management flows, and an admin area for managing restaurants, menu items, and orders.

## Features

- Customer-facing homepage with restaurant discovery and category browsing
- Restaurant-specific menu pages with food item listings
- Cart, checkout, order placement, and order tracking flows
- User authentication including sign up, login, logout, and account management
- Address management and order history APIs
- Admin dashboard for restaurants, menu items, and order management
- MySQL schema included for local setup and fresh database imports

## Technologies Used

- PHP
- MySQL / MariaDB
- JavaScript
- HTML5
- CSS3
- XAMPP (Apache + MySQL)

## Project Structure

- `index.php` and restaurant pages for the customer UI
- `api/` for frontend-facing PHP API endpoints
- `includes/` for shared backend logic and database access
- `admin/` for the administrative dashboard and APIs
- `database/` for schema and SQL import files
- `images/` for branding, restaurant, category, and food item assets

## Screenshots

Add screenshots here after deployment or local testing.

- Homepage
- Restaurant listing / menu page
- Cart and checkout flow
- Admin dashboard

## Setup Instructions for XAMPP

1. Copy or place this project inside your XAMPP `htdocs` directory.
2. Start `Apache` and `MySQL` from the XAMPP Control Panel.
3. Open `phpMyAdmin` and create or import the database using `database/schema.sql`.
4. Confirm the database credentials in `includes/db.php` match your local XAMPP setup.
   - Default values in this project use host `127.0.0.1`, port `3306`, user `root`, empty password, and database `cibo_db`.
5. Visit the project in your browser:
   - `http://localhost/food-app/`
6. To access the admin area, open:
   - `http://localhost/food-app/admin/`

## Database

The primary schema file is:

- `database/schema.sql`

An additional SQL file is also included:

- `database/cibo_foundation.sql`

## Notes

- This repository is intended for local development in XAMPP.
- If you are pushing to GitHub, update the remote URL to your actual GitHub username in place of `YOURUSERNAME`.
