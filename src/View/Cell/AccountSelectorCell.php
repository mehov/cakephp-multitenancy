<?php

namespace Bakeoff\Multitenancy\View\Cell;

class AccountSelectorCell extends \Cake\View\Cell
{

    private \Bakeoff\Multitenancy\Model\Table\AccountsTable $Accounts;
    private \CakeDC\Users\Model\Entity\User|null $user; // current user
    private \Bakeoff\Multitenancy\Model\Entity\Account|null $account; // current account

    public function initialize(): void
    {
        parent::initialize();
        // Prefetch Accounts table
        $this->Accounts = $this->fetchTable(\Bakeoff\Multitenancy\Plugin::getPlugin().'.Accounts');
        // Shorthands
        $request = $this->request;
        $session = $request->getSession();
        // Make current user and account available to other methods
        $this->user = $session->read('Auth');
        $this->account = $session->read(\Bakeoff\Multitenancy\Account::getSessionKey());
    }


    /**
     * Renders <select/> with accounts available to current user. In a template:
     * ```
     * echo $this->cell('Bakeoff/Multitenancy.AccountSelector::formControl');
     * ```
     *
     * If using FormProtection, pass FormHelper instance to add controls to:
     * ```
     * $this->cell('Bakeoff/Multitenancy.AccountSelector::formControl', [$this->Form]);
     * ```
     *
     * @param \Cake\View\Helper\FormHelper|\BootstrapUI\View\Helper\FormHelper|null $Form
     */
    public function formControl($Form = null)
    {
        $accounts = $this->Accounts
            ->find('byIdentity', $this->user)
            ->find('list')
            ->toArray()
        ;
        $this->set([
            'accounts' => $accounts,
            'account' => $this->account,
            'Form' => $Form,
        ]);
    }

}
