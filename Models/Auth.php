<?php
function authStart(): void {
    if (session_status() === PHP_SESSION_ACTIVE) return;
    ini_set('session.use_strict_mode', '1');
    session_name('CIVLINKSESSID');
    session_set_cookie_params(['path'=>'/CivLink/', 'httponly'=>true, 'samesite'=>'Lax', 'secure'=>!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off']);
    session_start();
}
function authDb(): PDO {
    static $db;
    if (!$db) $db = new PDO('mysql:host=127.0.0.1;port=3306;dbname=civlink_auth;charset=utf8mb4', 'root', '', [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_EMULATE_PREPARES=>false]);
    return $db;
}
function authEscape(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
function authToken(): string { return $_SESSION['csrf'] ??= bin2hex(random_bytes(32)); }
function authCheck(): bool { return isset($_POST['csrf']) && is_string($_POST['csrf']) && hash_equals(authToken(), $_POST['csrf']); }
function authPost(string $key): string { return is_string($_POST[$key] ?? null) ? $_POST[$key] : ''; }
function authRedirect(string $page): never { header('Location: /CivLink/Views/FrontOffice/pages/'.$page, true, 303); exit; }
function authLogin(array $user): void {
    session_regenerate_id(true);
    $_SESSION = ['user'=>['id'=>(int)$user['idUtilisateur'],'nom'=>$user['nom'],'prenom'=>$user['prenom'],'email'=>$user['email'],'role'=>$user['nomRole']]];
}
