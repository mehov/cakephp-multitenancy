<?php

use Cake\Routing\Route\DashedRoute;
use Cake\Routing\Route\InflectedRoute;

$routes->plugin($this->getName(), ['path' => '/multitenancy','_namePrefix' => 'Multitenancy:'], function ($routes) {
    $routes->connect(
        '/',
        [
            'plugin' => $this->getName(),
            'controller' => 'Accounts',
            'action' => 'home',
        ],
        [
            '_name' => 'Home',
        ]
    );
    $routes->connect(
        '/choose-account',
        ['controller' => 'Accounts', 'action' => 'choose'],
        ['_name' => 'ChooseAccount']
    );
    $routes->fallbacks(DashedRoute::class);
});
