# CivLink

Plateforme web qui centralise les services publics tunisiens : administrations, démarches, signalements, factures, rendez-vous et actualités.

Projet réalisé à **Esprit School of Engineering – Tunisie** (projet intégré, équipe **Innovia**), année universitaire 2026–2027.

## Technologies

- PHP (architecture MVC : `Controllers`, `Models`, `Views`)
- MySQL (accès via PDO)
- HTML, CSS, JavaScript
- XAMPP (Apache + MySQL) pour l'exécution en local

## Fonctionnalités

Deux espaces : **Front Office** (citoyen) et **Back Office** (administrateur).

| Gestion | Contenu | Responsable |
|---|---|---|
| Administrations | Administration + Service | Sarra Ben Hattab |
| Démarches | Démarche + Documents requis | Nour El Houda Ben Naceur |
| Signalements | Catégories de signalement + Signalement | Yassine Jabri |
| Factures | Facture + Mode de paiement (paiement simulé) | Nour Nouri |
| Rendez-vous | Créneaux horaires + Rendez-vous | Emna Benguiza |
| Actualités | Catégories d'actualité + Actualité | Adam Abdeljawed |

Fonctionnalités communes : authentification, gestion des utilisateurs et des rôles.

## Structure du projet

```
Controllers/   logique et routes
Models/        accès aux données (Auth.php, CivData.php)
Views/
  FrontOffice/ pages citoyen
  BackOffice/  pages administrateur
database_*.sql scripts de création des tables
index.php      point d'entrée
```

## Installation en local

1. Installer [XAMPP](https://www.apachefriends.org) et démarrer **Apache** et **MySQL**.
2. Cloner le dépôt dans le dossier `htdocs` de XAMPP :
   ```bash
   git clone https://github.com/yaasinejabri-code/Esprit-PI-2A9-2027-CivLink.git
   ```
3. Ouvrir **phpMyAdmin** et importer les scripts SQL du dépôt (`database_*.sql` et `actualites_exemples.sql`).
4. Vérifier les paramètres de connexion PDO dans `Models/` (par défaut : utilisateur `root`, sans mot de passe).
5. Ouvrir dans le navigateur : `http://localhost/<dossier-du-projet>/`
   (si Apache utilise un autre port, par exemple 8080 : `http://localhost:8080/<dossier-du-projet>/`).

Plus de détails dans `INSTALLATION.txt`.

## Équipe

- Sarra Ben Hattab
- Nour El Houda Ben Naceur
- Yassine Jabri
- Nour Nouri
- Emna Benguiza
- Adam Abdeljawed

## Remarque

Les données de factures (STEG / SONEDE) sont des données de démonstration ; aucun paiement bancaire réel n'est effectué.
