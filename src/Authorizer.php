<?php
/**
 * Authorizer
 *
 * @category    Erdiko
 * @package     Authorize
 * @copyright   Copyright (c) 2016, Arroyo Labs, http://www.arroyolabs.com
 * @author      Leo Daidone, leo@arroyolabs.com
 */


namespace erdiko\authorize;

use \AC\Kalinka\Authorizer\RoleAuthorizer;
use erdiko\authenticate\iErdikoUser;

class Authorizer extends RoleAuthorizer
{
	private $isGuest = true;

	public function __construct(iErdikoUser $user)
	{
		$this->isGuest = ($user->isAnonymous());

		if(!$this->isGuest) {
			// assumed that roles is a string array in User model
			$roleNames = $user->getRoles();
			parent::__construct($user, $roleNames);

			$config = \erdiko\core\Helper::getConfig("authorize");

			$guards = $config["guards"];
			$policies = $config["policies"];

			$register = array();
			foreach ($guards as $guard) {
				$class = '\\models\\guards\\' . ucfirst(strtolower($guard));
				$register[$guard] = new $class;
			}

			$this->registerGuards($register);

			$this->registerRolePolicies($policies);
		}
	}

	public function can($action, $resType, $guardObject = null)
	{
		if($this->isGuest) {
			if (in_array(strtolower($resType),array('index','login', 'logout'))){
				$response = in_array($action, array('read', 'login', 'logout'));
			} else {
				$response = false;
			}
		} else {
			if(($resType=='logout') && ($action=='read')) {
				$response = true;
			} else {
				try {
					$response = parent::can( $action, $resType, $guardObject );
				} catch ( \InvalidArgumentException $e ) {
					\error_log( $e->getMessage() );
					$response = false;
				}
			}
		}
		return $response;
	}

	public function cannot($action, $resType, $guardObject = null)
	{
		return (!$this->can($action, $resType, $guardObject));
	}
}