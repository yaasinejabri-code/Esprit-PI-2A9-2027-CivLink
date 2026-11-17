<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'CivLink') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --civ-navy:#223143; --civ-orange:#F07832; }
        .btn-civ { background:var(--civ-orange); color:#fff; border:0; }
        .btn-civ:hover { background:#d9651f; color:#fff; }
        .navbar-civ { background:var(--civ-navy); }
        .navbar-civ .nav-link { color:#fff; }
        .navbar-civ .nav-link.active { color:var(--civ-orange); font-weight:600; }
        footer.civ-footer { background:var(--civ-navy); color:#fff; }
    </style>
</head>
<body>