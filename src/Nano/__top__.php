<?php

if (!function_exists('o')) {

    /**
     * o
     *
     * This is a global function that will be used to interact with 
     * this class from inside our template files.
     *
     * @param string $query
     * @param \Cataleya\Apps\Nano\Engine $engine
     * @access public
     * @return mixed
     */
    function o($query, $engine = null) {
        static $__engine__;

        if (null !== $engine && null === $query) { 
            $__engine__ = $engine; 
            return true;
        }

        return $__engine__($query);
    }


}

/**
 * Init o function.
 *
 * Here we will inject the Nano\Engine instance into our function.
 *
 * Note that this sets the engine instance used in the fn every time 
 * a template file is included. It is safer that way since this function will 
 * only be defined once (forever!) and we might have different instances of the 
 * engine floating around: engine -- float
 *
 * This is all happening within a closure in the $__engine__ object instance 
 * so this $__engine__ instance remains relatively isolated.
 *
 * @param null
 * @param \Cataleya\Apps\Nano\Engine $__engine__
 */
o(null, $__engine__);

