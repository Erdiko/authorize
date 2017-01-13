<?php
/**
 * Index
 *
 * @package     erdiko/authorize/models
 * @copyright   Copyright (c) 2017, Arroyo Labs, http://www.arroyolabs.com
 * @author      Leo Daidone, leo@arroyolabs.com
 */


namespace erdiko\authorize\models\guards;


use AC\Kalinka\Guard\BaseGuard;

class Index extends BaseGuard
{
  public function getActions()
  {
    return ["read","login","logout"];
  }
}