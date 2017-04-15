<?php
/**
 * BuilderTrait
 *
 * @package     erdiko/authenticate/traits
 * @copyright   Copyright (c) 2016, Arroyo Labs, http://www.arroyolabs.com
 * @author      Leo Daidone, leo@arroyolabs.com
 */

namespace erdiko\authenticate\traits;


trait BuilderTrait
{

	public function buildValidators($storage = array())
	{
		$_container = new \Pimple\Container();
		foreach ($storage as $item){
			if($item["enabled"]==1){
				$class = '\\'
	                       . str_replace('_','\\',$item["namespace"]) . '\\'
	                       . ucfirst($item["classname"]);
                $this->_attributtes[$item["name"]] = $class::supportedAttributes();
				$_container[$item["name"]] = $this->container->factory(function ($c)use($class) {
					return new $class;
				});
			} else {
				continue;
			}
		}
		$this->container["VALIDATORS"] = $_container;
		unset($_container);
	}
}