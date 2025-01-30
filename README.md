# Multitenancy for CakePHP 5

This plugin extends [cakedc/users](https://github.com/cakedc/users) and introduces customer accounts and ability to scope application tables by specific customers (tenants).

For CakePHP 3, see (now archived) [pronique/multitenant](https://github.com/pronique/multitenant).

Read more about Multitenancy: [en.wikipedia.org/wiki/Multitenancy](https://en.wikipedia.org/wiki/Multitenancy).

### Installation
Require this plugin using composer:
```
composer require bakeoff/multitenancy
```
Create required tables:
```
bin/cake migrations migrate -p Multitenancy
```
In your application's `src/Application.php`:
```
$this->addPlugin(\Bakeoff\Multitenancy\Plugin::class);
```

### Usage

##### Configuring tables
Add the TenantScope Behavior to a table where records should belong to an account.
```
$this->addBehavior('Bakeoff/Multitenancy.TenantScope');
```

The above will ensure a `where()` condition is added to every `find()` call on that table, except e.g. [uniqueness checks](https://book.cakephp.org/5/en/orm/validation.html#creating-unique-field-rules) which use [`exists()`](https://api.cakephp.org/5.0/class-Cake.ORM.Table.html#exists()) internally.

By default, TenantScope checks against a column named `account_id` expected in the same table the behavior is added to. If you want to use a different column, pass it as `accountField`.

```
$this->addBehavior('Bakeoff/Multitenancy.TenantScope', [
    'accountField' => 'another_column_account_id'
]);
```

##### User interface
Once the Behavior is added to a table, every `find()` call to that table will include a condition for `accountField`. The account it will be looking for needs to be in a user session: either set directly by user, or automatically by just looking up the last accessed account for that user.

###### Choosing an account

Direct your users to `\Cake\Routing\Router::url(['_name' => 'Bakeoff/Multitenancy:ChooseAccount'])` where they can choose an existing accout or create a new one.

Use `<?= $this->element('Bakeoff/Multitenancy.account') ?>` in your view templates to display current account or link to the *Choose Account* page.

###### Setting an account

If you wish to manually set a specific account to be used.

```
// Load the accounts table. If you're in a controller, use fetchTable()
$accountsTable = $this->fetchTable('Bakeoff/Multitenancy.Accounts');
// Select the account
$account = $accountsTable->find('all')
    ->leftJoinWith('Users')
    ->where(['Users.id' => $user->get('id')])
    ->orderBy('accessed DESC')
    ->first();
// Update last accessed timestamp
$accountsTable->setAccessedNow($account);
// Cache a copy of this account we just found to session
\Bakeoff\Multitenancy\Account::set($account);
```

###### Automatically picking up last used account (Default)

See `\Bakeoff\Multitenancy\Model\Behavior\TenantScopeBehavior::detectAccount()`

##### Stopping the Behavior

In some cases you would want to stop checking the ownership for records in a specific table, and just get all entries instead. To do that, simply remove the behavior from that table.

```
$this->Articles->removeBehavior('TenantScope');
```
