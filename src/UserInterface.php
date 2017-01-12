<?php
/**
 * UserInterface
 *
 * @package     erdiko/authorize
 * @copyright   Copyright (c) 2017, Arroyo Labs, http://www.arroyolabs.com
 * @author      John Arroyo, john@arroyolabs.com
 */
namespace erdiko\authorize;

interface UserInterface 
{
	public function getRoles();
	public function isAnonymous();
	public function isAdmin();
}