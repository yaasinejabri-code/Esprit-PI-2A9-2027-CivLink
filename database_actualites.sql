-- Exécuter une seule fois dans phpMyAdmin, après database_auth.sql.
CREATE DATABASE IF NOT EXISTS civlink_auth CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE civlink_auth;
CREATE TABLE IF NOT EXISTS categorie_actualite (
 idCategorie INT AUTO_INCREMENT PRIMARY KEY,
 nom VARCHAR(90) NOT NULL UNIQUE,
 description VARCHAR(500) NOT NULL DEFAULT ''
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS actualite (
 idActualite INT AUTO_INCREMENT PRIMARY KEY,
 idCategorie INT NOT NULL,
 idAuteur INT NULL,
 titre VARCHAR(180) NOT NULL,
 resume VARCHAR(500) NOT NULL,
 contenu TEXT NOT NULL,
 icone VARCHAR(90) NOT NULL DEFAULT 'far fa-newspaper',
 lecture VARCHAR(20) NOT NULL DEFAULT '2 min',
 lien VARCHAR(80) DEFAULT NULL,
 lienTexte VARCHAR(100) DEFAULT NULL,
 statut ENUM('brouillon','publie') NOT NULL DEFAULT 'brouillon',
 datePublication DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 dateModification TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 CONSTRAINT fk_actualite_categorie FOREIGN KEY (idCategorie) REFERENCES categorie_actualite(idCategorie) ON DELETE RESTRICT,
 CONSTRAINT fk_actualite_auteur FOREIGN KEY (idAuteur) REFERENCES utilisateur(idUtilisateur) ON DELETE SET NULL,
 INDEX idx_actualite_pub (statut,datePublication)
) ENGINE=InnoDB;
INSERT IGNORE INTO categorie_actualite (nom,description) VALUES
 ('Services publics','Informations sur les services publics'),
 ('Démarches','Conseils pour les démarches administratives'),
 ('Vie citoyenne','Participation et signalements'),
 ('Factures','Informations sur les factures simulées'),
 ('Rendez-vous','Informations sur les rendez-vous'),
 ('CIVLINK','Informations sur le projet CIVLINK');
-- Les articles sont créés dans le Back Office. Aucun article officiel inventé.
