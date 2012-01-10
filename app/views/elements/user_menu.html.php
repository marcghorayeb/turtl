<ul class="inline">
	<li><?= $this->Html->link('Activité', array('controller' => 'accounts',	'action' => 'summary')) ?></li>
	<li><?= $this->Html->link('Stats', array('controller' => 'categories')) ?></li>
	<!--li><?= $this->Html->link('Budget', array('controller' => 'budgets', 'action' => 'details')) ?></li-->
</ul>

<ul class="inline dropdown">
	<li>
		<?= $this->Html->link('Profil', array('controller' => 'users', 'action' => 'view')) ?>
		<ul>
			<li><?= $this->Html->link('Déconnexion', array('controller' => 'users', 'action' => 'logout')) ?></li>
		</ul>
	</li>
</ul>