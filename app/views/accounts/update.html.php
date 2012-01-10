<section>
	<header>
		<h1><?php echo $this->Bank->title($account);?></h1>
	</header>
	
	<?php
	echo $this->form->create(
		$account,
		array(
			'type' => 'file'
		)
	);
		?>
		<fieldset class="popup">
			<legend>Mise à jour</legend>
			<?php
			echo $this->form->field(
				'file',
				array(
					'type' => 'file'
				)
			);
			?>
		</fieldset>
		<?php
		echo $this->form->submit(
			'Mettre à jour',
			array(
				'class' => 'button green'
			)
		);
		
	echo $this->form->end();
	?>
</section>