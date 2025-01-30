<?php
namespace Bakeoff\Multitenancy\Model\Behavior;

use Cake\ORM\Behavior;

class TenantScopeBehavior extends Behavior
{

    // Provides fetchTable() needed to get a copy of AccountsTable below
    use \Cake\ORM\Locator\LocatorAwareTrait;

    /**
     * Guesses which account to use if none was selected specifically
     *
     * This Behavior depends on knowing for which account to find entries.
     * Account to use is normally cached; otherwise find the one used last.
     *
     * @return \Bakeoff\Multitenancy\Model\Entity\Account|null
     * @throws \Exception
     */
    private function detectAccount()
    {
        // Check the cache
        $account = \Bakeoff\Multitenancy\Account::get();
        if (!empty($account)) {
            return $account; // use the cached account
        }
        // Get CakeDC/User auth
        $user = \Bakeoff\Multitenancy\Account::getSession()->read('Auth');
        if (!$user) {
            throw new \Exception('No user data is available. Try to log in.');
        }
        // Get an instance of AccountsTable
        $accountsTable = $this->fetchTable(\Bakeoff\Multitenancy\Plugin::getPlugin().'.Accounts');
        // Find the last account accessed by the current user
        $account = $accountsTable->find('all')
            ->leftJoinWith('Users')
            ->where(['Users.id' => $user->get('id')])
            ->orderBy('accessed DESC')
            ->first();
        // Return null if we couldn't find anything
        if (!$account) {
            return null;
        }
        // Update last accessed timestamp
        $accountsTable->setAccessedNow($account);
        // Cache a copy of this account we just found to session
        \Bakeoff\Multitenancy\Account::set($account);
        return $account;
    }

    /**
     * Locally cached copy of the account
     *
     * @var
     */
    private $account;

    /**
     * If this table `belongsTo` `Accounts`, there must be a foreign key
     *
     * @return string name of the column containing foreign key to an account
     */
    private function getAccountForeignKeyName()
    {
        if (!$this->_table->hasAssociation('Account')) {
            return $this->_table->getAlias().'.account_id';
        }
    }

    /**
     * @param \Cake\Event\EventInterface $event
     * @param \Cake\ORM\Query\SelectQuery $query
     * @param \ArrayObject $options
     * @return \Cake\ORM\Query\SelectQuery
     */
    public function beforeFind(\Cake\Event\EventInterface $event, \Cake\ORM\Query\SelectQuery $query, \ArrayObject $options)
    {
        // Skip if this is \Cake\ORM\Table::exists() checking uniqueness
        if (!$query->isHydrationEnabled()) {
            $selectClause = $query->clause('select');
            if (is_array($selectClause)
            && isset($selectClause['existing'])
            && $selectClause['existing'] === 1) {
                return $query;
            }
        }
        /*
         * Make sure we know what account to check ownership for
         *
         * Makes sense to check this inside TenantScopeBehavior::initialize(),
         * but that will prevent `removeBehavior('TenantScope')` if ever needed
         */
        if (empty($this->account)) {
            // See if we can automatically get an account to use
            $this->account = $this->detectAccount(); // save a copy locally
            if (empty($this->account)) {
                // Can't proceed without knowing what account to check ownership for
                throw new \Exception('Account required but not selected');
            }
        }
        $query->where([$this->getAccountForeignKeyName() => $this->account->id]);
        return $query;
    }

}
