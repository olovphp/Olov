<?php

require_once __DIR__.'/../src/Nano/Engine.php';

class NanoEngineTest extends PHPUnit_Framework_TestCase {

    private $engine;

    private $test_vars;

    public function setUp() 
    {
        $this->vars = [
            'page' => [
                'title' => 'Welcome to Nano!', 
                'body' => 'Nano is a micro template engine for PHP.'
            ]
        ];

        $this->engine = (new Nano\Engine())
            ->setPath(__DIR__.'/../examples');
        
    }

    /**
     * testSetVar
     *
     * @covers ::setVar
     * @covers ::getVar
     *
     * @return void
     */
    public function testSetVar() 
    {
        $name = "page.user";
        $value = "Gboyega Dada";

        $result = $this->engine
            ->setVar($name, $value)
            ->getVar($name);

        $this->assertEquals($result, $value);
    }

    /**
     * test__render
     *
     * @covers ::render
     * @dataProvider renderProvider
     *
     * @param string $template
     * @param array $vars
     * @return void
     */
    public function test__render($template) 
    {
        $output = $this->engine->render($template, $this->vars);
        $this->assertTrue(is_string($output) && !empty($output));

    }

    /**
     * test__invoke
     *
     * @covers ::__invoke
     * @dataProvider queryProvider
     *
     * @param mixed $query
     * @access public
     * @return void
     */
    public function test__invoke($query, $expected)
    {
        $result = $this->engine
            ->setVars($this->vars)
            ->__invoke($query);

        $this->assertEquals($result, $expected);
    }

    /**
     * queryProvider
     *
     * @return void
     */
    public function queryProvider()
    {
        return [
            ["page.title", "Welcome to Nano!"], 
            ["page.body", "Nano is a micro template engine for PHP."]
        ];
    }

    /**
     * renderProvider
     *
     * @return void
     */
    public function renderProvider()
    {
        return [
            ["base.html.php"], 
            ["hello.html.php"], 
            ["hello-again.html.php"]
        ];
    }


}
