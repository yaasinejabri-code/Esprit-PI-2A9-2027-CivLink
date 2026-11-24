<?php
/**
 * CivData : source unique des données (administrations, agences, documents, étapes).
 * Les pages Administrations et Démarches lisent toutes les deux ce fichier.
 * Plus tard : remplacer le contenu des méthodes par des requêtes SQL, sans toucher aux pages.
 *
 * ⚠ Coordonnées, adresses, téléphones et procédures = données d'exemple à vérifier
 *   auprès des sources officielles avant mise en production.
 */

if (!function_exists('h')) {
    function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
}

class CivData
{
    public static function categories(): array
    {
        return [
            'all'         => 'Toutes',
            'commune'     => 'Commune',
            'identite'    => 'Identité et état civil',
            'eau-energie' => 'Eau et énergie',
            'sante'       => 'Santé et social',
            'finances'    => 'Poste et finances',
        ];
    }

    /** Types d'administration + leurs documents (démarches) */
    public static function types(): array
    {
        return [
            'commune' => [
                'nom' => 'Municipalité', 'cat' => 'commune', 'icone' => 'fa-landmark',
                'desc' => 'Permis de bâtir, légalisations, propreté et voirie de la commune.',
                'docs' => [
                    'permis-batir' => [
                        'titre' => 'Permis de bâtir', 'icone' => 'fa-hard-hat',
                        'desc' => 'Autorisation de construire ou d’agrandir un bien immobilier.',
                        'delai' => '1 à 3 mois', 'cout' => 'Variable selon la surface (taxes communales)',
                        'pieces' => ['Titre foncier ou acte de propriété', 'Plans d’architecte signés', 'Dossier technique (étude de sol, béton armé)', 'Copie de la CIN'],
                        'etapes' => [
                            ['Préparer le dossier', 'Réunissez le titre foncier, les plans signés par un architecte agréé et le dossier technique.'],
                            ['Déposer la demande', 'Déposez le dossier complet au service d’urbanisme de la municipalité et conservez le récépissé.'],
                            ['Instruction du dossier', 'Les services techniques vérifient la conformité au plan d’aménagement. Une visite du terrain peut avoir lieu.'],
                            ['Payer les taxes', 'Une fois le dossier accepté, réglez les taxes à la recette municipale.'],
                            ['Retirer le permis', 'Retirez le permis signé avec votre récépissé et la quittance de paiement.'],
                        ],
                    ],
                    'legalisation' => [
                        'titre' => 'Légalisation de signature / copie conforme', 'icone' => 'fa-stamp',
                        'desc' => 'Faire certifier une signature ou la conformité d’une copie à l’original.',
                        'delai' => 'Immédiat', 'cout' => 'Timbre fiscal selon le tarif en vigueur',
                        'pieces' => ['CIN originale', 'Document à légaliser (et son original pour une copie conforme)'],
                        'etapes' => [
                            ['Se présenter au guichet', 'Rendez-vous au service de légalisation avec votre CIN et le document concerné.'],
                            ['Signer devant l’agent', 'Pour une légalisation de signature, signez en présence de l’agent.'],
                            ['Régler le timbre', 'Achetez le timbre fiscal nécessaire (recette des finances ou point de vente agréé).'],
                            ['Récupérer le document', 'L’agent appose le cachet et la signature : le document est prêt sur place.'],
                        ],
                    ],
                ],
            ],

            'etat-civil' => [
                'nom' => 'Bureau d’état civil', 'cat' => 'identite', 'icone' => 'fa-id-card',
                'desc' => 'Extraits de naissance, actes de mariage et copies d’actes.',
                'docs' => [
                    'extrait-naissance' => [
                        'titre' => 'Extrait de naissance', 'icone' => 'fa-baby',
                        'desc' => 'Obtenir une copie intégrale ou un extrait de votre acte de naissance.',
                        'delai' => 'Immédiat à 3 jours', 'cout' => 'Gratuit ou timbre fiscal selon le cas',
                        'pieces' => ['CIN du demandeur', 'Livret de famille (si disponible)', 'Lieu et date de naissance exacts'],
                        'etapes' => [
                            ['Identifier le bon bureau', 'La demande se fait auprès de la commune du lieu de naissance (ou d’inscription de l’acte).'],
                            ['Remplir la demande', 'Indiquez nom, prénom, date et lieu de naissance, et filiation au guichet.'],
                            ['Présenter votre CIN', 'L’agent vérifie votre identité et retrouve l’acte dans le registre.'],
                            ['Retirer l’extrait', 'L’extrait est remis sur place ou sous quelques jours si l’acte doit être recherché.'],
                        ],
                    ],
                    'acte-mariage' => [
                        'titre' => 'Copie d’acte de mariage', 'icone' => 'fa-ring',
                        'desc' => 'Obtenir une copie de l’acte de mariage pour vos démarches.',
                        'delai' => '1 à 3 jours', 'cout' => 'Timbre fiscal selon le tarif en vigueur',
                        'pieces' => ['CIN de l’un des époux', 'Date et lieu du mariage'],
                        'etapes' => [
                            ['Se rendre au bureau compétent', 'Allez à l’état civil de la commune où le mariage a été enregistré.'],
                            ['Déposer la demande', 'Précisez les noms des époux et la date du mariage.'],
                            ['Payer le timbre', 'Réglez le timbre fiscal si demandé.'],
                            ['Retirer la copie', 'Récupérez la copie certifiée au guichet.'],
                        ],
                    ],
                    'fiche-familiale' => [
                        'titre' => 'Fiche familiale d’état civil', 'icone' => 'fa-users',
                        'desc' => 'Document récapitulant la composition de votre famille.',
                        'delai' => '1 à 3 jours', 'cout' => 'Gratuit ou timbre fiscal selon le cas',
                        'pieces' => ['CIN du chef de famille', 'Livret de famille'],
                        'etapes' => [
                            ['Se présenter au guichet', 'Munissez-vous de la CIN et du livret de famille.'],
                            ['Demander la fiche', 'Indiquez l’usage (CNAM, école, administration) à l’agent.'],
                            ['Vérification', 'L’agent contrôle les informations du registre familial.'],
                            ['Retirer la fiche', 'La fiche vous est remise signée et cachetée.'],
                        ],
                    ],
                ],
            ],

            'securite' => [
                'nom' => 'Poste de police (CIN et passeport)', 'cat' => 'identite', 'icone' => 'fa-shield-alt',
                'desc' => 'Carte d’identité nationale et passeport.',
                'docs' => [
                    'cin' => [
                        'titre' => 'Carte d’identité nationale', 'icone' => 'fa-id-card',
                        'desc' => 'Première demande, renouvellement ou remplacement en cas de perte.',
                        'delai' => '2 à 4 semaines', 'cout' => 'Timbre fiscal selon le tarif en vigueur',
                        'pieces' => ['Extrait de naissance récent', '2 photos d’identité', 'Timbre fiscal', 'Ancienne CIN (renouvellement) ou déclaration de perte'],
                        'etapes' => [
                            ['Préparer les pièces', 'Obtenez un extrait de naissance récent, des photos et le timbre fiscal.'],
                            ['Déposer le dossier', 'Rendez-vous au poste compétent de votre lieu de résidence.'],
                            ['Empreintes et vérification', 'Vos données sont saisies et vos empreintes prélevées.'],
                            ['Attendre la fabrication', 'Un récépissé vous est remis, il sert de justificatif provisoire.'],
                            ['Retirer la carte', 'Présentez-vous avec le récépissé pour récupérer votre carte.'],
                        ],
                    ],
                    'passeport' => [
                        'titre' => 'Passeport', 'icone' => 'fa-passport',
                        'desc' => 'Première demande ou renouvellement de passeport.',
                        'delai' => '1 à 3 semaines', 'cout' => 'Timbre fiscal selon le tarif en vigueur',
                        'pieces' => ['CIN originale', 'Extrait de naissance récent', 'Photos d’identité conformes', 'Timbre fiscal', 'Ancien passeport (renouvellement)'],
                        'etapes' => [
                            ['Rassembler les pièces', 'Vérifiez la conformité des photos et la validité des documents.'],
                            ['Déposer la demande', 'Déposez le dossier au service des passeports.'],
                            ['Enregistrement biométrique', 'Photo et empreintes sont relevées sur place.'],
                            ['Retirer le passeport', 'Récupérez-le avec votre récépissé et votre CIN.'],
                        ],
                    ],
                ],
            ],

            'steg' => [
                'nom' => 'STEG', 'cat' => 'eau-energie', 'icone' => 'fa-bolt',
                'desc' => 'Électricité et gaz : abonnements, factures et pannes.',
                'docs' => [
                    'abonnement-elec' => [
                        'titre' => 'Nouvel abonnement électrique', 'icone' => 'fa-plug',
                        'desc' => 'Raccordement au réseau et pose d’un compteur.',
                        'delai' => '1 à 2 semaines', 'cout' => 'Frais de raccordement + caution',
                        'pieces' => ['CIN', 'Justificatif de propriété ou contrat de bail', 'Permis d’habiter ou autorisation (selon le cas)'],
                        'etapes' => [
                            ['Déposer la demande', 'Présentez-vous à l’agence STEG de votre zone avec les pièces.'],
                            ['Étude technique', 'Un agent évalue le raccordement et établit un devis si nécessaire.'],
                            ['Régler les frais', 'Payez les frais et la caution indiqués à l’agence.'],
                            ['Pose du compteur', 'Un technicien installe et met en service le compteur.'],
                        ],
                    ],
                    'changement-titulaire-elec' => [
                        'titre' => 'Changement de titulaire', 'icone' => 'fa-exchange-alt',
                        'desc' => 'Transférer un compteur existant à votre nom.',
                        'delai' => '2 à 5 jours', 'cout' => 'Frais administratifs selon le tarif',
                        'pieces' => ['CIN du nouveau titulaire', 'Justificatif de propriété ou bail', 'Dernière facture réglée'],
                        'etapes' => [
                            ['Réunir les pièces', 'Prenez la dernière facture et votre justificatif de logement.'],
                            ['Déposer la demande', 'Rendez-vous à l’agence avec l’ancien titulaire si possible.'],
                            ['Relevé du compteur', 'Un relevé est effectué pour solder l’ancien contrat.'],
                            ['Nouveau contrat', 'Signez le contrat à votre nom.'],
                        ],
                    ],
                ],
            ],

            'sonede' => [
                'nom' => 'SONEDE', 'cat' => 'eau-energie', 'icone' => 'fa-tint',
                'desc' => 'Eau potable : raccordement, compteurs et factures.',
                'docs' => [
                    'abonnement-eau' => [
                        'titre' => 'Abonnement eau potable', 'icone' => 'fa-faucet',
                        'desc' => 'Raccordement au réseau d’eau et pose d’un compteur.',
                        'delai' => '1 à 3 semaines', 'cout' => 'Frais de branchement + caution',
                        'pieces' => ['CIN', 'Justificatif de propriété ou bail', 'Plan de situation du logement'],
                        'etapes' => [
                            ['Déposer la demande', 'Rendez-vous à l’agence SONEDE de votre zone avec les pièces.'],
                            ['Visite technique', 'Un technicien vérifie la faisabilité du branchement.'],
                            ['Payer les frais', 'Réglez les frais de branchement et la caution.'],
                            ['Pose du compteur', 'Le compteur est installé et l’eau mise en service.'],
                        ],
                    ],
                    'changement-titulaire-eau' => [
                        'titre' => 'Changement de titulaire', 'icone' => 'fa-exchange-alt',
                        'desc' => 'Transférer un compteur d’eau à votre nom.',
                        'delai' => '2 à 5 jours', 'cout' => 'Frais administratifs selon le tarif',
                        'pieces' => ['CIN du nouveau titulaire', 'Justificatif de logement', 'Dernière facture réglée'],
                        'etapes' => [
                            ['Réunir les pièces', 'Prenez votre CIN, la dernière facture et le justificatif de logement.'],
                            ['Déposer la demande', 'Présentez le dossier à l’agence.'],
                            ['Relevé du compteur', 'Un relevé permet de solder l’ancien contrat.'],
                            ['Signer le contrat', 'Le contrat est établi à votre nom.'],
                        ],
                    ],
                ],
            ],

            'cnam' => [
                'nom' => 'CNAM', 'cat' => 'sante', 'icone' => 'fa-heartbeat',
                'desc' => 'Assurance maladie : remboursements et carnets de soins.',
                'docs' => [
                    'remboursement' => [
                        'titre' => 'Remboursement de soins', 'icone' => 'fa-file-invoice-dollar',
                        'desc' => 'Demander le remboursement de frais médicaux.',
                        'delai' => '2 à 4 semaines', 'cout' => 'Gratuit',
                        'pieces' => ['Carnet de soins', 'Factures et ordonnance', 'CIN de l’assuré'],
                        'etapes' => [
                            ['Rassembler les justificatifs', 'Conservez factures, ordonnances et feuilles de soins.'],
                            ['Déposer le dossier', 'Remettez-le au bureau CNAM dont vous dépendez.'],
                            ['Examen du dossier', 'La CNAM contrôle les pièces et calcule le remboursement.'],
                            ['Recevoir le remboursement', 'Le montant est versé selon le mode choisi.'],
                        ],
                    ],
                    'carnet-soins' => [
                        'titre' => 'Renouvellement du carnet de soins', 'icone' => 'fa-book-medical',
                        'desc' => 'Renouveler ou remplacer votre carnet de soins.',
                        'delai' => '1 à 2 semaines', 'cout' => 'Gratuit',
                        'pieces' => ['CIN', 'Ancien carnet (ou déclaration de perte)', 'Attestation d’affiliation'],
                        'etapes' => [
                            ['Préparer le dossier', 'Rassemblez CIN, ancien carnet et attestation.'],
                            ['Déposer la demande', 'Présentez-vous au bureau régional.'],
                            ['Vérification des droits', 'Vos droits et ayants droit sont contrôlés.'],
                            ['Retirer le carnet', 'Le nouveau carnet vous est remis sur place ou envoyé.'],
                        ],
                    ],
                ],
            ],

            'cnss' => [
                'nom' => 'CNSS', 'cat' => 'sante', 'icone' => 'fa-hands-helping',
                'desc' => 'Sécurité sociale : affiliation, cotisations et prestations.',
                'docs' => [
                    'attestation-affiliation' => [
                        'titre' => 'Attestation d’affiliation', 'icone' => 'fa-file-medical',
                        'desc' => 'Obtenir une attestation de sécurité sociale.',
                        'delai' => 'Immédiat', 'cout' => 'Gratuit',
                        'pieces' => ['CIN', 'Numéro d’affilié'],
                        'etapes' => [
                            ['Se présenter à l’agence', 'Apportez votre CIN et votre numéro d’affilié.'],
                            ['Demander l’attestation', 'Précisez l’usage de l’attestation.'],
                            ['Retirer le document', 'Elle est remise immédiatement.'],
                        ],
                    ],
                    'allocations-familiales' => [
                        'titre' => 'Allocations familiales', 'icone' => 'fa-child',
                        'desc' => 'Demander le versement des allocations pour vos enfants.',
                        'delai' => '2 à 6 semaines', 'cout' => 'Gratuit',
                        'pieces' => ['CIN', 'Livret de famille', 'Extraits de naissance des enfants', 'Certificat de scolarité (enfants scolarisés)'],
                        'etapes' => [
                            ['Réunir les pièces', 'Prenez les extraits de naissance et les certificats de scolarité.'],
                            ['Déposer la demande', 'Remettez le dossier au bureau CNSS.'],
                            ['Examen du droit', 'La CNSS vérifie vos cotisations et la situation familiale.'],
                            ['Début du versement', 'Les allocations sont versées selon le calendrier de la caisse.'],
                        ],
                    ],
                ],
            ],

            'poste' => [
                'nom' => 'Poste Tunisienne', 'cat' => 'finances', 'icone' => 'fa-envelope',
                'desc' => 'Courrier, colis, mandats et services financiers postaux.',
                'docs' => [
                    'mandat' => [
                        'titre' => 'Envoi d’un mandat postal', 'icone' => 'fa-money-check-alt',
                        'desc' => 'Envoyer de l’argent à un destinataire en Tunisie.',
                        'delai' => '1 à 3 jours', 'cout' => 'Frais proportionnels au montant',
                        'pieces' => ['CIN de l’expéditeur', 'Nom et adresse du destinataire'],
                        'etapes' => [
                            ['Se rendre au guichet', 'Présentez votre CIN à un bureau de poste.'],
                            ['Remplir le formulaire', 'Indiquez montant et coordonnées du destinataire.'],
                            ['Payer le montant et les frais', 'Réglez la somme plus les frais.'],
                            ['Conserver le reçu', 'Gardez le reçu comme preuve d’envoi.'],
                        ],
                    ],
                    'ccp' => [
                        'titre' => 'Ouverture d’un compte CCP', 'icone' => 'fa-piggy-bank',
                        'desc' => 'Ouvrir un compte courant postal.',
                        'delai' => 'Immédiat à 3 jours', 'cout' => 'Dépôt initial minimum',
                        'pieces' => ['CIN', 'Justificatif de domicile', 'Dépôt initial'],
                        'etapes' => [
                            ['Se présenter au bureau', 'Apportez votre CIN et un justificatif de domicile.'],
                            ['Remplir la demande', 'Complétez le formulaire d’ouverture.'],
                            ['Effectuer le dépôt', 'Versez le dépôt initial demandé.'],
                            ['Recevoir les accès', 'Vous recevez votre numéro de compte et votre carte.'],
                        ],
                    ],
                ],
            ],

            'recette' => [
                'nom' => 'Recette des finances', 'cat' => 'finances', 'icone' => 'fa-coins',
                'desc' => 'Impôts locaux, timbres fiscaux et quittances.',
                'docs' => [
                    'taxe-locale' => [
                        'titre' => 'Paiement de la taxe locale', 'icone' => 'fa-coins',
                        'desc' => 'Régler vos impôts locaux et obtenir la quittance.',
                        'delai' => 'Immédiat', 'cout' => 'Montant de l’avis d’imposition',
                        'pieces' => ['Avis d’imposition', 'CIN'],
                        'etapes' => [
                            ['Vérifier l’avis', 'Contrôlez le montant et la référence sur l’avis d’imposition.'],
                            ['Se rendre à la recette', 'Présentez l’avis au guichet.'],
                            ['Régler le montant', 'Payez le montant dû.'],
                            ['Récupérer la quittance', 'Conservez la quittance comme preuve de paiement.'],
                        ],
                    ],
                    'timbre-fiscal' => [
                        'titre' => 'Achat de timbres fiscaux', 'icone' => 'fa-receipt',
                        'desc' => 'Acheter les timbres exigés pour vos démarches.',
                        'delai' => 'Immédiat', 'cout' => 'Valeur faciale du timbre',
                        'pieces' => ['Aucune pièce (CIN conseillée)'],
                        'etapes' => [
                            ['Connaître la valeur requise', 'Demandez à l’administration concernée le montant du timbre.'],
                            ['Acheter le timbre', 'Achetez-le à la recette ou chez un revendeur agréé.'],
                            ['Le coller sur le dossier', 'Collez-le à l’endroit indiqué avant le dépôt.'],
                        ],
                    ],
                ],
            ],
        ];
    }

    /** Agences / bureaux (lat, lng approximatifs autour du Grand Tunis) */
    public static function sites(): array
    {
        $t = '+216 00 000 000';
        $s = [
            ['commune',    'Municipalité de Tunis',          'Place de la Kasbah, Tunis',           'Lun–Ven, 8h–17h', 36.7985, 10.1705],
            ['commune',    'Municipalité de l’Ariana',       'Centre-ville, Ariana',                'Lun–Ven, 8h–17h', 36.8665, 10.1647],
            ['commune',    'Municipalité de La Marsa',       'Centre-ville, La Marsa',              'Lun–Ven, 8h–17h', 36.8782, 10.3247],
            ['commune',    'Municipalité de Ben Arous',      'Centre-ville, Ben Arous',             'Lun–Ven, 8h–17h', 36.7531, 10.2189],
            ['etat-civil', 'État civil – Bab Souika',        'Arrondissement Bab Souika, Tunis',    'Lun–Ven, 8h–16h', 36.8115, 10.1695],
            ['etat-civil', 'État civil – El Menzah',         'Arrondissement El Menzah, Tunis',     'Lun–Ven, 8h–16h', 36.8430, 10.1680],
            ['etat-civil', 'État civil – La Marsa',          'Mairie annexe, La Marsa',             'Lun–Ven, 8h–16h', 36.8770, 10.3260],
            ['securite',   'Poste de police – Tunis centre', 'Avenue Habib Bourguiba, Tunis',      'Lun–Ven, 8h–16h', 36.7995, 10.1800],
            ['securite',   'Poste de police – Ariana',       'Centre-ville, Ariana',                'Lun–Ven, 8h–16h', 36.8610, 10.1930],
            ['steg',       'STEG – Agence Tunis centre',     'Centre-ville, Tunis',                 'Lun–Ven, 8h–16h', 36.8010, 10.1800],
            ['steg',       'STEG – Agence Ariana',           'Ariana',                              'Lun–Ven, 8h–16h', 36.8650, 10.1950],
            ['steg',       'STEG – Agence La Marsa',         'La Marsa',                            'Lun–Ven, 8h–16h', 36.8770, 10.3260],
            ['steg',       'STEG – Agence Ben Arous',        'Ben Arous',                           'Lun–Ven, 8h–16h', 36.7540, 10.2300],
            ['sonede',     'SONEDE – Agence Tunis centre',   'Centre-ville, Tunis',                 'Lun–Ven, 8h–16h', 36.7950, 10.1750],
            ['sonede',     'SONEDE – Agence Ariana',         'Ariana',                              'Lun–Ven, 8h–16h', 36.8600, 10.1900],
            ['sonede',     'SONEDE – Agence Ben Arous',      'Ben Arous',                           'Lun–Ven, 8h–16h', 36.7500, 10.2200],
            ['cnam',       'CNAM – Bureau régional Tunis',   'Tunis',                               'Lun–Ven, 8h–16h', 36.8065, 10.1815],
            ['cnam',       'CNAM – Bureau Ariana',           'Ariana',                              'Lun–Ven, 8h–16h', 36.8680, 10.1700],
            ['cnss',       'CNSS – Bureau régional Tunis',   'Tunis',                               'Lun–Ven, 8h–16h', 36.8070, 10.1780],
            ['cnss',       'CNSS – Bureau Ben Arous',        'Ben Arous',                           'Lun–Ven, 8h–16h', 36.7510, 10.2250],
            ['poste',      'Poste centrale de Tunis',        'Rue Charles de Gaulle, Tunis',        'Lun–Sam, 8h–17h', 36.7993, 10.1795],
            ['poste',      'Bureau de poste – Ariana',       'Centre-ville, Ariana',                'Lun–Sam, 8h–17h', 36.8660, 10.1630],
            ['poste',      'Bureau de poste – La Marsa',     'Centre-ville, La Marsa',              'Lun–Sam, 8h–17h', 36.8780, 10.3240],
            ['recette',    'Recette des finances – Tunis',   'Tunis centre',                        'Lun–Ven, 8h–16h', 36.8000, 10.1750],
            ['recette',    'Recette des finances – Ariana',  'Ariana',                              'Lun–Ven, 8h–16h', 36.8640, 10.1700],
        ];
        $out = [];
        foreach ($s as $i => $r) {
            $out[] = [
                'id' => 's' . ($i + 1), 'type' => $r[0], 'nom' => $r[1], 'adresse' => $r[2],
                'horaires' => $r[3], 'tel' => $t, 'lat' => $r[4], 'lng' => $r[5],
            ];
        }
        // Après l'import SQL, la base devient la source des administrations.
        // Avant l'import, les 25 lieux de démonstration restent disponibles.
        try {
            require_once __DIR__ . '/Auth.php';
            $q = authDb()->query('SELECT siteRef, typeAdministration, nom, adresse, horaires, telephone, latitude, longitude FROM administration WHERE actif=1 ORDER BY idAdministration');
            $dbSites = [];
            foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $r) {
                $dbSites[] = [
                    'id' => $r['siteRef'], 'type' => $r['typeAdministration'],
                    'nom' => $r['nom'], 'adresse' => $r['adresse'],
                    'horaires' => $r['horaires'], 'tel' => $r['telephone'],
                    'lat' => (float)$r['latitude'], 'lng' => (float)$r['longitude']
                ];
            }
            return $dbSites;
        } catch (PDOException $e) {
            return $out;
        }
    }

    /** Versions "à plat" pour le JS de la page carte */
    public static function sitesForMap(): array
    {
        $types = self::types();
        $cats  = self::categories();
        $out = [];
        foreach (self::sites() as $s) {
            $t = $types[$s['type']] ?? null;
            if (!$t) continue;
            $out[] = $s + [
                'typeNom' => $t['nom'], 'cat' => $t['cat'], 'catLabel' => $cats[$t['cat']],
                'icone' => $t['icone'], 'desc' => $t['desc'], 'nbDocs' => count($t['docs']),
            ];
        }
        // Chargement des services actifs associés aux administrations de la carte.
        // Pas de requête par administration : on récupère tous les services en une fois.
        $servicesBySite = [];
        try {
            require_once __DIR__ . '/Auth.php';
            $db = authDb();
            $stmt = $db->query(
                'SELECT a.siteRef, s.nom, s.description
                 FROM service s
                 INNER JOIN administration a ON a.idAdministration = s.idAdministration
                 WHERE s.actif = 1 AND a.actif = 1
                 ORDER BY s.nom'
            );
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $service) {
                $servicesBySite[$service['siteRef']][] = [
                    'nom' => $service['nom'],
                    'description' => $service['description'] ?? ''
                ];
            }
        } catch (PDOException $e) {
            // La carte reste utilisable si les tables n'ont pas encore été importées.
        }
        foreach ($out as &$site) {
            $site['services'] = $servicesBySite[$site['id']] ?? [];
        }
        unset($site);
        return $out;
    }

    public static function demarchesUrl(string $base, string $type, ?string $site = null, ?string $doc = null): string
    {
        $q = ['admin' => $type];
        if ($site) $q['site'] = $site;
        if ($doc)  $q['doc'] = $doc;
        return $base . '/pages/demarches.php?' . http_build_query($q);
    }
}