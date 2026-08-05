from flask_sqlalchemy import SQLAlchemy
from flask_login import UserMixin
from datetime import datetime

db = SQLAlchemy()

class User(db.Model, UserMixin):
    id = db.Column(db.Integer, primary_key=True)
    username = db.Column(db.String(50), unique=True, nullable=False)
    password_hash = db.Column(db.String(255), nullable=False)
    role = db.Column(db.String(20), nullable=False) # 'Admin', 'Receptionist'

class Room(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    number = db.Column(db.String(10), unique=True, nullable=False)
    type = db.Column(db.String(50), nullable=False)
    price_per_night = db.Column(db.Float, nullable=False)
    floor = db.Column(db.Integer, nullable=False)
    status = db.Column(db.String(20), default='Disponible') # 'Disponible', 'Occupé', 'Maintenance'
    reservations = db.relationship('Reservation', backref='room', lazy=True)

class Client(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    cin = db.Column(db.String(20), unique=True, nullable=False)
    first_name = db.Column(db.String(50), nullable=False)
    last_name = db.Column(db.String(50), nullable=False)
    phone = db.Column(db.String(20))
    email = db.Column(db.String(100))
    address = db.Column(db.String(200))
    reservations = db.relationship('Reservation', backref='client', lazy=True)

class Service(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    name = db.Column(db.String(50), nullable=False)
    price = db.Column(db.Float, nullable=False)

class ReservationService(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    reservation_id = db.Column(db.Integer, db.ForeignKey('reservation.id'), nullable=False)
    service_id = db.Column(db.Integer, db.ForeignKey('service.id'), nullable=False)
    quantity = db.Column(db.Integer, default=1)
    date_consumed = db.Column(db.DateTime, default=datetime.utcnow)
    
    service = db.relationship('Service')

class Payment(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    reservation_id = db.Column(db.Integer, db.ForeignKey('reservation.id'), nullable=False)
    amount = db.Column(db.Float, nullable=False)
    method = db.Column(db.String(20), nullable=False) # 'Espèces', 'Carte Bancaire', 'Virement'
    timestamp = db.Column(db.DateTime, default=datetime.utcnow)
    
    reservation = db.relationship('Reservation', backref='payments', lazy=True)

class Reservation(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    client_id = db.Column(db.Integer, db.ForeignKey('client.id'), nullable=False)
    room_id = db.Column(db.Integer, db.ForeignKey('room.id'), nullable=False)
    arrival_date = db.Column(db.Date, nullable=False)
    departure_date = db.Column(db.Date, nullable=False)
    actual_check_in = db.Column(db.DateTime)
    actual_check_out = db.Column(db.DateTime)
    extra_charges = db.Column(db.Float, default=0.0)
    total_amount = db.Column(db.Float, nullable=False)
    status = db.Column(db.String(20), default='Confirmée') # 'Confirmée', 'Checked-In', 'Checked-Out', 'Annulée'
    created_at = db.Column(db.DateTime, default=datetime.utcnow)
    
    consumed_services = db.relationship('ReservationService', backref='reservation', lazy=True, cascade="all, delete-orphan")
