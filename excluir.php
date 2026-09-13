<?php

require_once __DIR__ . '/bootstrap_sessao.php';
require __DIR__ . "/src/Repositorio/CatalogoRepositorio.php";
require __DIR__ . "/src/funcoes.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrfValido($_POST['csrf_token'] ?? null)) {
    header('Location: index.php');
    exit;
}

$repositorio = new CatalogoRepositorio(__DIR__ . "/dados/catalogo.json");
$id = $_POST['id'] ?? '';

if ($id !== '') {
    $itens = $repositorio->lerItens();
    $itens = array_values(array_filter($itens, fn($item) => $item['id'] !== $id));
    $repositorio->gravarItens($itens);
}

header('Location: index.php');
exit;
