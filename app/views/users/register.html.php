<div class="baseContainer">
	<fieldset>
		<legend>Enregistrez-vous</legend>
		<?php
		echo $this->form->create($registeringUser);
		
			echo $this->form->field(
				'firstName',
				array(
					'type' => 'text',
					'placeholder' => 'Prénom',
					'label' => 'Prénom'
				)
			);

			echo $this->form->field(
				'familyName',
				array(
					'type' => 'text',
					'placeholder' => 'Nom de famille',
					'label' => 'Nom de famille'
				)
			);

			echo $this->form->field(
				'email',
				array(
					'type' => 'email',
					'placeholder' => 'user@host.dom',
					'label' => 'Adresse électronique'
				)
			);

			echo $this->form->field(
					'password',
					array(
						'type' => 'password',
						'placeholder' => 'Mot de passe',
						'label' => 'Mot de passe'
					)
			);

			echo $this->form->field(
					'passwordVerify',
					array(
						'type' => 'password',
						'placeholder' => 'Mot de passe',
						'label' => 'Mot de passe'
					)
			);

			echo $this->form->submit(
				'Register',
				array(
					'class' => 'button blue'
				)
			);
		
		echo $this->form->end();
		?>
	</fieldset>
</div>