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

    'login' => [
        "method" => "passport",
    ],

    'model' => "GGPHP\User\Models\User",
];
