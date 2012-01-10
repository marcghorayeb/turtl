<section>
	<header>
		<h1>Suppression</h1>
	</header>
	
	
	<p>
		Etes-vous sûr de vouloir supprimer le compte bancaire <em><?php echo $account['id'];?></em> (<?php echo $this->Bank->title($account);?>)?<br/>
		Toutes les données concernant ce compte bancaire seront effacées de votre profil Turtl. Pour récupérer ces données, vous devrez importer à nouveau ce compte bancaire.
	</p>
	
	<?php
	echo $this->form->create($account);
		echo $this->form->hidden('userValidation', array('value' => true));

		?>
		<?php
		echo $this->form->submit(
			'Oui, supprimer ce compte',
			array('class' => 'button red')
		);
	echo $this->form->end();
	?>
</section>