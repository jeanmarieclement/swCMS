<?php

/**
 * Smarty modifier to check if a constant is defined
 *
 * @param string $constant The constant name to check
 * @return bool True if constant is defined
 */
function smarty_modifier_defined($constant)
{
    return defined($constant);
}
