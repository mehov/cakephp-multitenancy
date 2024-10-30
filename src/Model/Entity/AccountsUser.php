<?php
declare(strict_types=1);

namespace Multitenancy\Model\Entity;

use Cake\ORM\Entity;

/**
 * AccountsUser Entity
 *
 * @property string $account_id
 * @property string $user_id
 *
 * @property \Multitenancy\Model\Entity\Account $account
 * @property \Multitenancy\Model\Entity\User $user
 */
class AccountsUser extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'account' => true,
        'user' => true,
    ];
}
