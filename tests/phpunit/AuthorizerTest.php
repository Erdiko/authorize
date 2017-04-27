<?php
/**
 * Authorize
 *
 * @category    Erdiko
 * @package     Authenticate/Services
 * @copyright   Copyright (c) 2017, Arroyo Labs, http://www.arroyolabs.com
 * @author      Leo Daidone, leo@arroyolabs.com
 */

namespace tests\phpunit;

require_once dirname(__DIR__) . '/ErdikoTestCase.php';
require_once dirname(__DIR__) . '/factories/AuthenticationManager.php';
require_once dirname(__DIR__) . '/factories/MyErdikoUser.php';

use erdiko\authorize\Authorizer;
use erdiko\authorize\tests\factories\AuthenticationManager;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use \tests\ErdikoTestCase;



class AuthorizerTest extends ErdikoTestCase
{
	public $user_a;
	public $user_b;
	public $auth;
	public $authManager;


	public function setUp()
	{
	    $this->authManager = new AuthenticationManager();
	    $this->auth = new Authorizer($this->authManager, array());
        $this->user_a = new UsernamePasswordToken('bar@mail.com', 'asdf1234', 'main', array());
        $this->user_b = new UsernamePasswordToken('foo@mail.com', 'asdf1234', 'main', array());
	}

    public function testCanGranted()
    {
        $this->authManager->authenticate($this->user_a);
        $this->assertTrue($this->auth->can('VIEW_ADMIN_DASHBOARD'));
    }

    public function testCanRejected()
	{
        $this->authManager->authenticate($this->user_b);
        $this->assertFalse($this->auth->can('VIEW_ADMIN_DASHBOARD'));
	}
}
