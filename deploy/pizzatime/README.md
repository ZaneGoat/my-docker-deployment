# Pizzeria Web Application

This repository contains a Flask-based pizzeria management and ordering platform with:

- Admin dashboard for managing menu items, viewing analytics, clients, and orders
- Staff order management and status updates
- Customer menu browsing, checkout flow, and order confirmation
- SQLite database persistence via SQLAlchemy
- File upload support for pizza images

## Project Structure

- `app.py` — Flask application and route definitions
- `models.py` — SQLAlchemy models for users, clients, pizzas, orders, payments, and deliveries
- `seed_menu.py` — Seed script to populate the menu with pizza, dessert, and drink items
- `seed_samples.py` — Seed script to generate sample client orders and delivery data
- `templates/` — HTML templates for customer, admin, staff, and login pages
- `static/` — Front-end CSS, JavaScript, and image assets

## Requirements

Install the Python dependencies listed in `requirements.txt`.

```bash
python -m venv venv
venv\Scripts\activate
python -m pip install -r requirements.txt
```

## Setup

1. Initialize the database and create the default admin user:

```bash
flask --app app init-db
```

2. Seed the menu data:

```bash
python seed_menu.py
```

3. (Optional) Seed sample clients and orders for development:

```bash
python seed_samples.py
```

## Run the Application

Start the Flask development server:

```bash
flask --app app --debug run
```

Then open `http://127.0.0.1:5000/` in your browser.

## Default Credentials

- Username: `admin`
- Password: `admin123`

> Note: The default admin account is created only when the database is initialized via `flask init-db`.

## Notes

- The app uses SQLite at `pizzeria.db` by default.
- Uploaded pizza images are stored in `static/img`.
- The checkout flow supports pickup and delivery orders, with a delivery fee applied for delivery orders under a threshold.
- Staff users can view and update order statuses.

## Recommended Python Version

- Python 3.10 or newer
