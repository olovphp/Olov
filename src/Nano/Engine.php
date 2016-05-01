<?php


namespace Nano;

use Nano\Engine;
use RuntimeException;
use InvalidArgumentException;
use Closure;

/**
 * Nano template engine.
 *
 * @package FPP Labs Package
 * @author Gboyega Dada <gboyega@fpplabs.com>
 * @version $Id$
 */
class Engine {

    const IMPORT = 0;
    const EXTEND = 1;
    const RENDER = 2;

    /**
     * debug
     *
     * @var bool
     */
    private $debug = false;

    /**
     * base_path
     *
     * @var mixed
     */
    protected $base_path = null;

    /**
     * parent
     *
     * A stack heirachical stack of parents.
     * Filenames of templates that we are extending (if any.)
     *
     * @var mixed
     */
    protected $parents_stack = [];

    /**
     * child    
     *
     * Filename of template extending the current one.
     *
     * @var mixed
     */
    protected $child;

    /**
     * name 
     *
     * Template name.
     *
     * @var string
     */
    protected $name;

    /**
     * filename
     *
     * Full filename.
     *
     * @var string
     */
    protected $filename; 

    /**
     * blocks
     *
     * @var mixed[]
     */
    protected $blocks = [];

    /**
     * block_is_open
     *
     * Name of +block that is open or false;
     *
     * @var bool|string
     */
    protected $block_is_open = false;

    /**
     * cache
     *
     * @var mixed[]
     */
    protected $cache = [];

    /**
     * filters
     *
     * @var callable[]
     */
    protected $filters = [];

    /**
     * locked_filters
     *
     * List of filters that cannot be removed.
     *
     * @var string[]
     */
    protected $locked_filters = [
        'length' => 1, 
        'first' => 1, 
        'last' => 1, 
        'less' => 1, 
        'more' => 1, 
        'esc' => 1
    ];

    /**
     * vars
     *
     * Template variables.
     *
     * @var mixed[]
     */
    protected $vars = [];

    /**
     * __construct
     *
     * @return void
     */
    public function __construct($base_path = "") 
    {
        if (!empty($base_path) && !file_exists($base_path)) {

            throw new InvalidArgumentException(
                sprintf(
                    'Invalid argument (1): valid folder path required. ' . 
                    'Please check path - "%s"', $base_path
                )
            );
        }

        $this->base_path = is_string($base_path) ? $base_path : "";

        /* Base Filter Functions */

        /**
         *  This will return the length of a string or array.
         *
         *  @param string|array $var
         *
         */
        $this->filters['length'] = function ($arg) {
            return is_string($arg) 
                ? strlen($arg) 
                : count((array)$arg);
        };

        /**
         *  This will return the current date (kind of redundant because you can 
         *  just call date() from within the template.)
         *
         *  Maybe I will use this formatting instead; let's see.
         */
        $this->filters['date'] = function ($arg = 'M d, Y') {
            return date($arg);
        };

        /**
         *  Treats var like an array|string. Truncates $var to $len(gth)  
         *
         *  @param string|array $var
         *  @param int $len    Length
         */
        $this->filters['first'] = function ($var = '', $len = null) {
            return is_string($var) 
                ? substr($var, 0, (int)$len) 
                : array_slice($var, 0, (int)$len);
        };

        /**
         *  Treats var like an array|string. Returns {$len} number of elements|chars from the end 
         *  or $var.
         *
         *  @param string|array $var
         *  @param int $len    Length
         */
        $this->filters['last'] = function ($var = '', $len = null) {
            return is_string($var) 
                ? substr($var, strlen($var)-(int)$len) 
                : array_slice((array)$var, count((array)$var)-(int)$len);
        };

        /**
         *  Treats $var as an array|string. 
         *  Returns true if length of {$var} is less than {$len}.  
         *
         *  @param string|array $var
         *  @param int $len    Length
         */
        $this->filters['less'] = function ($var = '', $len = null) {
            return is_string($var) 
                ? (strlen($var) < (int)$len) 
                : (count((array)$var) < (int)$len);
        };

        /**
         *  Treats $var as an array|string. 
         *  Returns true if length of {$var} is more than {$len}.  
         *
         *  @param string|array $var
         *  @param int $len    Length
         */
        $this->filters['more'] = function ($var = '', $len = null) {
            return is_string($var) 
                ? (strlen($var) > (int)$len) 
                : (count((array)$var) > (int)$len);
        };

        /**
         *  Treats $var as an array|string. 
         *  Returns escaped output.  
         *
         *  @param mixed $var
         *  @param string|null $type    html|js
         */
        $this->filters['esc'] = function ($var = '', $type = 'html') {
            return is_scalar($var) 
                ? htmlspecialchars($var, ENT_COMPAT, 'UTF-8') 
                : array_map(function ($v) { 
                    return is_scalar($v) ? htmlspecialchars($v, ENT_COMPAT, 'UTF-8') : $v; 
                }, (array)$var);
        };
    }

    /**
     * __invoke
     *
     * @param string $query
     * @return mixed
     */
    public function __invoke($query) 
    {

        if (isset($this->cache[$query])) return $this->cache[$query];

        $q = $this->parseQuery($query);

        if ($q['type'] === 'fn' && $q['name'] === 'parent') {
            $this->_handle__parent();
        } else {

            $m = '_handle__'.$q['type'];
            if (!is_callable([$this, $m])) {
                throw new \BadMethodCallException(
                    sprintf('Unable to resolve query string "%s" with %s. Typo?', $query, $m)
                );
            }

            return $this->cache[$query] = $this->$m($q);
        }
    }


    /* -------------- HANDLERS ----------------------- */

    /**
     * _handle__variable
     *
     * <?= o('page.title|length') ?>
     *
     * @param array $data
     * @return mixed
     */
    protected function _handle__variable(array $data) 
    {
        $var = $this->getVar($data['name']);

        return $this->applyFilters($data['filters'], $var);
    }

    /**
     * _handle__exists
     *
     * <?php if (o('?page.title')) { // do stuff }
     *
     * @param array $data
     * @return bool
     */
    protected function _handle__exists(array $data) 
    {
        return $this->hasVar($data['name']);
    }

    /**
     * _handle__not_set
     *
     * <?php if (o('!page.title')) { // do stuff }
     *
     * @param array $data
     * @return bool
     */
    protected function _handle__not_set(array $data) 
    {
        return !$this->hasVar($data['name']);
    }

    /**
     * _handle__func
     *
     * <?= o('|date') ?>
     *
     * @param array $data
     * @return mixed
     */
    protected function _handle__fn(array $data) 
    {
        return $this->applyFilter($data['name']);
    }

    /**
     * _handle__import
     *
     * <?php o(':header.html.php') ?>
     *
     * @param array $data
     * @return void
     */
    protected function _handle__import(array $data) 
    {
        echo $this->compile($data['file'], self::IMPORT);

    }

    /**
     * _handle__parent
     *
     * <?php o('|parent') ?>
     *
     *
     * @return void
     */
    protected function _handle__parent() 
    {
        if ($this->block_is_open === false) {
            throw new RuntimeException('You can only call "|parent" inside a block!');
        }

        if ($this->hasParent()) {
            $block_name = $this->block_is_open;
            $parent_name = $this->getParent();
            
            // Interupt buffering
            $this->writeBlock($block_name, ob_get_clean());

            // Resume buffering
            ob_start(); 

            // We are yet to process the parent template file so we don't have 
            // the parent block in question at this instant. We will
            // set a closure to remind us which parent block to add when it is available.
            $this->writeBlock($block_name, function () use($block_name, $parent_name) {
                return $this->getBlock($block_name, $parent_name, true);
            });
        }

    }

    /**
     * _handle__extend
     *
     * <?php o('::base.html.php') ?>
     *
     * @param array $data
     * @return void
     */
    protected function _handle__extend(array $data) 
    {
        // add base template to stack
        $this->parents_stack[] = $data['name'];

    }

    /**
     * _handle__block_start
     *
     * <?php o('+header') ?>    // Header block
     *
     * @param array $data
     * @return void
     */
    protected function _handle__block_start(array $data) 
    {

        $name = $data['name'];

        if ($this->block_is_open !== false) {
            throw new RuntimeException(
                sprintf('Please close +%s before opening another block (+%s).', $this->block_is_open, $name)
            );
        }

        // Open block
        $this->block_is_open = $name;

        // Start a nested output buffer for current block.
        // We will close it and get block content when
        // o('-block') is called to signify end of block in template.
        ob_start(); 
    }

    /**
     * _handle__block_end
     *
     * <?php o('-header') ?>
     *
     * @param array $data
     * @return void
     */
    protected function _handle__block_end(array $data) 
    {
        $name = $data['name'];

        if ($this->block_is_open === false || $this->block_is_open !== $name) {
            $expected = $this->block_is_open ? $this->block_is_open : "none";

            throw new RuntimeException(
                sprintf(
                    'The "+%s" block is already closed or wasn\'t opened. ' . 
                    'Expected block ending: "-%s".', $name, $expected
                )
            );
        }

        // Close output buffer and get block content.
        $ob = ob_get_clean();

        // The block __buffer__ is where we will store the block content by doing: 
        // $this->setBlock('block_name', $content, '__buffer__'); and later this:
        // $this->getBlock('block_name', '__buffer__');
        // What we store here is what will actually be rendered. We replace 
        // blocks as templates are inherited and so on. Then finally we render blocks.

        // - Template has parent ?
        if ($this->hasParent()) { // true: collect blocks only..

            // - Template has child? (is a parent/inherited), 
            // - Child has current +block ? (override)
            if ($this->hasChild() && $this->hasBlock($name, $child = $this->getChild())) {

                // true: use child block to override/replace current block
                $this->setBlock($name, $this->getBlock($name, $child), '__buffer__');

                // Save this block's original content from ob in case child template calls "|parent"
                // and we need to go back and fetch it!
                $this->writeBlock($name, $ob);

            } else {
                // false: use own block

                // add to block buffer...
                $this->setBlock($name, $ob, '__buffer__');

                // Also save/attach to current template in case child template calls "|parent"
                // and we need to go back and fetch stuff we would have rendered even 
                // though it useless to us here.
                $this->writeBlock($name, $ob);

            }
        } 

        else { // false: final render

            // - Template has child? (is a parent/inherited), 
            // - Child has this +block ?
            if ($this->hasChild() && $this->hasBlock($name, $child = $this->getChild())) { 
                // true: render child block
                echo $this->getBlock($name, '__buffer__', true);
            } else {
                // false: render own block
                echo $ob;
            }
        }


        // close block
        $this->block_is_open = false;

    }


    /* -------------- HELPERS ------------------------ */

    /**
     * hasVar
     *
     * @param string $name  Dot key.
     * @return bool
     */
    protected function hasVar($name) 
    {
        $arr = $this->vars;
        $key = $name; // To keep the $name facade.

        if (empty($arr)) { return false; }
        if (array_key_exists($key, $arr)) { return true; }
        
        foreach (explode('.', $key) as $k) 
        {
            if (!is_array($arr) || !array_key_exists($k, $arr)) 
            {
                return false;
            }
            
            $arr = $arr[$k];
        }
        
        return true;
    }

    /**
     * getVar
     *
     * Get var from mutidim array with dot key.
     *
     * @param mixed $name   Dot key.
     * @return mixed
     */
    public function getVar($name) 
    {
        $arr = $this->vars;
        $key = $name; // To keep the $name facade.

        if (array_key_exists($key, $arr)) { return $arr[$key]; }
        
        foreach (explode('.', $key) as $k) 
        {
            if (!is_array($arr) || !array_key_exists($k, $arr)) 
            {
                $arr = null; break;
            }
            
            $arr = $arr[$k];
        }

        if ($arr === null) {
            throw new \RuntimeException(
                sprintf('Template variable %s not found.', $name)
            );
        }
        return $arr;

    }

    /**
     * hasParent
     *
     * @return bool
     */
    protected function hasParent() 
    {
        return !empty($this->parents_stack);
    }

    /**
     * getParent
     *
     * @return string
     */
    protected function getParent($pop=false)
    {
        return $this->hasParent() 
            ? ($pop ? array_pop($this->parents_stack) : end($this->parents_stack)) 
            : null;
    }

    /**
     * hasChild
     *
     * @return bool
     */
    protected function hasChild() 
    {
        return !empty($this->child);
    }

    /**
     * getChild
     *
     * @return string
     */
    protected function getChild()
    {
        return $this->hasChild() 
            ? $this->child 
            : null;
    }

    /**
     * parseBlock
     *
     * @param mixed $block
     * @return string
     */
    protected function parseBlock($block) 
    {
        return !is_array($block) 
            ? $block 
            : array_reduce($block, function ($buffer, $v) {
                return $buffer . ($v instanceOf Closure ? (string)$v() : $v);
            }, "");
    }

    /**
     * hasBlock
     *
     * @param string $name
     * @param string        Template filename (not always full path)
     * @return bool
     */
    protected function hasBlock($name, $filename=null) 
    {
        $fname = empty($filename) ? $this->name : $filename;

        return (
            isset($this->blocks[$filename]) && 
            isset($this->blocks[$filename][$name])
        );
    }

    /**
     * getBlock
     *
     * @param mixed $name   If no block name is provided, use 
     *                      current block name.
     * @param string        Template filename (not always full path)
     * @return void
     */
    protected function getBlock($name=null, $filename=null, $finalize=false) 
    {
        if ($name === null && $this->block_is_open === false) {
            throw new RuntimeException(
                'Missing argument (1): block name.'
            );
        }

        $name = empty($name) ? $this->block_is_open : $name;
        $fname = empty($filename) ? $this->name : $filename;

        if (!$this->hasBlock($name, $fname)) {
            throw new RuntimeException(sprintf('"+%s" block not found in "%s".', $name, $fname));
        }


        return $finalize  
            ? $this->parseBlock($this->blocks[$filename][$name]) 
            : $this->blocks[$filename][$name];
    }

    /**
     * resetBlock
     *
     * @param mixed $name
     * @param string        Template filename (not always full path)
     * @return void
     */
    protected function resetBlock($name=null, $filename=null) 
    {
        if ($name === null && $this->block_is_open === false) {
            throw new RuntimeException(
                'Missing argument (1): block name.'
            );
        }

        $name = empty($name) ? $this->block_is_open : $name;
        $fname = empty($filename) ? $this->name : $filename;

        if (!$this->hasBlock($name, $fname)) {
            return $this;
        }

        if (!isset($this->blocks[$fname])) {
            $this->blocks[$fname] = [ $name => [] ];
        } else {
            $this->blocks[$fname][$name] = [];
        }

        return $this;
    }

    /**
     * writeBlock
     *
     * @param string $name
     * @param string|Closure $data  This is either string data or a Closure 
     *                              that returns a name to be used to fetch
     *                              a parent block later.
     * @param string        Template filename (not always full path)
     * @return bool
     */
    protected function writeBlock($name, $data, $filename = null) 
    {
        if ($this->block_is_open === false || $this->block_is_open !== $name) {
            throw new RuntimeException(
                sprintf('Cannot write to block "+%s". It is already closed or wasn\'t opened; typo?', $name)
            );
        }

        $fname = empty($filename) ? $this->name : $filename;

        if (!isset($this->blocks[$fname])) {
            $this->blocks[$fname] = [ $name => [] ];
        }
        else if (!isset($this->blocks[$fname][$name])) {
            $this->blocks[$fname][$name] = [];
        }


        if ($data instanceOf Closure) {
            $data->bindTo($this);
        } 

        $this->blocks[$fname][$name] = array_merge($this->blocks[$fname][$name], (array)$data);
        

        return $this;

    }

    /**
     * setBlock
     *
     * @param string $name  Set instead of append.
     * @param string|Closure $data  This is either string data or a Closure 
     *                              that returns a name to be used to fetch
     *                              a parent block later.
     * @param string        Template filename (not always full path)
     * @return bool
     */
    protected function setBlock($name, $data, $filename = null) 
    {
        if ($this->block_is_open === false || $this->block_is_open !== $name) {
            throw new RuntimeException(
                sprintf('Cannot write to block "+%s". It is already closed or wasn\'t opened; typo?', $name)
            );
        }

        $fname = empty($filename) ? $this->name : $filename;

        if (!isset($this->blocks[$fname])) {
            $this->blocks[$fname] = [ $name => [] ];
        }


        if ($data instanceOf Closure) {
            $data->bindTo($this);
        } 

        $this->blocks[$fname][$name] = (array)$data;
        

        return $this;

    }
    /**
     * hasFilter
     *
     * @param mixed $name
     * @return bool
     */
    protected function hasFilter($name)
    {
        return isset($this->filters[$name]);
    }

    /**
     * applyFilter
     *
     * @param mixed $name
     * @return void
     */
    protected function applyFilters(array $filters, $arg=null)
    {

        if (empty($filters)) {
            return $arg;
        }

        foreach ($filters as $f) {
            $arg = $this->applyFilter($f, $arg);
        }


        return $arg;    
    }

    /**
     * applyFilter
     *
     * @param mixed $name
     * @return mixed
     */
    protected function applyFilter($filter, $arg=null)
    {

        $f = preg_split('/\s*:\s*/', $filter);
        if (!isset($this->filters[$f[0]])) {
            throw new \RuntimeException(
                sprintf('Filter "|%s" not found. Typo? Registered?', $f[0])
            );
        }

        $args = empty($arg) ? [] : [$arg];
        if (isset($f[1])) {
            $args = array_merge($args, preg_split('/\s*,\s*/', $f[1]));
        }

        return call_user_func_array($this->filters[$f[0]], $args);
    }

    /**
     * parseQuery
     *
     * @param mixed $q
     * @return array
     */
    protected function parseQuery($q) 
    {
        // match ending for single char operators
        if (preg_match('/^([a-zA-Z\d\|:,_\+\-\.]+)(\?|!|\*)$/', $q, $_)) {
            switch($_[2]) {
            case '?': return ['type'=>'exists', 'name'=>$_[1]];
            case '*': 
                $var = explode('|', $_[1]);
                return [
                    'type'=>'variable', 
                    'name'=>array_shift($var), 
                    'filters'=>$var
                ];
            }
        }

        if (preg_match('/^[a-zA-Z]/', $q)) {
            // assume it's a variable
            $var = explode('|', $q);
            $var[] = 'esc'; // add escape filter
            return [
                'type'=>'variable', 
                'name'=>array_shift($var), 
                'filters'=>$var
            ];
        }

        // match 2 char operators first
        switch(substr($q,0,2)) {
        case '::': return ['type'=>'extend', 'name'=>substr($q,2)];
        }

        // match beginning for single char operators
        switch($q[0]) {
        case '?': return ['type'=>'exists', 'name'=>substr($q,1)];
        case '!': return ['type'=>'not_set', 'name'=>substr($q,1)];
        case '+': return ['type'=>'block_start', 'name'=>substr($q,1)];
        case '-': return ['type'=>'block_end', 'name'=>substr($q,1)];
        case '|': return ['type'=>'fn', 'name'=>substr($q,1)];
        case ':': return ['type'=>'import', 'file'=>substr($q,1)];
        }

        throw new \RuntimeException(
            sprintf('Unable to resolve query string "%s". Typo?', $q)
        );
    }


    /**
     * compile
     *
     * @param string $__file__
     * @return void
     */
    protected function compile($__file__, $__type__) 
    {
        if (!file_exists($__file__)) {

            $__file__ = (string)$this->base_path . '/' . trim($__file__, '/');

            if (!file_exists($__file__)) {
                throw new \RuntimeException(
                    sprintf('Can\'t find template file %s. Please check path.', $__file__)
                );
            }
        }

        // set default path for imports and inheritence.
        if (empty($this->base_path)) {
            $this->base_path = pathinfo($__file__, PATHINFO_DIRNAME);
        }

        if ($__type__ === self::EXTEND || $__type__ === self::RENDER) {
            $this->filename = $__file__;
        } 

        
        /**
         * @var static $__engine__
         *
         * We will pass $this instance of the engine into the Closure scope.
         * This will also be the scope of any included template file. 
         * The engine instance is used by the o() function defined in the 
         * template __top__ (included).
         */
        $__engine__ = $this;

        /**
         * @var Closure $loader
         *
         * Our Closure will help us isolate the template scope from this one 
         * and the global scope.
         */
        $loader = function () use ($__file__, $__engine__) {
            ob_start();
            require_once(__DIR__."/__top__.php");
            require($__file__);
            return ob_get_clean();
        };

        return $loader();

    }




    /* ------------------ Public Interface --------------- */


    /**
     * render
     *
     * @param string $template  If you already set a base path, you do not need 
     *                          to provide a full filename.
     * @param array $vars
     * @return string
     */
    public function render($template, array $vars)
    {

        $this->name = $template;
        $this->vars = $vars;
        $output = $this->compile($template, self::RENDER);

        if ($this->debug) {
            assert(!empty($output), "Blank output returned from ::compile method.");
        }

        if ($this->hasParent()) {
            $this->child = $template; // It'S A BOY!! 
            $template = $this->getParent(true);

            // if current template has parent do this all over again
            return $this->render($template, $vars); 
        } else {

            // Reset ::child property
            $this->child = null;

            // Return final output
            return $output;
        }

    }

    /**
     * setPath
     *
     * @param string $path  Base path for your templates (especially for importing 
     *                      and extending other templates)
     * @return static
     */
    public function setPath($path) 
    {
        if (!file_exists($path)) {
            throw new \RuntimeException(sprintf('Can\'t find template folder %s. Please check path.', $path));
        }

        // set default path for imports and inheritence.
        $this->base_path = is_dir($path) ? $path : pathinfo($path, PATHINFO_DIRNAME);

        return $this;

    }

    /**
     * setVars
     *
     * @param array $vars
     * @return static
     */
    public function setVars(array $vars) 
    {
        $this->vars = $vars;

        return $this;
    }

    /**
     * setVar
     *
     * @param string $n
     * @param mixed $v
     * @return static
     */
    public function setVar($n, $v) 
    {
        return $this->set($this->vars, $n, $v);
    }
    
    /**
     * set
     *
     * @param array $a
     * @param mixed $n
     * @param mixed $v
     * @return static
     */
    protected function set(array &$a, $n, $v) 
    {
        if (array_key_exists($n, $a)) { $a[$n] = $v; }
        else {
            $partials = explode('.', $n);
            while ($n = array_shift($partials)) $a = &$a[$n]; 

            $a = $v;
        }

        return $this;
    }

    /**
     * registerFilter
     *
     * Register a closure as a filter.
     *
     * @param string $name
     * @param Closure $filter
     * @return static
     */
    public function registerFilter($name, Closure $filter) 
    {
        if ($this->hasFilter($name)) {
            throw new \RuntimeException(
                sprintf('Filter name "%s" is already use. Please choose something else.', $name)
            );
        }
        
        $filter->bindTo($this);
        $this->filters[$name] = $filter;
        
        return $this;
    }

    /**
     * removeFilter
     *
     * @param string $name
     * @return static
     */
    public function removeFilter($name) 
    {
        if (isset($this->locked_filters[$name])) {
            throw new \RuntimeException(
                sprintf('This filter (%s) cannot removed because it is locked.', $name)
            );
        }
        unset($this->filters[$name]);

        return $this;
    }

}
