CREATE DATABASE IF NOT EXISTS civlink_auth CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE civlink_auth;
CREATE TABLE IF NOT EXISTS role (idRole INT PRIMARY KEY AUTO_INCREMENT, nomRole VARCHAR(40) NOT NULL UNIQUE);
INSERT IGNORE INTO role (nomRole) VALUES ('citoyen'), ('administrateur');
CREATE TABLE IF NOT EXISTS utilisateur (
 idUtilisateur INT PRIMARY KEY AUTO_INCREMENT, nom VARCHAR(80) NOT NULL, prenom VARCHAR(80) NOT NULL,
 email VARCHAR(254) NOT NULL UNIQUE, motDePasseHash VARCHAR(255) NOT NULL,
 idRole INT NOT NULL, actif BOOLEAN NOT NULL DEFAULT TRUE,
 dateCreation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY (idRole) REFERENCES role(idRole)
);

CREATE TABLE IF NOT EXISTS creneau_horaire (
 idCreneau INT PRIMARY KEY AUTO_INCREMENT,
 typeService VARCHAR(40) NOT NULL,
 dateRendezVous DATE NOT NULL,
 heureDebut TIME NOT NULL,
 heureFin TIME NOT NULL,
 disponibilite BOOLEAN NOT NULL DEFAULT TRUE
);

CREATE TABLE IF NOT EXISTS rendez_vous (
 idRdv INT PRIMARY KEY AUTO_INCREMENT,
 dateReservation DATETIME DEFAULT CURRENT_TIMESTAMP,
 statut ENUM('en_attente','confirme','annule') NOT NULL DEFAULT 'en_attente',
 idUtilisateur INT NOT NULL,
 idCreneau INT NOT NULL,
 FOREIGN KEY (idUtilisateur) REFERENCES utilisateur(idUtilisateur),
 FOREIGN KEY (idCreneau) REFERENCES creneau_horaire(idCreneau)
);
