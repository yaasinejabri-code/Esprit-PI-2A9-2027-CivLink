-- CIVLINK - Gestion Administration / Service
-- Importer via phpMyAdmin dans civlink_auth. N'efface pas les autres tables.
CREATE DATABASE IF NOT EXISTS civlink_auth CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE civlink_auth;
CREATE TABLE IF NOT EXISTS administration (
 idAdministration INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
 siteRef VARCHAR(30) NOT NULL UNIQUE,
 typeAdministration VARCHAR(40) NOT NULL,
 nom VARCHAR(160) NOT NULL,
 adresse VARCHAR(220) NOT NULL,
 horaires VARCHAR(120) NOT NULL DEFAULT '',
 telephone VARCHAR(40) NOT NULL DEFAULT '',
 latitude DECIMAL(10,7) NOT NULL,
 longitude DECIMAL(10,7) NOT NULL,
 actif TINYINT(1) NOT NULL DEFAULT 1,
 INDEX idx_admin_type (typeAdministration)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS service (
 idService INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
 idAdministration INT NOT NULL,
 nom VARCHAR(120) NOT NULL,
 description TEXT NULL,
 actif TINYINT(1) NOT NULL DEFAULT 1,
 CONSTRAINT fk_service_administration FOREIGN KEY(idAdministration)
 REFERENCES administration(idAdministration) ON DELETE CASCADE,
 INDEX idx_service_administration (idAdministration)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- Conserve les IDs de la carte (s1...s25), sans écraser les modifications futures.
INSERT IGNORE INTO administration (siteRef,typeAdministration,nom,adresse,horaires,telephone,latitude,longitude,actif) VALUES ('s1', 'commune', 'Municipalité de Tunis', 'Place de la Kasbah, Tunis', 'Lun–Ven, 8h–17h', '+216 00 000 000', 36.7985, 10.1705, 1);
INSERT IGNORE INTO administration (siteRef,typeAdministration,nom,adresse,horaires,telephone,latitude,longitude,actif) VALUES ('s2', 'commune', 'Municipalité de l’Ariana', 'Centre-ville, Ariana', 'Lun–Ven, 8h–17h', '+216 00 000 000', 36.8665, 10.1647, 1);
INSERT IGNORE INTO administration (siteRef,typeAdministration,nom,adresse,horaires,telephone,latitude,longitude,actif) VALUES ('s3', 'commune', 'Municipalité de La Marsa', 'Centre-ville, La Marsa', 'Lun–Ven, 8h–17h', '+216 00 000 000', 36.8782, 10.3247, 1);
INSERT IGNORE INTO administration (siteRef,typeAdministration,nom,adresse,horaires,telephone,latitude,longitude,actif) VALUES ('s4', 'commune', 'Municipalité de Ben Arous', 'Centre-ville, Ben Arous', 'Lun–Ven, 8h–17h', '+216 00 000 000', 36.7531, 10.2189, 1);
INSERT IGNORE INTO administration (siteRef,typeAdministration,nom,adresse,horaires,telephone,latitude,longitude,actif) VALUES ('s5', 'etat-civil', 'État civil – Bab Souika', 'Arrondissement Bab Souika, Tunis', 'Lun–Ven, 8h–16h', '+216 00 000 000', 36.8115, 10.1695, 1);
INSERT IGNORE INTO administration (siteRef,typeAdministration,nom,adresse,horaires,telephone,latitude,longitude,actif) VALUES ('s6', 'etat-civil', 'État civil – El Menzah', 'Arrondissement El Menzah, Tunis', 'Lun–Ven, 8h–16h', '+216 00 000 000', 36.843, 10.168, 1);
INSERT IGNORE INTO administration (siteRef,typeAdministration,nom,adresse,horaires,telephone,latitude,longitude,actif) VALUES ('s7', 'etat-civil', 'État civil – La Marsa', 'Mairie annexe, La Marsa', 'Lun–Ven, 8h–16h', '+216 00 000 000', 36.877, 10.326, 1);
INSERT IGNORE INTO administration (siteRef,typeAdministration,nom,adresse,horaires,telephone,latitude,longitude,actif) VALUES ('s8', 'securite', 'Poste de police – Tunis centre', 'Avenue Habib Bourguiba, Tunis', 'Lun–Ven, 8h–16h', '+216 00 000 000', 36.7995, 10.18, 1);
INSERT IGNORE INTO administration (siteRef,typeAdministration,nom,adresse,horaires,telephone,latitude,longitude,actif) VALUES ('s9', 'securite', 'Poste de police – Ariana', 'Centre-ville, Ariana', 'Lun–Ven, 8h–16h', '+216 00 000 000', 36.861, 10.193, 1);
INSERT IGNORE INTO administration (siteRef,typeAdministration,nom,adresse,horaires,telephone,latitude,longitude,actif) VALUES ('s10', 'steg', 'STEG – Agence Tunis centre', 'Centre-ville, Tunis', 'Lun–Ven, 8h–16h', '+216 00 000 000', 36.801, 10.18, 1);
INSERT IGNORE INTO administration (siteRef,typeAdministration,nom,adresse,horaires,telephone,latitude,longitude,actif) VALUES ('s11', 'steg', 'STEG – Agence Ariana', 'Ariana', 'Lun–Ven, 8h–16h', '+216 00 000 000', 36.865, 10.195, 1);
INSERT IGNORE INTO administration (siteRef,typeAdministration,nom,adresse,horaires,telephone,latitude,longitude,actif) VALUES ('s12', 'steg', 'STEG – Agence La Marsa', 'La Marsa', 'Lun–Ven, 8h–16h', '+216 00 000 000', 36.877, 10.326, 1);
INSERT IGNORE INTO administration (siteRef,typeAdministration,nom,adresse,horaires,telephone,latitude,longitude,actif) VALUES ('s13', 'steg', 'STEG – Agence Ben Arous', 'Ben Arous', 'Lun–Ven, 8h–16h', '+216 00 000 000', 36.754, 10.23, 1);
INSERT IGNORE INTO administration (siteRef,typeAdministration,nom,adresse,horaires,telephone,latitude,longitude,actif) VALUES ('s14', 'sonede', 'SONEDE – Agence Tunis centre', 'Centre-ville, Tunis', 'Lun–Ven, 8h–16h', '+216 00 000 000', 36.795, 10.175, 1);
INSERT IGNORE INTO administration (siteRef,typeAdministration,nom,adresse,horaires,telephone,latitude,longitude,actif) VALUES ('s15', 'sonede', 'SONEDE – Agence Ariana', 'Ariana', 'Lun–Ven, 8h–16h', '+216 00 000 000', 36.86, 10.19, 1);
INSERT IGNORE INTO administration (siteRef,typeAdministration,nom,adresse,horaires,telephone,latitude,longitude,actif) VALUES ('s16', 'sonede', 'SONEDE – Agence Ben Arous', 'Ben Arous', 'Lun–Ven, 8h–16h', '+216 00 000 000', 36.75, 10.22, 1);
INSERT IGNORE INTO administration (siteRef,typeAdministration,nom,adresse,horaires,telephone,latitude,longitude,actif) VALUES ('s17', 'cnam', 'CNAM – Bureau régional Tunis', 'Tunis', 'Lun–Ven, 8h–16h', '+216 00 000 000', 36.8065, 10.1815, 1);
INSERT IGNORE INTO administration (siteRef,typeAdministration,nom,adresse,horaires,telephone,latitude,longitude,actif) VALUES ('s18', 'cnam', 'CNAM – Bureau Ariana', 'Ariana', 'Lun–Ven, 8h–16h', '+216 00 000 000', 36.868, 10.17, 1);
INSERT IGNORE INTO administration (siteRef,typeAdministration,nom,adresse,horaires,telephone,latitude,longitude,actif) VALUES ('s19', 'cnss', 'CNSS – Bureau régional Tunis', 'Tunis', 'Lun–Ven, 8h–16h', '+216 00 000 000', 36.807, 10.178, 1);
INSERT IGNORE INTO administration (siteRef,typeAdministration,nom,adresse,horaires,telephone,latitude,longitude,actif) VALUES ('s20', 'cnss', 'CNSS – Bureau Ben Arous', 'Ben Arous', 'Lun–Ven, 8h–16h', '+216 00 000 000', 36.751, 10.225, 1);
INSERT IGNORE INTO administration (siteRef,typeAdministration,nom,adresse,horaires,telephone,latitude,longitude,actif) VALUES ('s21', 'poste', 'Poste centrale de Tunis', 'Rue Charles de Gaulle, Tunis', 'Lun–Sam, 8h–17h', '+216 00 000 000', 36.7993, 10.1795, 1);
INSERT IGNORE INTO administration (siteRef,typeAdministration,nom,adresse,horaires,telephone,latitude,longitude,actif) VALUES ('s22', 'poste', 'Bureau de poste – Ariana', 'Centre-ville, Ariana', 'Lun–Sam, 8h–17h', '+216 00 000 000', 36.866, 10.163, 1);
INSERT IGNORE INTO administration (siteRef,typeAdministration,nom,adresse,horaires,telephone,latitude,longitude,actif) VALUES ('s23', 'poste', 'Bureau de poste – La Marsa', 'Centre-ville, La Marsa', 'Lun–Sam, 8h–17h', '+216 00 000 000', 36.878, 10.324, 1);
INSERT IGNORE INTO administration (siteRef,typeAdministration,nom,adresse,horaires,telephone,latitude,longitude,actif) VALUES ('s24', 'recette', 'Recette des finances – Tunis', 'Tunis centre', 'Lun–Ven, 8h–16h', '+216 00 000 000', 36.8, 10.175, 1);
INSERT IGNORE INTO administration (siteRef,typeAdministration,nom,adresse,horaires,telephone,latitude,longitude,actif) VALUES ('s25', 'recette', 'Recette des finances – Ariana', 'Ariana', 'Lun–Ven, 8h–16h', '+216 00 000 000', 36.864, 10.17, 1);
