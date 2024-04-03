
<body>
    
<?php
include_once 'navbar.php';
?>

    <h1>Bienvenido <?php echo $_SESSION['username'] ?></h1>
        <div class="dropdown">
            <button class="btn btn-success btn-sm dropdown-toggle" type="button" id="btn_session" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="far fa-user"></i><?php if ($_SESSION['role'] == 1){
                    echo 'ADMIN';
            } else {
                echo 'STAFF';
            }
                    ?>
            </button>
            <ul class="dropdown-menu" aria-labelledby="btn_session">
                <li><a class="dropdown-item" href="logout.php">Cerrar sesion</a></li>
            </ul>
        </div>

</body>


