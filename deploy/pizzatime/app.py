from flask import Flask, render_template, redirect, url_for, request, flash
from flask_login import LoginManager, login_user, logout_user, login_required, current_user
from werkzeug.security import generate_password_hash, check_password_hash
from werkzeug.utils import secure_filename
from models import db, User, Client, Pizza, Topping, Commande, OrderItem, Paiement, Cash, PizzaLivraison, Facture
from sqlalchemy import func
import os

app = Flask(__name__)
from werkzeug.middleware.proxy_fix import ProxyFix
app.wsgi_app = ProxyFix(app.wsgi_app, x_prefix=1)
app.config['SECRET_KEY'] = 'dev_secret_key'
app.config['SQLALCHEMY_DATABASE_URI'] = 'sqlite:///pizzeria.db'
app.config['SQLALCHEMY_TRACK_MODIFICATIONS'] = False
app.config['UPLOAD_FOLDER'] = 'static/img'

db.init_app(app)
login_manager = LoginManager()
login_manager.init_app(app)
login_manager.login_view = 'login'

@login_manager.user_loader
def load_user(user_id):
    return User.query.get(int(user_id))

# --- Routes ---

@app.route('/')
def index():
    if current_user.is_authenticated and current_user.role == 'admin':
        return redirect(url_for('admin_analytics'))
    pizzas = Pizza.query.filter_by(is_available=True).all()
    return render_template('customer/menu.html', pizzas=pizzas)

@app.route('/login', methods=['GET', 'POST'])
def login():
    if request.method == 'POST':
        username = request.form.get('username')
        password = request.form.get('password')
        user = User.query.filter_by(username=username).first()
        if user and check_password_hash(user.password_hash, password):
            login_user(user)
            if user.role == 'admin':
                return redirect(url_for('admin_analytics'))
            elif user.role == 'staff':
                return redirect(url_for('staff_dashboard'))
            return redirect(url_for('index'))
        flash('Login failed. Check username and password.')
    return render_template('login.html')

@app.route('/logout')
@login_required
def logout():
    logout_user()
    return redirect(url_for('index'))

# --- Admin Routes ---

@app.route('/admin')
@login_required
def admin_dashboard():
    if current_user.role != 'admin':
        return "Access Denied", 403
    return redirect(url_for('admin_analytics'))

@app.route('/admin/menu', methods=['GET', 'POST'])
@login_required
def admin_menu():
    if current_user.role != 'admin':
        return "Access Denied", 403
    if request.method == 'POST':
        name = request.form.get('name')
        category = request.form.get('category')
        description = request.form.get('description')
        price = float(request.form.get('price'))
        
        photo_url = None
        if 'photo' in request.files:
            file = request.files['photo']
            if file.filename != '':
                filename = secure_filename(file.filename)
                file.save(os.path.join(app.config['UPLOAD_FOLDER'], filename))
                photo_url = filename
        
        pizza = Pizza(name=name, category=category, description=description, photo_url=photo_url, base_price=price)
        db.session.add(pizza)
        db.session.commit()
        flash('Article ajouté au menu.')
    pizzas = Pizza.query.all()
    toppings = Topping.query.all()
    return render_template('admin/menu.html', pizzas=pizzas, toppings=toppings)

@app.route('/admin/menu/edit/<int:id>', methods=['GET', 'POST'])
@login_required
def admin_edit_menu(id):
    if current_user.role != 'admin':
        return "Access Denied", 403
    pizza = Pizza.query.get_or_404(id)
    if request.method == 'POST':
        pizza.name = request.form.get('name')
        pizza.category = request.form.get('category')
        pizza.description = request.form.get('description')
        pizza.base_price = float(request.form.get('price'))
        
        if 'photo' in request.files:
            file = request.files['photo']
            if file.filename != '':
                filename = secure_filename(file.filename)
                file.save(os.path.join(app.config['UPLOAD_FOLDER'], filename))
                pizza.photo_url = filename
        
        db.session.commit()
        flash('Article mis à jour.')
        return redirect(url_for('admin_menu'))
    return render_template('admin/edit_menu.html', pizza=pizza)

@app.route('/admin/pizza/<int:id>/toggle_availability')
@login_required
def toggle_availability(id):
    if current_user.role != 'admin':
        return "Access Denied", 403
    pizza = Pizza.query.get_or_404(id)
    pizza.is_available = not pizza.is_available
    db.session.commit()
    return redirect(url_for('admin_menu'))

# --- Staff Routes ---

@app.route('/staff')
@login_required
def staff_dashboard():
    if current_user.role != 'staff' and current_user.role != 'admin':
        return "Access Denied", 403
    orders = Commande.query.order_by(Commande.created_at.desc()).all()
    return render_template('staff/dashboard.html', orders=orders)

@app.route('/staff/order/<int:id>/update_status', methods=['POST'])
@login_required
def update_order_status(id):
    if current_user.role not in ['staff', 'admin']:
        return "Access Denied", 403
    order = Commande.query.get_or_404(id)
    data = request.get_json()
    order.status = data.get('status')
    db.session.commit()
    return {"message": "Status updated"}

# --- Customer Routes ---

@app.route('/checkout', methods=['GET', 'POST'])
def checkout():
    if request.method == 'POST':
        # Simple guest checkout for now or require login
        # For a better experience, we'll assume guest info for this prototype
        full_name = request.form.get('full_name')
        phone = request.form.get('phone')
        address = request.form.get('address')
        neighborhood = request.form.get('neighborhood')
        order_type = request.form.get('order_type')
        payment_method = request.form.get('payment_method')
        cart_data = request.form.get('cart_data') # JSON string from JS
        
        import json
        items = json.loads(cart_data)
        
        subtotal = sum(item['price'] * item['quantity'] for item in items)
        delivery_fee = 0 if (subtotal >= 60 or order_type == 'Pickup') else 10
        total = subtotal + delivery_fee
        
        # 1. Create/Update User and Client
        user = User.query.filter_by(username=phone).first()
        if not user:
            user = User(username=phone, password_hash='guest', role='customer')
            db.session.add(user)
            db.session.flush()
            client = Client(user_id=user.id, full_name=full_name, phone=phone, address=address)
            db.session.add(client)
        else:
            client = user.client_profile
            if not client:
                client = Client(user_id=user.id, full_name=full_name, phone=phone, address=address)
                db.session.add(client)
            else:
                # Update client info with latest checkout data
                client.full_name = full_name
                client.address = address
        
        # Ensure client is flushed to get an ID if we need it, 
        # but better yet, assign the object directly
        db.session.flush()
            
        # 2. Create Commande
        new_order = Commande(
            client_id=client.id,
            status='New',
            order_type=order_type,
            total_price=total
        )
        db.session.add(new_order)
        db.session.flush()
        
        # 3. Add Items
        for item in items:
            order_item = OrderItem(
                commande_id=new_order.id, 
                pizza_id=item['id'], 
                quantity=item['quantity']
            )
            db.session.add(order_item)
            
        # 4. Handle Payment
        payment = Paiement(commande_id=new_order.id, amount=total, method=payment_method)
        db.session.add(payment)
        db.session.flush()
        
        if payment_method == 'Cash':
            cash = Cash(paiement_id=payment.id) # Details filled by staff later
            db.session.add(cash)
            
        # 5. Delivery Info
        if order_type == 'Delivery':
            delivery = PizzaLivraison(commande_id=new_order.id, address=address, neighborhood=neighborhood, delivery_fee=delivery_fee)
            db.session.add(delivery)
            
        db.session.commit()
        flash('Votre commande a été reçue !')
        return redirect(url_for('order_success', order_id=new_order.id))
        
    return render_template('customer/checkout.html')

@app.route('/order_success/<int:order_id>')
def order_success(order_id):
    order = Commande.query.get_or_404(order_id)
    return render_template('customer/order_success.html', order=order)

@app.route('/admin/orders')
@login_required
def admin_orders():
    if current_user.role != 'admin':
        return "Access Denied", 403
    orders = Commande.query.order_by(Commande.created_at.desc()).all()
    return render_template('admin/orders.html', orders=orders)

@app.route('/admin/clients')
@login_required
def admin_clients():
    if current_user.role != 'admin':
        return "Access Denied", 403
    clients = Client.query.all()
    return render_template('admin/clients.html', clients=clients)

@app.route('/admin/analytics')
@login_required
def admin_analytics():
    if current_user.role != 'admin':
        return "Access Denied", 403
    
    from datetime import datetime, timedelta, date
    today = date.today()
    
    # KPIs
    total_revenue = db.session.query(func.sum(Commande.total_price)).scalar() or 0
    total_orders = Commande.query.count()
    avg_order = total_revenue / total_orders if total_orders > 0 else 0
    
    # New Stats
    orders_today = Commande.query.filter(func.date(Commande.created_at) == today).count()
    pizzas_sold = db.session.query(func.sum(OrderItem.quantity)).scalar() or 0
    new_clients = Client.query.count() # Simplified for prototype
    
    # Recent Orders (Latest 5)
    recent_orders = Commande.query.order_by(Commande.created_at.desc()).limit(5).all()
    
    # Best Sellers (Top 5)
    best_sellers = db.session.query(
        Pizza.name, 
        func.sum(OrderItem.quantity).label('total_qty')
    ).join(OrderItem).group_by(Pizza.id).order_by(func.sum(OrderItem.quantity).desc()).limit(5).all()
    
    # Daily Sales (Last 7 Days)
    seven_days_ago = datetime.utcnow() - timedelta(days=7)
    daily_sales = db.session.query(
        func.date(Commande.created_at).label('date'),
        func.sum(Commande.total_price).label('revenue')
    ).filter(Commande.created_at >= seven_days_ago).group_by(func.date(Commande.created_at)).all()

    return render_template('admin/analytics.html', 
        total_revenue=total_revenue, 
        total_orders=total_orders, 
        avg_order=avg_order,
        orders_today=orders_today,
        pizzas_sold=pizzas_sold,
        new_clients=new_clients,
        recent_orders=recent_orders,
        best_sellers=best_sellers,
        daily_sales=daily_sales
    )

# --- CLI Commands for Init ---

@app.cli.command("init-db")
def init_db():
    db.create_all()
    # Create default admin if not exists
    if not User.query.filter_by(username='admin').first():
        admin = User(
            username='admin', 
            password_hash=generate_password_hash('admin123'), 
            role='admin'
        )
        db.session.add(admin)
        db.session.commit()
        print("Database initialized and admin user created.")

if __name__ == '__main__':
    app.run(debug=True)
