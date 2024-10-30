<?php
declare(strict_types=1);

namespace Multitenancy\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * AccountsUsers Model
 *
 * @property \Multitenancy\Model\Table\AccountsTable&\Cake\ORM\Association\BelongsTo $Accounts
 * @property \Multitenancy\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 *
 * @method \Multitenancy\Model\Entity\AccountsUser newEmptyEntity()
 * @method \Multitenancy\Model\Entity\AccountsUser newEntity(array $data, array $options = [])
 * @method array<\Multitenancy\Model\Entity\AccountsUser> newEntities(array $data, array $options = [])
 * @method \Multitenancy\Model\Entity\AccountsUser get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \Multitenancy\Model\Entity\AccountsUser findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \Multitenancy\Model\Entity\AccountsUser patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\Multitenancy\Model\Entity\AccountsUser> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \Multitenancy\Model\Entity\AccountsUser|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \Multitenancy\Model\Entity\AccountsUser saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\Multitenancy\Model\Entity\AccountsUser>|\Cake\Datasource\ResultSetInterface<\Multitenancy\Model\Entity\AccountsUser>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\Multitenancy\Model\Entity\AccountsUser>|\Cake\Datasource\ResultSetInterface<\Multitenancy\Model\Entity\AccountsUser> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\Multitenancy\Model\Entity\AccountsUser>|\Cake\Datasource\ResultSetInterface<\Multitenancy\Model\Entity\AccountsUser>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\Multitenancy\Model\Entity\AccountsUser>|\Cake\Datasource\ResultSetInterface<\Multitenancy\Model\Entity\AccountsUser> deleteManyOrFail(iterable $entities, array $options = [])
 */
class AccountsUsersTable extends Table
{
    /**
     * Initialize method
     *
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('accounts_users');
        $this->setDisplayField(['user_id', 'account_id']);
        $this->setPrimaryKey(['user_id', 'account_id']);

        $this->belongsTo('Accounts', [
            'foreignKey' => 'account_id',
            'joinType' => 'INNER',
            'className' => 'Multitenancy.Accounts',
        ]);
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
            'className' => 'CakeDC/Users.Users',
        ]);
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn(['account_id'], 'Accounts'), ['errorField' => 'account_id']);
        $rules->add($rules->existsIn(['user_id'], 'Users'), ['errorField' => 'user_id']);

        return $rules;
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->uuid('account_id')
            ->notEmptyString('account_id');

        $validator
            ->uuid('user_id')
            ->notEmptyString('user_id');

        return $validator;
    }
}
