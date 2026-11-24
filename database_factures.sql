-- ============================================================================
-- CIVLINK / Gestion des factures et des modes de paiement
-- À importer dans phpMyAdmin APRÈS database_auth.sql (même base : civlink_auth).
-- Mode démonstration : aucune transaction bancaire réelle.
-- ============================================================================
USE civlink_auth;
SET NAMES utf8mb4;

-- Mode de paiement (CRUD réservé à l'administrateur)
CREATE TABLE IF NOT EXISTS modepaiement (
  idMode      INT PRIMARY KEY AUTO_INCREMENT,
  libelle     VARCHAR(80)  NOT NULL UNIQUE,
  description VARCHAR(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO modepaiement (libelle, description) VALUES
 ('Carte bancaire',      'Paiement par carte (simulation)'),
 ('Virement',            'Virement bancaire (simulation)'),
 ('Espèces',             'Paiement au guichet (simulation)'),
 ('Portefeuille mobile', 'Paiement via application mobile (simulation)');

-- Facture : appartient à un citoyen ; idMode reste NULL tant qu'elle est impayée.
CREATE TABLE IF NOT EXISTS facture (
  idFacture         INT PRIMARY KEY AUTO_INCREMENT,
  reference         VARCHAR(60)  NOT NULL UNIQUE,
  fournisseur       ENUM('STEG','SONEDE') NOT NULL,
  montant           DECIMAL(10,3) NOT NULL,
  dateEmission      DATE NOT NULL,
  dateEcheance      DATE NOT NULL,
  statut            ENUM('impayee','payee') NOT NULL DEFAULT 'impayee',
  idMode            INT NULL,
  referencePaiement VARCHAR(60) NULL UNIQUE,
  datePaiement      DATETIME NULL,
  idUtilisateur     INT NOT NULL,
  CONSTRAINT fk_facture_mode FOREIGN KEY (idMode)        REFERENCES modepaiement(idMode)    ON DELETE RESTRICT,
  CONSTRAINT fk_facture_user FOREIGN KEY (idUtilisateur) REFERENCES utilisateur(idUtilisateur) ON DELETE CASCADE,
  CONSTRAINT chk_facture_montant CHECK (montant > 0),
  CONSTRAINT chk_facture_dates   CHECK (dateEcheance >= dateEmission),
  CONSTRAINT chk_facture_paiement CHECK (statut = 'impayee' OR (referencePaiement IS NOT NULL AND datePaiement IS NOT NULL)),
  INDEX idx_facture_citoyen (idUtilisateur, dateEcheance),
  INDEX idx_facture_fournisseur (fournisseur)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Données de démonstration : 2 factures impayées + 1 réglée pour chaque citoyen déjà inscrit.
-- (INSERT IGNORE : sans danger si le script est importé plusieurs fois.)
INSERT IGNORE INTO facture (reference, fournisseur, montant, dateEmission, dateEcheance, statut, idUtilisateur)
SELECT CONCAT('STEG-2026-', u.idUtilisateur, '-00412'), 'STEG', 128.450, '2026-11-05', '2026-12-05', 'impayee', u.idUtilisateur
FROM utilisateur u JOIN role r ON r.idRole = u.idRole WHERE r.nomRole = 'citoyen';

INSERT IGNORE INTO facture (reference, fournisseur, montant, dateEmission, dateEcheance, statut, idUtilisateur)
SELECT CONCAT('SONEDE-2026-', u.idUtilisateur, '-00827'), 'SONEDE', 47.820, '2026-10-30', '2026-11-30', 'impayee', u.idUtilisateur
FROM utilisateur u JOIN role r ON r.idRole = u.idRole WHERE r.nomRole = 'citoyen';

INSERT IGNORE INTO facture (reference, fournisseur, montant, dateEmission, dateEcheance, statut, idMode, referencePaiement, datePaiement, idUtilisateur)
SELECT CONCAT('STEG-2026-', u.idUtilisateur, '-00365'), 'STEG', 96.300, '2026-09-10', '2026-10-10', 'payee',
       (SELECT MIN(idMode) FROM modepaiement), CONCAT('DEMO-20261007-', LPAD(u.idUtilisateur, 6, '0')), '2026-10-07 10:00:00', u.idUtilisateur
FROM utilisateur u JOIN role r ON r.idRole = u.idRole WHERE r.nomRole = 'citoyen';
