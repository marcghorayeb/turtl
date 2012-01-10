<section class="baseContainer">
	<header>
		<h1><?= $transaction->title;?></h1>
	</header>
	
	<?php
	echo $this->form->create($transaction, array('type' => 'file'));
		?>
		<fieldset>
			<legend>Edition</legend>
			<?php
			$categoryList += array('' => 'Aucune');

			echo $this->form->hidden('propagateChanges', array('value' => true));

			echo $this->form->field(
				'meta.tags',
				array(
					'label' => 'Tags',
					'value' => implode(',', $transaction->meta->tags->to('array'))
				)
			);

			echo $this->form->field(
				'meta.note',
				array(
					'label' => 'Note',
					'value' => $transaction->meta->note
				)
			);

			echo $this->form->field(
				'meta.category_id',
				array(
					'label' => 'Catégorie',
					'type' => 'select',
					'list' => $categoryList
				)
			);

			echo $this->form->field(
				'meta.verified',
				array(
					'label' => 'Verifiée',
					'type' => 'checkbox',
					'checked' => $transaction->meta->verified ? 'checked': ''
				)
			);
			?>
		</fieldset>
		<fieldset>
			<legend>Fichiers associés</legend>
			<?php
			if (count($files) > 0) {
				foreach ($files as $file) {
					echo '<p>Le fichier '.$file->filename.' est actuellement associé à cette transaction</p>';
				}
			}

			echo $this->form->field(
				'fileUpload',
				array(
					'label' => 'Fichier à associer',
					'type' => 'file'
				)
			);
			?>
		</fieldset>
		<?php
		echo $this->form->submit(
			'Modifier',
			array(
				'class' => 'button blue'
			)
		);

	echo $this->form->end();
	?>
</section>