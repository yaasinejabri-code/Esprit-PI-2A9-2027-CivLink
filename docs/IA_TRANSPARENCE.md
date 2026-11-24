# Usage de l'IA générative — transparence, justification, esprit critique

## 1. Outil et périmètre
Assistant utilisé : **Claude (Anthropic)**, pour le module « Gestion des factures et des modes de paiement ».

| Tâche | Rôle de l'IA | Rôle de l'équipe |
|---|---|---|
| Analyse du diagramme de cas d'utilisation | Cartographie cas d'utilisation → fichiers/actions | Fourniture du diagramme, validation du périmètre |
| Code (modèles, contrôleur JSON, pages admin, JS) | Rédaction du code | Relecture, adaptation à l'architecture CivLink, tests |
| SQL | Schéma `modepaiement` / `facture`, données de démo | Import et vérification sur la machine de démonstration |
| Documentation | Rédaction du README et de ce document | Relecture, ajout du nom du dépôt et des informations Git |

## 2. Justification
L'IA a servi à gagner du temps sur du code répétitif (CRUD, validation) et à croiser le diagramme avec le code existant.

## 3. Esprit critique : ce qui a été contrôlé et corrigé
- **Écart code ↔ diagramme** : la version précédente laissait le *citoyen* créer, modifier et supprimer des factures, alors que le diagramme réserve ces actions à l'*administrateur* → retiré de l'espace citoyen.

- **Fonctionnalités manquantes** : gestion admin ( filtres, annulation d'un paiement), CRUD des modes de paiement avec refus de suppression si utilisé, téléchargement du reçu.

- **Sécurité** : rôle relu en base, CSRF, requêtes préparées, isolation des factures par citoyen, anti double paiement — vérifiés par requêtes réelles .


## 4. Journal des prompts — à compléter par chaque membre
| Date | Membre | Demande à l'IA | Résultat utilisé / modifié / rejeté | Ce que j'ai vérifié |
|---|---|---|---|
| 22/11/2026 | N. Nouri | Extraire la partie factures selon le diagramme et la grille de validation | Utilisé après tests et corrections ci-dessus | Parcours citoyen + admin, validations, accès refusés |
|  |  | 
