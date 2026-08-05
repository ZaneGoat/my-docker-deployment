USE my_project;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(50) NOT NULL
);

CREATE TABLE IF NOT EXISTS client (
    N_clt INT AUTO_INCREMENT PRIMARY KEY,
    nom_clt VARCHAR(100),
    prenom VARCHAR(100),
    tele_clt VARCHAR(20),
    adrs_clt VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS fournisseur (
    N_frs INT AUTO_INCREMENT PRIMARY KEY,
    nom_frs VARCHAR(100),
    prnom_frs VARCHAR(100),
    tele_frs VARCHAR(20),
    adrs_frs VARCHAR(255),
    email_frs VARCHAR(100)
);

CREATE TABLE IF NOT EXISTS produit (
    N_prd INT AUTO_INCREMENT PRIMARY KEY,
    catg VARCHAR(100),
    NomPrd VARCHAR(160),
    QteStck INT,
    PrixAch DECIMAL(10,2),
    PrixVnt DECIMAL(10,2)
);

CREATE TABLE IF NOT EXISTS commande (
    N_cmd INT AUTO_INCREMENT PRIMARY KEY,
    date_cmd DATE,
    mt_cmd DECIMAL(12,2),
    N_frs INT,
    FOREIGN KEY (N_frs) REFERENCES fournisseur(N_frs)
);

CREATE TABLE IF NOT EXISTS livraison (
    N_livrs INT AUTO_INCREMENT PRIMARY KEY,
    date_livrs DATE,
    nom_frs VARCHAR(100),
    N_frs INT,
    FOREIGN KEY (N_frs) REFERENCES fournisseur(N_frs)
);

CREATE TABLE IF NOT EXISTS facture (
    N_fct INT AUTO_INCREMENT PRIMARY KEY,
    date_fct DATE,
    tva DECIMAL(5,2),
    total_HT DECIMAL(12,2),
    TTC DECIMAL(12,2),
    N_frs INT,
    FOREIGN KEY (N_frs) REFERENCES fournisseur(N_frs)
);

INSERT INTO users (username, password) VALUES ('admin', 'admin123');
