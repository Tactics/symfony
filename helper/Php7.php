<?php

/**
 * Backward-compatible trim function for legacy PHP code.
 *
 * In PHP < 8.0, calling trim(null) returned an empty string ("")
 * because null was implicitly cast to an empty string.
 *
 * This function replicates that behavior, allowing you to safely
 * find-and-replace trim() calls in old code without breaking anything
 * when running on PHP 8+.
 *
 * @param string|null $string     The input string or null.
 * @param string      $characters Optional list of characters to trim.
 *
 * @return string The trimmed string, or an empty string if null was given.
 */
function php7_trim(?string $string, string $characters = " \n\r\t\v\0"): string
{
    if ($string === null) {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $caller = $trace[1] ?? [];
        $file = $caller['file'] ?? 'unknown file';
        $line = $caller['line'] ?? 'unknown line';
        $function = $caller['function'] ?? 'unknown function';

        error_log(sprintf(
            '[php_trim] null passed at %s:%s in %s()',
            $file,
            $line,
            $function
        ));

        return '';
    }

    return trim($string, $characters);
}
