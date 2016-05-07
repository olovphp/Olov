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

use Olov\Engine;
use Olov\Encoder;

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
 * @author Gboyega Dada <boyega@gmail.com>
 * @version 1.x
 */
class Olov {

    public static function o($path) 
    {
        return new Engine($path);
    }

}
