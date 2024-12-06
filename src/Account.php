<?php

namespace Bakeoff\Multitenancy;

class Account
{

    /**
     * @return \Cake\Http\Session
     */
    public static function getSession()
    {
        return new \Cake\Http\Session();
    }

    /**
     * @return string
     */
    public static function getSessionKey()
    {
        return sprintf('%s.%s', Plugin::getPlugin(), 'Account');
    }

    /**
     * @param \Bakeoff\Multitenancy\Model\Entity\Account $account
     */
    public static function set($account)
    {
        $account = self::getSession()->write(self::getSessionKey(), $account);
    }

    /**
     * @return \Bakeoff\Multitenancy\Model\Entity\Account
     */
    public static function get()
    {
        return self::getSession()->read(self::getSessionKey());
    }

}
