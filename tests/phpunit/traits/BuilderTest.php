<?php
/**
 *
 * @todo add tests for the different template types
 */
namespace tests\phpunit\traits;

use erdiko\authorize\traits\BuilderTrait;

require_once dirname(dirname(__DIR__)) . '/ErdikoTestCase.php';
require_once dirname(dirname(__DIR__)) . '/factories/ExampleValidator.php';

class BuilderTest extends \tests\ErdikoTestCase
{
	use BuilderTrait;

	public $container = null;

    public $_attributtes = array();

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
		$this->container = new \Pimple\Container();
	}

	public function tearDown()
	{
		unset($this->container);
	}

	public function testBuildValidators()
	{
		$auth = json_decode($this->auth_json,true);
		$this->buildValidators($auth["validators"]["custom_types"]);

		$this->assertInstanceOf(\Pimple\Container::class,$this->container["VALIDATORS"]);

		$this->assertNotEmpty($this->container["VALIDATORS"]["example"]);
	}
}
