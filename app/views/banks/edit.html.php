<?= $this->Form->create($bank) ?>
	<section>
		<h2>Cartes</h2>
			<?php
			$i = -1;
			foreach ($bank->templates->carte as $i => $tmpl) {
				$field = 'templates.carte.'.$i;
				echo $this->Form->field(
					$field,
					array(
						'type' => 'input',
						'size' => '90'
					)
				);
			}
			?>

			<?= $this->Form->field('templates.carte.'.(++$i), array('type' => 'input', 'size' => '90')) ?>
	</section>
	<section>
		<h2>Prélèvements</h2>
			<?php
			$i = -1;
			foreach ($bank->templates->prelevement as $i => $tmpl) {
				$field = 'templates.prelevement.'.$i;
				echo $this->Form->field(
					$field,
					array(
						'type' => 'input',
						'size' => '90'
					)
				);
			}
			?>

			<?= $this->Form->field('templates.prelevement.'.(++$i), array('type' => 'input', 'size' => '90')) ?>
	</section>
	<section>
		<h2>Virements</h2>
			<?php
			$i = -1;
			foreach ($bank->templates->virement as $i => $tmpl) {
				$field = 'templates.virement.'.$i;
				echo $this->Form->field(
					$field,
					array(
						'type' => 'input',
						'size' => '90'
					)
				);
			}
			?>

			<?= $this->Form->field('templates.virement.'.(++$i), array('type' => 'input', 'size' => '90')) ?>
	</section>
	<section>
		<h2>Chèques</h2>
			<?php
			$i = -1;
			foreach ($bank->templates->cheque as $i => $tmpl) {
				$field = 'templates.cheque.'.$i;
				echo $this->Form->field(
					$field,
					array(
						'type' => 'input',
						'size' => '90'
					)
				);
			}
			?>

			<?= $this->Form->field('templates.cheque.'.(++$i), array('type' => 'input', 'size' => '90')) ?>
	</section>
	<?= $this->Form->submit('Modifier') ?>
<?= $this->Form->end() ?>