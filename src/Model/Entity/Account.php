<?php
declare(strict_types=1);

namespace Bakeoff\Multitenancy\Model\Entity;

use Cake\ORM\Entity;

/**
 * Account Entity
 *
 * @property string $id
 * @property bool $is_active
 * @property string $name
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 * @property \Cake\I18n\DateTime|null $accessed
 *
 * @property \CakeDC\Users\Model\Entity\User[] $users
 */
class Account extends Entity
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
        'is_active' => true,
        'name' => true,
        'created' => true,
        'modified' => true,
        'accessed' => true,
        'users' => true,
    ];
}
