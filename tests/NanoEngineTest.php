<?php

require_once __DIR__.'/../src/Nano/Engine.php';

class NanoEngineTest extends PHPUnit_Framework_TestCase {

    /**
     * engine
     *
     * @var \Nano\Engine
     */
    private $engine;

    /**
     * test_vars
     *
     * @var mixed[]
     */
    private $test_vars;

    public function setUp() 
    {
        $this->vars = [
            'page' => [
                'title' => 'Welcome to Nano!', 
                'body' => 'Nano is a micro template <b>engine</b> for PHP.', 
                'tags' => [
                    'php',
                    'nano', 
                    'esca>pe<=me', 
                    'template',
                    'html'
                ], 
                'mixed' => [
                    'php', 
                    'nano', 
                    'esca>pe<=me', 
                    ['template', new DateTime()], 
                    [new DateTime()],
                    new DateTime()
                ], 
                'links' => [
                    'Who? Me?', 
                    ['a:href'=>'http://unknown.com', 'li:class'=>'item'], 
                    ['text'=>'Facebook', 'bro'=>'Facebook >>>', 'a:href'=>'http://facebook.com', 'li:class'=>'item'], 
                    ['text'=>'Instagram', 'a:href'=>'http://instagram.com', 'li:class'=>'item'], 
                    ['txt'=>'Twitter', 'a:href'=>'http://twitter.com', 'li:class'=>'item'], 
                    ['val'=>'LinkedIn', 'a:href'=>'http://linkedin.com', 'li:class'=>'item'], 
                    ['value'=>'Pinterest', 'a:href'=>'http://pinterest.com', 'li:class'=>'item'], 
                ]

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
     * test__engineInstanceCanBeInvokedAndSyntaxCheck
     *
     * @covers ::__invoke
     * @dataProvider queryProvider
     *
     * @param mixed $query
     * @access public
     * @return void
     */
    public function test__engineInstanceCanBeInvokedAndSyntaxCheck($query, $expected)
    {
        $result = $this->engine
            ->setVars($this->vars)
            ->__invoke($query);

        $this->assertEquals($expected, $result);
    }

    /**
     * test__invalidArgumentExceptionIsThrowWhenArgIsBad
     *
     * @covers ::__invoke
     * @dataProvider badArgQueryProvider
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessageContains Allowed tags:
     *
     * @param mixed $query
     * @access public
     * @return void
     */
    public function test__invalidArgumentExceptionIsThrowWhenArgIsBad($query, $expected)
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
            ["?page.author", false], 
            ["?page.title", true], 
            ["page.title", "Welcome to Nano!"], 
            ["page.title|less:50", true], 
            ["page.title|less:10", false], 
            ["page.title|more:10", true], 
            ["page.title|more:100", false], 
            ["page.title|length", 16], 
            ["page.body", "Nano is a micro template &lt;b&gt;engine&lt;/b&gt; for PHP."],
            ["page.body*", "Nano is a micro template <b>engine</b> for PHP."], 
            ["page.body|esc*", "Nano is a micro template &lt;b&gt;engine&lt;/b&gt; for PHP."],
            ["page.tags", ['php','nano', 'esca&gt;pe&lt;=me', 'template', 'html']],
            ["page.tags*", ['php','nano', 'esca>pe<=me', 'template', 'html']],
            ["page.tags|length", 5], 
            ["page.tags|each", array_reduce(
                ['php', 'nano', 'esca>pe<=me', 'template', 'html'],
                function ($str, $v) {
                    $v = htmlspecialchars($v, ENT_COMPAT, 'UTF-8');
                    return "$str<li>$v</li>\n";
                }, "")
            ], 
            ["page.tags|each:div", array_reduce(
                ['php', 'nano', 'esca>pe<=me', 'template', 'html'],
                function ($str, $v) {
                    $v = htmlspecialchars($v, ENT_COMPAT, 'UTF-8');
                    return "$str<div>$v</div>\n";
                }, "")
            ] , 
            ["page.tags|each:b,a,li", array_reduce(
                ['php', 'nano', 'esca>pe<=me', 'template', 'html'],
                function ($str, $v) {
                    $v = htmlspecialchars($v, ENT_COMPAT, 'UTF-8');
                    return "$str<li><a><b>$v</b></a></li>\n";
                }, "")
            ], 
            ["page.mixed|each:b,a,li", array_reduce(
                ['php', 'nano', 'esca>pe<=me', ['template', 'object'], ['object'], 'object'],
                function ($str, $v) {
                    if (!is_string($v) && !is_numeric($v) && !is_array($v)) return $str;
                    if (is_array($v)) {
                        $v = array_pop($v);
                    }

                    $v = htmlspecialchars($v, ENT_COMPAT, 'UTF-8');

                    return "$str<li><a><b>$v</b></a></li>\n";
                }, "")
            ], 
            [
                "page.links|each:a,li",
                '<li><a>Who? Me?</a></li>' . "\n" .  
                '<li class="item"><a href="http://unknown.com">undefined</a></li>' . "\n" . 
                '<li class="item"><a href="http://facebook.com">Facebook &gt;&gt;&gt;</a></li>' . "\n" . 
                '<li class="item"><a href="http://instagram.com">Instagram</a></li>' . "\n" .  
                '<li class="item"><a href="http://twitter.com">Twitter</a></li>' . "\n" . 
                '<li class="item"><a href="http://linkedin.com">LinkedIn</a></li>' . "\n" .  
                '<li class="item"><a href="http://pinterest.com">Pinterest</a></li>' . "\n"
            ]
        ];





        /*







         */
    }

    /**
     * badArgQueryProvider
     *
     * @return array
     */
    public function badArgQueryProvider()
    {
        return [
            ["page.tags|each:ul", array_reduce(
                ['php', 'nano', 'esca>pe<=me', 'template', 'html'],
                function ($str, $v) {
                    $v = htmlspecialchars($v, ENT_COMPAT, 'UTF-8');
                    return "$str<li>$v</li>\n";
                }, "")
            ] 
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
