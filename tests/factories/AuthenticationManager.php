<?php
/**
 * AuthenticationManager
 *
 * @category    Erdiko
 * @package     Authenticate/Services
 * @copyright   Copyright (c) 2017, Arroyo Labs, http://www.arroyolabs.com
 * @author      Leo Daidone, leo@arroyolabs.com
 */
namespace erdiko\authorize\tests\factories;

use erdiko\authorize\Authorizer;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationProviderManager;
use Symfony\Component\Security\Core\Authentication\Provider\DaoAuthenticationProvider;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\PlaintextPasswordEncoder;
use Symfony\Component\Security\Core\User\InMemoryUserProvider;
use Symfony\Component\Security\Core\User\UserChecker;

class AuthenticationManager implements AuthenticationManagerInterface
{
    private $authenticationManager;

    public function __construct()
    {
        // implements UserProviderInterface
        $userProvider = new InMemoryUserProvider(
            array(
                'bar@mail.com' => array(
                    'password' => 'asdf1234',
                    'roles'    => array('ROLE_ADMIN'),
                ),
                'foo@mail.com' => array(
                    'password' => 'asdf1234',
                    'roles'    => array('ROLE_USER'),
                ),
            )
        );

        // Create an encoder factory that will "encode" passwords
        $encoderFactory = new \Symfony\Component\Security\Core\Encoder\EncoderFactory(array(
            // We simply use plaintext passwords for users from this specific class
            'Symfony\Component\Security\Core\User\User' => new PlaintextPasswordEncoder(),
        ));

        // The user checker is a simple class that allows to check against different elements (user disabled, account expired etc)
        $userChecker = new UserChecker();
        // The (authentication) providers are a way to make sure to match credentials against users based on their "providerkey".
        $userProvider = array(
            new DaoAuthenticationProvider($userProvider, $userChecker, 'main', $encoderFactory, true),
        );


        $this->authenticationManager = new AuthenticationProviderManager($userProvider, true);
    }

    public function authenticate(TokenInterface $unauthenticatedToken)
    {

        try {
            $authenticatedToken = $this->authenticationManager->authenticate($unauthenticatedToken);
            Authorizer::startSession();
            $tokenStorage = new TokenStorage();
            $tokenStorage->setToken($authenticatedToken);
            $_SESSION['tokenstorage'] = $tokenStorage;
        } catch (\Exception $failed) {
            // authentication failed
            throw new \Exception($failed->getMessage());
        }
        return $authenticatedToken;
    }
}