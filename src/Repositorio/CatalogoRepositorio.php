<?php

require_once __DIR__ . "/../Modelo/Genero.php";
require_once __DIR__ . "/../Modelo/Titulo.php";
require_once __DIR__ . "/../Modelo/Filme.php";
require_once __DIR__ . "/../Modelo/Serie.php";
require_once __DIR__ . "/../../app/Config/Database.php";

use App\Config\Database;

class CatalogoRepositorio
{
    public function __construct(string $caminhoArquivo = '')
    {
        // Parâmetro mantido por compatibilidade com quem instancia
        // a classe (`new CatalogoRepositorio(__DIR__ . "/dados/catalogo.json")`);
        // a persistência real agora é o PostgreSQL, não esse caminho.
    }

    /**
     * Lê os itens crus (arrays associativos) do catálogo.
     */
    public function lerItens(): array
    {
        $pdo = Database::getConnection();
        $titulos = $pdo->query('SELECT * FROM titulos ORDER BY nome')->fetchAll();

        // Todas as notas numa consulta só (antes: uma consulta por título).
        $notasPorTitulo = [];
        foreach ($pdo->query('SELECT id_titulo, nota FROM titulo_notas ORDER BY id') as $linha) {
            $notasPorTitulo[$linha['id_titulo']][] = (float) $linha['nota'];
        }

        return array_map(function ($t) use ($notasPorTitulo) {
            $notas = $notasPorTitulo[$t['id']] ?? [];

            $item = [
                'id' => $t['id'],
                'tipo' => $t['tipo'],
                'nome' => $t['nome'],
                'anoLancamento' => (int) $t['ano_lancamento'],
                'genero' => $t['genero'],
                'notas' => $notas,
            ];

            if ($t['tipo'] === 'serie') {
                $item['temporadas'] = (int) $t['temporadas'];
                $item['episodiosPorTemporada'] = (int) $t['episodios_por_temporada'];
                $item['minutosPorEpisodio'] = (int) $t['minutos_por_episodio'];
            } else {
                $item['duracaoEmMinutos'] = (int) $t['duracao_em_minutos'];
            }

            return $item;
        }, $titulos);
    }

    /**
     * Insere um título novo e suas notas iniciais numa transação.
     * Gravações pontuais: antes, cada operação reescrevia o catálogo inteiro.
     */
    public function inserir(array $item): bool
    {
        $pdo = Database::getConnection();
        $ehSerie = $item['tipo'] === 'serie';

        try {
            $pdo->beginTransaction();
            $pdo->prepare(
                'INSERT INTO titulos (id, tipo, nome, ano_lancamento, genero, duracao_em_minutos, temporadas, episodios_por_temporada, minutos_por_episodio)
                 VALUES (:id, :tipo, :nome, :ano, :genero, :duracao, :temporadas, :episodios, :min_ep)'
            )->execute([
                ':id' => $item['id'], ':tipo' => $item['tipo'], ':nome' => $item['nome'],
                ':ano' => $item['anoLancamento'], ':genero' => $item['genero'],
                ':duracao' => $ehSerie ? null : $item['duracaoEmMinutos'],
                ':temporadas' => $ehSerie ? $item['temporadas'] : null,
                ':episodios' => $ehSerie ? $item['episodiosPorTemporada'] : null,
                ':min_ep' => $ehSerie ? $item['minutosPorEpisodio'] : null,
            ]);
            foreach ($item['notas'] ?? [] as $nota) {
                $this->gravarNota($item['id'], (float) $nota);
            }
            $pdo->commit();
            return true;
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('inserir título falhou: ' . $e->getMessage());
            return false;
        }
    }

    /** Acrescenta uma nota sem tocar nas demais (duas avaliações simultâneas não se sobrescrevem). */
    public function adicionarNota(string $id, float $nota): bool
    {
        try {
            return $this->gravarNota($id, $nota);
        } catch (\Throwable $e) {
            error_log('adicionarNota falhou: ' . $e->getMessage());
            return false;
        }
    }

    /** Remove um título; as notas saem junto pelo ON DELETE CASCADE. */
    public function excluir(string $id): bool
    {
        try {
            $stmt = Database::getConnection()->prepare('DELETE FROM titulos WHERE id = :id');
            $stmt->execute([':id' => $id]);
            return $stmt->rowCount() > 0;
        } catch (\Throwable $e) {
            error_log('excluir título falhou: ' . $e->getMessage());
            return false;
        }
    }

    private function gravarNota(string $id, float $nota): bool
    {
        $stmt = Database::getConnection()->prepare(
            'INSERT INTO titulo_notas (id_titulo, nota) SELECT id, :nota FROM titulos WHERE id = :id'
        );
        $stmt->execute([':id' => $id, ':nota' => $nota]);
        return $stmt->rowCount() > 0;
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
