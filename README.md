# Erdiko Authorize

[![Package version](https://img.shields.io/packagist/v/erdiko/authorize.svg?style=flat-square)](https://packagist.org/packages/erdiko/authorize)

**Authorize**

An Erdiko package to provide user authorization.

Compatibility
-------------
This package is compatible with PHP 5.4 or above and the latest version of Erdiko.

Requirements
------------
This package requires Pimple version 3.0 or above and Symfony-security package version 3.2 or above.
To ensure all dependencies are installed when you run composer update in your project folder run the following commands:

`composer require pimple/pimple`

`composer require symfony/security`

Installation
------------
Add the eridko/authorize package using composer with this command:

`composer require erdiko/authorize`

How to Use
----------

Once you have installed the package you are ready to start. Basic Role based Admin validation works out of the box! 

To start using it in your code just create an instance of `Authorizer` class. This class will expect an instance of 
`AuthenticationManagerInterface` from symfony/security package as a constructor parameter.

Here's an example:
 ```php
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
 ```

It’s a best practice to add instance creation in the `_before` hook. An example of this best practice looks like this:
 
 ```php
 ...
     public function _before()
     {
         $authManager = new AuthenticationManager();
         $this->auth = new Authorizer($authManager);
         // Run the parent beore filter to prep the theme
         parent::_before();
     }
 ...
 ```
 
You will then have a `$this->auth` attribute available to use in any _get_ or _post_ action. This will be used in `can` 
methods that determine access, allowing you to grant or reject access to a resource.

For example, if current user has ADMIN role, then it will be redirected to admin dashboard (GRANTED), otherwise the user 
will be redirected to login page (REJECTED).
 
 ```
    php public function getDashboard()
    {
        if($this->auth->can("VIEW_ADMIN_DASHBOARD")) {
            // Add page data
            $this->setTitle('Erdiko Admin Dashboard');
            $this->addView('examples/admin/dashboard');
        } else {
            \erdiko\core\helpers\FlashMessages::set("You SHALL NO Pass!!", "danger");
            $this->redirect('/users/login');
        }
    }
 ```
Note that in this example, current user is an instance of `Symfony\Component\Security\Core\Authentication\Token\TokenInterface`,
stored in `$_SESSION['tokenstorage']`. 

Also available is the “VIEW_ADMIN_DASHBOARD” attribute we will use to grant or reject access for the current user.

You can use the same logic to validate Models by adding a `__construct` method where you will place the authorize creation

```php
   public function __construct()
   {
       $authManager = new AuthenticationManager();
       $this->auth = new Authorizer($authManager);
   }
```

Same for GRANT/REJECT:
```php
   public function doSomething1()
   {
       if($this->auth->can("CAN_DO_1")) {
           return "success something one";
       } else {
           throw new \Exception("You are not granted");
       }
   }
```

Customization
-------------

This package provides you with a framework to create custom validation. There are two different methods to create custom 
validation:
- Custom Voters

Implement `Symfony\Component\Security\Core\Authorization\Voter\VoterInterface` 
interface, and pass them in an array as second argument of `Authorizer` constructor.

- Custom Validator

Or you can create a `Validator` class that implements `erdiko\authorize ValidatorInterface` interface.
Then you will have to register all validators in `/app/config/default/authorize.json`, and voila, all the custom validation 
logic you've created is already available to the authorizer.  

authorize.json
```json
{
     "validators":{
       "custom_types": [{
         "name": "example",
         "namespace": "app_validators_example",
         "classname": "ExampleValidator",
         "enabled": true
       }]
     }
   }
```

In these validator classes you will be able to define custom attributes, "VIEW_ADMIN_DASHBOARD" as we mention above,
we might want to add "IS_PREMIUM_ACCOUNT", or any other attributes you want.

Let's implement the example class registered in the example JSON.

```php
class ExampleValidator implements ValidatorInterface
{
    public static function supportedAttributes()
    {
        return array('IS_PREMIUM_ACCOUNT');
    }

    public function supportsAttribute($attribute)
    {
        return in_array($attribute, self::supportedAttributes());
    }

    public function validate($token)
    {
        $result = false;
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            $result = false;
        } else {
            $result = ($user->getRole()=='ROLE_PREMIUM');
        }
        return $result;
    }
}
```


Special Thanks
--------------

Arroyo Labs - For sponsoring development, [http://arroyolabs.com](http://arroyolabs.com)