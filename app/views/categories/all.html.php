<section class="baseContainer">
	<header>
		<h1>Vos catégories</h1>
	</header>
	
	<table class="accountActivity">
		<thead>
			<th>Nom</th>
		</thead>
		<tbody>
			<?php
			foreach($categories as $category) {
				?>
				<tr>
					<td><?php echo $category['title'];?></td>
				</tr>
				<?php
			}
			?>
		</tbody>
		<caption align="bottom">
			<?php echo count($categories);?> catégorie(s)</td>
		</caption>
	</table>
	
	<footer>
		<ul class="inlineList">
			<li>
				<?php
				echo $this->Html->link(
					'Liste de catégorie prédéfinie',
					array(
						'controller' => 'categories',
						'action' => 'predefined'
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
					'Ajouter des catégories manuellement',
					array(
						'controller' => 'categories',
						'action' => 'add'
					),
					array(
						'class' => 'button medium green'
					)
				);
				?>
			</li>
		</ul>
	</footer>
</section>