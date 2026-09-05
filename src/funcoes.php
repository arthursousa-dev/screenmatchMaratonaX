<?php

function formatarDuracao(int $minutos): string
{
    $horas = intdiv($minutos, 60);
    $min = $minutos % 60;

    if ($horas === 0) {
        return "{$min}min";
    }

    return "{$horas}h{$min}min";
}

function formatarEstrelas(float $media): string
{
    // A escala do sistema é de 0 a 5.
    $notaEm5 = max(0, min(5, (int) round($media)));
    $html = '';

    for ($i = 1; $i <= 5; $i++) {
        $classe = $i <= $notaEm5 ? 'cheia' : 'vazia';
        $html .= "<span class=\"{$classe}\">★</span>";
    }

    return $html;
}

function iniciaisTitulo(string $nome): string
{
    $palavras = preg_split('/\s+/', trim($nome));
    $palavras = array_values(array_filter($palavras, fn($p) => preg_match('/[\p{L}\p{N}]/u', $p)));

    if (count($palavras) === 1) {
        return mb_strtoupper(mb_substr($palavras[0], 0, 2));
    }

    $iniciais = array_map(fn($p) => mb_strtoupper(mb_substr($p, 0, 1)), array_slice($palavras, 0, 2));
    return implode('', $iniciais);
}

/**
 * Token CSRF por sessão.
 */
function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfCampo(): string
{
    return '<input type="hidden" name="csrf_token" value="' . csrfToken() . '">';
}

function csrfValido(?string $token): bool
{
    return !empty($_SESSION['csrf_token']) && !empty($token) && hash_equals($_SESSION['csrf_token'], $token);
}
