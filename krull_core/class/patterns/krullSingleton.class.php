<?php

class krullSingleton extends krullObject
{
    function __construct() 
	{
        
    }

    function &__getInstanceImp($name)
	{
        static $instances = array();

        if (!isset($instances[$name]))
		{
            //$instances[$name] = new $name();
			eval('$instances[$name] = new '.$name.'();');
        }

        return $instances[$name];
    }

    function &getInstance() 
	{
        trigger_error('SingletonInterface::getInstance() needs to be overridden in a subclass.', E_USER_ERROR);
    }
}


?>