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

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nomeUsuario = $_POST["username"];
    $senha = $_POST["password"];

    $sql = "SELECT id, senha FROM usuarios WHERE nomeUsuario = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $nomeUsuario);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result) {
        $row = mysqli_fetch_assoc($result);
        if ($row && password_verify($senha, $row['senha'])) {
            session_regenerate_id(true);
            $_SESSION["usuario_autenticado"] = true;
            $_SESSION["usuario_id"] = $row['id'];

            mysqli_close($conn);
            header("Location: home.php");
            exit();
        } else {
            $_SESSION['login_erro'] = "Usuário ou senha inválidos.";
            mysqli_close($conn);
            header("Location: login.php");
            exit();
        }
    } else {
        $_SESSION['login_erro'] = "Erro na consulta: " . mysqli_error($conn);
        mysqli_close($conn);
        header("Location: login.php");
        exit();
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="login.css" />
    <title>Página de login</title>
</head>

<body>
    <div class="container">
        <form action="login.php" method="post">
            <label for="username">Usuário:</label>
            <input type="text" name="username" required><br>

            <label for="password">Senha:</label>
            <input type="password" name="password" required><br>

            <button type="submit">Entrar</button>
        </form>

        <p>Não tem uma conta? <a href="cadastro.php">Cadastre-se</a></p>

        <?php
        if (isset($_SESSION['login_erro'])) {
            echo "<p class='erro'>" . $_SESSION['login_erro'] . "</p>";
            unset($_SESSION['login_erro']);
        }
        ?>
    </div>
</body>

</html>
