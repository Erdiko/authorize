<?php
/**
 * Authorizer
 *
 * @package     erdiko/authorize
 * @copyright   Copyright (c) 2017, Arroyo Labs, http://www.arroyolabs.com
 * @author      Leo Daidone, leo@arroyolabs.com
 */

namespace erdiko\authorize;

use erdiko\authorize\traits\SessionAccessTrait;
use erdiko\authorize\voters\AdminDashboardVoter;
use erdiko\authorize\voters\CustomizeVoter;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManager;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use \Symfony\Component\Security\Core\Authorization\Voter\RoleVoter;

class Authorizer
{
    use SessionAccessTrait;

    private $voters = array();
    private $decisionManager;
    private $checker;
    private $tokenStorage;

    public function __construct(AuthenticationManagerInterface $authenticationManager, $voters=array())
    {
	    $sapi = php_sapi_name();
	    if(!$this->contains('cli', $sapi)){
		    self::startSession();
	    }

        $this->voters = array(
            new RoleVoter('ROLE_'),
            new AdminDashboardVoter(),
            new CustomizeVoter()
        );

        if(!empty($voters)) {
            foreach ($voters as $voter) {
                if($voter instanceof VoterInterface) {
                    array_push($this->voters, $voter);
                }
            }
        }

        // We store our (authenticated) token inside the token storage
        $this->tokenStorage = new TokenStorage();
        if(array_key_exists('tokenstorage',$_SESSION)){
            $this->tokenStorage->setToken($_SESSION['tokenstorage']->getToken());
        } else {
	        $token = new UsernamePasswordToken("general","general","main", array());
	        $this->tokenStorage->setToken($token);
        }

        $this->decisionManager = new AccessDecisionManager($this->voters, AccessDecisionManager::STRATEGY_AFFIRMATIVE, false, true);
        $this->checker  = new AuthorizationChecker(
            $this->tokenStorage,
            $authenticationManager,
            $this->decisionManager
        );
    }

    public function can($attribute, $resource=null)
    {
        try {
	        if(array_key_exists('tokenstorage',$_SESSION) && !empty($_SESSION['tokenstorage'])) {
		        $granted = $this->checker->isGranted( $attribute, $resource );
	        } else {
	        	$granted = false;
	        }
        } catch (AuthenticationCredentialsNotFoundException $e) {
            $granted = false;
        }

        return $granted;
    }

	public function contains($needle, $haystack)
	{
		return strpos($haystack, $needle) !== false;
	}
}