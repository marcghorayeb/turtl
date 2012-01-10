<?php
$this->html->script('defaults/jquery.jqote2.min', array('inline' => false));
$this->html->script('budgets/add', array('inline' => false));
?>

<script type="text/x-jqote-template" id="newTagRow">
<![CDATA[
	<tr data-id="<%= this.tagId %>">
		<td style="text-align: center;"><input type="hidden" value="<%= this.tagName %>" name="tags[<%= this.tagId%>][tag_title]"/>#<%= this.tagName %></td>
		<td style="text-align: center;"><input type="hidden" value="<%= this.tagLimit %>" name="tags[<%= this.tagId%>][limit]"/><%= this.tagLimit %>€</td>
		<td style="text-align: center;"><button type="button" class="removeTag">Ne plus suivre</button></td>
	</tr>
]]>
</script>

<tr>
	<td>
</tr>

<?php
echo $this->form->create($budget);
	?>
	<fieldset>
		<legend>Définissez votre budget</legend>
		
		<?php
		echo $this->form->field(
			'title',
			array(
				'placeholder' => 'ex: Budget 2011',
				'required' => 'required',
				'label' => 'Titre'
			)
		);
		?>
		
		<!--
		<p>Vous pouvez définir une période sur laquelle les règles définies pour ce budget seront appliquées:</p>
		<?php
		echo $this->form->field(
			'period.start',
			array(
				'type' => 'date',
				'label' => 'Date de début',
				'value' => ''
			)
		);
		
		echo $this->form->field(
			'period.end',
			array(
				'type' => 'date',
				'label' => 'Date de fin',
				'value' => ''
			)
		);
		?>
		-->
	</fieldset>

	<fieldset>
		<legend>Revenus</legend>

		<p>Ci-dessous, vous trouverez vos sources de revenus. Des valeurs par défaut ont été calculés à partir de votre profil Turtl. Vous pouvez garder ces valeurs par défaut ou bien les éditer.</p>

		<table id="revenues">
			<thead>
				<th>Source</th>
				<th>Occurence</th>
				<th>Montant</th>
			</thead>

			<tbody>
			</tbody>
		</table>
	</fieldset>

	<fieldset>
		<legend>Catégories</legend>
		
		<p>Ci-dessous, les catégories de dépenses qui seront appliquées à vos comptes. Vous pouvez définir manuellement les montants que vous pensez dépenser dans chaque catégorie ou alors laisser les montants par défaut.</p>

		<p>Pour ne pas utiliser une catégorie, appliquez un montant de 0 ou cliquer sur le bouton "Desactiver" .</p>
		
		<table id="categories">
			<thead>
				<tr>
					<th>Catégorie</th>
					<th>Budget alloué (par mois)</th>
					<th style="width: 125px;"></th>
				</tr>
			</thead>

			<tbody>
				<?php
				foreach ($budget->categories as $key => $category) {
					?>
					<tr data-id="<?=$category->category_id?>" data-suggested-amount="<?=$category->suggestedAmount?>" data-enabled="true">
						<td style="text-align:center;">
							<input type="hidden" name="categories[<?=$key?>][category_id]" value="<?=$category->category_id?>"/>
							<input type="hidden" name="categories[<?=$key?>][category_title]" value="<?=$category->category_title?>"/>
							<?=$category->category_title?>
						</td>
						<td style="text-align:center;">
							<input type="hidden" name="categories[<?=$key?>][suggestedAmount]" value="<?=$category->suggestedAmount?>"/>
							<input type="number" name="categories[<?=$key?>][limit]" min="0" value="<?=$category->suggestedAmount?>"/>
						</td>
						<td style="text-align:center;">
							<button type="button" class="toggleCategory">Desactiver</button>
						</td>
					</tr>
					<?php
				}
				?>
			</tbody>

			<!--tfoot>
				<tr>
					<td>Total:</td>
					<td id="categoriesTotal"></td>
				</tr>
			</tfoot-->
		</table>
	</fieldset>

	<fieldset>
		<legend>Tags</legend>
		
		<p>Vous pouvez décider de suivre librement les dépenses associé à des tags que vous définissez ci-dessous.</p>

		<table id="tags">
			<thead>
				<th>Tag</th>
				<th>Budget alloué (par mois)</th>
				<th style="width: 125px;"></th>
			</thead>

			<tbody>
			</tbody>

			<tfoot>
				<tr data-id="">
					<td style="text-align:center;">
						<input type="text" id="tagName"/>
					</td>
					<td style="text-align:center;">
						<input type="number" min="0" id="tagLimit"/>
					</td>
					<td>
						<button type="button" id="addNewTag">Suivre</button>
					</td>
				</tr>
			</tfoot>

			<!--tfoot>
				<tr>
					<td>Total:</td>
					<td id="categoriesTotal"></td>
				</tr>
			</tfoot-->
		</table>
	</fieldset>
	
	<?php
	echo $this->form->submit(
		'Créer budget',
		array('class' => 'button green')
	);
echo $this->form->end();
?>