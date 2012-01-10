<?php
$this->Html->style('accounts/summary', array('inline' => false));
$this->Html->script('accounts/summary', array('inline' => false));
?>

<article id="summary">
	<header>
		<h2>Activité du <?= $date;?></h2>

		<ul class="inline">
			<?php
			foreach ($periods as $i => $period) {
				//$class = in_array($period, $this->_request->params['args'], true) ? 'active' : '';
				$class =  (strcmp($date, $period) === 0) ? 'active' : '';
				?>
				<li>
					<?= $this->Html->link(
						$period,
						array(
							'controller' => 'accounts',
							'action' => 'summary',
							'args' => $period
						),
						array('class' => $class)
					)
					?>
				</li>
				<?php
			}
			?>
		</ul>
	</header>

	<?= $this->_render('element', 'transactions_table', array('preRenderTBody' => false, 'unverifiedCount' => $unverifiedCount)) ?>
	<?= $this->_render('element', 'transaction_tab') ?>

	<footer>
		<ul class="inline">
			<?php
			foreach ($periods as $period) {
				$class = in_array($period, $this->_request->params['args'], true) ? 'active' : '';
				?>
				<li>
					<?= $this->Html->link(
						$period,
						array(
							'controller' => 'accounts',
							'action' => 'summary',
							'args' => $period
						),
						array('class' => $class)
					)
					?>
				</li>
				<?php
			}
			?>
		</ul>
	</footer>
</article>