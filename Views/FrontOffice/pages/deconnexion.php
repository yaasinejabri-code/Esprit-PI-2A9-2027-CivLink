<?php
require_once __DIR__.'/../../../Models/Auth.php'; authStart();
if ($_SERVER['REQUEST_METHOD']!=='POST' || !authCheck()) { http_response_code(403); exit('Requête non autorisée.'); }
$_SESSION=[]; $p=session_get_cookie_params(); setcookie(session_name(),'',time()-3600,$p['path'],$p['domain'],$p['secure'],$p['httponly']); session_destroy(); authRedirect('authentification.php');
