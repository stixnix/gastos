<?php
// Iniciar a sessão
session_start();

// Destruir todas as variáveis da sessão
$_SESSION = [];

// Destruir a sessão
session_destroy();

// Redirecionar para a página de login (ou a página inicial)
header("Location: login.html");
exit;
?>
