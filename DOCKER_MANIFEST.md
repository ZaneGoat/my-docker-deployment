# 🐳 Docker Deployment Organization & Manifest

## 📁 Repository Structure & Docker Assets

```
my-docker-deployment/
├── 📄 docker-compose.yml              # Central Multi-Container Orchestration
├── 📄 nginx.conf                      # Master Nginx Gateway Routing (Port 80)
├── 📄 Dockerfile.flask                # Base Image for Python Flask Apps (python:3.9-slim)
├── 📄 Dockerfile.php                  # Base Image for PHP-FPM Apps (php:8.2-fpm + pdo/mysqli)
├── 📄 requirements.txt                # Unified Python Dependencies
├── 📜 get_ngrok.sh                    # Helper script for extracting live Ngrok URL
├── 🚀 One-Click-Start.bat             # One-Click Instant Deployment & Web Launcher
├── ⚙️ Start-Web-Portal.bat            # Interactive Terminal Selector Script
├── 🛑 stop-server.bat                 # Server Teardown Script
└── 📂 deploy/                         # Web Application Root
    ├── index.html                     # Cyberpunk Master Dashboard Page
    ├── index.php                      # Gatekeeper Security Lock Screen
    ├── fab.js                         # Global Floating Action Button Script
    ├── ayarestoPHP/                   # PHP Restaurant Management App
    ├── projrtZL/                      # PHP Business & Billing Platform
    ├── Ihsan/                         # Flask Beauty Center Management
    ├── traiteur_evenements/           # Flask Event Catering Management
    ├── khadija/Patisserie/            # Flask Bakery Management System
    ├── reservation_terrain/           # Flask Terrain Reservation System
    ├── pizzatime/                     # Flask Pizza Ordering & POS System
    ├── hotel/                         # Flask Hotel Management System
    ├── othman-terrain/                # Flask Sports Field Reservation System
    └── ipirnet/                       # Django IPIRNET V7 Management System
```

---

## 🚀 Managed Docker Containers & Specifications

| Container Name | Service | Image | Internal Command / Entrypoint | Internal Port | Mount / Volume | Nginx Route |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| `nginx-1` | Nginx | `nginx:latest` | `nginx -g 'daemon off;'` | 80 | `./nginx.conf`, `./deploy` | `http://localhost/` |
| `php-1` | PHP-FPM | `Dockerfile.php` | `php-fpm` | 9000 | `./deploy` | `http://localhost/*.php` |
| `flask1-1` | Ihsan | `localhost/my-flask-base` | `python app.py` | 5000 | `./deploy/Ihsan` | `http://localhost/ihsan/` |
| `flask2-1` | Traiteur | `localhost/my-flask-base` | `python app.py` | 5001 | `./deploy/traiteur_evenements` | `http://localhost/traiteur/` |
| `flask3-1` | Khadija | `localhost/my-flask-base` | `python run.py` | 5002 | `./deploy/khadija/Patisserie` | `http://localhost/khadija/` |
| `flask4-1` | Terrain | `localhost/my-flask-base` | `python app.py` | 5003 | `./deploy/reservation_terrain` | `http://localhost/terrain/` |
| `ipirnet-1` | IPIRNET | `python:3.11-slim` | `manage.py runserver 0.0.0.0:5004` | 5004 | `./deploy/ipirnet` | `http://localhost/ipirnet/` |
| `pizzatime-1` | PizzaTime | `localhost/my-flask-base` | `flask --app app run --port 5005` | 5005 | `./deploy/pizzatime` | `http://localhost/pizzatime/` |
| `hotel-1` | Hotel | `localhost/my-flask-base` | `flask --app app run --port 5006` | 5006 | `./deploy/hotel` | `http://localhost/hotel/` |
| `othman-terrain-1`| Othman Terrain| `localhost/my-flask-base` | `flask --app app run --port 5007` | 5007 | `./deploy/othman-terrain` | `http://localhost/othman-terrain/` |
| `mysql-1` | MySQL | `mysql:8.0` | `mysqld` | 3306 | `db_data` volume | Internal Database Host |
| `ngrok-1` | Ngrok | `ngrok/ngrok:latest` | `http 127.0.0.1:80` | 4040 | - | Public WAN URL |

---

## ⚡ Quick Management Commands

* **One-Click Start**: Double click `One-Click-Start.bat`
* **Interactive Terminal**: Double click `Start-Web-Portal.bat`
* **Manual Start via WSL**: `docker compose up -d`
* **Manual Stop**: `docker compose down`
