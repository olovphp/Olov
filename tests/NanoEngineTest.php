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
                'body' => 'Nano is a micro template <b>engine</b> for PHP.'
            ]
        ];

        $this->engine = new Nano\Engine(__DIR__.'/templates');
        
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

        $this->assertEquals($value, $result);
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

        $this->assertEquals($expected, $result);
    }

    /**
     * test__canRenderTemplateWithBaseTemplate
     * 
     * 1.   We will include ":header.html.php" and ":footer.html.php" 
     *      in "a.html.php".
     *
     * 2.   We will extend template "::a.html.php" in "b.html.php" 
     *      and override the parent +header block.
     *
     * @covers ::render
     * @return void
     */
    public function test__canRenderTemplateWithBaseTemplate()
    {
        $output = $this->engine->render('b.html.php', $this->vars);
        $expected = "TemplateB:HeaderTemplateA:ContentOriginalFooter";

        $output = $this->strip($output); // Remove white space characters from output.

        $this->assertSame($expected, $output);
    }

    /**
     * queryProvider
     *
     * @return array
     */
    public function queryProvider()
    {
        return [
            ["page.title", "Welcome to Nano!"], 
            ["page.title|less:50", true], 
            ["page.title|less:10", false], 
            ["page.title|more:10", true], 
            ["page.title|more:100", false], 
            ["page.title|length", 16], 
            ["page.body", "Nano is a micro template &lt;b&gt;engine&lt;/b&gt; for PHP."],
            ["page.body*", "Nano is a micro template <b>engine</b> for PHP."], 
            ["page.body|esc*", "Nano is a micro template &lt;b&gt;engine&lt;/b&gt; for PHP."],
        ];
    }

    /**
     * strip
     *
     * @param string $str
     * @return string
     */
    private function strip($str) 
    {
        return preg_replace('/\s\s*/', '', $str);
    }


}
