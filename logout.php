<?php
session_start();
session_destroy();
header("Location: joueur_login.php");
exit();
?>