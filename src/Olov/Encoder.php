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

namespace Olov;

use Olov\Engine;
use RuntimeException;
use BadMethodCallException;
use InvalidArgumentException;
use Closure;

/**
 * Olov PHP template engine.
 *
 * This is utility class and never to be instantiated.
 *
 * @author Gboyega Dada <boyega@gmail.com>
 * @version 1.x
 */
class Encoder {


    /**
     * Pattern to help detect invalid UTF8 characters in a string.
     * This way, we don't rely on `mb_check_encoding` which is often 
     * unavailable (as I have just deiscovered *IS INDEED* unavailable on this 
     * very machine.)
     */
    const UTF8_REGEX = '/(
        [\xC0-\xC1] # Invalid UTF-8 Bytes
        | [\xF5-\xFF] # Invalid UTF-8 Bytes
        | \xE0[\x80-\x9F] # Overlong encoding of prior code point
        | \xF0[\x80-\x8F] # Overlong encoding of prior code point
        | [\xC2-\xDF](?![\x80-\xBF]) # Invalid UTF-8 Sequence Start
        | [\xE0-\xEF](?![\x80-\xBF]{2}) # Invalid UTF-8 Sequence Start
        | [\xF0-\xF4](?![\x80-\xBF]{3}) # Invalid UTF-8 Sequence Start
        | (?<=[\x0-\x7F\xF5-\xFF])[\x80-\xBF] # Invalid UTF-8 Sequence Middle
        | (?<![\xC2-\xDF]|[\xE0-\xEF]|[\xE0-\xEF][\x80-\xBF]|[\xF0-\xF4]|[\xF0-\xF4][\x80-\xBF]|[\xF0-\xF4][\x80-\xBF]{2})[\x80-\xBF] # Overlong Sequence
        | (?<=[\xE0-\xEF])[\x80-\xBF](?![\x80-\xBF]) # Short 3 byte sequence
        | (?<=[\xF0-\xF4])[\x80-\xBF](?![\x80-\xBF]{2}) # Short 4 byte sequence
        | (?<=[\xF0-\xF4][\x80-\xBF])[\x80-\xBF](?![\x80-\xBF]) # Short 4 byte sequence (2)
        )/x';


    /**
     * encoding
     *
     * @var string
     */
    protected $encoding = 'UTF-8';

    /**
     * __construct
     *
     * @return void
     */
    public function __construct($charset = 'UTF-8') 
    {
        if (!empty($charset)) $this->encoding = $charset;

        $this->css_callback = [$this, 'esc_css_callback'];
        $this->js_callback = [$this, 'esc_js_callback'];
        $this->attr_callback = [$this, 'esc_attr_callback'];

    }

    /**
     * setEncoding
     *
     * @param mixed $charset
     * @return void
     */
    public function setEncoding($charset) 
    {
        $this->encoding = $charset;
    }

    /**
     * getEncoding
     *
     * @return string
     */
    public function getEncoding() 
    {
        return $this->encoding;
    }


    /**
     * is_utf8
     *
     * @param mixed $str
     * @return bool
     */
    public function is_utf8($str)
    {
        return (0 == preg_match(self::UTF8_REGEX, $str));
    }

    /**
     * convert_encoding
     *
     * @param string $str
     * @param string $to
     * @param string $from
     * @return string
     */
    public function convert_encoding($str, $to, $from) 
    {
        return ($to == $from) 
            ? $str
            : iconv($from, $to, $str);
    }

    /**
     * _esc_js
     *
     * Esc non-alphanumeric characters to \xHH or \xHHHH
     *
     * @param string $str
     * @param string $charset
     * @return string
     */
    public function esc_js($str) 
    {
        $str = $this->convert_encoding($str, 'UTF-8', $this->encoding);
        if (1 == preg_match(self::UTF8_REGEX, $str)) {
            throw new RuntimeException('Invalid UTF-8 string.');
        }

        $str = preg_replace_callback('/[^\w,\._]/Su', $this->js_callback, $str);

        return $this->convert_encoding($str, $this->encoding, 'UTF-8');
    }

    /**
     * esc_js_callback
     *
     * Internal use.
     *
     * @param array $matches
     * @return void
     */
    protected function esc_js_callback(array $m) 
    {
        $char = $m[0];

        // \xHH
        // Check if strlen === 1 (quicker than strlen)
        if (!isset($char[1])) {
            return sprintf('\\x%02X', ord($char));
        }

        // \uHHHH
        $char = $this->convert_encoding($char, 'UTF-16BE', 'UTF-8');

        return sprintf('\\u%04s', strtoupper(bin2hex($char)));
    }

    /**
     * _esc_css
     *
     * Esc non-alphanumeric characters to \xHH or \xHHHH
     *
     * @param string $str
     * @param string $charset
     * @return string
     */
    public function esc_css($str) 
    {
        $str = $this->convert_encoding($str, 'UTF-8', $this->encoding);
        if (1 == preg_match(self::UTF8_REGEX, $str)) {
            throw new RuntimeException('Invalid UTF-8 string.');
        }

        $str = preg_replace_callback('/[\W]/Su', $this->css_callback, $str);

        return $this->convert_encoding($str, $this->encoding, 'UTF-8');
    }

    /**
     * esc_css_callback
     *
     * Internal use.
     *
     * @param array $matches
     * @return void
     */
    protected function esc_css_callback(array $m) 
    {
        $char = $m[0];

        // \xHH
        // Check if strlen === 1 (quicker than strlen)
        if (!isset($char[1])) { 
            $ord = ord($char); 
        } else {
            // \uHHHH
            $char = $this->convert_encoding($char, 'UTF-16BE', 'UTF-8');
            $ord = hexdec(bin2hex($char));
        }

        return sprintf('\\%X ', $ord);
    }

    /**
     * esc_html
     *
     * @param string $str
     * @param string $charset
     * @return string
     */
    public function esc_html($str) 
    {
        return htmlspecialchars($str, ENT_QUOTES | ENT_SUBSTITUTE, $this->encoding); 

    }

    /**
     * esc_attr
     *
     * @param string $str
     * @param string $charset
     * @return string
     */
    public function esc_attr($str) 
    {
        $str = $this->convert_encoding($str, 'UTF-8', $this->encoding);
        if (1 == preg_match(self::UTF8_REGEX, $str)) {
            throw new RuntimeException('Invalid UTF-8 string.');
        }

        $str = preg_replace_callback('~[^\w,\.\-_:/]~Su', $this->attr_callback, $str);

        return $this->convert_encoding($str, $this->encoding, 'UTF-8');
    }

    /**
     * esc_attr_callback
     *
     * @param array $m
     * @return string
     */
    protected function esc_attr_callback(array $m) 
    {
        static $named_entity_map = [
            34 => 'quot',         // "
            38 => 'amp',          // &
            60 => 'lt',           // <
            62 => 'gt',           // >
        ];

        $char = $m[0]; // matched character
        $ord = ord($char); // Integer value of the char (code point)

        /**
         * Replace \x{control} and \x{special} characters with
         * hex entity for the Unicode replacement character a.k.a |?|
         */
        if (
            // 1. Control characters \x{0} - \x{1F}
            ($ord <= 0x1f && $char != "\t" && $char != "\n" && $char != "\r") || 

            // 2. Special characters \x{7F} - \x{9F}
            ($ord >= 0x7f && $ord <= 0x9f)
        ) {
            return '&#xFFFD;';
        }

        if (!isset($char[1])) { // strlen === 1
            $char = $this->convert_encoding($char, 'UTF-16BE', 'UTF-8');
        } 
        $ord = hexdec(bin2hex($char));

        return  isset($named_entity_map[$ord]) 

            // $char is an xml named entity
            ? '&' . $named_entity_map[$ord] . ';'

            // Use upper hex entities for any other other $char 
            : sprintf($ord > 255 ? '&#x%04X;' : '&#x%02X;', $ord);

    }

    /**
     * esc_url
     *
     * @param string $str
     * @return string
     */
    public function esc_url($str) 
    {
        return rawurlencode($str); 

    }

}
