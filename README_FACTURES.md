# CivLink — Module « Gestion des factures et des modes de paiement »

Module du projet **CivLink** (services publics en ligne, Tunisie). Auteur du module : Nour Nouri.
Mode **démonstration** : aucune transaction bancaire réelle, aucun service STEG/SONEDE connecté.

## 1. Périmètre — conformité au diagramme de cas d'utilisation

| Acteur | Cas d'utilisation | Où c'est implémenté |
|---|---|---|
| Citoyen | Consulter mes factures (montant, échéance, statut) | `pages/factures.php` + action `lister` |
| Citoyen | Filtrer mes factures par fournisseur (*extend*) | onglets STEG / SONEDE + recherche (`factures.js`) |
| Citoyen | Simuler un paiement, **choisir un mode de paiement** (*include*) | modale en 3 étapes : choix du mode → saisie propre au mode → reçu (`simulerPaiement`) |
| Citoyen | Consulter l'historique (factures réglées) | bloc « Historique des paiements » |
| Citoyen | Télécharger un reçu de démonstration (*extend*) | action `telecharger` (fichier `recu-….html`) |
| Administrateur | Ajouter une facture, **sélectionner le citoyen** (*include*) | `pages/factures_admin.php` → `adminAjouter` |
| Administrateur | Consulter les factures + filtrer par fournisseur (*extend*) | `adminLister` (filtres fournisseur / statut / recherche) |
| Administrateur | Modifier / Supprimer une facture **si impayée** | `adminModifier`, `adminSupprimer` |
| Administrateur | Annuler un paiement simulé (retour au statut impayée) | `adminAnnulerPaiement` |
| Administrateur | Ajouter / Consulter / Modifier un mode de paiement | `modeAjouter`, `adminLister`, `modeModifier` |
| Administrateur | Supprimer un mode **si non utilisé** | `modeSupprimer` (refus si une facture l'utilise) |

## 2. Fichiers du module

Les chemins respectent l'arborescence du projet : copier les dossiers dans `C:\xampp\htdocs\CivLink\` (fusion).

| Fichier | Statut | Rôle |
|---|---|---|
| `database_factures.sql` | nouveau | tables `modepaiement`, `facture` (+ données de démo) |
| `Models/Facture.php`, `Models/ModePaiement.php` | nouveaux | entités + accès base (PDO, requêtes préparées) |
| `Controllers/FactureController.php` | nouveau | API JSON : droits, CSRF, validation serveur |
| `Views/FrontOffice/pages/factures.php` | **remplace** l'ancienne page | espace citoyen |
| `Views/FrontOffice/pages/factures_admin.php` | nouveau | espace administrateur |
| `Views/FrontOffice/assets/js/factures.js` | **remplace** (plus de localStorage) | logique citoyen |
| `Views/FrontOffice/assets/js/factures_admin.js` | nouveau | logique admin + validation JS |
| `Views/FrontOffice/assets/css/factures.css`, `factures_admin.css` | remplace / nouveau | styles |

Supprimer le dossier `_sql_a_activer_plus_tard/` : son contenu est désormais actif dans `Models/` et `Controllers/`.

## 3. Installation, configuration, exécution

1. **XAMPP** : démarrer *Apache* et *MySQL*.
2. **Dossier** : copier le projet dans `C:\xampp\htdocs\CivLink` (le nom `CivLink` est utilisé dans les chemins du code).
3. **Base de données** (phpMyAdmin → Importer), dans cet ordre : `database_auth.sql` puis `database_factures.sql`. Base utilisée : `civlink_auth`. L'import de `database_factures.sql` peut être refait sans danger.
4. **Configuration** : connexion MySQL dans `Models/Auth.php` (`127.0.0.1:3306`, `root`, sans mot de passe par défaut). À adapter si besoin.
5. **Comptes** : créer 1 compte citoyen via *Créer un compte*, puis promouvoir un compte en administrateur :
   ```sql
   UPDATE utilisateur SET idRole = (SELECT idRole FROM role WHERE nomRole='administrateur') WHERE email = 'votre@email.tn';
   ```
   Les factures de démonstration ne sont créées que pour les citoyens **déjà inscrits** au moment de l'import ; sinon l'administrateur en ajoute depuis son espace.
6. **Adresses** (utiliser `http://localhost`, jamais `file:///`) :
   - Citoyen : `http://localhost/CivLink/Views/FrontOffice/pages/factures.php`
   - Administrateur : `http://localhost/CivLink/Views/FrontOffice/pages/factures_admin.php`

Prérequis : PHP ≥ 8.1, MariaDB ≥ 10.4 / MySQL ≥ 8.0.16 (contraintes `CHECK`), extension `pdo_mysql`. Les icônes (Font Awesome) et jQuery sont chargés depuis un CDN : une connexion Internet est nécessaire pour l'affichage complet.

## 4. Scénario de démonstration 

1. Citoyen : ouvrir *Factures* → filtrer SONEDE → **Simuler le paiement** → choisir *Virement* → tenter de valider sans cocher (message d'erreur) → valider → **Télécharger le reçu**.
2. Administrateur : *Ajouter une facture* en laissant le formulaire vide (erreurs par champ), puis avec des données valides en **sélectionnant le citoyen**.
3. Administrateur : filtrer *Réglées* → **Annuler le paiement** → la facture redevient impayée côté citoyen.
4. Onglet *Modes de paiement* : ajouter « Chèque », modifier, essayer de supprimer « Carte bancaire » (refus : utilisé).

## 5. Sécurité, sessions et contrôle d'accès (critère 1.3)

- **Sessions** : cookie `HttpOnly`, `SameSite=Lax`, mode strict (`Models/Auth.php`) ; mots de passe hachés avec `password_hash` / `password_verify` (module Connexion).
- **Contrôle d'accès** : `FactureController::utilisateurCourant()` relit **le rôle et l'état actif en base à chaque requête** (pas seulement la session) ; `exigerAdmin()` protège toute action d'administration ; `factures_admin.php` renvoie 302 vers la connexion si non connecté, 403 si non administrateur.
- **Isolation des données** : un citoyen ne lit et ne paie que **ses** factures (`WHERE idUtilisateur = ?`) ; reçu inaccessible pour la facture d'autrui.
- **CSRF** : jeton de session vérifié sur tout `POST` (`authCheck()`).
- **Injection SQL** : requêtes préparées PDO partout, `LIKE` échappé.
- **XSS** : échappement côté PHP (`htmlspecialchars`) et côté JS (`esc()`), y compris dans le reçu téléchargé.
- **Intégrité** : `UPDATE … WHERE statut='impayee'` (pas de double paiement, même en concurrence), clés étrangères `RESTRICT`, contraintes `CHECK` (montant > 0, échéance ≥ émission, paiement complet).

## 6. Contrôle des saisies (critère 2.1)

| Formulaire | Règles (JS **et** PHP identiques) |
|---|---|
| Facture (admin) | citoyen actif obligatoire ; référence 3–60 car. `[A-Za-z0-9._/-]`, unique ; fournisseur ∈ {STEG, SONEDE} ; montant > 0, 3 décimales max ; dates réelles ; échéance ≥ émission |
| Mode de paiement (admin) | libellé 2–80 car., unique ; description ≤ 255 car. |
| Paiement (citoyen) | mode existant (serveur) ; carte 16 chiffres / expiration / CVV, banque + confirmation, code guichet, téléphone + code SMS (JS) |

Le JS affiche l'erreur sous chaque champ (à la sortie du champ et à l'envoi) ; le serveur renvoie `422` avec le nom du champ fautif.

## 7. Règles métier (critère 1.2)

Une facture appartient à un citoyen ; modifiable et supprimable **uniquement tant qu'impayée** ; payable une seule fois ; l'annulation d'un paiement (admin) remet `statut`, `idMode`, `referencePaiement`, `datePaiement` à zéro ; un mode utilisé par au moins une facture ne peut pas être supprimé ; seul un citoyen actif peut recevoir une facture.

## 8. Architecture (critère 1.4)

```
Navigateur ── factures.js / factures_admin.js ──fetch──▶ Controllers/FactureController.php
                                                          │ 1. session + rôle (base)  2. CSRF  3. validation
                                                          ▼
                                             Models/Facture.php · ModePaiement.php ──PDO──▶ MySQL (civlink_auth)
```
Choix : PHP/JS sans framework (cohérent avec le reste de CivLink) ; API JSON unique pour garder des pages légères ; modèles statiques pour concentrer le SQL ; validation dupliquée volontairement (confort côté JS, sécurité côté PHP).

## 9. Tests réalisés

Testé sur PHP 8.x + MariaDB 10.11 (serveur PHP intégré) : accès sans session (401), citoyen sur route admin (403), POST sans CSRF (403), paiement de la facture d'autrui (409), double paiement (409), validations (422), suppression/modification d'une facture réglée (409), suppression d'un mode utilisé (409), import SQL répété, parcours navigateur complet (Playwright). Non testé : XAMPP sous Windows, MySQL 8.

## 10. Git / GitHub, Project Board, nomenclature (critères 3.1 – 3.3)

- **Nom du dépôt** : respecter strictement la consigne de l'enseignant ➜ `…………` *(à compléter)*.
- **Branches** : `main` (stable) ← `develop` ← `feature/factures-citoyen`, `feature/factures-admin`, `feature/modes-paiement`, `fix/…`. Pull Request + relecture par un autre membre avant fusion.
- **Commits** : petits et réguliers, au présent, préfixés `feat:`, `fix:`, `docs:`, `refactor:` (ex. `feat: annulation d'un paiement simulé par l'admin`).
- **GitHub Project Board** : colonnes *Backlog → À faire → En cours → En revue → Terminé* ; une carte par cas d'utilisation du tableau , assignée à un membre.
- **Ne pas pousser** : mots de passe réels, dumps de données personnelles.

## 11. Usage de l'IA générative (critère 4.1)

Voir `docs/IA_TRANSPARENCE.md`.

## 12. Limites connues

Paiement **simulé** uniquement (les codes SMS/carte de la démo ne sont pas vérifiés côté serveur) ; pas de limitation de tentatives ni de journal d'audit ; `database_factures.sql` ne crée des factures de démo que pour les citoyens déjà inscrits.
