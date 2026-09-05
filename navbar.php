<?php $paginaAtual = basename($_SERVER['PHP_SELF']); ?>
<header class="topbar">
  <a href="index.php" class="logo">Maratona<span>X</span></a>
  <nav class="nav">
    <a href="index.php" class="<?= $paginaAtual === 'index.php' ? 'ativo' : '' ?>">Catálogo</a>
    <a href="adicionar.php" class="<?= $paginaAtual === 'adicionar.php' ? 'ativo' : '' ?>">+ Adicionar título</a>
    <a href="maratona.php" class="<?= $paginaAtual === 'maratona.php' ? 'ativo' : '' ?>">Montar maratona</a>
  </nav>
</header>
