
<?php

session_start();

$_SESSION = [];

session_destroy();

header('Location: /public/index.php?seccion=login');
exit;
