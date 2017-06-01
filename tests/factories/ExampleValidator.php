<?php
/**
 * Created by IntelliJ IDEA.
 * User: leodaido
 * Date: 4/27/17
 * Time: 2:42 PM
 */

namespace erdiko\authorize\tests\factories;


use \erdiko\authorize\ValidatorInterface;

class ExampleValidator implements ValidatorInterface
{
    public static function supportedAttributes()
    {
        return array('VIEW_TEST');
    }

    public function supportsAttribute($attribute)
    {
        return in_array($attribute, self::supportedAttributes());
    }

    public function validate($token, $attribute='', $object=null)
    {
        return true;
    }
}