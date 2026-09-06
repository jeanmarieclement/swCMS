<?php

namespace App\Controllers\Frontend;

// Loaded only inside isolated PHPUnit child processes: CLI has no SAPI request input.
function filter_input($type, $name, $filter = FILTER_DEFAULT, $options = 0)
{
    $input = $type === INPUT_GET ? $_GET : $_POST;
    return array_key_exists($name, $input) ? filter_var($input[$name], $filter, $options) : null;
}
