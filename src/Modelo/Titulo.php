<?php

class Titulo
{
    private array $notas;

    public function __construct(
        public readonly string $nome,
        public readonly int $anoLancamento,
        public readonly Genero $genero,
    ) {
        $this->notas = [];
    }

    public function avalia(float $nota): void
    {
        if ($nota < 0 || $nota > 5) {
            throw new InvalidArgumentException('A nota deve estar entre 0 e 5.');
        }

        $this->notas[] = $nota;
    }

    public function notas(): array
    {
        return $this->notas;
    }

    public function media(): float
    {
        $somaNotas = array_sum($this->notas);
        $quantidadeNotas = count($this->notas);

        if ($quantidadeNotas === 0) {
            return 0.0;
        }

        return max(0.0, min(5.0, $somaNotas / $quantidadeNotas));
    }

    public function duracaoEmMinutos(): int
    {
        return 0;
    }
}