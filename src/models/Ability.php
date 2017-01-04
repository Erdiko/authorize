<?php
/**
 * Ability
 *
 * @category    Erdiko
 * @package     Authorize
 * @copyright   Copyright (c) 2016, Arroyo Labs, http://www.arroyolabs.com
 * @author      Leo Daidone, leo@arroyolabs.com
 */

namespace erdiko\authorize\models;

use AC\Kalinka\Guard\BaseGuard;

class Ability extends BaseGuard
{
    const DEFAULT_ACTIONS = ['read','write','update','delete'];

    private static $instance;
    private static $baseRolesRuleset = [
        "anonymous" => [
            "index" => [
                "read" => "allow",
                "login" => "allow"
            ],
            "dashboard" => [
                "read" => "allow"
            ]
        ]
    ];

    private static $baseRuleset = [
        "index" => [
            "read" => "allow",
            "login" => "allow"
        ],
        "dashboard" => [
            "read" => "allow"
        ]
    ];

    private $container;
    private $actions = array();

    public function getInstace()
    {
        if(empty(self::$instance)){
            self::$instance = new Ability();
        }
        return self::$instance;
    }

    protected function __construct()
    {
        $this->container = new \Pimple\Container();
        $this->defaultActions = ['read','write','update','delete'];
        $this->container['POLICIES'] = array();
        $this->addRules(self::$baseRuleset);
    }

    public function getActions()
    {
        return array_merge(Ability::DEFAULT_ACTIONS, $this->actions);
    }

    public function addAction($action)
    {
        if(empty($action) || !is_string($action)) throw new \Exception("Action must be string");
        array_push($this->actions, $action);
    }

    public function removeAction($action)
    {
        if(empty($action) || !is_string($action)) throw new \Exception("Action must be string");
        $key = array_search($action,$this->actions,true);
        if(!empty($key)) unset($this->actions[$key]);
    }

    /**
     * @param array $rule => array('policyName'=>evaluation)
     */
    public function addRule($rule=array())
    {
        array_push($this->container['POLICIES'],$rule);
    }

    public function addRules($rules=array())
    {
        foreach ($rules as $rule){
            $this->addRule($rule);
        }
    }

    public function removeRule($ruleName)
    {
        $policies = array_keys($this->container['POLICIES']);
        if(in_array($ruleName, $policies)){
            unset($this->container['POLICIES'][$ruleName]);
        }
    }

    public function __call($name, $arguments)
    {
        // Nota: el valor $name es sensible a mayúsculas.
        if(strpos($name,'policy')==0){
            $result = false;
            $policies = array_keys($this->container['POLICIES']);
            if (preg_match('/^policy(.+)$/', $name, $matches)) {
                $policyName = lcfirst($matches[1]);
                if(in_array($policyName, $policies)){
                    $fun = create_function('$subject,$resource', $this->container['POLICIES'][$policyName]);
                    $result = (bool)$fun($arguments);
                }
            } elseif (method_exists($this,$name)) {
                $result = $this->$name($arguments);
            }
            return $result;
        }
        return $this->$name($arguments);
    }
}