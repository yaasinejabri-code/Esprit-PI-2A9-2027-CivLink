# CivLink – Plateforme des services publics intelligents

## Overview
This project was developed as part of the Projet Technologies Web – 2nd Year Engineering Program at **Esprit School of Engineering** (Academic Year 2026–2027).

CivLink centralise les services municipaux en ligne : démarches administratives, signalements de problèmes urbains, paiement de factures et assistant IA. *L'innovation au service des citoyens.*

## Features
- Carte interactive des administrations
- Démarches administratives et documents requis
- Signalements avec photo, localisation et catégorisation par IA
- Factures et paiement (simulé)
- Assistant IA / FAQ
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
- Emna Benguiza – Utilisateurs
- Sarra Ben Hattab – Administrations
- Nour El Houda Ben Naceur – Démarches
- Yassine Jabri – Signalements
- Nour Nouri – Factures
- Adam Abdeljawed – Assistant FAQ

## Academic Context
Developed at **Esprit School of Engineering – Tunisia**
Projet Technologies Web 2A | 2026–2027

## Getting Started
1. Cloner le dépôt :
   ```bash
   git clone https://github.com/<organisation>/Esprit-<PI>-<Classe>-2026-CivLink.git
   ```
2. Placer le dossier dans `htdocs` (XAMPP) ou `www` (WAMP) et démarrer Apache + MySQL.
3. Importer `database/civlink.sql` dans phpMyAdmin.
4. Ouvrir `http://localhost/CivLink/public/index.php` (Front) et `.../admin.php` (Back).

## Acknowledgments
Merci à Mme Oumeima Ibn Elfekih et aux tuteurs de **Esprit School of Engineering**. Templates : eLearning (ThemeWagon) et TailAdmin (MIT).
