<?php

function redirecionarAlerta(string $url, string $tipo, string $mensagem): void
{
    $separador = strpos($url, "?") === false ? "?" : "&";
    $destino = $url
        . $separador
        . "alerta=" . urlencode($tipo)
        . "&msg=" . urlencode($mensagem);

    header("Location: " . $destino);
    exit();
}
