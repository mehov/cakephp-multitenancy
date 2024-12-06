<?php

namespace Bakeoff\Multitenancy;

use Cake\Core\BasePlugin;
use Cake\Core\PluginApplicationInterface;

class Plugin extends BasePlugin
{

    /**
     * @return string
     */
    public static function getPlugin()
    {
        return str_replace('\\', '/', __NAMESPACE__);
    }

}
