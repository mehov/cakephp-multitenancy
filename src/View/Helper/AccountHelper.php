<?php

namespace Multitenancy\View\Helper;

class AccountHelper extends \Cake\View\Helper
{

    public function getAccount()
    {
        $request = $this->getView()->getRequest();
        return $request->getSession()->read(\Multitenancy\Account::getSessionKey());
    }

}
