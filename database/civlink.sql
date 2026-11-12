CREATE DATABASE IF NOT EXISTS civlink CHARACTER SET utf8mb4;
USE civlink;
CREATE TABLE categorie (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(80) NOT NULL
);
CREATE TABLE signalement (
  id INT AUTO_INCREMENT PRIMARY KEY,
  titre VARCHAR(150) NOT NULL,
  description TEXT,
  photo VARCHAR(255),
  latitude DECIMAL(9,6),
  longitude DECIMAL(9,6),
  statut ENUM('en_attente','en_cours','resolu') DEFAULT 'en_attente',
  categorie_id INT,
  date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (categorie_id) REFERENCES categorie(id)
);
INSERT INTO categorie (nom) VALUES ('Voirie'),('Éclairage'),('Déchets'),('Eau');
