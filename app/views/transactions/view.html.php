<article id="transaction">
	<header>
		<h2><?= $transaction->title;?></h2>
		
		<p>
			<?php
			foreach ($transaction->meta->tags as $tag) {
				?>
				<span class="tag"><?= $this->User->tagTitle($tag);?></span>
				<?php
			}
			?>
		</p>

		<p>
			<?php
			if ($transaction->meta->verified) {
				echo 'Transaction vérifiée.';
			}
			else {
				echo 'Transaction non vérifiée.';
			}
			?>
		</p>
	</header>

	<p>
		<time datetime="<?php echo date('Y-m-d', $transaction->date->sec);?>" data-epoch="<?php echo $transaction->date->sec?>" data-format="DD, d MM, yy"><?php echo date('d/m/Y', $transaction->date->sec);?></time>
	</p>
	
	<?php
	if (strcmp($transaction->title, $transaction->description) !== 0) {
		echo '<p>'.$transaction->description.'</p>';
	}
	?>

	<?php
	if (!empty($transaction->meta->note)) {
		echo '<p>Note personelle: '.$transaction->meta->note.'</p>';
	}
	?>

	<section>
		<h3>Montant</h3>

		<p><?= $this->Bank->credit($transaction);?></p>
		<p><?= $this->Bank->debit($transaction);?></p>
	</section>
	
	<section>
		<h3>Catégorie</h3>
		
		<p>
			<?php
			if (!empty($transaction->meta->category_id)) {
				echo $this->Category->title($categories, $transaction->meta->category_id);
			}
			else {
				?>
				Cette transaction n'est rangée dans aucune catégorie pour l'instant.
				<?php
			}
			?>
		</p>
	</section>
	
	

	<section>
		<h3>Fichier associé</h3>

		<ul>
			<?php
			if (count($files) > 0) {
				foreach ($files as $file) {
					?>
					<li><?= $this->Html->link(
						$file->filename,
						array(
							'controller' => 'files',
							'action' => 'view',
							'args' => (string) $file->_id
						)
					)?>, <?= $this->Html->link(
						'Supprimer',
						array(
							'controller' => 'files',
							'action' => 'delete',
							'args' => (string) $file->_id
						)
					) ?>
					<?php
				}
			}
			else {
				?>
				<li>Aucun fichier associé à cette transaction pour l'instant.</li>
				<?php
			}
			?>
		</ul>
	</section>
	
	<footer>
		<ul class="inline">
			<li>
				<?= $this->Html->link(
					'Edit',
					array(
						'controller' => 'transactions',
						'action' => 'edit',
						'args' => (string) $transaction->_id
					),
					array(
						'class' => 'button medium blue'
					)
				);
				?>
			</li>
		</ul>
	</footer>
</article>