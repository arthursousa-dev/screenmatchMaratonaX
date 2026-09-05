<?php

require __DIR__ . "/src/Repositorio/CatalogoRepositorio.php";
require __DIR__ . "/src/Calculos/CalculadoraDeMaratona.php";
require __DIR__ . "/src/funcoes.php";

$repositorio = new CatalogoRepositorio(__DIR__ . "/dados/catalogo.json");
$itens = $repositorio->lerItens();

$resultado = null;
$idsEscolhidos = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idsEscolhidos = $_POST['ids'] ?? [];

    $calculadora = new CalculadoraDeMaratona();
    $titulosEscolhidos = [];

    foreach ($itens as $item) {
        if (in_array($item['id'], $idsEscolhidos, true)) {
            $titulo = $repositorio->hidratar($item);
            $calculadora->inclui($titulo);
            $titulosEscolhidos[] = $titulo;
        }
    }

    if (!empty($titulosEscolhidos)) {
        $totalMinutos = $calculadora->duracao();
        $resultado = [
            'totalMinutos' => $totalMinutos,
            'titulos' => $titulosEscolhidos,
            'diasEstimados' => round($totalMinutos / 180, 1), // ~3h de tela por dia
        ];
    }
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MaratonaX — Montar maratona</title>
  <link rel="stylesheet" href="css/estilo.css">
</head>
<body>

<?php require __DIR__ . "/navbar.php"; ?>

<main>
  <div class="cabecalho-secao">
    <div>
      <h1>Montar maratona</h1>
      <p>selecione títulos e calcule o tempo total</p>
    </div>
  </div>

  <?php if ($resultado): ?>
    <div class="resultado-maratona">
      <div class="valor" id="valorMaratona" data-minutos="<?= $resultado['totalMinutos'] ?>">0min</div>
      <div class="sub">
        <?= count($resultado['titulos']) ?> título(s) selecionado(s) —
        aproximadamente <?= $resultado['diasEstimados'] ?> dia(s) assistindo ~3h por dia
      </div>
    </div>
  <?php endif; ?>

  <?php if (empty($itens)): ?>
    <div class="vazio">
      <p>Você ainda não tem títulos no catálogo.</p>
      <p><a href="adicionar.php">Adicione um filme ou série</a> para montar sua maratona.</p>
    </div>
  <?php else: ?>
    <form method="post">
      <div class="lista-selecionar">
        <?php foreach ($itens as $item):
          $titulo = $repositorio->hidratar($item);
          $marcado = in_array($item['id'], $idsEscolhidos, true);
        ?>
          <label class="item-selecionar">
            <input type="checkbox" name="ids[]" value="<?= htmlspecialchars($item['id']) ?>" <?= $marcado ? 'checked' : '' ?>>
            <span class="checkbox-custom">
              <svg viewBox="0 0 24 24" fill="none" stroke="#06231d" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
            </span>
            <span class="nome-item"><?= htmlspecialchars($titulo->nome) ?> <span class="tipo-item">(<?= $item['tipo'] === 'serie' ? 'Série' : 'Filme' ?>)</span></span>
            <span class="duracao-item"><?= formatarDuracao($titulo->duracaoEmMinutos()) ?></span>
          </label>
        <?php endforeach; ?>
      </div>

      <button type="submit" class="btn btn-submit">Calcular maratona</button>
    </form>
  <?php endif; ?>
</main>

<footer>MaratonaX // projeto de estudo (POO em PHP) // Arthur Sousa da Costa</footer>

<script>
const elValor = document.getElementById('valorMaratona');
if (elValor) {
  const totalMin = parseInt(elValor.dataset.minutos, 10);
  const reduzMovimento = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function formatar(min) {
    const h = Math.floor(min / 60);
    const m = min % 60;
    return h === 0 ? `${m}min` : `${h}h${m}min`;
  }

  if (reduzMovimento) {
    elValor.textContent = formatar(totalMin);
  } else {
    let inicio = null;
    const duracao = 800;
    function passo(ts) {
      if (!inicio) inicio = ts;
      const progresso = Math.min((ts - inicio) / duracao, 1);
      elValor.textContent = formatar(Math.floor(progresso * totalMin));
      if (progresso < 1) requestAnimationFrame(passo);
      else elValor.textContent = formatar(totalMin);
    }
    requestAnimationFrame(passo);
  }
}
</script>
</body>
</html>
