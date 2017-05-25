<?php
/**
 * ValidatorInterface
 *
 * @package     erdiko/authorize
 * @copyright   Copyright (c) 2017, Arroyo Labs, http://www.arroyolabs.com
 * @author      Leo Daidone, leo@arroyolabs.com
 */

namespace erdiko\authorize;


interface ValidatorInterface
{
    /**
     * Should return array of supported attributes as uppercase strings
     *
     * @return array of strings
     */
    public static function supportedAttributes();

    /**
     * Validate if $attribute is supported by this validator
     *
     * @param $attribute
     * @return bool
     */
    public function supportsAttribute($attribute);

	/**
	 * @param        $token
	 * @param string $attribute
	 *
	 * @return bool
	 */
    public function validate($token, $attribute='');
}