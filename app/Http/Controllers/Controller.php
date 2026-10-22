<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

abstract class Controller
{
    /**
     * Return a validated Y-m-d date parameter.
     *
     * Falls back to $default when the request value is missing, not a
     * string, or not a real calendar date. Prevents arbitrary values from
     * reaching whereBetween() date filters.
     */
    protected function validDateParam(Request $request, string $key, string $default): string
    {
        $value = $request->get($key, $default);

        if (! is_string($value) || preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) !== 1) {
            return $default;
        }

        $parsed = \DateTime::createFromFormat('!Y-m-d', $value);

        return ($parsed !== false && $parsed->format('Y-m-d') === $value) ? $value : $default;
    }
}
