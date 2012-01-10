<section class="baseContainer">
	<header>
		<hgroup>
			<h1>Activité dans la catégorie <?php echo $category['title'];?></h1>
		</hgroup>
	</header>
	
	<?php
	echo $this->_render('element', 'transactions_table');
	?>
</section>
