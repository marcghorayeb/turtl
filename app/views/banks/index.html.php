<ul>
<?php
foreach ($banks as $bank) {
	echo '<li>'.$bank->title.'</li>';
	echo '<li>'.$this->Html->link('Editer', array('controller' => 'banks', 'action' => 'edit', 'args' => (string) $bank->_id)).'</li>';
}
?>
</ul>