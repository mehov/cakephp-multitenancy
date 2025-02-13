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
        /*
         * If entities in current table have account ID column, or are directly
         * associated to a table where account ID is, but on save it's empty,
         * the account ID (or foreign key to it) can be set automatically.
         *
         * Works only if:
         * - account ID is in current table and entity doesn't have it
         * - account ID is in directly associated table and entity isn't linked
         *   to a record in that table (i.e. doesn't have foreign ID set)
         *
         * Account ID is read from current session. (If empty, will skip.)
         *
         * Examples:
         * - saving $user; account ID is Users.account
         *   if $user->account not set, set it to account ID of current user
         * - saving $order; Orders belongsTo Users; account ID is Users.account
         *   if $order->user_id not set, find user by account and link user_id
         *
         * Helpful when Orders doesn't have a default value for user_id. Saving
         * without explicitly providing user_id results in database error:
         * > SQLSTATE[HY000]: General error: Field doesn't have a default value
         *
         * Note: will skip on deep associations e.g.:
         * Orders belongsTo Customers belongsTo Users; account is Users.account
         */
        'autoLinkDirect' => false,
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
     * Parse accountField into pieces needed to refer to account ID in queries.
     *
     * Usage:
     *
     * ```
     * list($column, $table, $associations) = $this->parseAccountField($field);
     * ```
     *
     * Results for both $accountField as single column name and dot notation:
     *
     *                 | 'account_id'   | 'SomeTable.OtherTable.column'
     * $column         | account_id     | column
     * $table          | CurrentTable   | OtherTable
     * $associations   | (null)         | SomeTable.OtherTable
     *
     * @param string $accountField see documentation for $_defaultConfig above
     * @return array [$column, $table, $associations]
     */
    private function parseAccountField(string $accountField): array
    {
        /*
         * No dot notation means accountField is single column in current table
         */
        if (strpos($accountField, '.') === false) {
            return [$accountField, $this->_table->getAlias(), null];
        }
        /*
         * Dot notation means deep association like SomeTable.OtherTable.column
         */
        // Split dot notation into parts
        $parts = explode('.', $accountField);
        // Take out column name. Shortened $parts is now table(s) only
        $column = array_pop($parts);
        // Last part is final table in association; that's where $column is
        $table = end($parts);
        // Refer to whole association in dot notation
        $associations = implode('.', $parts);
        return [$column, $table, $associations];
    }

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
         * See also: https://stackoverflow.com/a/74582840
         */
        if (!$query->isHydrationEnabled()) { // exists() disables hydration
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
        // Parse into column, table where account ID is stored; associated path
        list($column, $table, $associations) = $this->parseAccountField($accountField);
        // Use match() for associations, e.g. SomeTable.OtherTable.column
        if (!empty($associations)) {
            // In where() below we will need to refer to OtherTable.column
            $accountField = $table . '.' . $column;
            // Filter records that are being selected by current account ID
            $query->matching($associations, function($q) use($accountField) {
                return $q->where([$accountField => $this->account->id]);
            });
        // No associations means column in this table, so use simple where()
        } else {
            // $table refers to current table; $column is where account ID is
            $accountField = $table . '.' . $column;
            // Filter records that are being selected by current account ID
            $query->where([$accountField => $this->account->id]);
        }
        return $query;
    }

    /**
     * @param \Cake\Event\EventInterface $event
     * @param \Cake\Datasource\EntityInterface $entity
     * @param \ArrayObject $options
     */
    public function beforeSave($event, $entity, $options)
    {
        // If this is not a primary save, return early
        if (!isset($options['_primary']) || !$options['_primary']) {
            return;
        }
        // Return early if configured NOT to auto link
        if (!$this->getConfig('autoLinkDirect')) {
            return;
        }
        // See if we know the account
        if (empty($this->account)) {
            // See if we can automatically get an account to use
            $this->account = $this->detectAccount(); // save a copy locally
            if (empty($this->account)) {
                // Skip silently. Do not enforce anything
                return;
            }
        }
        // Configured account field (can be single column name or dot notation)
        $accountField = $this->getConfig('accountField');
        // Parse into column, table where account ID is stored, associated path
        list($column, $table, $associations) = $this->parseAccountField($accountField);
        // Finish and return early if account ID is stored in current table
        if (empty($associations) && !empty($column)) {
            $entity->{$column} = $this->account->id;
            return;
        }
        // If accound ID is in direct association, find foreign ID
        if (!empty($associations) && strpos($associations, '.') === false) {
            // Get association details on table where account ID is stored
            $association = $event->getSubject()->getAssociation($associations);
            // Get an instance of said table
            $associatedTable = $this->fetchTable($association->getClassName());
            // Shorthand to primary key of said table
            $primaryKey = $associatedTable->getPrimaryKey();
            // Find entry in associated table belonging to current account
            /*
             * Assuming we have only one record in associated table, find it
             * by current account ID. Condition will be added in beforeFind()
             */
            $entry = $associatedTable->find('all')
                ->select($primaryKey)
                ->first() // need only one entry
                ;
            // Field in entity being saved where associated ID goes
            $foreignKey = $association->getForeignKey();
            $entity->{$foreignKey} = $entry->get($primaryKey);
            return;
        }
    }
}
