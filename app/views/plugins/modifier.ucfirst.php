<?php
/**
 * Smarty modifier to capitalize the first character of a string
 * 
 * @param string $string The string to modify
 * @return string String with first character capitalized
 */
function smarty_modifier_ucfirst($string) {
    return ucfirst(strtolower($string));
}
?>