from app import app
from models import db, Pizza

def seed_data():
    with app.app_context():
        # Clean existing pizzas to avoid duplicates during dev
        Pizza.query.delete()
        
        menu_items = [
            # Top Offres
            ("Offre Spéciale 1", "Une pizza délicieuse à prix réduit", "Top Offre", 45.0, "top offres 1.webp"),
            ("Offre Spéciale 2", "Le duo parfait pour votre soirée", "Top Offre", 50.0, "top offres 2.webp"),
            ("Offre Spéciale 3", "La favorite des familles", "Top Offre", 55.0, "top offres 3.webp"),
            ("Offre Spéciale 4", "L'offre gourmande à ne pas rater", "Top Offre", 60.0, "top offres 4.webp"),

            # Pizza Menu 1 (Using new image names)
            ("Hawaiienne", "Ananas, jambon, mozzarella", "Pizza", 65.0, "Hawaiienne.jpg"),
            ("La Pecheur", "Fruits de mer frais, mozzarella", "Pizza", 85.0, "La Pecheur.jpg"),
            ("La Veggie", "Légumes du soleil, olives", "Pizza", 65.0, "la veggie.jpg"),
            ("Legend Chicken BBQ", "Poulet, sauce BBQ, poivrons", "Pizza", 75.0, "legend chiken BBQ.jpg"),
            ("Chicken Legend Chipotle", "Poulet, sauce chipotle épicée", "Pizza", 80.0, "chiken lengend chiptle.jpg"),
            ("Pizza 1/2 Kilo de Fromage", "Pour les vrais amateurs de fromage", "Pizza", 90.0, "Pizza 1 2 kilo de fromage.jpg"),
            
            # Additional items from previous lists (using placeholders if no specific image)
            ("Margherita", "Sauce tomate, mozzarella, origan", "Pizza", 55.0, "Margherita.jpg"),
            ("Regina", "Jambon, mozzarella, champignons", "Pizza", 65.0, "Regina.jpg"),
            ("4 Fromages", "Mozzarella, parmesan, chèvre, roquefort", "Pizza", 75.0, "4 Fromages.jpg"),
            ("Thon", "Thon, olives, mozzarella, oignons", "Pizza", 65.0, "Thon.jpg"),
            ("Kebab", "Viande kebab, oignons, fromage", "Pizza", 75.0, "Kebab.jpg"),
            ("ChefAmin Spéciale", "Viande hachée, œuf, fromage, poivrons", "Pizza", 85.0, "chefamin.jpg"),
            ("TexMex", "Viande épicée, maïs, jalapeños", "Pizza", 80.0, "TexMex.jpg"),
            ("Calzone", "Pizza fermée jambon-fromage", "Pizza", 75.0, "Calzone.jpg"),
            ("Orientale", "Merguez, poivrons, olives", "Pizza", 85.0, "Orientale.jpg"),
            
            # Desserts
            ("Tiramisu", "Classique italien", "Dessert", 25.0, "Tiramisu.jpg"),
            ("Mousse au Chocolat", "Maison", "Dessert", 25.0, "Mousse au Chocolat.jpg"),
            ("Panna Cotta", "Coulis de fruits rouges", "Dessert", 25.0, "Panna Cotta.jpg"),
            ("Tarte au Citron", "Meringuée", "Dessert", 25.0, "Tarte au Citron.jpg"),
            ("Glace Vanille / Chocolat", "2 boules au choix", "Dessert", 20.0, "Glace Vanille Chocolat.jpg"),
            
            # Boissons
            ("Coca-Cola", "33cl", "Boisson", 10.0, "coca cola.jpg"),
            ("Pepsi", "33cl", "Boisson", 10.0, "pepsi.jpg"),
            ("Fanta", "33cl", "Boisson", 10.0, None), # No photo yet, will be hidden
            ("Sprite", "33cl", "Boisson", 10.0, "sprite.jpg"),
            ("Eau Minérale", "50cl", "Boisson", 8.0, "eau miniral.jpg"),
            ("Jus d’Orange Frais", "Pressé minute", "Boisson", 15.0, "jus orange.jpg"),
            ("Jus Citron", "Frais", "Boisson", 15.0, "jus citron.jpg"),
            ("Café Espresso", "Pur Arabica", "Boisson", 12.0, None), # No photo yet, will be hidden
        ]

        for name, desc, cat, price, photo in menu_items:
            item = Pizza(name=name, description=desc, category=cat, base_price=price, photo_url=photo)
            db.session.add(item)
        
        db.session.commit()
        print("Menu items seeded with new images successfully!")

if __name__ == "__main__":
    seed_data()
