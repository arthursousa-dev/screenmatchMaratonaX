<?php

use App\Config\Database;
use PHPUnit\Framework\TestCase;

final class RepositorioTest extends TestCase
{
    private const ID = 'titulo_teste_repositorio';
    private CatalogoRepositorio $repo;

    protected function setUp(): void
    {
        $this->repo = new CatalogoRepositorio();
        $this->repo->excluir(self::ID);
    }

    protected function tearDown(): void
    {
        $this->repo->excluir(self::ID);
    }

    private function inserirFilme(): void
    {
        $filme = new Filme('Filme de Teste', 2020, Genero::Comedia, 100);
        $filme->avalia(3);
        $this->assertTrue($this->repo->inserir($this->repo->desidratar($filme, self::ID, 'filme')));
    }

    private function item(): ?array
    {
        foreach ($this->repo->lerItens() as $item) {
            if ($item['id'] === self::ID) {
                return $item;
            }
        }
        return null;
    }

    public function test_inserir_grava_titulo_e_nota_inicial(): void
    {
        $this->inserirFilme();
        $item = $this->item();

        $this->assertSame('Filme de Teste', $item['nome']);
        $this->assertSame(100, $item['duracaoEmMinutos']);
        $this->assertSame([3.0], $item['notas']);
    }

    public function test_adicionar_nota_nao_mexe_nas_notas_dos_outros_titulos(): void
    {
        $this->inserirFilme();
        $pdo = Database::getConnection();
        $outras = (int) $pdo->query("SELECT count(*) FROM titulo_notas WHERE id_titulo <> '" . self::ID . "'")->fetchColumn();

        $this->assertTrue($this->repo->adicionarNota(self::ID, 4.5));
        $this->assertSame([3.0, 4.5], $this->item()['notas']);
        $this->assertSame($outras, (int) $pdo->query("SELECT count(*) FROM titulo_notas WHERE id_titulo <> '" . self::ID . "'")->fetchColumn());
    }

    public function test_nota_para_titulo_inexistente_e_recusada(): void
    {
        $this->assertFalse($this->repo->adicionarNota('nao_existe', 3));
    }

    public function test_tipo_invalido_nao_grava_nada(): void
    {
        $item = ['id' => self::ID, 'tipo' => 'documentario', 'nome' => 'X', 'anoLancamento' => 2020,
                 'genero' => 'Drama', 'duracaoEmMinutos' => 90, 'notas' => []];
        $this->assertFalse(@$this->repo->inserir($item));
        $this->assertNull($this->item());
    }

    public function test_excluir_remove_titulo_e_notas(): void
    {
        $this->inserirFilme();
        $this->assertTrue($this->repo->excluir(self::ID));
        $this->assertNull($this->item());
        $this->assertFalse($this->repo->excluir(self::ID), 'segunda exclusão não encontra nada');
    }
}
