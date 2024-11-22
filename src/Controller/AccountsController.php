<?php

namespace Multitenancy\Controller;

class AccountsController extends PluginController
{

    /**
     * @var \Multitenancy\Model\Table\AccountsTable
     */
    protected \Multitenancy\Model\Table\AccountsTable $Accounts;

    public function initialize(): void
    {
        parent::initialize();
        $this->Accounts = $this->fetchTable($this->getPlugin().'.Accounts');
    }

    /**
     * Creates a new account
     *
     * @return \Cake\Http\Response|null
     * @throws \Exception
     */
    public function create()
    {
        // Shortcut to current CakeDC/Users User identity
        $identity = $this->getRequest()->getAttribute('identity');
        if (!$identity) {
            throw new \Exception('No valid identity is available. Try to log in.');
        }
        $entity = $this->Accounts->newEmptyEntity();
        if ($this->getRequest()->isPost()) {
            $patch = $this->getRequest()->getData();
            $patch['users'][] = ['id' => $identity->get('id')];
            $entity = $this->Accounts->patchEntity($entity, $patch, [
                'associated' => ['Users',],
            ]);
            if (!$entity->hasErrors()) {
                $this->Accounts->save($entity);
                return $this->redirect(\Cake\Routing\Router::url(['action' => 'choose']));
            }
        }
        $this->set('entity', $entity);
    }

    /**
     * Sets account with provided ID as curently selected. Otherwise lists all.
     *
     * @param null $id account id to choose
     * @throws \Exception
     */
    public function choose($id = null)
    {
        // Shortcut to current CakeDC/Users User identity
        $identity = $this->getRequest()->getAttribute('identity');
        if (!$identity) {
            throw new \Exception('No valid identity is available. Try to log in.');
        }
        // Find all accounts available to the current user
        $accounts = $this->Accounts->find('byIdentity', $identity)
            ->all()
            ->indexBy('id')
            ->toArray();
        // If ID is provided, select it
        if ($id) {
            // Make sure the provided ID is available to current user
            if (!isset($accounts[$id])) {
                throw new \Exception('Account not found');
            }
            // Update last accessed timestamp
            $this->Accounts->setAccessedNow($accounts[$id]);
            // Cache a copy to the session
            \Multitenancy\Account::set($accounts[$id]);
        }
        $this->set('accounts', $accounts);
    }

}
