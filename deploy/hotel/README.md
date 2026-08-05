# Système de Gestion d'Hôtel

## Installation
1. Installez les dépendances :
   ```bash
   pip install -r requirements.txt
   ```

2. Lancez l'application :
   ```bash
   python app.py
   ```

## Accès
- URL : `http://127.0.0.1:5000`
- Identifiants Admin par défaut :
  - Utilisateur : `admin`
  - Mot de passe : `admin123`

## Fonctionnalités
- **Dashboard** : Statistiques en temps réel.
- **Gestion des Chambres** : Ajouter, afficher et suivre le statut des chambres.
- **Gestion des Clients** : Enregistrer les informations des clients.
- **Gestion des Réservations** : Créer des réservations avec calcul automatique du montant total.
- **Check-In** : Valider l'arrivée des clients et marquer la chambre comme occupée.
- **Check-Out** : Finaliser le séjour, libérer la chambre et générer une facture imprimable.
- **Gestion des Services** : Gérer un catalogue de services (SPA, Piscine, etc.) et les affecter aux séjours des clients avec facturation détaillée.
- **Gestion des Paiements** : Enregistrer les paiements par différents modes (Espèces, Carte, Virement), suivre les soldes restants et imprimer des reçus de paiement.

## UML
- Analyse UML du projet : [`uml-analysis.md`](uml-analysis.md)
