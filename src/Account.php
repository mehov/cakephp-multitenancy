<?php

namespace Multitenancy;

class Account
{

    /**
     * @return string
     */
    private static function getPlugin()
    {
        return __NAMESPACE__;
    }

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
    private static function getSessionKey()
    {
        return sprintf('%s.%s', self::getPlugin(), 'Account');
    }

    /**
     * @param \Multitenancy\Model\Entity\Account $account
     */
    public static function set($account)
    {
        $account = self::getSession()->write(self::getSessionKey(), $account);
    }

    /**
     * @return \Multitenancy\Model\Entity\Account
     */
    public static function get()
    {
        return self::getSession()->read(self::getSessionKey());
    }

}
