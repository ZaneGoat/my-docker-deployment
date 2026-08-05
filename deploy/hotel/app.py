import os
from flask import Flask, render_template, redirect, url_for, request, flash, jsonify
from flask_sqlalchemy import SQLAlchemy
from flask_login import LoginManager, login_user, logout_user, login_required, current_user
from werkzeug.security import generate_password_hash, check_password_hash
from sqlalchemy.exc import IntegrityError
from sqlalchemy import or_
from models import db, User, Room, Client, Reservation, Service, ReservationService, Payment
from datetime import datetime, date

app = Flask(__name__)
from werkzeug.middleware.proxy_fix import ProxyFix
app.wsgi_app = ProxyFix(app.wsgi_app, x_prefix=1)
app.config['SECRET_KEY'] = 'dev-secret-key'
app.config['SQLALCHEMY_DATABASE_URI'] = 'sqlite:///hotel.db'
app.config['SQLALCHEMY_TRACK_MODIFICATIONS'] = False

db.init_app(app)
login_manager = LoginManager()
login_manager.login_view = 'login'
login_manager.init_app(app)

@login_manager.user_loader
def load_user(user_id):
    return User.query.get(int(user_id))


def reservation_overlaps(room_id, arrival, departure, exclude_reservation_id=None):
    query = Reservation.query.filter(
        Reservation.room_id == room_id,
        Reservation.status.in_(['Confirmée', 'Checked-In']),
        Reservation.arrival_date < departure,
        Reservation.departure_date > arrival
    )
    if exclude_reservation_id is not None:
        query = query.filter(Reservation.id != exclude_reservation_id)
    return query.first()


def room_display_status(room, today):
    if room.status != 'Disponible':
        return room.status

    overlap = Reservation.query.filter(
        Reservation.room_id == room.id,
        Reservation.status == 'Confirmée',
        Reservation.arrival_date <= today,
        Reservation.departure_date >= today
    ).first()
    if overlap:
        return 'Réservée'
    return 'Disponible'


def reservation_is_closed(reservation):
    return reservation.status == 'Checked-Out'


def block_closed_reservation_action(reservation):
    if reservation_is_closed(reservation):
        flash('Cette réservation est clôturée. Seul l\'export PDF est autorisé.')
        return True
    return False

# --- Database Initialization ---
def init_db():
    with app.app_context():
        db.create_all()
        if not User.query.filter_by(username='admin').first():
            admin = User(
                username='admin',
                password_hash=generate_password_hash('admin123'),
                role='Admin'
            )
            db.session.add(admin)
            db.session.commit()
            print("Admin user created: admin / admin123")
        
        if not Service.query.first():
            services = [
                Service(name='Petit-déjeuner', price=50.0),
                Service(name='Piscine', price=80.0),
                Service(name='Spa', price=150.0),
                Service(name='Salle de sport', price=70.0),
                Service(name='Parking', price=30.0)
            ]
            db.session.add_all(services)
            db.session.commit()
            print("Default services created")

init_db()

# --- Routes ---

@app.route('/')
@login_required
def index():
    return redirect(url_for('dashboard'))

@app.route('/login', methods=['GET', 'POST'])
def login():
    if current_user.is_authenticated:
        return redirect(url_for('dashboard'))
    if request.method == 'POST':
        username = request.form.get('username')
        password = request.form.get('password')
        user = User.query.filter_by(username=username).first()
        if user and check_password_hash(user.password_hash, password):
            login_user(user)
            return redirect(url_for('dashboard'))
        flash('Invalid username or password')
    return render_template('login.html')

@app.route('/logout')
@login_required
def logout():
    logout_user()
    return redirect(url_for('login'))

@app.route('/dashboard')
@login_required
def dashboard():
    today = date.today()
    
    # --- Room Metrics ---
    total_rooms = Room.query.count()
    maintenance_rooms = Room.query.filter_by(status='Maintenance').count()
    occupied_rooms = Room.query.filter_by(status='Occupé').count()
    
    # Calculate truly available vs reserved for today
    all_dispo = Room.query.filter_by(status='Disponible').all()
    available_rooms_count = 0
    reserved_rooms_count = 0
    for r in all_dispo:
        res = Reservation.query.filter(
            Reservation.room_id == r.id,
            Reservation.status == 'Confirmée',
            Reservation.arrival_date <= today,
            Reservation.departure_date >= today
        ).first()
        if res:
            reserved_rooms_count += 1
        else:
            available_rooms_count += 1
    
    # --- Operational Metrics ---
    today_arrivals = Reservation.query.filter_by(status='Confirmée').filter(Reservation.arrival_date == today).count()
    today_departures = Reservation.query.filter_by(status='Checked-In').filter(Reservation.departure_date == today).count()
    active_stays = Reservation.query.filter_by(status='Checked-In').count()
    
    # --- Financial Metrics ---
    # Total Collected this month
    monthly_collected = db.session.query(db.func.sum(Payment.amount)).filter(
        db.extract('month', Payment.timestamp) == today.month,
        db.extract('year', Payment.timestamp) == today.year
    ).scalar() or 0.0
    
    # Service Revenue (total ever)
    service_revenue = db.session.query(db.func.sum(ReservationService.quantity * Service.price)).join(Service).scalar() or 0.0
    
    # --- Activity Feeds ---
    recent_payments = Payment.query.order_by(Payment.timestamp.desc()).limit(5).all()
    recent_customers = Client.query.order_by(Client.id.desc()).limit(5).all()
    
    return render_template('dashboard.html', 
                           total_rooms=total_rooms, 
                           available_rooms=available_rooms_count,
                           reserved_rooms=reserved_rooms_count,
                           occupied_rooms=occupied_rooms,
                           maintenance_rooms=maintenance_rooms,
                           today_arrivals=today_arrivals,
                           today_departures=today_departures,
                           active_stays=active_stays,
                           monthly_revenue=monthly_collected,
                           service_revenue=service_revenue,
                           recent_payments=recent_payments,
                           recent_customers=recent_customers)

# --- Room Management ---
@app.route('/rooms')
@login_required
def rooms():
    all_rooms = Room.query.all()
    today = date.today()
    for room in all_rooms:
        room.display_status = room_display_status(room, today)
    return render_template('rooms.html', rooms=all_rooms)

@app.route('/rooms/add', methods=['POST'])
@login_required
def add_room():
    if current_user.role != 'Admin':
        flash('Permission denied')
        return redirect(url_for('rooms'))
    
    number = request.form.get('number')
    rtype = request.form.get('type')
    price = request.form.get('price')
    floor = request.form.get('floor')

    if Room.query.filter_by(number=number).first():
        flash(f'La chambre {number} existe déjà')
        return redirect(url_for('rooms'))
    
    new_room = Room(number=number, type=rtype, price_per_night=float(price), floor=int(floor))
    db.session.add(new_room)
    try:
        db.session.commit()
    except IntegrityError:
        db.session.rollback()
        flash('Impossible d\'ajouter la chambre: données déjà utilisées ou invalides')
        return redirect(url_for('rooms'))
    flash('Room added successfully')
    return redirect(url_for('rooms'))

# --- Client Management ---
@app.route('/clients')
@login_required
def clients():
    all_clients = Client.query.all()
    return render_template('clients.html', clients=all_clients)

@app.route('/clients/add', methods=['POST'])
@login_required
def add_client():
    cin = request.form.get('cin')
    fname = request.form.get('first_name')
    lname = request.form.get('last_name')
    phone = request.form.get('phone')
    email = request.form.get('email')
    address = request.form.get('address')

    if not cin:
        flash('Le CIN est obligatoire')
        return redirect(url_for('clients'))

    existing_client = Client.query.filter_by(cin=cin).first()
    if existing_client:
        flash(f'Le client avec le CIN {cin} existe déjà')
        return redirect(url_for('clients'))
    
    new_client = Client(cin=cin, first_name=fname, last_name=lname, phone=phone, email=email, address=address)
    db.session.add(new_client)
    try:
        db.session.commit()
    except IntegrityError:
        db.session.rollback()
        flash(f'Impossible d\'ajouter le client avec le CIN {cin}')
        return redirect(url_for('clients'))
    flash('Client added successfully')
    return redirect(url_for('clients'))

# --- Reservation Management ---
@app.route('/reservations')
@login_required
def reservations():
    page = request.args.get('page', 1, type=int)
    per_page = request.args.get('per_page', 15, type=int)
    per_page = min(max(per_page, 5), 50)
    search = (request.args.get('search') or '').strip()
    status_filter = (request.args.get('status') or '').strip()

    query = Reservation.query.join(Client).join(Room)

    if search:
        like = f"%{search}%"
        query = query.filter(or_(
            Client.first_name.ilike(like),
            Client.last_name.ilike(like),
            Client.cin.ilike(like),
            Room.number.ilike(like),
            Room.type.ilike(like),
            Reservation.status.ilike(like)
        ))

    if status_filter and status_filter != 'All':
        query = query.filter(Reservation.status == status_filter)

    query = query.order_by(Reservation.created_at.desc())
    pagination = query.paginate(page=page, per_page=per_page, error_out=False)
    all_reservations = pagination.items

    total_reservations = Reservation.query.count()
    active_stays = Reservation.query.filter_by(status='Checked-In').count()
    today_arrivals = Reservation.query.filter_by(status='Confirmée').filter(Reservation.arrival_date == date.today()).count()
    total_revenue = db.session.query(db.func.sum(Payment.amount)).scalar() or 0.0

    rooms = Room.query.order_by(Room.number).all()
    today = date.today()
    for room in rooms:
        room.display_status = room_display_status(room, today)
    clients = Client.query.all()
    services_list = Service.query.all()
    return render_template(
        'reservations.html',
        reservations=all_reservations,
        pagination=pagination,
        rooms=rooms,
        clients=clients,
        services_list=services_list,
        search=search,
        status_filter=status_filter or 'All',
        per_page=per_page,
        total_reservations=total_reservations,
        active_stays=active_stays,
        today_arrivals=today_arrivals,
        total_revenue=total_revenue
    )

@app.route('/reservations/add', methods=['POST'])
@login_required
def add_reservation():
    client_id = request.form.get('client_id')
    room_id = request.form.get('room_id')
    try:
        arrival = datetime.strptime(request.form.get('arrival_date'), '%Y-%m-%d').date()
        departure = datetime.strptime(request.form.get('departure_date'), '%Y-%m-%d').date()
    except (TypeError, ValueError):
        flash('Dates invalides')
        return redirect(url_for('reservations'))
    
    room = Room.query.get(room_id)
    client = Client.query.get(client_id)
    if not room or not client:
        flash('Chambre ou client introuvable')
        return redirect(url_for('reservations'))

    days = (departure - arrival).days
    if days <= 0:
        flash('Erreur : La date de départ doit être après la date d\'arrivée')
        return redirect(url_for('reservations'))

    if room.status == 'Maintenance':
        flash(f'Indisponible : La chambre {room.number} est en maintenance')
        return redirect(url_for('reservations'))

    overlapping_res = reservation_overlaps(room_id, arrival, departure)
    if overlapping_res:
        flash(f'Indisponible : La chambre {room.number} est déjà réservée ou occupée pour ces dates')
        return redirect(url_for('reservations'))

    total = days * room.price_per_night
    
    new_res = Reservation(
        client_id=client_id,
        room_id=room_id,
        arrival_date=arrival,
        departure_date=departure,
        total_amount=total,
        status='Confirmée'
    )
    db.session.add(new_res)
    db.session.commit()
    flash(f'Réservation confirmée pour la chambre {room.number}')
    return redirect(url_for('reservations'))

# --- Stay Management (Check-In / Out) ---

@app.route('/stays')
@login_required
def stay_management():
    today = date.today()
    # Pending Arrivals
    pending = Reservation.query.filter_by(status='Confirmée').order_by(Reservation.arrival_date).all()
    # Active Stays
    active = Reservation.query.filter_by(status='Checked-In').all()
    # History
    recent_outs = Reservation.query.filter_by(status='Checked-Out').order_by(Reservation.actual_check_out.desc()).limit(20).all()
    # Catalog
    services = Service.query.all()
    
    # Enrich active stays with calculated data
    for res in active:
        res.nights = (res.departure_date - res.arrival_date).days
        res.total_paid = sum(p.amount for p in res.payments)
        res.balance = res.total_amount - res.total_paid
        # Overstay detection
        res.is_overstay = today > res.departure_date
        
    return render_template('stays.html', 
                           pending=pending, 
                           active=active, 
                           recent_outs=recent_outs, 
                           services=services,
                           today=today)

@app.route('/check-in/<int:res_id>', methods=['POST'])
@login_required
def process_check_in(res_id):
    res = Reservation.query.get_or_404(res_id)
    room = Room.query.get(res.room_id)

    if res.status != 'Confirmée':
        flash('Cette réservation ne peut pas être enregistrée en check-in')
        return redirect(url_for('stay_management'))

    today = date.today()
    if today < res.arrival_date:
        flash('Check-in impossible avant la date d\'arrivée')
        return redirect(url_for('stay_management'))

    if today > res.departure_date:
        flash('Cette réservation est expirée')
        return redirect(url_for('stay_management'))

    if room.status != 'Disponible':
        flash(f'Room {room.number} is currently {room.status}')
        return redirect(url_for('stay_management'))
    
    res.status = 'Checked-In'
    res.actual_check_in = datetime.now()
    room.status = 'Occupé'
    db.session.commit()
    flash(f'Check-in successful for Room {room.number}')
    return redirect(url_for('stay_management'))

@app.route('/check-out/<int:res_id>', methods=['POST'])
@login_required
def process_check_out(res_id):
    res = Reservation.query.get_or_404(res_id)
    room = Room.query.get(res.room_id)

    if res.status != 'Checked-In':
        flash('Seuls les séjours en cours peuvent être clôturés')
        return redirect(url_for('stay_management'))

    res.status = 'Checked-Out'
    res.actual_check_out = datetime.now()
    room.status = 'Disponible'
    db.session.commit()
    flash(f'Check-out réussi pour Chambre {room.number}')
    return redirect(url_for('stay_management'))

# --- Service Management ---

@app.route('/services')
@login_required
def services():
    all_services = Service.query.all()
    return render_template('services.html', services=all_services)

@app.route('/services/add', methods=['POST'])
@login_required
def add_service():
    name = request.form.get('name')
    price = request.form.get('price')
    new_service = Service(name=name, price=float(price))
    db.session.add(new_service)
    db.session.commit()
    flash('Service ajouté au catalogue')
    return redirect(url_for('services'))

@app.route('/services/delete/<int:s_id>')
@login_required
def delete_service(s_id):
    service = Service.query.get_or_404(s_id)
    db.session.delete(service)
    db.session.commit()
    flash('Service supprimé')
    return redirect(url_for('services'))

@app.route('/stays/assign-service/<int:res_id>', methods=['POST'])
@login_required
def assign_service(res_id):
    res = Reservation.query.get_or_404(res_id)
    if block_closed_reservation_action(res):
        return redirect(request.referrer or url_for('reservations'))
    service_id = request.form.get('service_id')
    qty = int(request.form.get('quantity', 1))
    
    service = Service.query.get(service_id)
    if service:
        res_service = ReservationService(
            reservation_id=res.id,
            service_id=service.id,
            quantity=qty
        )
        charge = service.price * qty
        res.extra_charges += charge
        res.total_amount += charge
        db.session.add(res_service)
        db.session.commit()
        flash(f'Service {service.name} ajouté')
    return redirect(request.referrer or url_for('stay_management'))

@app.route('/stays/remove-service/<int:rs_id>')
@login_required
def remove_consumed_service(rs_id):
    rs = ReservationService.query.get_or_404(rs_id)
    res = Reservation.query.get(rs.reservation_id)
    if block_closed_reservation_action(res):
        return redirect(request.referrer or url_for('reservations'))
    charge = rs.service.price * rs.quantity
    res.extra_charges -= charge
    res.total_amount -= charge
    db.session.delete(rs)
    db.session.commit()
    flash('Service retiré')
    return redirect(request.referrer or url_for('stay_management'))

# --- Payment Management ---

@app.route('/payments')
@login_required
def payment_history():
    all_payments = Payment.query.order_by(Payment.timestamp.desc()).all()
    return render_template('payments.html', payments=all_payments)

@app.route('/reservations/pay/<int:res_id>', methods=['POST'])
@login_required
def record_payment(res_id):
    res = Reservation.query.get_or_404(res_id)
    if block_closed_reservation_action(res):
        return redirect(request.referrer or url_for('reservations'))
    try:
        amount = float(request.form.get('amount', 0))
    except ValueError:
        flash('Montant invalide')
        return redirect(request.referrer or url_for('stay_management'))

    if amount <= 0:
        flash('Le montant doit être supérieur à zero')
        return redirect(request.referrer or url_for('stay_management'))

    method = request.form.get('method')
    new_payment = Payment(reservation_id=res.id, amount=amount, method=method)
    db.session.add(new_payment)
    db.session.commit()
    flash(f'Paiement de {amount} DH enregistré via {method}')
    return redirect(request.referrer or url_for('stay_management'))

@app.route('/payments/delete/<int:p_id>')
@login_required
def delete_payment(p_id):
    payment = Payment.query.get_or_404(p_id)
    if payment.reservation and reservation_is_closed(payment.reservation):
        flash('Cette réservation est clôturée. La suppression des paiements n\'est pas autorisée.')
        return redirect(request.referrer or url_for('payment_history'))
    db.session.delete(payment)
    db.session.commit()
    flash('Paiement supprimé de l\'historique')
    return redirect(request.referrer or url_for('payment_history'))

@app.route('/payments/receipt/<int:p_id>')
@login_required
def print_receipt(p_id):
    payment = Payment.query.get_or_404(p_id)
    return render_template('receipt.html', p=payment)

@app.route('/payments/receipt/pdf/<int:p_id>')
@login_required
def download_receipt_pdf(p_id):
    payment = Payment.query.get_or_404(p_id)
    
    # Render HTML template for PDF (using the existing receipt template or a specific one)
    html = render_template('receipt_pdf.html', p=payment)
    
    # Convert to PDF
    result = BytesIO()
    pdf = pisa.pisaDocument(BytesIO(html.encode("UTF-8")), result)
    
    if not pdf.err:
        response = make_response(result.getvalue())
        response.headers['Content-Type'] = 'application/pdf'
        response.headers['Content-Disposition'] = f'attachment; filename=recu_{payment.id}.pdf'
        return response
    
    return "Error generating PDF", 500

# --- Other Actions ---

@app.route('/reservations/cancel/<int:res_id>')
@login_required
def cancel_reservation(res_id):
    res = Reservation.query.get_or_404(res_id)
    if block_closed_reservation_action(res):
        return redirect(request.referrer or url_for('reservations'))
    room = Room.query.get(res.room_id)
    if res.status == 'Checked-In':
        room.status = 'Disponible'
    res.status = 'Annulée'
    db.session.commit()
    flash('Réservation annulée')
    return redirect(request.referrer or url_for('reservations'))

@app.route('/reservations/delete/<int:res_id>')
@login_required
def delete_reservation(res_id):
    res = Reservation.query.get_or_404(res_id)
    if block_closed_reservation_action(res):
        return redirect(request.referrer or url_for('reservations'))
    if res.status == 'Checked-In':
        room = Room.query.get(res.room_id)
        if room: room.status = 'Disponible'
    db.session.delete(res)
    db.session.commit()
    flash('Réservation supprimée')
    return redirect(request.referrer or url_for('reservations'))

@app.route('/reservations/delete-cancelled')
@login_required
def delete_cancelled_reservations():
    count = Reservation.query.filter_by(status='Annulée').delete()
    db.session.commit()
    flash(f'{count} annulations supprimées')
    return redirect(url_for('reservations'))

@app.route('/reservations/edit/<int:res_id>', methods=['GET', 'POST'])
@login_required
def edit_reservation(res_id):
    res = Reservation.query.get_or_404(res_id)
    if res.status == 'Checked-Out':
        flash('Cette réservation est clôturée. La modification n\'est pas autorisée.')
        return redirect(url_for('reservations'))
    if request.method == 'POST':
        client_id = request.form.get('client_id')
        room_id = request.form.get('room_id')
        try:
            arrival = datetime.strptime(request.form.get('arrival_date'), '%Y-%m-%d').date()
            departure = datetime.strptime(request.form.get('departure_date'), '%Y-%m-%d').date()
        except (TypeError, ValueError):
            flash('Dates invalides')
            return redirect(url_for('edit_reservation', res_id=res_id))

        room = Room.query.get(room_id)
        client = Client.query.get(client_id)
        if not room or not client:
            flash('Chambre ou client introuvable')
            return redirect(url_for('edit_reservation', res_id=res_id))

        days = (departure - arrival).days
        if days <= 0:
            flash('Dates invalides')
            return redirect(url_for('edit_reservation', res_id=res_id))
        if room.status == 'Maintenance':
            flash('Chambre indisponible')
            return redirect(url_for('edit_reservation', res_id=res_id))

        overlapping = reservation_overlaps(room_id, arrival, departure, exclude_reservation_id=res_id)
        if overlapping:
            flash('Chambre indisponible')
            return redirect(url_for('edit_reservation', res_id=res_id))

        res.client_id = client_id
        res.room_id = room_id
        res.arrival_date = arrival
        res.departure_date = departure
        res.total_amount = (days * room.price_per_night) + res.extra_charges
        db.session.commit()
        flash('Réservation mise à jour')
        return redirect(url_for('reservations'))
    clients = Client.query.all()
    rooms = Room.query.all()
    return render_template('edit_reservation.html', res=res, clients=clients, rooms=rooms)

@app.route('/invoice/<int:res_id>')
@login_required
def view_invoice(res_id):
    res = Reservation.query.get_or_404(res_id)
    total_paid = sum(p.amount for p in res.payments)
    return render_template('invoice.html', res=res, total_paid=total_paid)

from io import BytesIO
from xhtml2pdf import pisa
from flask import make_response

@app.route('/reservations/invoice/pdf/<int:res_id>')
@login_required
def download_invoice_pdf(res_id):
    res = Reservation.query.get_or_404(res_id)
    total_paid = sum(p.amount for p in res.payments)
    
    # Render HTML template for PDF
    html = render_template('invoice_pdf.html', res=res, total_paid=total_paid, date=date)
    
    # Convert to PDF
    result = BytesIO()
    pdf = pisa.pisaDocument(BytesIO(html.encode("UTF-8")), result)
    
    if not pdf.err:
        response = make_response(result.getvalue())
        response.headers['Content-Type'] = 'application/pdf'
        response.headers['Content-Disposition'] = f'attachment; filename=facture_{res.id}.pdf'
        return response
    
    return "Error generating PDF", 500

if __name__ == '__main__':
    init_db()
    app.run(debug=True)
