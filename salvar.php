<?php

require __DIR__ . "/src/Repositorio/CatalogoRepositorio.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: adicionar.php');
    exit;
}

$repositorio = new CatalogoRepositorio(__DIR__ . "/dados/catalogo.json");

$tipo = $_POST['tipo'] ?? 'filme';
$nome = trim($_POST['nome'] ?? '');
$ano = (int) ($_POST['ano'] ?? 0);
$genero = CatalogoRepositorio::generoFromNome($_POST['genero'] ?? 'Drama');

if ($nome === '' || $ano === 0) {
    header('Location: adicionar.php');
    exit;
}

if ($tipo === 'serie') {
    $titulo = new Serie(
        $nome,
        $ano,
        $genero,
        (int) ($_POST['temporadas'] ?? 1),
        (int) ($_POST['episodiosPorTemporada'] ?? 1),
        (int) ($_POST['minutosPorEpisodio'] ?? 1),
    );
} else {
    $titulo = new Filme(
        $nome,
        $ano,
        $genero,
        (int) ($_POST['duracaoEmMinutos'] ?? 0),
    );
}

if (isset($_POST['nota']) && $_POST['nota'] !== '' && is_numeric($_POST['nota'])) {
    $nota = (float) $_POST['nota'];
    if ($nota >= 0 && $nota <= 5) {
        $titulo->avalia($nota);
    }
}

$id = uniqid('titulo_', true);
$item = $repositorio->desidratar($titulo, $id, $tipo);

$itens = $repositorio->lerItens();
$itens[] = $item;
$repositorio->gravarItens($itens);

header('Location: index.php');
exit;
