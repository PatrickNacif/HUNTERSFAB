<?php
session_start();

// =======================
// CONFIGURAÇÕES DE BANCO DE DADOS
// =======================
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "formulario_cadastro";

// =======================
// CONEXÃO
// =======================
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("<div class='mensagem erro'>❌ Erro na conexão: " . $conn->connect_error . "</div>");
}

// =======================
// FUNÇÃO DE MENSAGEM
// =======================
function exibir_mensagem($texto, $tipo = 'erro', $redirect = null, $tempo = 3) {
    $cor = ($tipo === 'sucesso') ? '#28a745' : '#dc3545';

    // Se houver redirecionamento, adiciona meta refresh
    $meta = $redirect ? "<meta http-equiv='refresh' content='{$tempo};url={$redirect}'>" : "";

    echo "<!DOCTYPE html>
    <html lang='pt-BR'>
    <head>
        <meta charset='UTF-8'>
        {$meta}
        <title>Login</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #f3f3f3;
                display: flex;
                align-items: center;
                justify-content: center;
                height: 100vh;
                margin: 0;
            }
            .msg {
                background: #fff;
                padding: 30px 50px;
                border-radius: 12px;
                box-shadow: 0 0 10px rgba(0,0,0,0.1);
                text-align: center;
            }
            h2 { color: {$cor}; }
            a { color: #007bff; text-decoration: none; margin-top: 15px; display: inline-block; }
        </style>
    </head>
    <body>
        <div class='msg'>
            <h2>{$texto}</h2>";

    // Se for erro, mostra link para tentar novamente
    if ($tipo !== 'sucesso') {
        echo "<a href='javascript:history.back()'>Tentar novamente</a>";
    }

    echo "</div>
    </body>
    </html>";
    exit;
}

// =======================
// LOGIN
// =======================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if (empty($email) || empty($senha)) {
        exibir_mensagem("❌ Preencha todos os campos!", "erro");
    }

    $stmt = $conn->prepare("SELECT * FROM mensagens WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $row = $result->fetch_assoc()) {
        if (password_verify($senha, $row['senha'])) {
            $_SESSION['usuario'] = $email;

            // ✅ Mensagem de sucesso + redirecionamento após 2 segundos
            exibir_mensagem("✅ Login realizado com sucesso! Redirecionando...", "sucesso", "performace.html", 2);
        } else {
            exibir_mensagem("❌ Usuário ou senha incorretos!", "erro");
        }
    } else {
        exibir_mensagem("❌ Usuário ou senha incorretos!", "erro");
    }

    $stmt->close();
}

$conn->close();
?>
