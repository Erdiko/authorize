<?php

namespace erdiko\authorize\helpers;

use erdiko\authenticate\AuthenticationManager;
use erdiko\authorize\Authorizer;
use erdiko\users\models\user\UserProvider;
use Exception;

class AuthorizerHelper
{
    const DEFAULT_MSG = 'Sorry, you do not have permission';

    /**
     * @var Authorizer
     */
    protected static $authorizer = null;

    /**
     * Verify if the current user is allowed to make an action
     *
     * @param $action
     * @param null $object
     * @param null $message
     * @return bool
     * @throws Exception
     */
    public static function can($action, $object=null, $message=null)
    {
        if (!static::getAuthorizer()->can($action, $object)) {
            throw new Exception($message ?: self::DEFAULT_MSG, 403);
        }
        return true;
    }

    /**
     * Retrieve Authorizer
     *
     * @return Authorizer
     */
    public static function getAuthorizer()
    {
        if (is_null(static::$authorizer)) {
            static::$authorizer = new Authorizer(new AuthenticationManager(new UserProvider()));
        }
        return static::$authorizer;
    }
}