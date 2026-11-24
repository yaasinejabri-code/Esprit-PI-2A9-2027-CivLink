-- Importer dans civlink_auth (après database_administrations.sql)
CREATE DATABASE IF NOT EXISTS civlink_auth CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE civlink_auth;

CREATE TABLE IF NOT EXISTS document_requis (
  idDocument INT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(180) NOT NULL,
  description TEXT NULL,
  actif TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS demarche (
  idDemarche INT AUTO_INCREMENT PRIMARY KEY,
  idAdministration INT NOT NULL,
  titre VARCHAR(180) NOT NULL,
  description TEXT NULL,
  delai VARCHAR(120) NOT NULL DEFAULT '',
  cout VARCHAR(120) NOT NULL DEFAULT '',
  etapes TEXT NULL,
  actif TINYINT(1) NOT NULL DEFAULT 1,
  INDEX idx_demarche_admin(idAdministration),
  CONSTRAINT fk_demarche_administration FOREIGN KEY (idAdministration)
    REFERENCES administration(idAdministration) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS demarche_document (
  idDemarche INT NOT NULL,
  idDocument INT NOT NULL,
  PRIMARY KEY (idDemarche, idDocument),
  CONSTRAINT fk_dd_demarche FOREIGN KEY (idDemarche)
    REFERENCES demarche(idDemarche) ON DELETE CASCADE,
  CONSTRAINT fk_dd_document FOREIGN KEY (idDocument)
    REFERENCES document_requis(idDocument) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
