from flask import Blueprint, render_template, redirect, url_for, request, flash
from flask_login import login_required, current_user
from models import db, User, Terrain, Reservation
from datetime import datetime, time, timedelta
from sqlalchemy import func

main = Blueprint('main', __name__)

@main.route('/')
def index():
    return redirect(url_for('auth.login'))

@main.route('/dashboard')
@login_required
def dashboard():
    if current_user.role == 'admin':
        terrains = Terrain.query.all()
        all_reservations = Reservation.query.order_by(Reservation.date.desc(), Reservation.start_time.desc()).limit(5).all()
        
        # Statistics for Charts
        # 1. Reservations per Terrain
        terrain_stats = db.session.query(
            Terrain.name, 
            func.count(Reservation.id).label('count'),
            func.sum(Reservation.total_price).label('revenue')
        ).outerjoin(Reservation).group_by(Terrain.id).all()
        
        terrain_names = [s[0] for s in terrain_stats]
        terrain_counts = [s[1] for s in terrain_stats]
        terrain_revenues = [float(s[2] or 0) for s in terrain_stats]
        
        # 2. Reservations per Player
        player_stats = db.session.query(
            User.username,
            func.count(Reservation.id).label('count')
        ).join(Reservation).group_by(User.id).order_by(func.count(Reservation.id).desc()).limit(5).all()
        
        player_names = [s[0] for s in player_stats]
        player_counts = [s[1] for s in player_stats]
            
        stats = {
            'terrain_names': terrain_names,
            'terrain_counts': terrain_counts,
            'terrain_revenues': terrain_revenues,
            'player_names': player_names,
            'player_counts': player_counts
        }
        
        return render_template('admin_dashboard.html', 
                             terrains=terrains, 
                             reservations=all_reservations,
                             stats=stats)
    else:
        terrains = Terrain.query.all()
        my_reservations = Reservation.query.filter_by(user_id=current_user.id).order_by(Reservation.date.desc(), Reservation.start_time.desc()).limit(5).all()
        
        # Player Statistics
        # 1. Reservations per Terrain (My)
        my_terrain_stats = db.session.query(
            Terrain.name,
            func.count(Reservation.id)
        ).join(Reservation).filter(Reservation.user_id == current_user.id).group_by(Terrain.id).all()
        
        my_terrain_names = [s[0] for s in my_terrain_stats]
        my_terrain_counts = [s[1] for s in my_terrain_stats]
        
        # 2. Monthly Spending (Last 6 months)
        my_spending_stats = db.session.query(
            func.strftime('%Y-%m', Reservation.date),
            func.sum(Reservation.total_price)
        ).filter(Reservation.user_id == current_user.id).group_by(func.strftime('%Y-%m', Reservation.date)).order_by(Reservation.date.desc()).limit(6).all()
        
        my_spending_stats.reverse()
        
        my_months = [s[0] for s in my_spending_stats]
        my_spending = [float(s[1] or 0) for s in my_spending_stats]
        
        player_stats = {
            'terrain_names': my_terrain_names,
            'terrain_counts': my_terrain_counts,
            'months': my_months,
            'spending': my_spending
        }
        
        return render_template('player_dashboard.html', 
                             terrains=terrains, 
                             reservations=my_reservations,
                             stats=player_stats)

@main.route('/admin/add_terrain', methods=['POST'])
@login_required
def add_terrain():
    if current_user.role != 'admin':
        return redirect(url_for('main.dashboard'))
    
    name = request.form.get('name')
    price = float(request.form.get('price'))
    
    new_terrain = Terrain(name=name, price_per_hour=price)
    db.session.add(new_terrain)
    db.session.commit()
    flash(f'Terrain {name} ajouté avec succès !')
    return redirect(url_for('main.dashboard'))

@main.route('/reserve/<int:terrain_id>', methods=['GET', 'POST'])
@login_required
def reserve(terrain_id):
    terrain = Terrain.query.get_or_404(terrain_id)
    
    if request.method == 'POST':
        date_str = request.form.get('date')
        time_str = request.form.get('time')
        duration = int(request.form.get('duration'))
        
        res_date = datetime.strptime(date_str, '%Y-%m-%d').date()
        res_time = datetime.strptime(time_str, '%H:%M').time()
        
        new_start_dt = datetime.combine(res_date, res_time)
        new_end_dt = new_start_dt + timedelta(hours=duration)
        
        existing_reservations = Reservation.query.filter_by(terrain_id=terrain_id, date=res_date).all()
        
        overlap = False
        for res in existing_reservations:
            ex_start_dt = datetime.combine(res.date, res.start_time)
            ex_end_dt = ex_start_dt + timedelta(hours=res.duration)
            
            if ex_start_dt < new_end_dt and new_start_dt < ex_end_dt:
                overlap = True
                break
        
        if overlap:
            flash('Ce créneau horaire est déjà réservé. Veuillez en choisir un autre.')
            return render_template('reserve.html', terrain=terrain, today=datetime.utcnow().date().strftime('%Y-%m-%d'))
        
        total_price = terrain.price_per_hour * duration
        
        new_res = Reservation(
            user_id=current_user.id,
            terrain_id=terrain_id,
            date=res_date,
            start_time=res_time,
            duration=duration,
            total_price=total_price
        )
        db.session.add(new_res)
        db.session.commit()
        
        return redirect(url_for('main.receipt', res_id=new_res.id))
        
    return render_template('reserve.html', terrain=terrain, today=datetime.utcnow().date().strftime('%Y-%m-%d'))

@main.route('/reservations')
@login_required
def reservations():
    query = Reservation.query
    
    # Filtering for player
    if current_user.role != 'admin':
        query = query.filter_by(user_id=current_user.id)
    
    # Search and Filters
    search = request.args.get('search')
    status = request.args.get('status')
    
    if search:
        # Join with User and Terrain for searching
        query = query.join(User).join(Terrain).filter(
            (User.username.ilike(f'%{search}%')) | 
            (Terrain.name.ilike(f'%{search}%'))
        )
    
    if status:
        query = query.filter(Reservation.payment_status == status)
        
    all_reservations = query.order_by(Reservation.date.desc(), Reservation.start_time.desc()).all()
    
    return render_template('reservations.html', reservations=all_reservations)

@main.route('/cancel_reservation/<int:res_id>')
@login_required
def cancel_reservation(res_id):
    res = Reservation.query.get_or_404(res_id)
    
    # Check permission: Only owner can cancel (if pending) or admin can cancel anytime
    if current_user.role != 'admin' and (res.user_id != current_user.id or res.payment_status == 'Paid'):
        flash('Vous n''avez pas la permission de annuler cette réservation.')
        return redirect(url_for('main.reservations'))
    
    db.session.delete(res)
    db.session.commit()
    flash(f'Réservation #{res_id} annulée avec succès.')
    return redirect(url_for('main.reservations'))

@main.route('/receipt/<int:res_id>')
@login_required
def receipt(res_id):
    reservation = Reservation.query.get_or_404(res_id)
    if reservation.user_id != current_user.id and current_user.role != 'admin':
        return redirect(url_for('main.dashboard'))
    return render_template('receipt.html', res=reservation)

@main.route('/admin/mark_paid/<int:res_id>')
@login_required
def mark_paid(res_id):
    if current_user.role != 'admin':
        return redirect(url_for('main.dashboard'))
    
    res = Reservation.query.get_or_404(res_id)
    res.payment_status = 'Paid'
    db.session.commit()
    flash(f'Réservation #{res_id} marquée comme Payée.')
    return redirect(url_for('main.dashboard'))

@main.route('/admin/players')
@login_required
def manage_players():
    if current_user.role != 'admin':
        return redirect(url_for('main.dashboard'))
    
    players = User.query.filter_by(role='player').all()
    player_data = []
    for player in players:
        res_count = Reservation.query.filter_by(user_id=player.id).count()
        total_spent = db.session.query(func.sum(Reservation.total_price)).filter_by(user_id=player.id).scalar() or 0
        player_data.append({
            'user': player,
            'res_count': res_count,
            'total_spent': total_spent
        })
        
    return render_template('admin_players.html', players=player_data)

@main.route('/admin/settings', methods=['GET', 'POST'])
@login_required
def admin_settings():
    if current_user.role != 'admin':
        return redirect(url_for('main.dashboard'))
    
    if request.method == 'POST':
        new_admin_username = request.form.get('username')
        new_admin_password = request.form.get('password')
        
        user_exists = User.query.filter_by(username=new_admin_username).first()
        if user_exists:
            flash('Ce nom d''utilisateur existe déjà.')
        else:
            new_admin = User(username=new_admin_username, role='admin')
            new_admin.set_password(new_admin_password)
            db.session.add(new_admin)
            db.session.commit()
            flash(f'Compte administrateur pour {new_admin_username} créé avec succès !')
            
    admins = User.query.filter_by(role='admin').all()
    return render_template('admin_settings.html', admins=admins)

@main.route('/admin/delete_player/<int:user_id>')
@login_required
def delete_player(user_id):
    if current_user.role != 'admin':
        return redirect(url_for('main.dashboard'))
    
    user = User.query.get_or_404(user_id)
    if user.role == 'admin':
        flash('Impossible de supprimer des administrateurs.')
        return redirect(url_for('main.manage_players'))
    
    Reservation.query.filter_by(user_id=user_id).delete()
    db.session.delete(user)
    db.session.commit()
    flash(f'Joueur {user.username} supprimé.')
    return redirect(url_for('main.manage_players'))
