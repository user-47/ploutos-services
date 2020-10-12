<?php

return [

    'frontend' => [
        'url' => env('FRONT_END_APP_URL', 'https://ploutos-app.herokuapp.com/'),
        'routes' => [
            'home' => env('FRONT_END_HOME_ROUTE', 'home')
        ],
    ],

];