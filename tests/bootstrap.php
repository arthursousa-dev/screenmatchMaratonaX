<?php
// Os testes de banco criam e apagam títulos: só rodam contra um banco terminado em _test.
//
//   DB_NAME=maratonax_test vendor/bin/phpunit

if (!str_ends_with(getenv('DB_NAME') ?: '', '_test')) {
    fwrite(STDERR, "\nDB_NAME precisa apontar para um banco de testes (ex.: maratonax_test).\n\n");
    exit(1);
}

require_once __DIR__ . '/../src/Repositorio/CatalogoRepositorio.php';
require_once __DIR__ . '/../src/Calculos/CalculadoraDeMaratona.php';
require_once __DIR__ . '/../src/funcoes.php';
