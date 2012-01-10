<?php
//$this->html->script('accounts/all', array('inline' => false));
?>

<div class="popup">
	<header>
		<h3>Ajout d'un compte</h3>
	</header>

	<p>La liste ci-dessous représente les comptes bancaires associés à votre profil Turtl.</p>
	<p>Pour rajouter un nouveau compte bancaire, <a href="/accounts/add">rien de plus simple</a>!</p>
</div>

<?php
if (count($accounts) > 0) {
	foreach ($accounts as $account) {
		echo $this->_render('element', 'account_description', compact('account'));
	}
}
?>