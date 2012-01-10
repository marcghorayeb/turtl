<?php
$this->Html->style('pages/home', array('inline' => false));
?>

<article id="home">
	<!--header>
		<?= $this->Html->image(
			'logo.png',
			array(
				'title' => 'Turtl',
				'alt' => 'Turtl'
			)
		); ?>
		<h1>L'outil qui manquait à votre compte en banque!</h1>
	</header-->

	<?= $this->_render('element', 'login_form') ?>

	<section id="vision">
		<h2>Notre vision</h2>

		<p>Les banques sont lentes à intégrer des fonctionnalités utiles à leurs portails internet.</p>
		
		<p>Les notifications par mail ou la classification de transactions sont autant de fonctionnalités que nous sommes en mesure d'attendre d'un service bancaire web 2.0. qui ne sont toujours pas universelles.</p>

		<p>Nous souhaitons proposer un service autour du relevé bancaire permettant à chacun de brancher ses comptes courants et obtenir des informations claires quant aux dépenses réalisées.</p>
	</section>

	<section id="projet">
		<h2>Notre projet</h2>

		<p>Turtl est né de la vision d'un étudiant tournant la page des études pour entrer dans la vie active. Avec celle-ci est venue la nécessité de mieux gérer ses dépenses mensuelles.</p>

		<p>Turtl a été programmé pour un usage personnel.</p>

		<p>Le service ici en démonstration est disponible à tout un chacun de façon gratuite. Néanmoins, aucune garanti quant à son utilisation, les résultats, et la sécurité des données n'est faite. L'auteur du site Internet ne pourra être tenu comme responsable de dysfonctionnement ou de perte de données. Il s'agit d'un projet en constante évolution pour un usage personnel. Vous pouvez participer à son évolution en obtenant le code source sur GitHub. Toute contribution est accepté et encouragé!</p>
	</section>

	<!--section id="mailing">
		<h2>Mise en service</h2>

		<p>Turtl est un projet en cours. Sa mise en service est prévue pour 2012.</p>
	</section-->

	<!--section id="presse">
		<h2>Ils parlent de nous</h2>
	</section-->

	<!--section id="team">
		<h2>L'équipe</h2>
	</section-->
</article>