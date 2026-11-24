<?php

/*
 * Only the values that differ from the package defaults live here.
 * This starter uses personal access tokens (Authorization: Bearer ...),
 * so the cookie/session guard is disabled.
 */

return [

    'guard' => [],

    // Minutes until a token expires. Set SANCTUM_EXPIRATION= (empty) for tokens that never expire.
    'expiration' => ((int) env('SANCTUM_EXPIRATION', 10080)) ?: null,

    'token_prefix' => env('SANCTUM_TOKEN_PREFIX', ''),

];
