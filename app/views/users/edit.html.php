<article class="baseContainer">
	<header>
		<h1><?= $user->firstName;?> <?= $user->familyName;?></h1>
	</header>

	<?php
	echo $this->form->create($user);
		?>
		<fieldset>
			<legend>Edition</legend>
			<?= $this->form->field(
				'email',
				array(
					'label' => 'Adresse mail',
				)
			);
			?>
			<?= $this->form->field(
				'login.notify',
				array(
					'type' => 'checkbox',
					'label' => 'Notification de login par e-mail',
					'checked' => $user->login->notify ? 'checked' : ''
				)
			);
			?>
		</fieldset>
		<?php
		echo $this->form->submit(
			'Mettre à jour',
			array(
				'class' => 'button blue'
			)
		);
	echo $this->form->end();
	
	echo $this->form->create($user);
		?>
		<fieldset>
			<legend>Notifications</legend>

			<ul>
				<?php
				foreach ($categories as $i => $category) {
					$pre = 'categories.'.$i.'.';
					echo $this->form->hidden($pre.'id', array('value' => (string) $category->_id));
					?>
					<li><?= $category->title() ?></li>
					<ul>
						<li>
						<?= $this->form->field(
							$pre.'notify.summary',
							array(
								'type' => 'checkbox',
								'label' => 'Résumé hebdomadaire'
							)
						);
						?>
						</li>
						<li>
						<?= $this->form->field(
							$pre.'notify.limit',
							array(
								'type' => 'number',
								'label' => 'Alerte de dépassement'
							)
						);
						?>
						</li>
					</ul>
					<?php
				}
				?>
			</ul>
		</fieldset>
		<?php
		echo $this->form->submit(
			'Mettre à jour',
			array(
				'class' => 'button blue'
			)
		);
	echo $this->form->end();
	
	echo $this->form->create($user);
		?>
		<fieldset>
			<legend>Changer de mot de passe</legend>
			<?php
			echo $this->form->field(
				'oldPassword',
				array(
					'label' => 'Mot de passe actuel',
					'type' => 'password'
				)
			);

			echo $this->form->field(
				'newPassword',
				array(
					'label' => 'Nouveau mot de passe',
					'type' => 'password'
				)
			);

			echo $this->form->field(
				'newPasswordVerify',
				array(
					'label' => 'Vérification du mot de passe',
					'type' => 'password'
				)
			);
			?>
		</fieldset>
		<?php
		echo $this->form->submit(
			'Changer le mot de passe',
			array(
				'class' => 'button blue'
			)
		);

	echo $this->form->end();
	?>
</article>