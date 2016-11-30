<?php
/**
 *
 * @todo add tests for the different template types
 */
namespace tests\phpunit;

use erdiko\authorize\Authorizer;

require_once dirname(__DIR__) . '/ErdikoTestCase.php';
require_once 'MyErdikoUser.php';


class AuthorizerTest extends \tests\ErdikoTestCase
{
	public $user;
	public $auth;

	public function setUp()
	{
		$this->user = MyErdikoUser::getAnonymous();
		$this->auth = new Authorizer($this->user);
	}

	public function tearDown()
	{
		unset($this->auth);
		unset($this->user);
	}

	public function testCan()
	{
		$this->assertTrue($this->auth->can('read','index'));
		$this->assertFalse($this->auth->can('write','login'));
		$this->assertTrue($this->auth->can('read','logout'));
		$this->assertFalse($this->auth->can('read','plans'));
	}

	public function testCannot()
	{
		$this->assertFalse($this->auth->cannot('read','index'));
		$this->assertTrue($this->auth->cannot('write','login'));
		$this->assertFalse($this->auth->cannot('read','logout'));
		$this->assertTrue($this->auth->cannot('read','plans'));
	}
}
