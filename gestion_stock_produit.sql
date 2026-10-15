-- ============================================================
-- BASE DE DONNÉES : gestion_stock_produit
-- Projet : Gestion Stock Produit
-- Élaboré par : RAKOTOMAMONJY Jean Luciano
-- ============================================================

CREATE DATABASE IF NOT EXISTS gestion_stock_produit
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE gestion_stock_produit;

CREATE TABLE famille (
    id_famille INT AUTO_INCREMENT PRIMARY KEY,
    nom_famille VARCHAR(100) NOT NULL,
    code_famille VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE produit (
    id_prod INT AUTO_INCREMENT PRIMARY KEY,
    id_famille INT NOT NULL,
    espece VARCHAR(100) NOT NULL,
    calibre VARCHAR(20),
    reference_produit VARCHAR(50) NOT NULL UNIQUE,
    FOREIGN KEY (id_famille) REFERENCES famille(id_famille) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE depot (
    id_depot INT AUTO_INCREMENT PRIMARY KEY,
    code_depot VARCHAR(50) NOT NULL UNIQUE,
    nom_depot VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE navire (
    id_navire INT AUTO_INCREMENT PRIMARY KEY,
    nom_navire VARCHAR(100) NOT NULL,
    code_navire VARCHAR(50) NOT NULL UNIQUE,
    capitaine_navire VARCHAR(100)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE campagne (
    id_camp INT AUTO_INCREMENT PRIMARY KEY,
    annee_camp INT NOT NULL,
    date_debut_camp DATE NOT NULL,
    date_fin_camp DATE NOT NULL,
    id_navire INT NOT NULL,
    FOREIGN KEY (id_navire) REFERENCES navire(id_navire) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE voyage (
    id_voyage INT AUTO_INCREMENT PRIMARY KEY,
    date_debut_voyage DATE NOT NULL,
    date_fin_voyage DATE,
    lieu_voyage VARCHAR(100),
    id_navire INT NOT NULL,
    id_camp INT NOT NULL,
    FOREIGN KEY (id_navire) REFERENCES navire(id_navire) ON DELETE RESTRICT,
    FOREIGN KEY (id_camp) REFERENCES campagne(id_camp) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE capture (
    id_capture INT AUTO_INCREMENT PRIMARY KEY,
    id_prod INT NOT NULL,
    id_voyage INT NOT NULL,
    lieu_capture VARCHAR(100),
    reference_capture VARCHAR(50),
    quantite_capture DECIMAL(10,2) NOT NULL,
    unite_capture ENUM('tonne','kg','g') NOT NULL DEFAULT 'kg',
    date_capture DATE NOT NULL,
    periode ENUM('jour','nuit') NOT NULL DEFAULT 'jour',
    FOREIGN KEY (id_prod) REFERENCES produit(id_prod) ON DELETE RESTRICT,
    FOREIGN KEY (id_voyage) REFERENCES voyage(id_voyage) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE stock (
    id_stock INT AUTO_INCREMENT PRIMARY KEY,
    id_prod INT NOT NULL,
    id_capture INT,
    id_depot INT NOT NULL,
    id_navire INT,
    quantite_stock DECIMAL(10,2) NOT NULL,
    unite ENUM('tonne','kg','en carton') NOT NULL DEFAULT 'kg',
    date_entree_stock DATE NOT NULL,
    date_peremption DATE,
    statut VARCHAR(50) DEFAULT 'disponible',
    lot VARCHAR(50),
    FOREIGN KEY (id_prod) REFERENCES produit(id_prod) ON DELETE RESTRICT,
    FOREIGN KEY (id_capture) REFERENCES capture(id_capture) ON DELETE SET NULL,
    FOREIGN KEY (id_depot) REFERENCES depot(id_depot) ON DELETE RESTRICT,
    FOREIGN KEY (id_navire) REFERENCES navire(id_navire) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','user') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Données de test (mot de passe en clair)
INSERT INTO users (username, password, role) VALUES
('informatique', 'admin123', 'admin'),
('user1', 'user123', 'user');
