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

        $notasStmt = $pdo->prepare('SELECT nota FROM titulo_notas WHERE id_titulo = :id ORDER BY id');

        return array_map(function ($t) use ($notasStmt) {
            $notasStmt->execute([':id' => $t['id']]);
            $notas = array_map('floatval', $notasStmt->fetchAll(\PDO::FETCH_COLUMN));

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
     * Grava os itens crus no catálogo (upsert por ID, dentro de uma
     * transação; remove do banco o que não veio mais no array).
     */
    public function gravarItens(array $itens): void
    {
        $pdo = Database::getConnection();

        try {
            $pdo->beginTransaction();

            $idsAtuais = $pdo->query('SELECT id FROM titulos')->fetchAll(\PDO::FETCH_COLUMN);
            $idsNovos = array_column($itens, 'id');

            $upsert = $pdo->prepare(
                'INSERT INTO titulos (id, tipo, nome, ano_lancamento, genero, duracao_em_minutos, temporadas, episodios_por_temporada, minutos_por_episodio)
                 VALUES (:id, :tipo, :nome, :ano, :genero, :duracao, :temporadas, :episodios, :min_ep)
                 ON CONFLICT (id) DO UPDATE SET
                    tipo=EXCLUDED.tipo, nome=EXCLUDED.nome, ano_lancamento=EXCLUDED.ano_lancamento,
                    genero=EXCLUDED.genero, duracao_em_minutos=EXCLUDED.duracao_em_minutos,
                    temporadas=EXCLUDED.temporadas, episodios_por_temporada=EXCLUDED.episodios_por_temporada,
                    minutos_por_episodio=EXCLUDED.minutos_por_episodio'
            );
            $limpaNotas = $pdo->prepare('DELETE FROM titulo_notas WHERE id_titulo = :id');
            $insereNota = $pdo->prepare('INSERT INTO titulo_notas (id_titulo, nota) VALUES (:id, :nota)');

            foreach ($itens as $item) {
                $ehSerie = $item['tipo'] === 'serie';
                $upsert->execute([
                    ':id' => $item['id'], ':tipo' => $item['tipo'], ':nome' => $item['nome'],
                    ':ano' => $item['anoLancamento'], ':genero' => $item['genero'],
                    ':duracao' => $ehSerie ? null : $item['duracaoEmMinutos'],
                    ':temporadas' => $ehSerie ? $item['temporadas'] : null,
                    ':episodios' => $ehSerie ? $item['episodiosPorTemporada'] : null,
                    ':min_ep' => $ehSerie ? $item['minutosPorEpisodio'] : null,
                ]);

                $limpaNotas->execute([':id' => $item['id']]);
                foreach (($item['notas'] ?? []) as $nota) {
                    $insereNota->execute([':id' => $item['id'], ':nota' => $nota]);
                }
            }

            $remover = array_diff($idsAtuais, $idsNovos);
            if ($remover) {
                $in = implode(',', array_map(fn($id) => $pdo->quote($id), $remover));
                $pdo->exec("DELETE FROM titulos WHERE id IN ($in)");
            }

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            error_log('gravarItens falhou: ' . $e->getMessage());
        }
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
