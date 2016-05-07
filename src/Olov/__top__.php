<?php
/**
 * This file is a part of Olov.
 *
 * @link      http://olovphp.github.io/Olov
 * @copyright Copyright (c) 2016 Gboyega Dada
 * @license   https://github.com/olovphp/Olov/blob/1.x/LICENSE.txt (GPLv3 License)
 *
 * Olov is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Olov is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Olov.  If not, see <http://www.gnu.org/licenses/>.
 *
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

        if (null === $query && $engine instanceOf \Olov\Engine) { 
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

