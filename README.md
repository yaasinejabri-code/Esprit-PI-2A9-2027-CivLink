# CivLink – Plateforme des services publics intelligents

## Overview
This project was developed as part of the Projet Technologies Web – 2nd Year Engineering Program at **Esprit School of Engineering** (Academic Year 2026–2027).

CivLink centralise les services municipaux en ligne : administrations, démarches administratives, signalements de problèmes urbains, factures, rendez-vous et actualités. *L'innovation au service des citoyens.*

## Features
- Carte interactive des administrations et services
- Démarches administratives et documents requis
- Signalements urbains avec photo, localisation et catégorie
- Factures et paiement simulé
- Rendez-vous (créneaux, réservation, annulation)
- Actualités publiées par l'administration
- Gestion des utilisateurs et des rôles
- Front Office (citoyen) et Back Office (administrateur)

## Tech Stack
### Frontend
- HTML5, CSS3, JavaScript, Bootstrap (template eLearning)
### Backend
- PHP (architecture MVC), MySQL (PDO), TailAdmin (Back Office)

## Architecture
```
app/Controllers · app/Models · app/Views/{front,back}/partials · public/ · database/
```

## Contributors
- [Sarra Ben Hattab](https://github.com/sarra931) – Administrations
- [Nour El Houda Ben Naceur](https://github.com/nourelhoudabennaceur) – Démarches
- [Yassine Jabri](https://github.com/yaasinejabri-code) – Signalements
- Nour Nouri – Factures
- [Emna Benguiza](https://github.com/emnabenguiza) – Rendez-vous
- [Adam Abdeljawed](https://github.com/adam1595) – Actualités

## Academic Context
Developed at **Esprit School of Engineering – Tunisia**
Projet Technologies Web 2A | 2026–2027

## Getting Started
1. Cloner le dépôt :
   ```bash
   git clone https://github.com/yaasinejabri-code/Esprit-PI-2A9-2027-CivLink.git
   ```
2. Placer le dossier dans `htdocs` (XAMPP) ou `www` (WAMP) et démarrer Apache + MySQL.
3. Importer `database/civlink.sql` dans phpMyAdmin.
4. Ouvrir `http://localhost/Esprit-PI-2A9-2027-CivLink/public/index.php` (Front) et `http://localhost/Esprit-PI-2A9-2027-CivLink/public/admin.php` (Back).

## Acknowledgments
Templates : eLearning (ThemeWagon) et TailAdmin (MIT).