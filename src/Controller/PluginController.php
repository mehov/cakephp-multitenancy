<?php

namespace Multitenancy\Controller;

class PluginController extends \App\Controller\AppController
{

    public function initialize(): void
    {
        parent::initialize();
        $this->viewBuilder()->addHelper($this->getPlugin().'.Account');
    }

}
