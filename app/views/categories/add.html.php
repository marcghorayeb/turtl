<?php
$this->html->script('categories/add', array('inline' => false));
?>

<div class="baseContainer">
	<?php
	echo $this->form->create($category);
		?>
		<fieldset>
			<legend>Définissez une nouvelle catégorie</legend>
			
			<p>Ci-dessous, vous pouvez définir manuellement pour classer vos achats. Si les categories prédéfinies par Turtlr sont insuffisantes, nous vous invitons à créer vos propres catégories.</p>
			
			<button id="addCategory" class="medium button blue">Ajouter une catégorie</button>
			
			<ul id="categories">
			</ul>
		</fieldset>
		<?php
		echo $this->form->submit(
			'Créer',
			array('class' => 'button blue')
		);
	echo $this->form->end();
	?>
</div>