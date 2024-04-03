<?php 
require 'config/config.php';
require 'clases/clienteFunciones.php';

session_destroy();

header("Location: login.php");
?>