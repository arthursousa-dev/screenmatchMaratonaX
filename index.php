<?php

require __DIR__ . "/src/Repositorio/CatalogoRepositorio.php";
require __DIR__ . "/src/funcoes.php";

$repositorio = new CatalogoRepositorio(__DIR__ . "/dados/catalogo.json");
$itens = $repositorio->lerItens();

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MaratonaX — Catálogo</title>
  <link rel="stylesheet" href="css/estilo.css">
</head>
<body>

<?php require __DIR__ . "/navbar.php"; ?>

<main>
  <div class="cabecalho-secao">
    <div>
      <h1>Seu catálogo</h1>
      <p><?= count($itens) ?> título(s) cadastrado(s)</p>
    </div>
    <a href="adicionar.php" class="btn">+ Adicionar título</a>
  </div>

  <?php if (empty($itens)): ?>
    <div class="vazio">
      <p>Seu catálogo ainda está vazio.</p>
      <p><a href="adicionar.php">Adicione o primeiro filme ou série</a> para começar.</p>
    </div>
  <?php else: ?>
    <div class="grid-catalogo">
      <?php foreach ($itens as $i => $item):
        $titulo = $repositorio->hidratar($item);
        $temNotas = count($titulo->notas()) > 0;
      ?>
        <div class="card" style="--i:<?= $i ?>">
          <div class="poster">
            <span class="badge-tipo"><?= $item['tipo'] === 'serie' ? 'Série' : 'Filme' ?></span>
            <span class="iniciais"><?= htmlspecialchars(iniciaisTitulo($titulo->nome)) ?></span>
          </div>
          <div class="card-corpo">
            <h3><?= htmlspecialchars($titulo->nome) ?></h3>
            <div class="meta">
              <span><?= $titulo->anoLancamento ?></span>
              <span class="separador">/</span>
              <span><?= formatarDuracao($titulo->duracaoEmMinutos()) ?></span>
            </div>
            <span class="badge-genero"><?= CatalogoRepositorio::rotuloGenero($titulo->genero) ?></span>

            <?php if ($temNotas): ?>
              <div class="estrelas" title="Nota média: <?= number_format($titulo->media(), 1) ?>">
                <?= formatarEstrelas($titulo->media()) ?>
                <span class="valor"><?= number_format($titulo->media(), 1) ?></span>
              </div>
            <?php else: ?>
              <div class="sem-notas">Ainda sem avaliações</div>
            <?php endif; ?>

            <div class="card-rodape">
              <form class="form-nota" action="avaliar.php" method="post">
                <input type="hidden" name="id" value="<?= htmlspecialchars($item['id']) ?>">
                <input type="number" name="nota" min="0" max="5" step="0.5" placeholder="Nota" title="Nota de 0 a 5" required>
                <button type="submit">Avaliar</button>
              </form>
              <a class="btn-excluir" href="excluir.php?id=<?= urlencode($item['id']) ?>" onclick="return confirm('Remover este título do catálogo?');" title="Excluir">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
              </a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</main>

<footer>MaratonaX // projeto de estudo (POO em PHP) // Arthur Sousa da Costa</footer>
</body>
</html>
