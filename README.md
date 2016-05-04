## Olov is a micro templating engine for PHP ##

Olov uses native PHP templates but with a single elegant function `o()` 
to help you do amazing things with very simple syntax.

#### 1. Defining blocks... ####

Blocks are defined like this: ` <?php o('+blockname') ?> ` with a matching close 
like this ` <?php o('-blockname') ?> `. 

*Example of block definitions:*

```php
<?php /* ============  ./base.html.php ===============  */ ?>
<!DOCTYPE html>
<html>
<head><title>Welcome to Olov!</title></head>
<body>
<div class="header">
<?php o('+header'); ?>
<h3>Default Header Content</h3>
<?php o('-header'); ?>
</div>

<div class="content">
<?php o('+content'); ?>
<p>
The default content goes here and can be replaced in a child template that 
extends this one.
</p>
<?php o('-content'); ?>
</div>

<div class="footer">
<?php o('+footer'); ?>
<hr />
<small>&copy; 2016 Default Footer Content</small>
<?php o('-footer'); ?>
</div>

</body>
</html>
```

------------------------------------------------------------------

#### 2. Extending a base template... ####

To extend another template, at the very top, do: ` <?php o('::base.html.php') ?> ` 
*See example of block definitions below.*

```php
<?php /* ==============  ./hello.html.php  ============ */ ?>

<?php o('::base.html.php');  ?>

<?php o('+header'); ?>
<h2>Pimp My Header</h2>
<?php o('-header'); ?>

<?php o('+content'); ?>
<p>
To override the default +content block, create one in the child template 
and put whatever youu like in the new block!
</p>
<?php o('-content'); ?>

<?php o('+footer'); ?>
<hr />
<small>&copy; 2020 <i>Hmm, upgrades.</i></small>
<?php o('-footer'); ?>

```

------------------------------------------------------------------

#### 3. Template partials... ####

To include a template partial, do: ` <?php o(':partial.html.php') ?> ` 
*See example of partial include below (note the difference between the syntax for ::exending and :including)*

```php
<?php /* ==============  ./hello.html.php  ============ */ ?>

<?php o('::base.html.php');  ?>

<?php o('+header'); ?>
<?php o(':header.html.php'); ?>
<?php o('-header'); ?>

<?php o('+content'); ?>
<p>Content body here.</p>
<?php o('-content'); ?>

<?php o('+footer'); ?>
<?php o(':footer.html.php'); ?>
<?php o('-footer'); ?>

```

------------------------------------------------------------------

#### 4. Template variables... ####

You can define your template variables in an associative ` array[] ` like this:

```php
<?php 

/* ============== ./index.php =================== */ 
// Include `Olov\Engine` class (or use composer autoloader)
require '../path/to/olov/src/Olov/Engine.php';

// Declare vars...
$vars = [
    'page' => [
        'title' => 'Olov Template Engine for PHP', 
        'body'  =>  'Values are automatically <b>escaped</b> when used the template.', 
        'tags'  => [
            'php', 'template engine', 'olov'
        ]
    ];
```

**Then create Olov engine instance and render:**
```php
// Our templates folder...
$templates_path = "./my/templates/folder/":

// New instance of `Olov\Engine`
$Olov = new Olov\Engine($templates_path);

// Render...
$Olov->render('hello.html.php', $vars);

?>


```

**Our template: ./hello.html.php**

```php
<?php /* ==============  ./hello.html.php  ============ */ ?>

<?php o('::base.html.php');  ?>

<?php o('+header'); ?>
<h2><?= o('page.title') ?></h2>
<?php o('-header'); ?>

<?php o('+content'); ?>

<p>
<?= o('page.body') ?>
</p>

<p><small>Character count: <?= o('page.body|length') ?></small></p>

<ul>
<?php o('page.tags|each:i,li'); ?>
</ul>

<?php o('-content'); ?>

<?php o('+footer'); ?>
<hr />
<small>&copy; 2020 <i>Hmm, upgrades.</i></small>
<?php o('-footer'); ?>

```

------------------------------------------------------------------

#### 5. Loops ####

You can automatically print array values by doing: 
```php 
<ul>
<?php o('page.tags|each'); ?>
</ul>
```

This will output:

* php
* template engine
* olov


Olov wraps your array values with the ` <li> ` by default and auto escapes the text values. 
Olov can also wrap your loop items in multiple concentric layers of tags. For eaxample:
```php
<?php o('page.tags|each:b,a,li'); ?>
```
Outputs:
```html
<li><a><b>Text Value</b></a></li>
```


To set tag properties and attributes, define your list in your ` $vars ` array like this:
```php
$vars = [
    // ....
    'users' => [
        ['Jamie Foxx', 'a:href'=>'https://en.wikipedia.org/wiki/Jamie_Foxx', 'li:class'=>'name'],   
        ['Marlon Brando', 'a:href'=>'https://en.wikipedia.org/wiki/Marlon_Brando', 'li:class'=>'name'],   
        ['Thandie Newton', 'a:href'=>'https://en.wikipedia.org/wiki/Thandie_Newton', 'li:class'=>'name']
    ]
];
```
Then...
```php
<ul class="hollywood-actors">
<?php o('page.tags|each:a,li'); ?>
</ul>
```
Throw in the blender and ...
```html
<ul class="hollywood-actors">
<li class="name"><a href="https://en.wikipedia.org/wiki/Jamie_Foxx">Jamie Foxx</a></li>
<li class="name"><a href="https://en.wikipedia.org/wiki/Marlon_Brando">Marlon Brando</a></li>
<li class="name"><a href="https://en.wikipedia.org/wiki/Thandie_Newton">Thandie Newton</a></li>
</ul>
```   
* [Jamie Foxx](https://en.wikipedia.org/wiki/Jamie_Foxx)
* [Marlon Brando](https://en.wikipedia.org/wiki/Marlon_Brando)
* [Jamie Foxx](https://en.wikipedia.org/wiki/Thandie_Newton)

-----------------------------------------------------------------------

#### 6. Installation ####

You can [require](https://getcomposer.org/doc/03-cli.md) with Composer:
```shell
$ composer require "olov/olov:~1.0"
```
 
Or clone this repo with:
```shell
$ git clone https://github.com/olovphp/Olov.git
```

Or download the zip folder.



--------------------------------------------------------------------------


This is a quickstart guide not a real documentation (that is on the way.) In the meantime 
please clone the repo and run ` examples/index.php ` to see a live example. I hope you 
find this useful in your projects and bug reports are most welcome! 

