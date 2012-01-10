<?php
$this->html->script('accounts/add', array('inline' => false));
?>

<section class="baseContainer">
	<header>
		<h1>Ajout d'un nouveau compte bancaire</h1>
	</header>

	<?php
	if (empty($accountList)) {
		echo $this->form->create($account);
			?>
			<fieldset>
				<legend>Portail bancaire</legend>

				<p>
					Vous pouvez préciser l'identifiant et mot de passe que vous utilisez sur le portail internet de votre banque.<br/>
					Turtl se chargera alors de tenir à jour votre compte automatiquement!
				</p>

				<?php
				echo $this->form->field(
					'bank_id',
					array(
						'type' => 'select',
						'label' => 'Banque',
						'list' => $bankList
					)
				);

				echo $this->form->field(
					'portal.login',
					array(
						'type' => 'text',
						'label' => 'Identifiant'
					)
				);

				echo $this->form->field(
					'portal.password',
					array(
						'type' => 'password',
						'label' => 'Mot de passe'
					)
				);
				?>
			</fieldset>
			<?php
			echo $this->form->submit('Ajouter', array('class' => 'button green'));
		echo $this->form->end();
	}
	else {
		echo $this->form->create($account);
			?>
			<fieldset>
				<legend>Comptes disponibles</legend>
				<?php
				echo $this->form->field(
					'bank_id',
					array(
						'type' => 'select',
						'label' => 'Banque',
						'list' => $bankList,
						'value' => $this->_request->data['bank_id'],
						'readonly' => 'readonly'
					)
				);

				echo $this->form->field(
					'portal.login',
					array(
						'type' => 'text',
						'label' => 'Identifiant',
						'value' => $this->_request->data['portal']['login'],
						'readonly' => 'readonly'
					)
				);

				echo $this->form->field(
					'user_id',
					array(
						'type' => 'hidden'
					)
				);

				echo $this->form->field(
					'portal.password',
					array(
						'type' => 'hidden',
						'value' => $this->_request->data['portal']['password']
					)
				);

				echo $this->form->field(
					'',
					array(
						'type' => 'text',
						'label' => 'Mot de passe',
						'value' => 'Caché',
						'readonly' => 'readonly'
					)
				);
				
				echo $this->form->field(
					'id',
					array(
						'type' => 'select',
						'label' => 'Compte',
						'list' => $accountList
					)
				)
				?>
			</fieldset>
			<?php
			echo $this->form->submit('Ajouter', array('class' => 'button green'));
		echo $this->form->end();
	}
	?>
</section>