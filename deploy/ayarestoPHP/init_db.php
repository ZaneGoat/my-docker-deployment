<?php
// init_db.php - Script to create the database tables based on the MCD
require 'db.php';

$queries = [
    "CREATE TABLE IF NOT EXISTS Client (
        id_client INT AUTO_INCREMENT PRIMARY KEY,
        nom VARCHAR(255) NOT NULL,
        prenom VARCHAR(255) NOT NULL,
        tel VARCHAR(50),
        email VARCHAR(255) UNIQUE
    ) ENGINE=InnoDB;",
    
    "CREATE TABLE IF NOT EXISTS Categorie (
        id_categorie INT AUTO_INCREMENT PRIMARY KEY,
        nom VARCHAR(255) NOT NULL,
        description TEXT,
        photo VARCHAR(255)
    ) ENGINE=InnoDB;",
    
    "CREATE TABLE IF NOT EXISTS Produit (
        id_produit INT AUTO_INCREMENT PRIMARY KEY,
        libelle VARCHAR(255) NOT NULL,
        prix DECIMAL(10, 2) NOT NULL,
        description TEXT,
        photo VARCHAR(255),
        id_categorie INT,
        FOREIGN KEY (id_categorie) REFERENCES Categorie(id_categorie) ON DELETE SET NULL
    ) ENGINE=InnoDB;",
    
    "CREATE TABLE IF NOT EXISTS Commande (
        id_commande INT AUTO_INCREMENT PRIMARY KEY,
        statut VARCHAR(100),
        date DATETIME DEFAULT CURRENT_TIMESTAMP,
        No_ticket VARCHAR(100),
        mode_paiement VARCHAR(100),
        id_client INT,
        FOREIGN KEY (id_client) REFERENCES Client(id_client) ON DELETE CASCADE
    ) ENGINE=InnoDB;",
    
    "CREATE TABLE IF NOT EXISTS Detail_Commande (
        id_commande INT,
        id_produit INT,
        quantite INT DEFAULT 1,
        PRIMARY KEY (id_commande, id_produit),
        FOREIGN KEY (id_commande) REFERENCES Commande(id_commande) ON DELETE CASCADE,
        FOREIGN KEY (id_produit) REFERENCES Produit(id_produit) ON DELETE CASCADE
    ) ENGINE=InnoDB;"
];

foreach ($queries as $query) {
    try {
        $pdo->exec($query);
        echo "<p>Table setup step executed successfully.</p>";
    } catch (PDOException $e) {
        echo "<p>Error executing query: " . $e->getMessage() . "</p>";
    }
}
echo "<h3>Database Initialization Complete.</h3>";
?>
