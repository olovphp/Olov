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
            ->setPath(__DIR__.'/templates');
        
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
     * test__engineInstanceCanBeInvoked
     *
     * @covers ::__invoke
     * @dataProvider queryProvider
     *
     * @param mixed $query
     * @access public
     * @return void
     */
    public function test__engineInstanceCanBeInvoked($query, $expected)
    {
        $result = $this->engine
            ->setVars($this->vars)
            ->__invoke($query);

        $this->assertEquals($result, $expected);
    }

    /**
     * test__canRenderTemplateWithPartials
     * 
     * We will include ":header.html.php" and ":footer.html.php" 
     * in "a.html.php".
     *
     * @covers ::render
     * @return void
     */
    public function test__canRenderTemplateWithPartials()
    {
        $output = $this->engine->render('a.html.php', $this->vars);
        $expected = 
<<< END1
Original Header

Template A: Content

Original Footer
END1;

        $this->assertEquals(trim($expected), trim($output));
    }

    /**
     * test__canRenderTemplateWithBaseTemplate
     * 
     * We will extend template "::a.html.php" in "b.html.php" 
     * and override the parent +header block.
     *
     * @covers ::render
     * @return void
     */
    public function test__canRenderTemplateWithBaseTemplate()
    {
        $output = $this->engine->render('b.html.php', $this->vars);
        $expected = 
<<< END2
Template B: Header

Template A: Content

Original Footer
END2;

var_dump($output);

        $this->assertEquals(trim($expected), trim($output));
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


}
