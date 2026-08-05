<?php
// seed.php - Adds Moroccan test values to the database
require 'db.php';

try {
    $pdo->beginTransaction();

    // 1. Insert Categories
    $pdo->exec("INSERT INTO Categorie (nom, description) VALUES 
        ('Plats Traditionnels', 'Nos célèbres tagines et couscous marocains authentiques.'),
        ('Entrées & Soupes', 'Des entrées chaudes et froides pour commencer en beauté.'),
        ('Desserts & Boissons', 'Douceurs marocaines et notre fameux thé à la menthe.')
    ");

    // Get the IDs of the newly inserted categories (assuming the table was empty, they will be 1, 2, 3)
    // To be safe, let's fetch them:
    $stmt = $pdo->query("SELECT id_categorie, nom FROM Categorie");
    $cats = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if (strpos($row['nom'], 'Plats') !== false) $cats['plats'] = $row['id_categorie'];
        if (strpos($row['nom'], 'Entrées') !== false) $cats['entrees'] = $row['id_categorie'];
        if (strpos($row['nom'], 'Desserts') !== false) $cats['desserts'] = $row['id_categorie'];
    }

    // 2. Insert Products
    if (isset($cats['plats'], $cats['entrees'], $cats['desserts'])) {
        $stmt_prod = $pdo->prepare("INSERT INTO Produit (libelle, prix, description, id_categorie) VALUES (?, ?, ?, ?)");
        
        $produits = [
            // Plats Traditionnels
            ['Tagine de Poulet aux Olives et Citron Confit', 75.00, 'Un classique marocain mijoté lentement avec des épices douces, des olives vertes et du citron.', $cats['plats']],
            ['Couscous Royal aux Sept Légumes', 90.00, 'Grains de semoule fins servis avec un assortiment de sept légumes de saison et de la viande de bœuf fondante.', $cats['plats']],
            ['Tagine de Kefta aux Oeufs', 65.00, 'Boulettes de viande hachée épicées dans une sauce tomate riche avec des œufs pochés sur le dessus.', $cats['plats']],
            
            // Entrées & Soupes
            ['Harira Traditionnelle', 35.00, 'Soupe marocaine réconfortante à base de tomates, lentilles, pois chiches et coriandre fraîche.', $cats['entrees']],
            ['Zaalouk d\'Aubergines', 25.00, 'Caviar d\'aubergines grillées à la tomate, à l\'ail et à l\'huile d\'olive.', $cats['entrees']],
            ['Salade Marocaine', 20.00, 'Salade rafraîchissante de tomates, concombres et oignons finement hachés avec une vinaigrette légère.', $cats['entrees']],
            
            // Desserts & Boissons
            ['Thé à la Menthe Fraîche', 15.00, 'Le véritable thé vert marocain servi bien chaud et sucré avec des feuilles de menthe.', $cats['desserts']],
            ['Assortiment de Pâtisseries (Cornes de Gazelle, Chebakia)', 40.00, 'Sélection de nos meilleures pâtisseries traditionnelles aux amandes et au miel.', $cats['desserts']],
            ['Pastilla au Lait et Amandes', 45.00, 'Feuilles de brick croustillantes superposées avec une crème de lait parfumée à la fleur d\'oranger.', $cats['desserts']]
        ];

        foreach ($produits as $p) {
            $stmt_prod->execute($p);
        }
    }

    // 3. Insert a Sample Client
    $pdo->exec("INSERT INTO Client (nom, prenom, tel, email) VALUES ('Alami', 'Mohammed', '0600112233', 'med.alami@example.com')");
    $id_client = $pdo->lastInsertId();

    // 4. Insert a Sample Commande
    $pdo->exec("INSERT INTO Commande (statut, No_ticket, mode_paiement, id_client) VALUES ('Validée', 'TICK-TEST-001', 'Espèces', $id_client)");
    $id_commande = $pdo->lastInsertId();

    // 5. Add items to the Commande
    $stmt_detail = $pdo->prepare("INSERT INTO Detail_Commande (id_commande, id_produit, quantite) VALUES (?, ?, ?)");
    // Find the first product to add
    $id_prod1 = $pdo->query("SELECT id_produit FROM Produit LIMIT 1")->fetchColumn();
    $stmt_detail->execute([$id_commande, $id_prod1, 2]); // 2 Tagines

    $pdo->commit();
    echo "Moroccan test data seeded successfully!";

} catch (Exception $e) {
    $pdo->rollBack();
    echo "Error seeding data: " . $e->getMessage();
}
?>
