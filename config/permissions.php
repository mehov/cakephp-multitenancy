<?php

/*
 * Permissions compatible with CakeDC/Users, see their documentation at:
 *  - APP/vendor/cakedc/users/Docs/Documentation/Permissions.md
 *  - github.com/CakeDC/users
 *
 * Our bootstrap.php checks whether CakeDC/Users is installed and if so, merges
 * the rules in this file with those in APP/config/permissions.php
 *
 * To avoid rule collision elements in the array below have to have unique keys
 */

return [
    'CakeDC/Auth' => [
        'permissions' => [
            // Elements in this array have to have unique keys
            'Bakeoff/Multitenancy-AllowAll' => [
                'plugin' => 'Bakeoff/Multitenancy',
                'prefix' => '*',
                'controller' => '*',
                'action' => '*',
                'bypassAuth' => true,
            ],
        ],
    ],
];
