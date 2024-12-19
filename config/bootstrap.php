<?php

use Cake\Core\Plugin;
use Cake\Core\Configure;

// If CakeDC/Users is enabled and loaded
if (Plugin::isLoaded('CakeDC/Users')) {
    // ...load permissions required by this plugin
    Configure::load($this->getName().'.permissions', 'default', true);
}
