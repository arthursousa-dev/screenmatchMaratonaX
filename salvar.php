<?php

require_once __DIR__ . '/bootstrap_sessao.php';
require __DIR__ . "/src/Repositorio/CatalogoRepositorio.php";
require __DIR__ . "/src/funcoes.php";

if (!csrfValido($_POST['csrf_token'] ?? null)) {
    http_response_code(403);
    die('Sessão expirada. Recarregue a página e tente novamente.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: adicionar.php');
    exit;
}

$repositorio = new CatalogoRepositorio(__DIR__ . "/dados/catalogo.json");

$tipo = $_POST['tipo'] ?? 'filme';
$nome = trim($_POST['nome'] ?? '');
$ano = (int) ($_POST['ano'] ?? 0);
$genero = CatalogoRepositorio::generoFromNome($_POST['genero'] ?? 'Drama');
$inteiro = fn(string $campo) => (int) ($_POST[$campo] ?? 0);
// Limites do schema (VARCHAR(150), SMALLINT) e do formulário; fora deles o INSERT falhava em silêncio.
$positivo = fn(int $v) => $v >= 1 && $v <= 32767;

$valido = in_array($tipo, ['filme', 'serie'], true)
    && $nome !== '' && strlen($nome) <= 150
    && $ano >= 1888 && $ano <= (int) date('Y') + 5
    && ($tipo === 'filme'
        ? $positivo($inteiro('duracaoEmMinutos'))
        : $positivo($inteiro('temporadas')) && $positivo($inteiro('episodiosPorTemporada')) && $positivo($inteiro('minutosPorEpisodio')));

if (!$valido) {
    $_SESSION['erro'] = 'Confira os dados: nome, ano entre 1888 e ' . ((int) date('Y') + 5) . ' e durações maiores que zero.';
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

if (!$repositorio->inserir($item)) {
    $_SESSION['erro'] = 'Não foi possível salvar o título. Tente novamente.';
    header('Location: adicionar.php');
    exit;
}

header('Location: index.php');
exit;
