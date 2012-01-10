<article class="accountDescription">
	<header>
		<hgroup>
			<h2><?= $this->Bank->title($account) ?></h2>
			<h3><?= $account->id ?> <span style="float:right"><?= $account->balance->amount ?>€</span></h3>
		</hgroup>
	</header>

	<p>Dernière mise à jour le <time datetime="<?= date('Y-m-d', $account->balance->date->sec) ?>"><?= date('d/m/Y', $account->balance->date->sec) ?></time>.
		<?= $this->Html->link(
			'Rafraichir',
			array(
				'controller' => 'accounts',
				'action' => 'refresh',
				'args' => (string) $account->_id
			)
		);?> ou <?= $this->Html->link(
			'Supprimer',
			array(
				'controller' => 'accounts',
				'action' => 'delete',
				'args' => (string) $account->_id
			)
		); ?>
	</p>
</article>