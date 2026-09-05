<?php

session_start();
require __DIR__ . "/src/Repositorio/CatalogoRepositorio.php";
require __DIR__ . "/src/funcoes.php";

if (!csrfValido($_POST['csrf_token'] ?? null)) {
    http_response_code(403);
    die('Sessão expirada. Recarregue a página e tente novamente.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$repositorio = new CatalogoRepositorio(__DIR__ . "/dados/catalogo.json");
$id = $_POST['id'] ?? '';
$nota = $_POST['nota'] ?? null;

if ($id === '' || $nota === null || !is_numeric($nota)) {
    header('Location: index.php');
    exit;
}

$nota = (float) $nota;

if ($nota < 0 || $nota > 5) {
    header('Location: index.php');
    exit;
}

$itens = $repositorio->lerItens();

foreach ($itens as &$item) {
    if ($item['id'] === $id) {
        $titulo = $repositorio->hidratar($item);
        $titulo->avalia($nota);
        $item = $repositorio->desidratar($titulo, $item['id'], $item['tipo']);
        break;
    }
}
unset($item);

$repositorio->gravarItens($itens);

header('Location: index.php');
exit;
