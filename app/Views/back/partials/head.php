<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'CivLink Admin') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --civ-midnight:#0D1320; --civ-navy:#223143; --civ-orange:#F07832; --civ-ivory:#E9E0D4; }
        body { background:#f4f6f9; }
        .admin-sidebar { position:fixed; top:0; left:0; bottom:0; width:250px; background:var(--civ-navy); color:#fff; padding:1rem; overflow-y:auto; }
        .admin-sidebar .brand { font-weight:700; font-size:1.4rem; margin-bottom:.2rem; }
        .admin-sidebar .brand span { color:var(--civ-orange); }
        .admin-sidebar .sub { font-size:.8rem; opacity:.7; margin-bottom:1.5rem; }
        .admin-sidebar a { display:block; color:#fff; text-decoration:none; padding:.6rem .8rem; border-radius:.5rem; margin-bottom:.25rem; }
        .admin-sidebar a:hover { background:rgba(255,255,255,.1); }
        .admin-sidebar a.active { background:var(--civ-orange); font-weight:600; }
        .admin-main { margin-left:250px; min-height:100vh; display:flex; flex-direction:column; }
        .admin-main > main { flex:1; }
        .admin-header { background:#fff; border-bottom:1px solid #dee2e6; }
        .btn-civ { background:var(--civ-orange); color:#fff; border:0; }
        .btn-civ:hover { background:#d9651f; color:#fff; }
        .stat-card { border-left:5px solid var(--civ-orange); }
    </style>
</head>
<body>