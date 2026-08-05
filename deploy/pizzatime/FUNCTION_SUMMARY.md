# Project Function Summary

This summary covers the Python functions and major application routes in the `amin` project.

## Project File Structure

- `app.py` — main Flask application and routes
- `models.py` — SQLAlchemy models and database schema
- `requirements.txt` — Python dependencies
- `README.md` — project overview and setup instructions
- `FUNCTION_SUMMARY.md` — this function and structure summary
- `seed_menu.py` — seed data script for menu items
- `seed_samples.py` — additional sample data seeding script
- `instance/` — configuration and runtime instance files
- `static/`
  - `css/style.css` — styles for the web UI
  - `img/` — image assets
  - `js/` — JavaScript assets
- `templates/`
  - `base.html`
  - `login.html`
  - `admin/` — admin interface templates
  - `customer/` — customer-facing templates
  - `staff/` — staff-facing templates

## app.py

### `load_user(user_id)`
- Flask-Login user loader.
- Retrieves a `User` by primary key and returns it for session authentication.

### `index()`
- Route: `/`
- If authenticated admin, redirects to admin analytics.
- Otherwise loads available pizzas and renders `customer/menu.html`.

### `login()`
- Route: `/login` (GET, POST)
- Handles login form submission.
- Validates username/password against stored password hash.
- Redirects users based on role: admin -> analytics, staff -> staff dashboard, customer -> index.
- On failure, flashes an error message and re-renders `login.html`.

### `logout()`
- Route: `/logout`
- Requires login.
- Logs out the current user and redirects to index.

### `admin_dashboard()`
- Route: `/admin`
- Requires login and admin role.
- Redirects to admin analytics.

### `admin_menu()`
- Route: `/admin/menu` (GET, POST)
- Requires admin role.
- GET: loads all pizzas and toppings, renders `admin/menu.html`.
- POST: creates a new `Pizza` menu item, optionally uploads a photo, saves to database, flashes success.

### `admin_edit_menu(id)`
- Route: `/admin/menu/edit/<int:id>` (GET, POST)
- Requires admin role.
- GET: loads pizza item by id and renders `admin/edit_menu.html`.
- POST: updates pizza fields and optionally updates photo, commits changes, flashes success, redirects back to admin menu.

### `toggle_availability(id)`
- Route: `/admin/pizza/<int:id>/toggle_availability`
- Requires admin role.
- Flips the availability boolean for the pizza and commits.
- Redirects back to admin menu.

### `staff_dashboard()`
- Route: `/staff`
- Requires login with staff or admin role.
- Loads all orders ordered by newest first and renders `staff/dashboard.html`.

### `update_order_status(id)`
- Route: `/staff/order/<int:id>/update_status` (POST)
- Requires staff or admin role.
- Receives JSON payload containing new status.
- Updates the `Commande.status` and commits.
- Returns a JSON confirmation.

### `checkout()`
- Route: `/checkout` (GET, POST)
- GET: renders `customer/checkout.html`.
- POST: processes guest checkout data and cart JSON.
- Creates/upserts `User` and `Client` records for the phone number.
- Creates `Commande`, `OrderItem`, `Paiement`, optional `Cash` record, and optional `PizzaLivraison` record.
- Computes subtotal, delivery fee, and total.
- Commits to the database and redirects to order success.

### `order_success(order_id)`
- Route: `/order_success/<int:order_id>`
- Loads the order and renders `customer/order_success.html`.

### `admin_orders()`
- Route: `/admin/orders`
- Requires admin role.
- Loads all orders and renders `admin/orders.html`.

### `admin_clients()`
- Route: `/admin/clients`
- Requires admin role.
- Loads all clients and renders `admin/clients.html`.

### `admin_analytics()`
- Route: `/admin/analytics`
- Requires admin role.
- Computes analytics metrics:
  - total revenue
  - total orders
  - average order value
  - orders today
  - total pizzas sold
  - total clients
  - recent orders
  - best-selling pizzas
  - last 7 days daily sales
- Renders `admin/analytics.html` with all computed values.

### `init_db()`
- CLI command: `flask init-db`
- Creates database tables using SQLAlchemy.
- Ensures a default admin user exists with the username `admin` and password `admin123`.

## models.py

### `db = SQLAlchemy()`
- Database object used by Flask-SQLAlchemy.

### `User` model
- Inherits `UserMixin` and `db.Model`.
- Fields: `id`, `username`, `password_hash`, `role`.
- Relationship: one-to-one `client_profile` to `Client`.

### `Client` model
- Fields: `id`, `user_id`, `full_name`, `phone`, `address`.
- Relationship: one-to-many `orders` to `Commande`.

### `Pizza` model
- Fields: `id`, `name`, `description`, `category`, `photo_url`, `base_price`, `is_available`.
- Relationship: one-to-many `order_items` to `OrderItem`.

### `Topping` model
- Fields: `id`, `name`, `price`.

### `Commande` model
- Fields: `id`, `client_id`, `status`, `order_type`, `total_price`, `created_at`.
- Relationships: `items` to `OrderItem`, `payment` to `Paiement`, `delivery_info` to `PizzaLivraison`.

### `OrderItem` model
- Fields: `id`, `commande_id`, `pizza_id`, `quantity`, `customizations`.
- Stores order line item details.

### `Paiement` model
- Fields: `id`, `commande_id`, `amount`, `method`, `timestamp`.
- Relationship: one-to-one `cash_details` to `Cash`.

### `Cash` model
- Fields: `id`, `paiement_id`, `amount_received`, `change_given`.

### `PizzaLivraison` model
- Fields: `id`, `commande_id`, `address`, `neighborhood`, `delivery_fee`.

### `Facture` model
- Fields: `id`, `commande_id`, `invoice_number`, `created_at`, `content_html`.

## Data Schema

### `user`
- `id` INTEGER PRIMARY KEY
- `username` VARCHAR(80), unique, not null
- `password_hash` VARCHAR(128), not null
- `role` VARCHAR(20), not null

### `client`
- `id` INTEGER PRIMARY KEY
- `user_id` INTEGER, foreign key to `user.id`, not null
- `full_name` VARCHAR(100)
- `phone` VARCHAR(20)
- `address` VARCHAR(200)

### `pizza`
- `id` INTEGER PRIMARY KEY
- `name` VARCHAR(100), not null
- `description` TEXT
- `category` VARCHAR(50), default `Pizza`
- `photo_url` VARCHAR(200)
- `base_price` FLOAT, not null
- `is_available` BOOLEAN, default true

### `topping`
- `id` INTEGER PRIMARY KEY
- `name` VARCHAR(50), not null
- `price` FLOAT, default 0.0

### `commande`
- `id` INTEGER PRIMARY KEY
- `client_id` INTEGER, foreign key to `client.id`, not null
- `status` VARCHAR(20), default `New`
- `order_type` VARCHAR(20)
- `total_price` FLOAT, not null
- `created_at` DATETIME, default current timestamp

### `order_item`
- `id` INTEGER PRIMARY KEY
- `commande_id` INTEGER, foreign key to `commande.id`, not null
- `pizza_id` INTEGER, foreign key to `pizza.id`, not null
- `quantity` INTEGER, default 1
- `customizations` VARCHAR(200)

### `paiement`
- `id` INTEGER PRIMARY KEY
- `commande_id` INTEGER, foreign key to `commande.id`, not null
- `amount` FLOAT, not null
- `method` VARCHAR(20)
- `timestamp` DATETIME, default current timestamp

### `cash`
- `id` INTEGER PRIMARY KEY
- `paiement_id` INTEGER, foreign key to `paiement.id`, not null
- `amount_received` FLOAT
- `change_given` FLOAT

### `pizza_livraison`
- `id` INTEGER PRIMARY KEY
- `commande_id` INTEGER, foreign key to `commande.id`, not null
- `address` VARCHAR(255), not null
- `neighborhood` VARCHAR(100)
- `delivery_fee` FLOAT, default 0.0

### `facture`
- `id` INTEGER PRIMARY KEY
- `commande_id` INTEGER, foreign key to `commande.id`, not null
- `invoice_number` VARCHAR(50), unique
- `created_at` DATETIME, default current timestamp
- `content_html` TEXT

## seed_menu.py

### `seed_data()`
- Seeds pizza menu items into the database.
- Deletes existing pizza records before inserting to avoid duplicates.
- Inserts a fixed set of pizza, dessert, and drink items with categories, descriptions, prices, and photo filenames.
- Commits database changes and prints success message.

## seed_samples.py

### `seed_random_data(count=15)`
- Seeds random sample clients and orders for development/testing.
- Requires pizzas already seeded with `seed_menu.py`.
- Creates clients using random Moroccan-style names, phone numbers, addresses, and neighborhoods.
- For each client, generates 1–3 orders with random order types, statuses, and dates within the last 7 days.
- Generates order items, computes totals, creates payments and optional delivery info.
- Commits all changes and prints success message.

---

## Notes

- The project is primarily a Flask web application with SQLAlchemy models.
- `app.py` contains the request handlers, route definitions, and checkout/order processing logic.
- `models.py` defines the database schema.
- `seed_menu.py` and `seed_samples.py` are CLI/development scripts for populating the database.
- There are no custom utility functions outside route handlers and seed scripts.
