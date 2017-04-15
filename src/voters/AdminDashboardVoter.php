<?php
/**
 * AdminDashboardVoter
 *
 * @package     erdiko/authorize
 * @copyright   Copyright (c) 2017, Arroyo Labs, http://www.arroyolabs.com
 * @author      Leo Daidone, leo@arroyolabs.com
 */

namespace erdiko\authorize\voters;


use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class AdminDashboardVoter implements VoterInterface
{

    public function supportsAttribute($attribute)
    {
        return $attribute == 'VIEW_ADMIN_DASHBOARD';
    }

    public function vote(TokenInterface $token, $object, array $attributes)
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return VoterInterface::ACCESS_DENIED;
        }
        foreach ($attributes as $attribute) {
            if (!$this->supportsAttribute($attribute)) {
                continue;
            }
            foreach ($user->getRoles() as $role) {
                if (stripos($role, '_ADMIN') !== false) {
                    return VoterInterface::ACCESS_GRANTED;
                }
            }
        }
        return VoterInterface::ACCESS_ABSTAIN;
    }
}