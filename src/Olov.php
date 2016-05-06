<?php
/**
 * Olov PHP Template Engine (http://olovphp.github.io/Olov)
 *
 * @link      http://olovphp.github.io/Olov
 * @copyright Copyright (c) 2016 Gboyega Dada
 * @license   https://github.com/olovphp/Olov/blob/master/gpl-3.0.txt (GPLv3 License)
 */

use Olov\Engine;
use Olov\Encoder;
use RuntimeException;
use BadMethodCallException;
use InvalidArgumentException;

/**
 * Olov PHP template engine.
 *
 * Use this class to get an Olov\Engine instance like this:
 *
 *      `$o = Olov::o($path);`
 *
 * You can also instantiate the Engine class directly like this:
 *
 *      `$o = new Olov\Engine($path);`
 *
 * @author Gboyega Dada <gboyega@fpplabs.com>
 * @version 0.1.0
 */
class Olov {

    public static function o($path) 
    {
        return new Engine($path);
    }

}
