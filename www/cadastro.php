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
// FUNÇÃO DE MENSAGEM (MESMO ESTILO DO LOGIN)
// =======================
function exibir_mensagem($texto, $tipo = 'erro', $redirect = null, $tempo = 3) {
    $cor = ($tipo === 'sucesso') ? '#28a745' : '#dc3545';
    $meta = $redirect ? "<meta http-equiv='refresh' content='{$tempo};url={$redirect}'>" : "";

    echo "<!DOCTYPE html>
    <html lang='pt-BR'>
    <head>
        <meta charset='UTF-8'>
        {$meta}
        <title>Cadastro</title>
        <link rel='stylesheet' href='css/index.css'>
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

    if ($tipo !== 'sucesso') {
        echo "<a href='javascript:history.back()'>Tentar novamente</a>";
    }

    echo "</div>
    </body>
    </html>";
    exit;
}

// =======================
// PEGANDO OS DADOS DO FORMULÁRIO
// =======================
$nome         = trim($_POST['nome'] ?? '');
$email        = trim($_POST['email'] ?? '');
$telefone     = trim($_POST['telefone'] ?? '');
$modelo_carro = trim($_POST['modelo_carro'] ?? '');
$ano          = (int)($_POST['ano'] ?? 0);
$km           = trim($_POST['km'] ?? '');
$cidade       = trim($_POST['cidade'] ?? '');
$estado       = trim($_POST['estado'] ?? '');
$endereco     = trim($_POST['endereco'] ?? '');
$senha        = $_POST['senha'] ?? '';

// =======================
// VERIFICAÇÃO BÁSICA
// =======================
if (empty($nome) || empty($email) || empty($senha)) {
    exibir_mensagem("❌ Nome, e-mail e senha são obrigatórios!", "erro");
}

// =======================
// VERIFICA SE O EMAIL JÁ EXISTE
// =======================
$check = $conn->prepare("SELECT id FROM mensagens WHERE email = ?");
$check->bind_param("s", $email);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    $check->close();
    $conn->close();
    exibir_mensagem("❌ E-mail já cadastrado. Tente novamente com outro endereço.", "erro");
}
$check->close();

// =======================
// HASH DA SENHA
// =======================
$senha_hash = password_hash($senha, PASSWORD_DEFAULT);

// =======================
// INSERE DADOS NO BANCO
// =======================
$stmt = $conn->prepare("INSERT INTO mensagens 
    (nome, email, telefone, modelo_carro, ano, km, cidade, estado, endereco, senha)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

if (!$stmt) {
    exibir_mensagem("❌ Erro ao preparar statement: " . $conn->error, "erro");
}

$stmt->bind_param(
    "ssssisssss",
    $nome,
    $email,
    $telefone,
    $modelo_carro,
    $ano,
    $km,
    $cidade,
    $estado,
    $endereco,
    $senha_hash
);

if ($stmt->execute()) {
    exibir_mensagem("✅ Cadastro realizado com sucesso! Redirecionando para login...", "sucesso", "login.html", 2);
} else {
    exibir_mensagem("❌ Erro ao cadastrar: " . $stmt->error, "erro");
}

$stmt->close();
$conn->close();
?>
