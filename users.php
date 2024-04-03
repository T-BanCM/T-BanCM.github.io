<?php 
    include_once 'navbar.php';
    verificasesion();

    // Verificar si el usuario tiene el rol de ADMIN
    if ($_SESSION['role'] != 1) {
        header("Location: index.php");
        exit;
    }

?>