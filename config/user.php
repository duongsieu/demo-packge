<?php

/*
 * You can place your custom package configuration in here.
 */
return [
    'TOKEN' => [
        'TOKEN_EXPIRE_IN' => 15,
        'REFRESH_TOKEN_EXPIRE_IN' => 30,
        'TOKEN_VERIFY_LENGTH' => 50,
        'REMEMBER_TOKEN_LENGTH' => 10,
        'TYPE' => 'Bearer',
    ],
    'PASSPORT_CLIENT_ID' => 2,
    'HTTP_STATUS_CODE' => [
        'NOT_FOUND' => 404,
        'BAD_REQUEST' => 400,
        'SERVER_ERROR' => 500,
        'METHOD_NOT_ALLOWED' => 405,
        'UNAUTHORIZED' => 401,
        'PERMISSION_DENIED' => 403,
        'UNPROCESSABLE_ENTITY' => 422,
        'NOT_ACCEPTABLE' => 406,
        'SUCCESS' => 200,
    ],

    'login' => [
        "method" => "passport",
    ],
];
