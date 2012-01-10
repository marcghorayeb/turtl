<?php
$this->html->script('defaults/highcharts/highcharts', array('inline' => false));
//$this->html->script('defaults/highcharts/themes/gray', array('inline' => false));
$this->html->script('budgets/details', array('inline' => false));
?>

<article id="budget">
	<header>
		<h1><?php echo $budget['title'];?></h1>

		<ul class="inline">
			<li>
				<?php
				echo $this->Html->link('Rafraichir', array('controller' => 'budgets', 'action' => 'refresh', 'args' => (string) $budget['_id']));
				?>
			</li>
			<li>
				<?php
				echo $this->Html->link('Supprimer', array('controller' => 'budgets', 'action' => 'delete', 'args' => (string) $budget['_id']));
				?>
			</li>
		</ul>
	</header>

	<section>
		<header>
			<h2>Position mensuelle</h2>
		</header>

		<section>
			<h3>Catégories</h3>

			<div id="currentMonthChart" data-chart-type="pie" style="width: 300px; float: left;"></div>
			<div id="currentLimits" data-chart-type="column" style="width: 670px; float: right;"></div>
		</section>

		<section>
			<h3>Tags suivis</h3>

			<div id="currentLimitsTags" data-chart-type="bar"></div>
		</section>
		
		<div style="clear: both;"></div>
	</section>

	<section>
		<header>
			<h2>Historique</h2>
		</header>

		<section>
			<h3>Catégories</h3>
		
			<div id="historyMonthChart" data-chart-type="plot"></div>
		</section>

		<section>
			<h3>Tags suivis</h3>

			<div id="historyMonthChartTags" data-chart-type="pie"></div>
		</section>

		<div style="clear: both;"></div>
	</section>

</article>