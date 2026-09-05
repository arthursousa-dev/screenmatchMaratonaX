<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MaratonaX — Adicionar título</title>
  <link rel="stylesheet" href="css/estilo.css">
</head>
<body>

<?php require __DIR__ . "/navbar.php"; ?>

<main>
  <div class="cabecalho-secao">
    <div>
      <h1>Adicionar título</h1>
      <p>cadastre um filme ou uma série</p>
    </div>
  </div>

  <div class="form-card">
    <form action="salvar.php" method="post" id="formTitulo">

      <div class="tipo-toggle" id="toggleTipo">
        <div class="indicador"></div>
        <label id="labelFilme" class="ativo">
          <input type="radio" name="tipo" value="filme" checked>
          Filme
        </label>
        <label id="labelSerie">
          <input type="radio" name="tipo" value="serie">
          Série
        </label>
      </div>

      <div class="campo">
        <label for="nome">Nome</label>
        <input type="text" name="nome" id="nome" required placeholder="Ex: Thor - Ragnarok">
      </div>

      <div class="linha-dois">
        <div class="campo">
          <label for="ano">Ano de lançamento</label>
          <input type="number" name="ano" id="ano" required min="1900" max="2100">
        </div>
        <div class="campo">
          <label for="genero">Gênero</label>
          <select name="genero" id="genero">
            <option value="Acao">Ação</option>
            <option value="Comedia">Comédia</option>
            <option value="Terror">Terror</option>
            <option value="SuperHeroi">Super-herói</option>
            <option value="Drama">Drama</option>
          </select>
        </div>
      </div>

      <div class="campos-filme ativo" id="camposFilme">
        <div class="campo">
          <label for="duracao">Duração (minutos)</label>
          <input type="number" name="duracaoEmMinutos" id="duracao" min="1" placeholder="Ex: 180">
        </div>
      </div>

      <div class="campos-serie" id="camposSerie">
        <div>
          <div class="linha-dois">
            <div class="campo">
              <label for="temporadas">Temporadas</label>
              <input type="number" name="temporadas" id="temporadas" min="1" placeholder="Ex: 6">
            </div>
            <div class="campo">
              <label for="episodios">Episódios por temporada</label>
              <input type="number" name="episodiosPorTemporada" id="episodios" min="1" placeholder="Ex: 20">
            </div>
          </div>
          <div class="campo">
            <label for="minutosEpisodio">Minutos por episódio</label>
            <input type="number" name="minutosPorEpisodio" id="minutosEpisodio" min="1" placeholder="Ex: 45">
          </div>
        </div>
      </div>

      <div class="campo">
        <label>Nota inicial (opcional)</label>
        <div class="picker-notas" id="pickerNotas">
          <?php for ($i = 1; $i <= 5; $i++): ?>
            <button type="button" class="estrela-btn" data-valor="<?= $i ?>">★</button>
          <?php endfor; ?>
          <span class="rotulo-nota" id="rotuloNota">—</span>
        </div>
        <input type="hidden" name="nota" id="notaEscondida" value="">
      </div>

      <button type="submit" class="btn btn-submit">Salvar no catálogo</button>
    </form>
  </div>
</main>

<footer>MaratonaX // projeto de estudo (POO em PHP) // Arthur Sousa da Costa</footer>

<script>
// ---- toggle segmentado Filme / Série ----
const toggle = document.getElementById('toggleTipo');
const labelFilme = document.getElementById('labelFilme');
const labelSerie = document.getElementById('labelSerie');
const camposFilme = document.getElementById('camposFilme');
const camposSerie = document.getElementById('camposSerie');

function alternarTipo(tipo) {
  const ehSerie = tipo === 'serie';
  toggle.classList.toggle('serie', ehSerie);
  labelFilme.classList.toggle('ativo', !ehSerie);
  labelSerie.classList.toggle('ativo', ehSerie);

  camposFilme.classList.toggle('ativo', !ehSerie);
  camposSerie.classList.toggle('ativo', ehSerie);

  document.getElementById('duracao').required = !ehSerie;
  document.getElementById('temporadas').required = ehSerie;
  document.getElementById('episodios').required = ehSerie;
  document.getElementById('minutosEpisodio').required = ehSerie;
}

toggle.addEventListener('change', (e) => alternarTipo(e.target.value));
alternarTipo('filme');

// ---- seletor de nota por estrelas ----
const botoesEstrela = document.querySelectorAll('#pickerNotas .estrela-btn');
const notaEscondida = document.getElementById('notaEscondida');
const rotuloNota = document.getElementById('rotuloNota');
let notaSelecionada = 0;

function pintarEstrelas(valor, classe) {
  botoesEstrela.forEach(btn => {
    const v = parseInt(btn.dataset.valor, 10);
    btn.classList.toggle(classe, v <= valor);
  });
}

botoesEstrela.forEach(btn => {
  btn.addEventListener('mouseenter', () => pintarEstrelas(parseInt(btn.dataset.valor, 10), 'preview'));
  btn.addEventListener('mouseleave', () => pintarEstrelas(0, 'preview'));
  btn.addEventListener('click', () => {
    const v = parseInt(btn.dataset.valor, 10);
    notaSelecionada = (notaSelecionada === v) ? 0 : v;
    notaEscondida.value = notaSelecionada || '';
    rotuloNota.textContent = notaSelecionada ? notaSelecionada.toFixed(1) : '—';
    pintarEstrelas(notaSelecionada, 'selecionada');
  });
});
</script>
</body>
</html>
