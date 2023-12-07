<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "devprefa";

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Conexão falhou: " . mysqli_connect_error());
}

$nomeUsuario = isset($_POST['nomeUsuario']) ? $_POST['nomeUsuario'] : '';
$nome = isset($_POST['nome']) ? $_POST['nome'] : '';
$email = isset($_POST['email']) ? $_POST['email'] : '';
$numero = isset($_POST['numero']) ? $_POST['numero'] : '';
$sexo = isset($_POST['opcoes']) ? $_POST['opcoes'] : '';
$senha = isset($_POST['senha']) ? $_POST['senha'] : '';

$sql_check_username = "SELECT * FROM usuarios WHERE nomeUsuario = ?";
$stmt_check_username = mysqli_prepare($conn, $sql_check_username);

if (!$stmt_check_username) {
    die("Preparação da consulta falhou: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt_check_username, "s", $nomeUsuario);
mysqli_stmt_execute($stmt_check_username);
$result_check_username = mysqli_stmt_get_result($stmt_check_username);

$sql_check_email = "SELECT * FROM usuarios WHERE email = ?";
$stmt_check_email = mysqli_prepare($conn, $sql_check_email);

if (!$stmt_check_email) {
    die("Preparação da consulta falhou: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt_check_email, "s", $email);
mysqli_stmt_execute($stmt_check_email);
$result_check_email = mysqli_stmt_get_result($stmt_check_email);

if (mysqli_num_rows($result_check_username) > 0) {
    $_SESSION['cadastro_erro'] = "Erro: Já existe um usuário com esse Nome de Usuário.";
    header("Location: cadastro.php");
    exit();
} elseif (mysqli_num_rows($result_check_email) > 0) {
    $_SESSION['cadastro_erro'] = "Erro: Já existe um usuário com esse Email.";
    header("Location: login.php");
    exit();
} else {
    $hashedSenha = password_hash($senha, PASSWORD_DEFAULT);

    $sql_insert_user = "INSERT INTO usuarios (nomeUsuario, nome, email, numero, sexo, senha) 
                        VALUES (?, ?, ?, ?, ?, ?)";
    $stmt_insert_user = mysqli_prepare($conn, $sql_insert_user);

    if (!$stmt_insert_user) {
        die("Preparação da consulta falhou: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt_insert_user, "ssssss", $nomeUsuario, $nome, $email, $numero, $sexo, $hashedSenha);

    if (mysqli_stmt_execute($stmt_insert_user)) {
        $last_inserted_id = mysqli_insert_id($conn);
        $_SESSION['cadastro_sucesso'] = "Usuário cadastrado com sucesso! ID do usuário: " . $last_inserted_id;
        header("Location: home.php");
        exit();
    } else {
        $_SESSION['cadastro_erro'] = "Erro ao cadastrar usuário: " . mysqli_stmt_error($stmt_insert_user);
        header("Location: cadastro.php");
        exit();
    }
}


?>
