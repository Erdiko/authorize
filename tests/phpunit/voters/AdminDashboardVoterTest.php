<?php

namespace tests\phpunit\voters;

require_once dirname(dirname(__DIR__)) . '/ErdikoTestCase.php';
require_once dirname(dirname(__DIR__)) . '/factories/AuthenticationManager.php';

use erdiko\authorize\tests\factories\AuthenticationManager;
use erdiko\authorize\voters\AdminDashboardVoter;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use \tests\ErdikoTestCase;


class AdminDashboardVoterTest  extends ErdikoTestCase
{
    public $voter;
    public $authManager;

    public function setUp()
    {
        $this->voter = new AdminDashboardVoter();
        $this->authManager = new AuthenticationManager();
    }

    public function testSupportsAttribute()
    {
        $this->assertTrue($this->voter->supportsAttribute('VIEW_ADMIN_DASHBOARD'));
    }

    public function testSupportsAttributeFail()
    {
        $this->assertFalse($this->voter->supportsAttribute('IS_SUPER_ADMIN'));
    }

    public function testVoteGrant()
    {
        $_tokenA = new UsernamePasswordToken('bar@mail.com', 'asdf1234', 'main', array());
        $tokenA = $this->authManager->authenticate($_tokenA);
        $this->assertTrue((bool)$this->voter->vote($tokenA, null, array('VIEW_ADMIN_DASHBOARD')));
    }

    public function testVoteReject()
    {
        $_tokenA = new UsernamePasswordToken('bar@mail.com', 'asdf1234', 'main', array());
        $tokenA = $this->authManager->authenticate($_tokenA);
        $this->assertFalse((bool)$this->voter->vote($tokenA, null, array('SUPER_DASHBOARD')));
        $_tokenB = new UsernamePasswordToken('foo@mail.com', 'asdf1234', 'main', array());
        $tokenB = $this->authManager->authenticate($_tokenB);
        $this->assertFalse((bool)$this->voter->vote($tokenB, null, array('VIEW_ADMIN_DASHBOARD')));
    }
}