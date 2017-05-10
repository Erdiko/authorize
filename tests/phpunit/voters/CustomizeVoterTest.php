<?php

namespace tests\phpunit\voters;

require_once dirname(dirname(__DIR__)) . '/ErdikoTestCase.php';
require_once dirname(dirname(__DIR__)) . '/factories/AuthenticationManager.php';
require_once dirname(dirname(__DIR__)) . '/factories/ExampleValidator.php';

use erdiko\authorize\tests\factories\AuthenticationManager;
use erdiko\authorize\voters\CustomizeVoter;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use \tests\ErdikoTestCase;


class CustomizeVoterTest extends ErdikoTestCase
{
    public $voter;
    public $authManager;

    public $auth_json = "{
  \"validators\":{
    \"custom_types\": [{
      \"name\": \"example\",
      \"namespace\": \"erdiko_authorize_tests_factories\",
      \"classname\": \"ExampleValidator\",
      \"enabled\": true
    }]
  }
}";

    public function setUp()
    {
        $this->voter = new CustomizeVoter();
        $auth = json_decode($this->auth_json,true);
        $this->voter->buildValidators($auth["validators"]["custom_types"]);
        $this->authManager = new AuthenticationManager();
    }

    public function testSupportsAttribute()
    {
        $this->assertInstanceOf('erdiko\authorize\ValidatorInterface',$this->voter->supportsAttribute('VIEW_TEST'));
    }

    public function testSupportsAttributeFail()
    {
        $this->assertFalse($this->voter->supportsAttribute('IS_SUPER_ADMIN'));
    }

    public function testVoteGrant()
    {
        $_tokenA = new UsernamePasswordToken('bar@mail.com', 'asdf1234', 'main', array());
        $tokenA = $this->authManager->authenticate($_tokenA);
        $this->assertTrue((bool)$this->voter->vote($tokenA, null, array('VIEW_TEST')));
    }

    public function testVoteReject()
    {
        $_tokenB = new UsernamePasswordToken('foo@mail.com', 'asdf1234', 'main', array());
        $tokenB = $this->authManager->authenticate($_tokenB);
        $this->assertEquals(-1,$this->voter->vote($tokenB, null, array('VIEW_ADMIN_DASHBOARD')));
    }
}