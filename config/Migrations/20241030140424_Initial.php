<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class Initial extends AbstractMigration
{
    /**
     * Up Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-up-method
     * @return void
     */
    public function up(): void
    {
        $this->table('accounts', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'uuid', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('is_active', 'boolean', [
                'default' => true,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('created', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('accessed', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addIndex(
                [
                    'id',
                ],
                [
                    'name' => 'id_UNIQUE',
                    'unique' => true,
                ]
            )
            ->create();

        $this->table('accounts_users', ['id' => false, 'primary_key' => ['account_id', 'user_id']])
            ->addColumn('account_id', 'uuid', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('user_id', 'uuid', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'user_id',
                ],
                [
                    'name' => 'idx_accounts_users_user_idx',
                ]
            )
            ->addIndex(
                [
                    'account_id',
                ],
                [
                    'name' => 'fk_accounts_users_account_idx',
                ]
            )
            ->create();

        $this->table('accounts_users')
            ->addForeignKey(
                'account_id',
                'accounts',
                'id',
                [
                    'update' => 'CASCADE',
                    'delete' => 'CASCADE',
                    'constraint' => 'fk_accounts_users_account'
                ]
            )
            ->update();
    }

    /**
     * Down Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-down-method
     * @return void
     */
    public function down(): void
    {
        $this->table('accounts_users')
            ->dropForeignKey(
                'account_id'
            )->save();

        $this->table('accounts')->drop()->save();
        $this->table('accounts_users')->drop()->save();
    }
}
