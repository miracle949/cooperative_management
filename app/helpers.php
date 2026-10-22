<?php

if (! function_exists('csp_nonce')) {
    /**
     * Return the Content-Security-Policy nonce generated for the current
     * request. The nonce is created once per request in SetSecurityHeaders
     * and stays stable for the whole request lifetime (including
     * AJAX-rendered partials). Empty outside an HTTP request.
     */
    function csp_nonce(): string
    {
        return app()->bound('csp-nonce') ? (string) app('csp-nonce') : '';
    }
}