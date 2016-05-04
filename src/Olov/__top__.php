<?php
/**
 * Olov Template Engine (https://github.com/olovphp/Olov)
 *
 * @link      https://github.com/olovphp/Olov
 * @copyright Copyright (c) 2016 Gboyega Dada
 * @license   https://github.com/olovphp/Olov/blob/master/gpl-3.0.txt (GPLv3 License)
 * @author Gboyega Dada <gboyega@fpplabs.com>
 * @version 1.0
 */

if (!function_exists('o')) {

    /**
     * o
     *
     * This is a global function that will be used to interact with 
     * this class from inside our template files.
     *
     * @param string $query
     * @param \Olov\Engine $engine
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
 * Here we will inject the Olov\Engine instance into our function.
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
 * @param \Olov\Engine $__engine__
 */
o(null, $__engine__);

