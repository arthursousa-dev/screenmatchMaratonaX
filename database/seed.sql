-- Maratonax — catálogo de demonstração
-- O catálogo original não tinha nenhum dado de exemplo; estes
-- títulos foram adicionados só para a aplicação não abrir vazia.

INSERT INTO titulos (id, tipo, nome, ano_lancamento, genero, duracao_em_minutos) VALUES
('titulo_seed_001', 'filme', 'Nômade Estelar', 2019, 'SuperHeroi', 132),
('titulo_seed_002', 'filme', 'A Última Piada', 2021, 'Comedia', 104),
('titulo_seed_003', 'filme', 'Sombras do Pântano', 2017, 'Terror', 96);

INSERT INTO titulos (id, tipo, nome, ano_lancamento, genero, temporadas, episodios_por_temporada, minutos_por_episodio) VALUES
('titulo_seed_004', 'serie', 'Fronteira Vermelha', 2020, 'Drama', 3, 10, 48),
('titulo_seed_005', 'serie', 'Delegacia 9', 2022, 'Comedia', 2, 12, 22);

INSERT INTO titulo_notas (id_titulo, nota) VALUES
('titulo_seed_001', 4.5),
('titulo_seed_001', 5.0),
('titulo_seed_002', 3.5),
('titulo_seed_004', 4.0),
('titulo_seed_004', 4.5),
('titulo_seed_004', 5.0),
('titulo_seed_005', 4.0);
