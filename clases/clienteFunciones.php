<?php 

function esNulo(array $parametros)
{
    foreach ($parametros as $parametro){
        if(strlen(trim($parametro)) < 1) {
            return true;
        }
    }
    return false;
}

function esEmail($email)
{
    if(filter_var($email, FILTER_VALIDATE_EMAIL)){
        return true;
    }
    return false;
}

function validaPassword($password, $repassword){
    if(strcmp($password, $repassword) === 0){
        return true;
    }
    return false;
}

function generarToken()
{
    return md5(uniqid(mt_rand(),false));
}

function registraCliente(array $datos, $con)
{
    $sql = $con->prepare("INSERT INTO clientes (nombres, apellidos, email, telefono, estatus, fecha_alta) VALUES (?, ?, ?, ?, 1, now())");
    if($sql->execute($datos)){
        return $con->lastInsertId();
    }
    return 0;
}

function registraUsuario(array $datos, $con){

    $sql = $con->prepare("INSERT INTO usuarios (usuario, password, token, id_cliente, fecha_alta) VALUES (?, ?, ?, ?, now())");
    if ($sql->execute($datos)){
        return $con->lastInsertId();
    }
    return 0;
}

function usuarioExiste($usuario, $con)
{
    $sql = $con->prepare("SELECT id FROM usuarios WHERE usuario LIKE ? LIMIT 1");
    $sql->execute([$usuario]);
    if($sql->fetchColumn() > 0){
        return true;
    }
    return false;
}

function emailExiste($email, $con)
{
    $sql = $con->prepare("SELECT id FROM clientes WHERE email LIKE ? LIMIT 1");
    $sql->execute([$email]);
    if($sql->fetchColumn() > 0){
        return true;
    }
    return false;
}

function mostrarMensajes(array $errors){
    if(count($errors) > 0){
        echo '<div class="alert alert-warning alert-dismissible fade show" role="alert"><ul>';
        foreach($errors as $error){
            echo '<li>'.$error.'</li>';
        }
        echo '</ul>';
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
    }
}

function validaToken($id, $token, $con)
{
    $msg = "";
    $sql = $con->prepare("SELECT id FROM usuarios WHERE id = ? AND token LIKE ? LIMIT 1");
    $sql->execute([$id, $token]);
    if($sql->fetchColumn() > 0){
        if(activarUsuario($id, $con)){
            $msg = "Cuenta Activada.";
            echo ('<a href="login.php">Iniciar sesion</a>');
        } else {
            $msg = "Error al activar cuenta.";
        }
    } else {
        $msg = "No existe el registro del cliente.";
    }
    return $msg;
}

function activarUsuario($id, $con){
    $sql = $con->prepare("UPDATE usuarios SET activacion = 1 WHERE id = ?");
    return $sql->execute([$id]);

}

function login($usuario, $password, $con)
{
    $sql = $con->prepare("SELECT id, usuario, password FROM usuarios WHERE usuario LIKE ? LIMIT 1");
    $sql->execute([$usuario]);
    if ($row = $sql->fetch(PDO::FETCH_ASSOC)) {
        if (esActivo($usuario, $con)) {
            if (password_verify($password, $row['password'])) {
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['username'] = $row['usuario'];
                header("Location: index.php");
                exit;
            }
        } else {
            return 'El usuario no ha sido activado.';
        }
    }
    return 'El usuario y/o contraseña son incorrectos.';
}

function esActivo($usuario, $con)
{
    $sql = $con->prepare("SELECT activacion FROM usuarios WHERE usuario LIKE ? LIMIT 1");
    $sql->execute([$usuario]);
    $row = $sql->fetch(PDO::FETCH_ASSOC);
    if ($row['activacion'] == 1) {
        return true;
    }
    return false;
}

function solicitaPassword($user_id, $con)
{

    $token = generarToken();

    $sql = $con->prepare("UPDATE usuarios SET token_password=?, password_request=1 WHERE id = ?");
    if ($sql->execute([$token, $user_id])) {
        return $token;
    }
    return null;
}

function verificaTokenRequest($user_id, $token, $con)
{
    $sql = $con->prepare("SELECT id FROM usuarios WHERE id = ? AND token_password LIKE ? AND password_request=1 LIMIT 1");
    $sql->execute([$user_id, $token]);
    if ($sql->fetchColumn() > 0) {
        return true;
    }
    return false;
}

function actualizaPassword($user_id, $password, $con)
{
    $sql = $con->prepare("UPDATE usuarios SET password=?, token_password = '', password_request = 0, fecha_modifica = now() WHERE id = ?");
    if ($sql->execute([$password, $user_id])) {
        return true;
    }
    return false;
}

function verificasesion(){
    if(isset($_SESSION['user_id']) && (isset($_SESSION['username']))){

    }
    else{
        header('location: login.php');
        exit();
    }
}


function hasrole($usuario,$con) {
    $sql = $con->prepare("SELECT role_id FROM usuarios WHERE usuario LIKE ? LIMIT 1");
    $sql->execute([$usuario]);
    $row = $sql->fetch(PDO::FETCH_ASSOC);

    if ($row['role_id'] == 1) {
        $_SESSION['role'] = $row['role_id'];
        $_SESSION['nombre'] = 'ADMIN';
        return true; // El usuario tiene el rol de ADMIN
    } else if ($row['role_id'] == 2){
        $_SESSION['role'] = $row['role_id'];
        return true; 
    } else {
        echo 'no tiene rol';
        return false;// El usuario no tiene el rol de ADMIN o no se encontró en la base de datos
    }
}

function verificarol() {    
    if (isset($_SESSION['role_id']) != 1) {
        header ('location: index.php');
    }
} 


/*function verificarol($usuario,$con) {
    $sql = $con->prepare("SELECT role_id FROM usuarios WHERE usuario LIKE ? LIMIT 1");
    $sql->execute([$usuario]);
    $row = $sql->fetch(PDO::FETCH_ASSOC);

    if ($row['role_id'] != 1) {
        header('location: login.php');
    }
} */


/*function esActivo($usuario, $con)
{
    $sql = $con->prepare("SELECT activacion FROM usuarios WHERE usuario LIKE ? LIMIT 1");
    $sql->execute([$usuario]);
    $row = $sql->fetch(PDO::FETCH_ASSOC);
    if ($row['activacion'] == 1) {
        return true;
    }
    return false;
}*/

?>