-- CIVLINK - Gestion des rendez-vous. Importer dans phpMyAdmin apres database_auth.sql.
CREATE DATABASE IF NOT EXISTS civlink_auth CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE civlink_auth;
CREATE TABLE IF NOT EXISTS creneau_horaire (
 idCreneau INT AUTO_INCREMENT PRIMARY KEY,
 serviceNom VARCHAR(120) NOT NULL,
 lieu VARCHAR(180) NOT NULL,
 dateCreneau DATE NOT NULL,
 heureDebut TIME NOT NULL,
 heureFin TIME NOT NULL,
 actif TINYINT(1) NOT NULL DEFAULT 1,
 dateCreation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT chk_creneau_heures CHECK (heureFin > heureDebut),
 UNIQUE KEY uq_service_lieu_debut (serviceNom, lieu, dateCreneau, heureDebut),
 KEY idx_creneau_dispo (dateCreneau, actif)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS rendez_vous (
 idRendezVous INT AUTO_INCREMENT PRIMARY KEY,
 idUtilisateur INT NOT NULL,
 idCreneau INT NOT NULL,
 motif VARCHAR(240) NOT NULL,
 statut ENUM('confirme','annule') NOT NULL DEFAULT 'confirme',
 dateReservation TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 -- Une seule reservation active par creneau, historique annule conserve.
 creneauActif INT GENERATED ALWAYS AS (CASE WHEN statut='confirme' THEN idCreneau ELSE NULL END) STORED,
 UNIQUE KEY uq_reservation_active (creneauActif),
 CONSTRAINT fk_rdv_user FOREIGN KEY (idUtilisateur) REFERENCES utilisateur(idUtilisateur) ON DELETE RESTRICT,
 CONSTRAINT fk_rdv_creneau FOREIGN KEY (idCreneau) REFERENCES creneau_horaire(idCreneau) ON DELETE RESTRICT,
 KEY idx_rdv_user (idUtilisateur, statut)
) ENGINE=InnoDB;
-- Créneaux fictifs d'essai: date relative a l'import, pas de disponibilites reelles.
INSERT IGNORE INTO creneau_horaire (serviceNom,lieu,dateCreneau,heureDebut,heureFin) VALUES
('État civil','Municipalité de Tunis',DATE_ADD(CURDATE(), INTERVAL 3 DAY),'09:00:00','09:30:00'),
('État civil','Municipalité de Tunis',DATE_ADD(CURDATE(), INTERVAL 3 DAY),'10:00:00','10:30:00'),
('Démarches administratives','Municipalité de l’Ariana',DATE_ADD(CURDATE(), INTERVAL 4 DAY),'09:30:00','10:00:00'),
('Information citoyenne','Municipalité de La Marsa',DATE_ADD(CURDATE(), INTERVAL 5 DAY),'11:00:00','11:30:00'),
('État civil','Municipalité de Tunis',DATE_ADD(CURDATE(), INTERVAL 7 DAY),'14:00:00','14:30:00');
