<html>
<head>
<title><?= o('page.title') ?></title>
<style>
body {
    background-color: #ffe;
    color: #555;
}

.wrap {
    display: block;
    position: relative;
    width: 80%;
    margin: 0 auto;
}

hr {
    border-bottom: solid 1px #111;
}

.article {
    border-bottom: dotted 1px #ddd;
}

.count {
    display: inline-block;
    background-color: #fff;
    padding: 5px 15px;
    box-shadow: 1px 1px 3px rgba(100, 100, 70, .1);
    border-radius: 0.4em;
    margin-top: 5px;
    text-align: center;
}   

</style>
</head>

<body>
<div class="wrap">
<?php o('+header'); ?>
<?php o(':header.hello.html.php') ?>
<?php o('-header'); ?>

<?php o('+content'); ?>
<h3>I should be replaced. -Default Text, MD.</h3>
<?php o('-content'); ?>

<?php o('+footer'); ?>
<?php o(':footer.hello.html.php') ?>
<?php o('-footer'); ?>
</div>
</body>
</html>
