<article class="accountDescription baseContainer">
	<header>
		<hgroup>
			<h1><?php echo $this->Bank->title($account['bankName']).' - '.$account['id'];?></h1>
			<h2><?php echo $account['balance']['amount'].' ('.$account['balance']['date'].')';?></h2>
		</hgroup>
	</header>
	
	<?php
	echo $this->_render('element', 'transactions_table');
	?>
	
	<footer>
		<ul class="inlineList">
			<li>
				<?php
				echo $this->Html->link(
					'Mettre à jour',
					array(
						'controller' => 'accounts',
						'action' => 'update',
						'args' => $account['id']
					),
					array(
						'class' => 'button medium green'
					)
				);
				?>
			</li>
			<li>
				<?php
				echo $this->Html->link(
					'Supprimer',
					array(
						'controller' => 'accounts',
						'action' => 'delete',
						'args' => $account['_id']
					),
					array(
						'class' => 'button medium red'
					)
				);
				?>
			</li>
		</ul>
	</footer>
</article>