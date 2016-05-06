<?php

require_once __DIR__.'/../src/Olov.php';
require_once __DIR__.'/../src/Olov/Engine.php';
require_once __DIR__.'/../src/Olov/Encoder.php';

class OlovEngineTest extends PHPUnit_Framework_TestCase {

    /**
     * engine
     *
     * @var \Olov\Engine
     */
    private $o;

    /**
     * test_vars
     *
     * @var mixed[]
     */
    private $vars;

    public function setUp() 
    {
        $this->vars = [
            'page' => [
                'title' => 'Welcome to Olov!', 
                'body' => 'Olov is a micro template <b>engine</b> for PHP.', 
                'tags' => [
                    'php',
                    'olov', 
                    'esca>pe<=me', 
                    'url.unsafe*&^', 
                    'template',
                    'html'
                ], 
                'mixed' => [
                    'php', 
                    'olov', 
                    'esca>pe<=me', 
                    ['template', new DateTime()], 
                    [new DateTime()],
                    new DateTime()
                ], 
                'links' => [
                    'Who? Me?', 
                    ['a:href'=>'http://unknown.com', 'li:class'=>'item'], 

                    // Crazy input
                    [
                        'text'=>'Facebook', 
                        'bro'=>'Facebook >>>', 
                        'a:href'=>'http://facebook.com" onClick="alert(\'badness\')"', 
                        'li:class'=>'item'
                    ], 
                    ['text'=>'Instagram', 'a:href'=>'http://instagram.com/u/gboyega?a=7', 'li:class'=>'item'], 
                    ['txt'=>'Twitter', 'a:href'=>'http://twitter.com', 'li:class'=>'item'], 
                    ['val'=>'LinkedIn', 'a:href'=>'http://linkedin.com', 'li:class'=>'item'], 
                    ['value'=>'Pinterest', 'a:href'=>'http://pinterest.com', 'li:class'=>'item'], 
                ], 
                'mycrushes'=> [
                    ['Grace Huang', 'input:type'=>'radio', 'input:name'=>'dev', 'input:value'=>'Grace Hunag'], 
                    ['Poppy Delevigne', 'input:type'=>'radio', 'input:name'=>'dev', 'input:value'=>'Poppy Delevigne'], 
                    ['Harley Viera-Newton', 'input:type'=>'radio', 'input:name'=>'dev', 'input:value'=>'Harley Viera-Newton']
                ]

            ]
        ];

        $this->o = Olov::o(__DIR__.'/templates');
        
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

        $result = $this->o
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
        $result = $this->o
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
        $result = $this->o
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
        $output = $this->o->render('b.html.php', $this->vars);
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
            // : Expect false if variable is not set
            ["?page.author", false], 

            // : Expect true if variable is set
            ["?page.title", true], 

            // : Expect variable content
            ["page.title", "Welcome to Olov!"], 

            // : Expect true if variable's content length < 50 
            ["page.title|less:50", true], 

            // : Expect false if variable's content length is not < 10 
            ["page.title|less:10", false], 

            // : Expect true if variable's content length > 10 
            ["page.title|more:10", true], 

            // : Expect false if variable's content length not > 100 
            ["page.title|more:100", false], 

            // : Expect string content length 
            ["page.title|length", 16], 

            // : Expect array content length 
            ["page.links|length", 7],

            // : Expect escaped variable content
            ["page.body", "Olov is a micro template &lt;b&gt;engine&lt;/b&gt; for PHP."],

            // : Expect raw variable content
            ["page.body*", "Olov is a micro template <b>engine</b> for PHP."], 

            // : Expect escaped variable content (* turns it off, esc turns it back on -- zero sum)
            ["page.body|esc*", "Olov is a micro template &lt;b&gt;engine&lt;/b&gt; for PHP."],

            // : Expect html escaped array variable values
            ["page.tags", ['php','olov', 'esca&gt;pe&lt;=me', 'url.unsafe*&amp;^', 'template', 'html']],

            // : Expect url escaped array variable values
            [
                "page.tags|esc: url", 
                ['php','olov', 'esca%3Epe%3C%3Dme', 'url.unsafe%2A%26%5E', 'template', 'html']
            ],

            // : Expect js+html escaped array variable values
            [
                "page.tags|esc: css", 
                [
                    'php','olov', 'esca\3E pe\3C \3D me', 
                    'url\2E unsafe\2A \26 \5E ', 
                    'template', 'html'
                ]
            ],

            // : Expect raw array variable values
            ["page.tags*", ['php','olov', 'esca>pe<=me', 'url.unsafe*&^', 'template', 'html']],

            // : Expect Olov to echo array content with each entry wrapped in <li>
            [
                "page.tags|each", 

                "<li>php</li>\n" . 
                "<li>olov</li>\n" . 
                "<li>esca&gt;pe&lt;=me</li>\n" . 
                "<li>url.unsafe*&amp;^</li>\n" . 
                "<li>template</li>\n" . 
                "<li>html</li>\n" 
            ], 

            // : Expect Olov to echo array content with each entry wrapped in <div>
            [
                "page.tags|each:div", 

                "<div>php</div>\n" . 
                "<div>olov</div>\n" . 
                "<div>esca&gt;pe&lt;=me</div>\n" . 
                "<div>url.unsafe*&amp;^</div>\n" . 
                "<div>template</div>\n" . 
                "<div>html</div>\n" 
            ] , 

            // : Expect Olov to echo array content with each entry wrapped in <li><a><b> --- </b></a></li>
            [
                "page.tags|each:b,a,li", 

                "<li><a><b>php</b></a></li>\n" . 
                "<li><a><b>olov</b></a></li>\n" . 
                "<li><a><b>esca&gt;pe&lt;=me</b></a></li>\n" . 
                "<li><a><b>url.unsafe*&amp;^</b></a></li>\n" . 
                "<li><a><b>template</b></a></li>\n" . 
                "<li><a><b>html</b></a></li>\n" 
            ], 

            // : Expect Olov to echo array content with each entry wrapped in <li><a><b> --- </b></a></li>
            //     and handle invalid entries like objects by printing their type instead ("object").
            ["page.mixed|each:b,a,li", array_reduce(
                ['php', 'olov', 'esca>pe<=me', ['template', 'object'], ['object'], 'object'],
                function ($str, $v) {
                    if (!is_string($v) && !is_numeric($v) && !is_array($v)) return $str;
                    if (is_array($v)) {
                        $v = array_pop($v);
                    }

                    $v = htmlspecialchars($v, ENT_COMPAT, 'UTF-8');

                    return "$str<li><a><b>$v</b></a></li>\n";
                }, "")
            ], 

            // : Expect Olov to echo array content with each entry wrapped in <li><a> --- </a></li>
            //     with mapped tag properties rendered correctly.
            // : Expect Olov to escape quotes in property values. 
            [
                "page.links|each:a,li",
                '<li><a>Who? Me?</a></li>' . "\n" .  
                '<li class="item"><a href="http://unknown.com"></a></li>' . "\n" . 
                '<li class="item"><a href="http://facebook.com&quot;&#x20;onClick&#x3D;' . 
                '&quot;alert&#x28;&#x27;badness&#x27;&#x29;&quot;">Facebook &gt;&gt;&gt;</a></li>' . "\n" . 
                '<li class="item"><a href="http://instagram.com">Instagram</a></li>' . "\n" .  
                '<li class="item"><a href="http://twitter.com">Twitter</a></li>' . "\n" . 
                '<li class="item"><a href="http://linkedin.com">LinkedIn</a></li>' . "\n" .  
                '<li class="item"><a href="http://pinterest.com">Pinterest</a></li>' . "\n"
            ], 

            // : Expect Olov to handle self-closing tags (ex: <li><input> --- </li>)
            [
                "page.mycrushes|each:input,li",
                '<li><input type="radio" name="dev" value="Grace&#x20;Hunag" />Grace Huang</li>' . "\n" . 
                '<li><input type="radio" name="dev" value="Poppy&#x20;Delevigne" />Poppy Delevigne</li>' . "\n" . 
                '<li><input type="radio" name="dev" value="Harley&#x20;Viera-Newton" />Harley Viera-Newton</li>' . "\n"   
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
                ['php', 'olov', 'esca>pe<=me', 'template', 'html'],
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
