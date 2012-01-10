<?php
$this->Html->style('elements/loginForm', array('inline' => false));
?>

<!--section id="loginForm">
	<h2>Connectez-vous</h2>
	
	<?= $this->form->create(null, array('url' => 'users/login')) ?>
		<?= $this->form->text('email', array('type' => 'email', 'placeholder' => 'user@host.dom', 'autofocus' => 'autofocus')) ?>
		<?= $this->form->password('password', array('placeholder' => 'password')) ?>
		<?= $this->form->submit('Login') ?>
	<?= $this->form->end() ?>

	<p>Vous n'avez pas de compte? <?= $this->Html->link('Enregistrez-vous', array('controller' => 'users', 'action' => 'register')) ?> dès aujourd'hui, c'est gratuit!</p>
</section-->

<section id="login">
	<header>
		<img id="secure" src="/img/all-turtl-icons/original/lock64x64.png" title="Connexion sécurisée par SSL" width="64px" height="64px"></img>
		<h1>Connectez-vous</h1>
	</header>

	<div class="loginCard">
		<div id="cardReflect"></div>
		<div id="magneticBand"></div>
		<?= $this->form->create(null, array('url' => '/users/login')) ?>
			<div id="nameBand">
				<?= $this->form->field(
					'email',
					array(
						'type' => 'email',
						'placeholder' => 'Adresse électronique (e.g. user@domaine.com)',
						'autofocus' => 'autofocus',
						'label' => ''
					)
				) ?>
			</div>
			<div id="pwdBand">
				<?= $this->form->field(
					'password',
					array(
						'type' => 'password',
						'placeholder' => 'Mot de passe',
						'label' => ''
					)
				) ?>
			</div>
			<?= $this->form->submit('Connexion') ?>
		<?= $this->form->end() ?>
	</div>

	<p>Pas encore de compte ? <a href="/users/register">Inscrivez-vous</a> facilement!</p>
</section>