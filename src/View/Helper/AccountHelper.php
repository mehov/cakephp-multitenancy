<?php

namespace Bakeoff\Multitenancy\View\Helper;

class AccountHelper extends \Cake\View\Helper
{

    public function getAccount()
    {
        $request = $this->getView()->getRequest();
        return $request->getSession()->read(\Bakeoff\Multitenancy\Account::getSessionKey());
    }

}
