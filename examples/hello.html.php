<?php o('::base.html.php') ?>

<?php o('+content'); ?>
<div class="article">
<p>
<?= o('page.body*') ?>
</p>
<p class="count"><small><?= o('page.body|length') ?> chars.</small></p>
</div>
<?php if (o('!page.devs')) { ?><strong>Where'd you go?</strong><?php } ?>
<?php if (o('?page.devs') && o('page.devs|less:5')) { ?>
<p>
<strong>Developers (<?= o('page.devs|length') ?>)</strong><br />
<ul>
<?php o('page.devs|each:a,input,li'); ?>
</ul>
</p>
<?php } ?>
<?php o('-content'); ?>


<?php o('+header'); ?>
<h3>Policeman overide yaa; Policeman overide yaa.</h3>
<?php o('-header'); ?>

