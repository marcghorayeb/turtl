<article class="baseContainer">
	<header>
		<h1><?= $user->fullName() ?></h1>
	</header>

	<section>
		<p>
			Dernière connexion: <time datetime="<?=date('Y-m-d', $user->login->last->sec);?>" data-epoch="<?=$user->login->last->sec;?>"><?=date('d M', $user->login->last->sec);?></time>.
		</p>
		<p>Adresse mail de contact: <?=$user->email;?></p>
		<p>Notification de connexion par e-mail: <?= $user->login->notify ? 'Oui' : 'Non' ?></p>
		<?= $this->Html->link(
			'Modifier',
			array(
				'controller' => 'users',
				'action' => 'edit'
			),
			array(
				'class' => 'button medium blue'
			)
		);
		?>
	</section>

	<section>
		<header>
			<h2>Notifications</h2>
		</header>

		<p>Vous pouvez gérer ici la façon dont Turtl va vous notifier par mail.</p>
		<p>Sélectionnez les catégories que vous voulez suivre. Vous recevrez un résumé une fois par semaine. Si vous précisez un montant limite, vous serez notifié de tout dépassements.</p>

		<ul>
			<?php
			foreach ($categories as $category) {
				?>
				<li><?= $category->title() ?></li>
				<ul>
					<li>Résumé hebdomadaire: <?= $category->notify->summary ? 'Oui' : 'Non' ?></li>
					<li>Alerte de dépassement: <?= $category->notify->limit ? $category->notify->limit.'€' : 'Non' ?></li>
				</ul>
				<?php
			}
			?>
		</ul>
	</section>

	<section>
		<header>
			<h1>Compte bancaires</h1>
		</header>

		<p>La liste ci-dessous représente les comptes bancaires associés à votre profil Turtl.</p>
		<p>Pour rajouter un nouveau compte bancaire, <a href="/accounts/add">rien de plus simple</a>!</p>

		<?php
		if (count($accounts) > 0) {
			foreach ($accounts as $account) {
				echo $this->_render('element', 'account_description', compact('account'));
			}
		}
		else {
			?>
			<p>Aucun compte n'est associé à votre profil Turtl.</p>
			<?php
		}
		?>
	</section>
</article>