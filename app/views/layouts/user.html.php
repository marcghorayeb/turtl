<!doctype html>

<html>
<head>
	<?= $this->Html->charset() ?>
	<title>Turtl <?= $this->title() ?></title>
	
	<?= $this->Html->style(array(
		'http://fonts.googleapis.com/css?family=Poly',
		'defaults/normalize',
		'defaults/jquery.qtip',
		'defaults/defaults',
		'defaults/header',
		'defaults/footer',
		'defaults/buttons',
		'elements/flashMessage'
	)) ?>
	
	<?= $this->styles() ?>
	
	<link rel="apple-touch-icon" href="/apple-touch-icon.png" />
	<?= $this->Html->link('Icon', null, array('type' => 'icon')) ?>
</head>

<body>
	<header role="banner">
		<?php
		$flash = $this->flash->output();
		if (!empty($flash)) {
			?>
			<div class="popup flash-message"><?= $flash ?></div>
			<?php
		}
		?>

		<a href="#top" id="logo"><img src="/img/logo.png"/></a>

		<nav role="navigation">
			<?= $this->_render('element', 'user_menu') ?>
		</nav>
	</header>

	<section class="baseContainer" id="mainContainer" role="main">
		<?= $this->content() ?>
	</section>

	<div id="notifications"></div>
	
	<footer>
		<!--nav>
			<ul class="inline">
				<li><a href="/pages/about">A propos</a></li>
				<li><a href="/pages/contact">Contact</a></li>
			</ul>
		</nav-->

		<p>&copy; Turtl 2011-2012</p>
	</footer>

	<?= $this->Html->script(array(
		'https://ajax.googleapis.com/ajax/libs/jquery/1.7.0/jquery.min.js',
		'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.min.js',
		'defaults/jquery.qtip',
		'defaults/jquery.scrollTo-min',
		'defaults/jquery.localscroll-min',
		'defaults/defaults',
		'layouts/user'
	)) ?>

	<?= $this->scripts() ?>
</body>
</html>