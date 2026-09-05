<?php

require __DIR__ . "/src/Repositorio/CatalogoRepositorio.php";

$repositorio = new CatalogoRepositorio(__DIR__ . "/dados/catalogo.json");
$id = $_GET['id'] ?? '';

if ($id !== '') {
    $itens = $repositorio->lerItens();
    $itens = array_values(array_filter($itens, fn($item) => $item['id'] !== $id));
    $repositorio->gravarItens($itens);
}

header('Location: index.php');
exit;
