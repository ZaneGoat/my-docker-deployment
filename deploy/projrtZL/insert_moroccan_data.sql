USE my_project;

-- Insert Clients (Moroccan names)
INSERT INTO client (nom_clt, prenom, tele_clt, adrs_clt) VALUES 
('Benjelloun', 'Othman', '0661234567', 'Casablanca, Maarif'),
('Bennani', 'Fatima', '0667654321', 'Rabat, Agdal'),
('El Fassi', 'Mohammed', '0612345678', 'Fes, Ville Nouvelle'),
('Alaoui', 'Khadija', '0623456789', 'Marrakech, Gueliz'),
('Chraibi', 'Omar', '0634567890', 'Tanger, Centre Ville'),
('Tazi', 'Youssef', '0645678901', 'Oujda, Hay Al Andalous'),
('Mekouar', 'Amina', '0656789012', 'Agadir, Talborjt');

-- Insert Fournisseurs (Moroccan names)
INSERT INTO fournisseur (nom_frs, prnom_frs, tele_frs, adrs_frs, email_frs) VALUES 
('Lahlou', 'Mehdi', '0651112233', 'Casablanca, Sidi Maarouf', 'mehdi.lahlou@example.ma'),
('Tahiri', 'Nadia', '0652223344', 'Agadir, Dakhla', 'nadia.tahiri@example.ma'),
('Mennani', 'Hassan', '0653334455', 'Oujda, Lazaret', 'hassan.mennani@example.ma'),
('Zerouali', 'Karim', '0654445566', 'Rabat, Hassan', 'karim.zerouali@example.ma');

-- Insert Produits
INSERT INTO produit (catg, NomPrd, QteStck, PrixAch, PrixVnt) VALUES
('Electroménager', 'Réfrigérateur LG', 15, 4500.00, 5200.00),
('Electroménager', 'Machine à laver Whirlpool', 20, 3000.00, 3500.00),
('Multimédia', 'Téléviseur Samsung 55"', 10, 4800.00, 5600.00),
('Informatique', 'PC Portable HP', 25, 4200.00, 5000.00),
('Informatique', 'Souris sans fil Logitech', 50, 120.00, 180.00),
('Multimédia', 'Smartphone Apple iPhone 14', 30, 8500.00, 9500.00),
('Multimédia', 'Ecouteurs Bluetooth Sony', 40, 450.00, 600.00);

-- Insert Commandes
INSERT INTO commande (date_cmd, mt_cmd, N_frs) VALUES
('2023-10-01', 13500.00, 1),
('2023-10-05', 9600.00, 2),
('2023-10-10', 45000.00, 3);

-- Insert Livraisons
INSERT INTO livraison (date_livrs, nom_frs, N_frs) VALUES
('2023-10-03', 'Lahlou', 1),
('2023-10-07', 'Tahiri', 2),
('2023-10-12', 'Mennani', 3);

-- Insert Factures
INSERT INTO facture (date_fct, tva, total_HT, TTC, N_frs) VALUES
('2023-10-04', 20.00, 13500.00, 16200.00, 1),
('2023-10-08', 20.00, 9600.00, 11520.00, 2),
('2023-10-15', 20.00, 45000.00, 54000.00, 3);
