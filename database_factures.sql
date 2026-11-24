-- CIVLINK / Mode démonstration : aucune transaction bancaire réelle
-- Importer dans civlink_auth après database_auth.sql
CREATE TABLE IF NOT EXISTS facture (
 idFacture INT AUTO_INCREMENT PRIMARY KEY,
 idUtilisateur INT NOT NULL,
 referenceFacture VARCHAR(65) NOT NULL UNIQUE,
 fournisseur ENUM('STEG','SONEDE') NOT NULL,
 libelle VARCHAR(160) NOT NULL,
 periode VARCHAR(120) NOT NULL,
 dateEcheance DATE NOT NULL,
 montant DECIMAL(12,3) NOT NULL,
 dateCreation TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_facture_utilisateur FOREIGN KEY (idUtilisateur) REFERENCES utilisateur(idUtilisateur),
 CONSTRAINT chk_facture_montant CHECK(montant > 0),
 INDEX idx_facture_citoyen (idUtilisateur, dateEcheance)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS paiement (
 idPaiement INT AUTO_INCREMENT PRIMARY KEY,
 idFacture INT NOT NULL UNIQUE,
 montant DECIMAL(12,3) NOT NULL,
 datePaiement DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 modePaiement VARCHAR(40) NOT NULL DEFAULT 'simulation',
 referencePaiement VARCHAR(65) NOT NULL UNIQUE,
 CONSTRAINT fk_paiement_facture FOREIGN KEY (idFacture) REFERENCES facture(idFacture) ON DELETE RESTRICT,
 CONSTRAINT chk_paiement_montant CHECK(montant > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
