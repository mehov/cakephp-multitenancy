<?php
declare(strict_types=1);

namespace Multitenancy\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Accounts Model
 *
 * @property \CakeDC\Users\Model\Table\UsersTable&\Cake\ORM\Association\BelongsToMany $Users
 *
 * @method \Multitenancy\Model\Entity\Account newEmptyEntity()
 * @method \Multitenancy\Model\Entity\Account newEntity(array $data, array $options = [])
 * @method array<\Multitenancy\Model\Entity\Account> newEntities(array $data, array $options = [])
 * @method \Multitenancy\Model\Entity\Account get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \Multitenancy\Model\Entity\Account findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \Multitenancy\Model\Entity\Account patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\Multitenancy\Model\Entity\Account> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \Multitenancy\Model\Entity\Account|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \Multitenancy\Model\Entity\Account saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\Multitenancy\Model\Entity\Account>|\Cake\Datasource\ResultSetInterface<\Multitenancy\Model\Entity\Account>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\Multitenancy\Model\Entity\Account>|\Cake\Datasource\ResultSetInterface<\Multitenancy\Model\Entity\Account> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\Multitenancy\Model\Entity\Account>|\Cake\Datasource\ResultSetInterface<\Multitenancy\Model\Entity\Account>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\Multitenancy\Model\Entity\Account>|\Cake\Datasource\ResultSetInterface<\Multitenancy\Model\Entity\Account> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class AccountsTable extends Table
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

        $this->setTable('accounts');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsToMany('Users', [
            'foreignKey' => 'account_id',
            'targetForeignKey' => 'user_id',
            'joinTable' => 'accounts_users',
            'className' => 'CakeDC/Users.Users',
        ]);
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
            ->boolean('is_active')
            ->notEmptyString('is_active');

        $validator
            ->scalar('name')
            ->maxLength('name', 255)
            ->requirePresence('name', 'create')
            ->notEmptyString('name');

        $validator
            ->dateTime('accessed')
            ->allowEmptyDateTime('accessed');

        return $validator;
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
        $rules->add($rules->isUnique(['id']), ['errorField' => 'id']);

        return $rules;
    }

    public function setAccessedNow($account)
    {
        $account->accessed = \Cake\I18n\DateTime::now();
        return $this->save($account);
    }

    /**
     * Finder for accounts belonging to given user. Usage (e.g. in Controller):
     *
     * ```
     * $identity = $this->getRequest()->getAttribute('identity');
     * $accountsQuery = $this->Accounts->find('byIdentity', $identity);
     * ```
     *
     * @param \Cake\ORM\Query\SelectQuery $selectQuery
     * @param \Authorization\Identity $identity
     * @return \Cake\ORM\Query\SelectQuery
     */
    public function findByIdentity($selectQuery, $identity)
    {
        $selectQuery = $selectQuery->find('all');
        // When no valid identity passed, silently return nothing
        if (!$identity) {
            return $selectQuery->where(['1 = 0']);
        }
        return $selectQuery
            ->leftJoinWith('Users')
            ->where(['Users.id' => $identity->get('id')])
        ;
    }
}
