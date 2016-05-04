<?php

require_once __DIR__.'/../src/Olov/Engine.php';

class OlovEngineTest extends PHPUnit_Framework_TestCase {

    /**
     * engine
     *
     * @var \Olov\Engine
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
                'title' => 'Welcome to Olov!', 
                'body' => 'Olov is a micro template <b>engine</b> for PHP.', 
                'tags' => [
                    'php',
                    'olov', 
                    'esca>pe<=me', 
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
                    ['text'=>'Facebook', 'bro'=>'Facebook >>>', 'a:href'=>'http://facebook.com', 'li:class'=>'item'], 
                    ['text'=>'Instagram', 'a:href'=>'http://instagram.com', 'li:class'=>'item'], 
                    ['txt'=>'Twitter', 'a:href'=>'http://twitter.com', 'li:class'=>'item'], 
                    ['val'=>'LinkedIn', 'a:href'=>'http://linkedin.com', 'li:class'=>'item'], 
                    ['value'=>'Pinterest', 'a:href'=>'http://pinterest.com', 'li:class'=>'item'], 
                ], 
                'todos'=> [
                    ['Grace Huang', 'input:type'=>'radio', 'input:name'=>'dev', 'input:value'=>'Grace Hunag'], 
                    ['Poppy Delevigne', 'input:type'=>'radio', 'input:name'=>'dev', 'input:value'=>'Poppy Delevigne'], 
                    ['Harley Viera-Newton', 'input:type'=>'radio', 'input:name'=>'dev', 'input:value'=>'Harley Viera-Newton']
                ]

            ]
        ];

        $this->engine = new Olov\Engine(__DIR__.'/templates');
        
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
            // 0: Expect false if variable is not set
            ["?page.author", false], 

            // 1: Expect true if variable is set
            ["?page.title", true], 

            // 2: Expect variable content
            ["page.title", "Welcome to Olov!"], 

            // 3: Expect true if variable's content length < 50 
            ["page.title|less:50", true], 

            // 4: Expect false if variable's content length is not < 10 
            ["page.title|less:10", false], 

            // 5: Expect true if variable's content length > 10 
            ["page.title|more:10", true], 

            // 6: Expect false if variable's content length not > 100 
            ["page.title|more:100", false], 

            // 7: Expect string content length 
            ["page.title|length", 16], 

            // 7: Expect array content length 
            ["page.links|length", 7],

            // 8: Expect escaped variable content
            ["page.body", "Olov is a micro template &lt;b&gt;engine&lt;/b&gt; for PHP."],

            // 9: Expect raw variable content
            ["page.body*", "Olov is a micro template <b>engine</b> for PHP."], 

            // 10: Expect escaped variable content (* turns it off, esc turns it back on -- zero sum)
            ["page.body|esc*", "Olov is a micro template &lt;b&gt;engine&lt;/b&gt; for PHP."],

            // 11: Expect escaped array variable values
            ["page.tags", ['php','olov', 'esca&gt;pe&lt;=me', 'template', 'html']],

            // 12: Expect raw array variable values
            ["page.tags*", ['php','olov', 'esca>pe<=me', 'template', 'html']],

            // 13: Expect Olov to echo array content with each entry wrapped in <li>
            ["page.tags|each", array_reduce(
                ['php', 'olov', 'esca>pe<=me', 'template', 'html'],
                function ($str, $v) {
                    $v = htmlspecialchars($v, ENT_COMPAT, 'UTF-8');
                    return "$str<li>$v</li>\n";
                }, "")
            ], 

            // 14: Expect Olov to echo array content with each entry wrapped in <div>
            ["page.tags|each:div", array_reduce(
                ['php', 'olov', 'esca>pe<=me', 'template', 'html'],
                function ($str, $v) {
                    $v = htmlspecialchars($v, ENT_COMPAT, 'UTF-8');
                    return "$str<div>$v</div>\n";
                }, "")
            ] , 

            // 15: Expect Olov to echo array content with each entry wrapped in <li><a><b> --- </b></a></li>
            ["page.tags|each:b,a,li", array_reduce(
                ['php', 'olov', 'esca>pe<=me', 'template', 'html'],
                function ($str, $v) {
                    $v = htmlspecialchars($v, ENT_COMPAT, 'UTF-8');
                    return "$str<li><a><b>$v</b></a></li>\n";
                }, "")
            ], 

            // 16: Expect Olov to echo array content with each entry wrapped in <li><a><b> --- </b></a></li>
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

            // 17: Expect Olov to echo array content with each entry wrapped in <li><a> --- </a></li>
            //     with mapped tag properties rendered correctly.
            [
                "page.links|each:a,li",
                '<li><a>Who? Me?</a></li>' . "\n" .  
                '<li class="item"><a href="http://unknown.com"></a></li>' . "\n" . 
                '<li class="item"><a href="http://facebook.com">Facebook &gt;&gt;&gt;</a></li>' . "\n" . 
                '<li class="item"><a href="http://instagram.com">Instagram</a></li>' . "\n" .  
                '<li class="item"><a href="http://twitter.com">Twitter</a></li>' . "\n" . 
                '<li class="item"><a href="http://linkedin.com">LinkedIn</a></li>' . "\n" .  
                '<li class="item"><a href="http://pinterest.com">Pinterest</a></li>' . "\n"
            ], 

            // 18: Expect Olov to handle self-closing tags (ex: <li><input> --- </li>)
            [
                "page.todos|each:input,li",
                '<li><input type="radio" name="dev" value="Grace Hunag" />Grace Huang</li>' . "\n" . 
                '<li><input type="radio" name="dev" value="Poppy Delevigne" />Poppy Delevigne</li>' . "\n" . 
                '<li><input type="radio" name="dev" value="Harley Viera-Newton" />Harley Viera-Newton</li>' . "\n"   
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
