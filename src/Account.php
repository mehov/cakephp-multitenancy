<?php

namespace Multitenancy;

class Account
{

    private static function getPlugin()
    {
        return __NAMESPACE__;
    }

    public static function getSession()
    {
        return new \Cake\Http\Session();
    }

    private static function getSessionKey()
    {
        return sprintf('%s.%s', self::getPlugin(), 'Account');
    }

    public static function set($account)
    {
        $account = self::getSession()->write(self::getSessionKey(), $account);
    }

    public static function get()
    {
        return self::getSession()->read(self::getSessionKey());
    }

}
