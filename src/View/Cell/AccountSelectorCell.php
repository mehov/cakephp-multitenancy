<?php

namespace Multitenancy\View\Cell;

class AccountSelectorCell extends \Cake\View\Cell
{

    private \Multitenancy\Model\Table\AccountsTable $Accounts;
    private \CakeDC\Users\Model\Entity\User|null $user; // current user
    private \Multitenancy\Model\Entity\Account|null $account; // current account

    public function initialize(): void
    {
        parent::initialize();
        // Prefetch Accounts table
        $this->Accounts = $this->fetchTable('Multitenancy.Accounts');
        // Shorthands
        $request = $this->request;
        $session = $request->getSession();
        // Make current user and account available to other methods
        $this->user = $session->read('Auth');
        $this->account = $session->read('Multitenancy.Account');
    }


    /**
     * Renders <select/> with accounts available to current user. In a template:
     * ```
     * echo $this->cell('Multitenancy.AccountSelector::formControl');
     * ```
     */
    public function formControl()
    {
        $accounts = $this->Accounts
            ->find('byIdentity', $this->user)
            ->find('list')
            ->toArray()
        ;
        $this->set([
            'accounts' => $accounts,
            'account' => $this->account,
        ]);
    }

}