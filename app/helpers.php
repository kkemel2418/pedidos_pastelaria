<?php

if (!function_exists('example_function')) {
    /**
     * Exemplo de função auxiliar.
     *
     * @param  string  $param
     * @return string
     */
    function example_function($param)
    {
        // Implemente a lógica da função aqui
        return 'Exemplo: ' . $param;
    }
}

// Outras funções auxiliares podem ser adicionadas aqui
// ...

// Exemplo de função para formatar uma data
if (!function_exists('format_date')) {
    /**
     * Formata uma data para o formato desejado.
     *
     * @param  string  $date
     * @param  string  $format
     * @return string
     */
    function format_date($date, $format = 'd/m/Y')
    {
        return date($format, strtotime($date));
    }
}

// Mais funções auxiliares podem ser adicionadas aqui
// ...

?>
