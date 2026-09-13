-- ============================================================
-- Maratonax — Schema PostgreSQL
-- ============================================================

CREATE TYPE tipo_titulo AS ENUM ('filme', 'serie');
CREATE TYPE genero_titulo AS ENUM ('Acao', 'Comedia', 'Terror', 'SuperHeroi', 'Drama');

CREATE TABLE titulos (
    id                       VARCHAR(60) PRIMARY KEY,
    tipo                     tipo_titulo NOT NULL,
    nome                     VARCHAR(150) NOT NULL,
    ano_lancamento           SMALLINT NOT NULL,
    genero                   genero_titulo NOT NULL,
    duracao_em_minutos       SMALLINT NULL,
    temporadas               SMALLINT NULL,
    episodios_por_temporada  SMALLINT NULL,
    minutos_por_episodio     SMALLINT NULL,
    criado_em                TIMESTAMP NOT NULL DEFAULT now(),

    CONSTRAINT chk_campos_por_tipo CHECK (
        (tipo = 'filme' AND duracao_em_minutos IS NOT NULL AND temporadas IS NULL)
        OR
        (tipo = 'serie' AND temporadas IS NOT NULL AND episodios_por_temporada IS NOT NULL
         AND minutos_por_episodio IS NOT NULL AND duracao_em_minutos IS NULL)
    )
);

CREATE TABLE titulo_notas (
    id         SERIAL PRIMARY KEY,
    id_titulo  VARCHAR(60) NOT NULL REFERENCES titulos(id) ON DELETE CASCADE,
    nota       NUMERIC(2,1) NOT NULL CHECK (nota BETWEEN 0 AND 5),
    criado_em  TIMESTAMP NOT NULL DEFAULT now()
);

CREATE INDEX idx_titulo_notas_titulo ON titulo_notas(id_titulo);
