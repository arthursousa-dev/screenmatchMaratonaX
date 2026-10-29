<?php

use PHPUnit\Framework\TestCase;

final class ModeloTest extends TestCase
{
    public function test_duracao_de_filme_e_de_serie(): void
    {
        $filme = new Filme('Duna', 2021, Genero::Acao, 155);
        $serie = new Serie('Dark', 2017, Genero::Drama, 3, 8, 55);

        $this->assertSame(155, $filme->duracaoEmMinutos());
        $this->assertSame(3 * 8 * 55, $serie->duracaoEmMinutos());
    }

    public function test_calculadora_soma_a_maratona(): void
    {
        $calc = new CalculadoraDeMaratona();
        $calc->inclui(new Filme('Duna', 2021, Genero::Acao, 155));
        $calc->inclui(new Serie('Dark', 2017, Genero::Drama, 1, 10, 50));

        $this->assertSame(655, $calc->duracao());
        $this->assertSame('10h55min', formatarDuracao($calc->duracao()));
        $this->assertSame('45min', formatarDuracao(45));
    }

    public function test_media_das_notas_e_nota_fora_da_escala(): void
    {
        $titulo = new Filme('Duna', 2021, Genero::Acao, 155);
        $this->assertSame(0.0, $titulo->media());

        $titulo->avalia(4);
        $titulo->avalia(5);
        $this->assertSame(4.5, $titulo->media());

        $this->expectException(InvalidArgumentException::class);
        $titulo->avalia(5.5);
    }

    public function test_estrelas_e_iniciais(): void
    {
        $this->assertSame(4, substr_count(formatarEstrelas(4.4), 'cheia'));
        $this->assertSame(5, substr_count(formatarEstrelas(9), 'cheia'));
        $this->assertSame('TR', iniciaisTitulo('Thor - Ragnarok'));
        $this->assertSame('DA', iniciaisTitulo('Dark'));
    }

    public function test_hidratar_e_desidratar_preservam_o_titulo(): void
    {
        $repo = new CatalogoRepositorio();
        $serie = new Serie('Dark', 2017, Genero::Drama, 3, 8, 55);
        $serie->avalia(4.5);

        $item = $repo->desidratar($serie, 'id1', 'serie');
        $volta = $repo->hidratar($item);

        $this->assertInstanceOf(Serie::class, $volta);
        $this->assertSame($serie->duracaoEmMinutos(), $volta->duracaoEmMinutos());
        $this->assertSame([4.5], $volta->notas());
        $this->assertSame(Genero::Drama, CatalogoRepositorio::generoFromNome('Inexistente'));
    }
}
