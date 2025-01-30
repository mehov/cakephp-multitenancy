<?php
namespace Bakeoff\Multitenancy\Model\Behavior;

use Cake\ORM\Behavior;

class TenantScopeBehavior extends Behavior
{

    // Provides fetchTable() needed to get a copy of AccountsTable below
    use \Cake\ORM\Locator\LocatorAwareTrait;

    /**
     * Default configuration. Overwrite when adding behavior to model, e.g.:
     *
     * ```
     * $this->addBehavior('Bakeoff/Multitenancy.TenantScope', [
     *     'accountField' => 'example_column_account_id'
     * ]);
     * ```
     *
     * @var array<string, mixed>
     */
    protected array $_defaultConfig = [
        /*
         * Reference to column containing account ID. May be in another table.
         *
         * - simple `account_id` = column in table this behavior is added to
         * - dot notation `OtherTable.account` = column in associated table
         */
        'accountField' => 'account_id',
    ];

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
     * @param \Cake\Event\EventInterface $event
     * @param \Cake\ORM\Query\SelectQuery $query
     * @param \ArrayObject $options
     * @param bool $primary indicates if this is root query or associated query
     * @return \Cake\ORM\Query\SelectQuery
     */
    public function beforeFind(\Cake\Event\EventInterface $event, \Cake\ORM\Query\SelectQuery $query, \ArrayObject $options, $primary)
    {
        /*
         * Skip e.g. joined associations generated by `contain()`
         *
         * Not skipping will add account checks to INNER JOIN clauses, relying
         * on 'accountField' column that is not present or accessible.
         */
        if (!$primary) {
            return $query;
        }
        /*
         * Skip if this is \Cake\ORM\Table::exists() checking uniqueness
         * See: https://stackoverflow.com/a/74582840
         */
        if (!$query->isHydrationEnabled()) {
            $select = $query->clause('select');
            if (is_array($select) && $select === ['existing' => 1]) {
                return $query;
            }
            unset($select);
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
        // Configured account field (can be single column name or dot notation)
        $accountField = $this->getConfig('accountField');
        // Dot notation means deep association as SomeTable.OtherTable.column
        if (strpos($accountField, '.') !== false) {
            // Split dot notation into parts
            $parts = explode('.', $accountField);
            // Take out column name. Shortened $parts is now table(s) only
            $column = array_pop($parts);
            // Last part is final table in association; that's where $column is
            $table = end($parts);
            // In where() below we will need to refer to OtherTable.column
            $accountField = $table . '.' . $column;
            unset($table, $column); // clean up
            // Refer to whole association in dot notation
            $assoc = implode('.', $parts);
            // Filter records that are being selected by current account ID
            $query->matching($assoc, function($q) use($accountField) {
                return $q->where([$accountField => $this->account->id]);
            });
        // No dot notation means accountField is single column in current table
        } else {
            // Prepend current table alias
            $accountField = $this->_table->getAlias() . '.' . $accountField;
            // Filter records that are being selected by current account ID
            $query->where([$accountField => $this->account->id]);
        }
        return $query;
    }

}
