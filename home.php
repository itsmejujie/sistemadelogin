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

$next_id = '';
$idUsuario = "";
$nomeUsuario = "Nome do Usuário"; 
$success_message = '';

if (isset($_SESSION['idUsuario'])) {
    $idUsuario = $_SESSION['idUsuario'];

    $sql_nome_usuario = "SELECT nomeUsuario FROM usuarios WHERE idUsuario = $idUsuario";
    $result_nome_usuario = mysqli_query($conn, $sql_nome_usuario);

    if ($result_nome_usuario && $row_nome_usuario = mysqli_fetch_assoc($result_nome_usuario)) {
        $nomeUsuario = $row_nome_usuario['nomeUsuario'];
    }
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $descricao = mysqli_real_escape_string($conn, $_POST['descricao']);
    $data = $_POST['data'];

    mysqli_begin_transaction($conn);

    $sql_next_protocol_id = "SELECT AUTO_INCREMENT FROM information_schema.TABLES WHERE TABLE_SCHEMA = '$dbname' AND TABLE_NAME = 'protocolos'";
    $result_next_protocol_id = mysqli_query($conn, $sql_next_protocol_id);

    if ($result_next_protocol_id && $row_next_protocol_id = mysqli_fetch_assoc($result_next_protocol_id)) {
        $next_id = $row_next_protocol_id['AUTO_INCREMENT'];
    } else {
        $next_id = 1;
    }

    $sql_check_id = "SELECT id_protocolo FROM protocolos WHERE id_protocolo = $next_id";
    $result_check_id = mysqli_query($conn, $sql_check_id);

    while ($result_check_id && mysqli_num_rows($result_check_id) > 0) {
        $next_id++;
        $sql_check_id = "SELECT id_protocolo FROM protocolos WHERE id_protocolo = $next_id";
        $result_check_id = mysqli_query($conn, $sql_check_id);
    }

    $sql_insert_protocolo = "INSERT INTO protocolos (id_protocolo, descricao, data, contribuinte) VALUES ($next_id, '$descricao', '$data', '$nomeUsuario (ID: $idUsuario)')";
    $result_insert_protocolo = mysqli_query($conn, $sql_insert_protocolo);

    if ($result_insert_protocolo) {
        mysqli_commit($conn);
        $success_message = 'Protocolo enviado com sucesso!';
    } else {
        mysqli_rollback($conn);
        die("Erro na inserção: " . mysqli_error($conn));
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="home.css" />
    <title>PROTOCOLO</title>
</head>

<body>
    <h2>Criar Novo Protocolo</h2>

    <?php if (!empty($success_message)) : ?>
        <div style="color: green;"><?php echo $success_message; ?></div>
    <?php endif; ?>

    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <label for="id">ID do Protocolo:</label>
        <input type="text" id="id" name="id" value="<?php echo $next_id; ?>" readonly>

        <label for="descricao">Descrição do protocolo:</label>
        <textarea id="descricao" name="descricao" rows="4" cols="50" maxlength="255" required></textarea>

        <label for="data">Data:</label>
        <input type="date" id="data" name="data" value="<?php echo date("Y-m-d"); ?>" required>

        <label for="contribuinte">Contribuinte:</label>
        <input type="text" name="contribuinte" value="<?php echo htmlspecialchars($nomeUsuario) . " (ID: $idUsuario)"; ?>" readonly>

        <input type="submit" value="Criar Protocolo">
    </form>
</body>

</html>
