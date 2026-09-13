# MaratonaX

Catálogo pessoal de filmes e séries desenvolvido em PHP, com Programação Orientada a Objetos. Projeto construído a partir dos estudos do curso de PHP da Alura, evoluído de um exercício de terminal para uma aplicação web completa, com catálogo visual, avaliação de títulos e calculadora de maratona.

## Funcionalidades

- Cadastro de filmes e séries, com campos específicos para cada tipo (duração para filmes; temporadas, episódios e minutos por episódio para séries)
- Catálogo visual em cards, com gênero, ano, duração e nota média
- Avaliação de títulos (notas de 0 a 5, meio ponto em meio ponto)
- Calculadora de maratona: selecione títulos do catálogo e veja o tempo total necessário para assistir a todos
- Exclusão de títulos
- Persistência em **PostgreSQL**, com o repositório (`CatalogoRepositorio`) isolando toda a camada de dados

## Segurança

- Proteção CSRF (token por sessão, validado com `hash_equals()`) em todo formulário que altera dado — cadastro, avaliação e exclusão
- Exclusão de título feita via `POST`, nunca `GET` — evita que um clique acidental em link, um crawler ou o prefetch do navegador disparem uma exclusão sem intenção do usuário
- Cookies de sessão com `httponly`, `samesite=Lax` e `secure` (quando em HTTPS)
- `.htaccess` bloqueando acesso direto a `.env`/`.sql`/`.log` e às pastas `app/`, `database/`, `src/`

## Design

Interface própria, com paleta unificada (base escura + um único acento — verde-água — sem gradientes coloridos por gênero) e identidade tipográfica consistente com meus outros projetos: Bricolage Grotesque para títulos, IBM Plex Sans para texto corrido e JetBrains Mono para metadados/tags.

Microinterações: cards com entrada escalonada, seletor de nota por estrelas interativo (com preview no hover), toggle Filme/Série com indicador deslizante, checkboxes customizados na tela de maratona e contador animado no resultado.

## Conceitos de POO aplicados

- Herança: `Filme` e `Serie` estendem a classe base `Titulo`
- Enum nativo do PHP para representar os gêneros (`Genero`)
- Encapsulamento: notas armazenadas de forma privada, acessadas via métodos (`avalia()`, `media()`, `notas()`)
- Composição: `CalculadoraDeMaratona` recebe objetos `Titulo` para somar a duração total
- Camada de repositório (`CatalogoRepositorio`) separando a persistência de dados da lógica de domínio

## Tecnologias

- PHP 8.3 (tipagem forte, enums, propriedades `readonly`)
- **PostgreSQL** via PDO, com prepared statements
- HTML5 / CSS3

## Como rodar localmente

```bash
createdb maratonax
psql maratonax < database/schema.sql
psql maratonax < database/seed.sql

cp .env.example .env

php -S localhost:8000
```

Acesse `http://localhost:8000`.

## Origem do projeto

Este projeto nasceu do curso **PHP: crie a sua primeira aplicação** (Alura), que introduz Orientação a Objetos em PHP através do exemplo do ScreenMatch — aqui rebatizado como **MaratonaX**. As classes de domínio (`Titulo`, `Filme`, `Serie`, `Genero`, `CalculadoraDeMaratona`) foram mantidas fiéis ao conteúdo do curso; toda a camada de persistência, interface web e funcionalidades de catálogo foram desenvolvidas por conta própria como evolução do exercício original.

## Autor

Desenvolvido por **Arthur Sousa da Costa** como projeto de estudo e portfólio.
