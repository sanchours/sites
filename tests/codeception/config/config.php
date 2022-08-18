<?php
/**
 * Application configuration shared by all test types.
 */
return [
    'language' => 'ru',
    'components' => [
        'mailer' => [
            'useFileTransport' => true,
        ],
        'urlManager' => [
            'showScriptName' => true,
        ],
    ],
];
