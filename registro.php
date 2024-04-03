<?php
    require 'config/config.php';
    require 'config/database.php';
    require 'clases/clienteFunciones.php';
    $db = new Database();
    $con = $db->conectar();


    $errors = [];

    if(!empty($_POST)){
        $nombres = trim($_POST['nombres']);
        $apellidos = trim($_POST['apellidos']);
        $email = trim($_POST['email']);
        $telefono = trim($_POST['telefono']);
        $usuario = trim($_POST['usuario']);
        $password = trim($_POST['password']);
        $repassword = trim($_POST['repassword']);

        if(esNulo([$nombres, $apellidos, $email, $telefono, $usuario, $password, $repassword]))
        {
            $errors[] = "Debe llenar todos los campos";
        }

        if(!esEmail($email)){
            $errors[] = "la direccion de correo no es valida";
        }

        if (strlen($password) < 8){
            $errors [] = "La contraseña debe ser de 8 caracteres";
        }

        if (!validaPassword($password, $repassword)){
            $errors[] = "Las contraseñas no coinciden";
        }

        if(usuarioExiste($usuario, $con)){
            $errors[] = "Usuario $usuario ya existe";
        }

        if(emailExiste($email, $con)){
            $errors[] = "El correo electrónico ya existe";
        }

        if(count($errors) == 0){

            $id = registraCliente([$nombres, $apellidos, $email, $telefono], $con);

            if($id > 0){

                require 'clases/Mailer.php';
                $mailer = new Mailer();
                $token = generarToken();
                $pass_hash = password_hash($password, PASSWORD_DEFAULT);
                
                $idUsuario = registraUsuario([$usuario, $pass_hash, $token, $id], $con);
                if ($idUsuario > 0){

                    $url = SITE_URL.'/activar_cliente.php?id='.$idUsuario.'&token='.$token;
                    $asunto ="Activar cuenta";
                    $cuerpo = "Estimado $nombres: <br> Para continuar con el registro da click en la siguente liga <a href='$url'>Activar Cuenta<a/>";

                    if($mailer->enviarEmail($email, $asunto, $cuerpo)){
                        echo "Para terminar su registro siga las instrucciones que le enviamos a la direccion de correo electrónico $email";

                        exit;
                    }

                } else {
                    $errors[] = "Error al registrar Usuario";
                }
            } else {
                $errors[] = "Error al registrar Cliente";
            }
        }
    }
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
    <title>Registro</title>
</head>
<body>
    <main>
        <div class="container">
            <h2>Datos del Cliente</h2>

            <?php mostrarMensajes($errors);?>

            <i><span class="text-danger">*</span>Obligatorio</i>
            <br><br>
            <form action="registro.php" method="post" class="row g-3" autocomplete="off">
                <div class="col-md-6">
                    <label for="nombres"><span class="text-danger">*</span>Nombres</label>
                    <input type="text" name="nombres" id="nombres" class="form-control" requireda>
                </div>
                <div class="col-md-6">
                    <label for="apellidos"><span class="text-danger">*</span>Apellidos</label>
                    <input type="text" name="apellidos" id="apellidos" class="form-control" requireda>
                </div>
                <div class="col-md-6">
                    <label for="email"><span class="text-danger">*</span>Correo Electronico</label>
                    <input type="email" name="email" id="email" class="form-control" requireda>
                    <span id="validaEmail" class="text-danger" ></span>
                </div>
                <div class="col-md-6">
                    <label for="telefono"><span class="text-danger">*</span>Telefono</label>
                    <input type="text" name="telefono" id="telefono" class="form-control" requireda>
                </div>
                <div class="col-md-6">
                    <label for="usuario"><span class="text-danger">*</span>Usuario</label>
                    <input type="text" name="usuario" id="usuario" class="form-control" requireda>
                    <span id="validaUsuario" class="text-danger" ></span>
                </div>
                <div class="col-md-6">
                    <label for="password"><span class="text-danger">*</span>Contraseña</label>
                    <input type="password" name="password" id="password" class="form-control" requireda>
                    <div id="Msjerror"></div>
                </div>
                <div class="col-md-6">
                    <label for="repassword"><span class="text-danger">*</span>Repetir Contraseña</label>
                    <input type="password" name="repassword" id="repassword" class="form-control" requireda>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Registrar</button>
                </div>


            </form>

        </div>
    </main>

    <script>
        let txtUsuario = document.getElementById('usuario')
        txtUsuario.addEventListener("blur", function(){
            existeUsuario(txtUsuario.value)
        }, false)

        let txtEmail = document.getElementById('email')
        txtEmail.addEventListener("blur", function(){
            existeEmail(txtEmail.value)
        }, false)

        function existeUsuario(usuario){

            let url = "clases/clienteAjax.php"
            let formData = new FormData()
            formData.append("action", "existeUsuario")
            formData.append("usuario", usuario)

            fetch(url, {
                method: 'POST',
                body: formData
            }).then(response => response.json())
            .then(data => {

                if(data.ok){
                    document.getElementById('usuario').value = ''
                    document.getElementById('validaUsuario').innerHTML = 'Usuario no Disponible'
                } else {
                    document.getElementById('validaUsuario').innerHTML = ''
                }
            })
        }

        function existeEmail(email){

            let url = "clases/clienteAjax.php"
            let formData = new FormData()
            formData.append("action", "existeEmail")
            formData.append("email", email)

            fetch(url, {
                method: 'POST',
                body: formData
            }).then(response => response.json())
            .then(data => {

                if(data.ok){
                    document.getElementById('email').value = ''
                    document.getElementById('validaEmail').innerHTML = 'Email ya regsitrado'
                } else {
                    document.getElementById('validaEmail').innerHTML = ''
                }
            })
        }

        
        const passwordInput = document.getElementById('password');
        const Msjerror = document.getElementById('Msjerror');

        function validatePasswordLength() {
            const password = passwordInput.value.trim(); 

            if (password.length < 8) {
                Msjerror.textContent = 'La contraseña debe tener al menos 8 caracteres.';
                Msjerror.style.color = 'red';
            } else {
                Msjerror.textContent = '';
            }
        }

        passwordInput.addEventListener('input', validatePasswordLength);

    </script>

</body>
</html>
