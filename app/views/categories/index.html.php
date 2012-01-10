<?php
$this->Html->script('defaults/highcharts/highcharts', array('inline' => false));
$this->Html->script('defaults/highcharts/themes/turtl', array('inline' => false));
$this->Html->script('categories/index', array('inline' => false));
?>

<article id="monthStats">
	<header>
		<h1>Statistiques du <?= $date ?></h1>
	</header>

	<div id="categoriesMonthBar" data-chart-type="bar"></div>
	<div class="clear"></div>
</article>

<article id="historyStats">
	<header>
		<h1>Historique des dépenses</h1>
	</header>

	<div id="categoryHistoryPlot" data-chart-type="line"></div>
	<div class="clear"></div>
</article>