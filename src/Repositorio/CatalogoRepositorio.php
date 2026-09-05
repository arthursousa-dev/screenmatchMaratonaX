<?php

require_once __DIR__ . "/../Modelo/Genero.php";
require_once __DIR__ . "/../Modelo/Titulo.php";
require_once __DIR__ . "/../Modelo/Filme.php";
require_once __DIR__ . "/../Modelo/Serie.php";

class CatalogoRepositorio
{
    private string $caminhoArquivo;

    public function __construct(string $caminhoArquivo)
    {
        $this->caminhoArquivo = $caminhoArquivo;

        if (!file_exists($this->caminhoArquivo)) {
            file_put_contents($this->caminhoArquivo, json_encode([]));
        }
    }

    /**
     * Lê os itens crus (arrays associativos) do catálogo.
     */
    public function lerItens(): array
    {
        $conteudo = file_get_contents($this->caminhoArquivo);
        $itens = json_decode($conteudo, true);

        return is_array($itens) ? $itens : [];
    }

    /**
     * Grava os itens crus no catálogo, com escrita segura (flock).
     */
    public function gravarItens(array $itens): void
    {
        $handle = fopen($this->caminhoArquivo, 'c+');
        flock($handle, LOCK_EX);
        ftruncate($handle, 0);
        fwrite($handle, json_encode($itens, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        fflush($handle);
        flock($handle, LOCK_UN);
        fclose($handle);
    }

    /**
     * Retorna todos os títulos já hidratados como objetos Filme/Serie.
     * @return Titulo[]
     */
    public function todosOsTitulos(): array
    {
        return array_map([$this, 'hidratar'], $this->lerItens());
    }

    /**
     * Reconstrói um objeto Filme ou Serie a partir do array salvo em JSON.
     */
    public function hidratar(array $item): Titulo
    {
        $genero = self::generoFromNome($item['genero']);

        if ($item['tipo'] === 'serie') {
            $titulo = new Serie(
                $item['nome'],
                (int) $item['anoLancamento'],
                $genero,
                (int) $item['temporadas'],
                (int) $item['episodiosPorTemporada'],
                (int) $item['minutosPorEpisodio'],
            );
        } else {
            $titulo = new Filme(
                $item['nome'],
                (int) $item['anoLancamento'],
                $genero,
                (int) $item['duracaoEmMinutos'],
            );
        }

        foreach ($item['notas'] ?? [] as $nota) {
            $titulo->avalia((float) $nota);
        }

        return $titulo;
    }

    /**
     * Converte um objeto Titulo (Filme/Serie) de volta para array, pronto para salvar.
     */
    public function desidratar(Titulo $titulo, string $id, string $tipo): array
    {
        $item = [
            'id' => $id,
            'tipo' => $tipo,
            'nome' => $titulo->nome,
            'anoLancamento' => $titulo->anoLancamento,
            'genero' => $titulo->genero->name,
            'notas' => $titulo->notas(),
        ];

        if ($titulo instanceof Serie) {
            $item['temporadas'] = $titulo->temporadas;
            $item['episodiosPorTemporada'] = $titulo->episodiosPorTemporada;
            $item['minutosPorEpisodio'] = $titulo->minutosPorEpisodio;
        } else {
            $item['duracaoEmMinutos'] = $titulo->duracaoEmMinutos;
        }

        return $item;
    }

    public static function generoFromNome(string $nome): Genero
    {
        return match ($nome) {
            'Acao' => Genero::Acao,
            'Comedia' => Genero::Comedia,
            'Terror' => Genero::Terror,
            'SuperHeroi' => Genero::SuperHeroi,
            'Drama' => Genero::Drama,
            default => Genero::Drama,
        };
    }

    public static function rotuloGenero(Genero $genero): string
    {
        return match ($genero) {
            Genero::Acao => 'Ação',
            Genero::Comedia => 'Comédia',
            Genero::Terror => 'Terror',
            Genero::SuperHeroi => 'Super-herói',
            Genero::Drama => 'Drama',
        };
    }
}
