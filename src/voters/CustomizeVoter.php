<?php
/**
 * CustomizeVoter
 *
 * @package     erdiko/authorize
 * @copyright   Copyright (c) 2017, Arroyo Labs, http://www.arroyolabs.com
 * @author      Leo Daidone, leo@arroyolabs.com
 */

namespace erdiko\authorize\voters;

use erdiko\authorize\traits\BuilderTrait;
use erdiko\authorize\traits\ConfigLoaderTrait;
use erdiko\authorize\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class CustomizeVoter implements VoterInterface
{
    use ConfigLoaderTrait;
    use BuilderTrait;

    public $_attributtes = array();
    private $container;

    public function __construct()
    {
        $config = $this->loadFromJson();
        $this->container = new \Pimple\Container();
        $validators = $config['validators'];
        $this->buildValidators($validators);
    }

    public function supportsAttribute($attribute)
    {
        foreach ($this->_attributtes as $validator=>$attributes) {
            if (in_array($attribute, $attributes)) {
                return $this->container['VALIDATORS'][$validator];
                break;
            }
        }
        return false;
    }

    public function vote(TokenInterface $token, $subject, array $attributes)
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return VoterInterface::ACCESS_DENIED;
        }

        $result = VoterInterface::ACCESS_ABSTAIN;

        foreach ($attributes as $attribute) {
            $validator = $this->supportsAttribute($attribute);
            if (!$validator) {
                continue;
            }
            $result = $validator->validate($token, $attribute, $subject)
                ? VoterInterface::ACCESS_GRANTED
                : VoterInterface::ACCESS_ABSTAIN;
        }

        return $result;
    }
}