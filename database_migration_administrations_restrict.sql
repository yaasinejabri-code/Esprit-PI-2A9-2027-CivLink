SET NAMES utf8mb4;
-- CIVLINK - Gestion Administration / Service
-- Base déjà importée avec l'ancienne contrainte ON DELETE CASCADE : passage à ON DELETE RESTRICT.
-- Ne supprime et ne modifie aucune donnée. À exécuter une seule fois.
USE civlink_auth;
ALTER TABLE service DROP FOREIGN KEY fk_service_administration;
ALTER TABLE service ADD CONSTRAINT fk_service_administration FOREIGN KEY (idAdministration)
 REFERENCES administration(idAdministration) ON DELETE RESTRICT;
