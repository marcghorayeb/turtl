<?php
$this->Html->style('elements/transactionsTable', array('inline' => false));
$this->Html->script('elements/transactions_table', array('inline' => false));
?>

<script type="javascript/x-jqote-template" id="transactionRow">
<![CDATA[
<tr data-id="<%= this._id %>" data-note="<%= this.meta.note %>" data-verified="<%= this.meta.verified %>" data-tags="<%= this.meta.tags.join(',') %>">
	<td class="actions"><input type="checkbox"/></td>
	<td class="date">
		<time datetime="<%= epochToDateTime(this.date, 'yy-m-d') %>" data-epoch="<%= this.date %>" data-format="none">	
				<div class="m"><%= epochToDateTime(this.date, 'M') %></div>
				<div class="d"><%= epochToDateTime(this.date, 'd')%></div>
		</time>	
	</td>
	<td class="description">
		<%
		var verifiedClass = this.meta.verified ? '' : ' unverified',
			desc = (this.meta.to == '') ? this.description : this.meta.to;
		%>
		<div class="description <%= verifiedClass %>"><a href="<%= '/transactions/view/'+this._id %>" title="<%= this.description %>"><%= desc %></a></div>

		<div class="meta">
			<% for (var i=0; i<this.meta.tags.length; i++) { %>
				<!--span class="tag"><%= '#'+this.meta.tags[i] %></span-->
			<% } %>

			<span class="note"><%= this.meta.note %></span>
		</div>
	</td>
	<td class="category">
		<% if (this.meta.category_id) {
			var cat = getCategoryById(this.meta.category_id); %>
			<span class="category" data-id="<%= this.meta.category_id %>">
				<%= cat.title %>
			</span>
		<% } %>
	</td>
	<td class="amount number">
		<% if (this.credit) { %>
			<span class="credit"><%= this.credit+'€' %></span>
		<% } %>
		<% if (this.debit) { %>
			<span class="debit"><%= this.debit+'€' %></span>
		<% } %>
	</td>
</tr>
]]>
</script>

<script type="javascript/x-jqote-template" id="transactionsCaption">
<![CDATA[
<p><%= this.count %> transaction(s), dont <span id="unverifiedCount"><%= this.unverifiedCount %></span> non vérifiée(s).</p>
]]>
</script>

<table class="transactions">
	<caption>
		<?php
		$count = count($transactions);
		echo '<p>'.$count.' transactions, dont <span id="unverifiedCount">'.$unverifiedCount.'</span> non vérifiée'.(($unverifiedCount > 1) ? 's':'').'.</p>';
		?>
	</caption>

	<thead>
		<tr>
			<th class="actions"><input type="checkbox" id="toggleAllCheckbox"/></th>
			<th class="date">Date</th>
			<th class="description">Description</th>
			<th class="category"></th>
			<th class="amount alignRight">Montant</th>
		</tr>
	</thead>
	
	<tbody>
		<?php
		$credit = (float) 0;
		$debit = (float) 0;
		
		if ($preRenderTBody) {
			$odd = false;
			$dateOfLastRow = 0;
			foreach ($transactions as $i => $transaction) {
				//Change row background color only if last row has a different date
				$dateOfRow = date('Y-m-d', $transaction->date->sec);
				$odd = ($dateOfLastRow != $dateOfRow) ? !$odd : $odd;
				$dateOfLastRow = $dateOfRow;
				?>
				<tr <?= $odd ? 'class="odd"' : ''; ?> data-id="<?= $transaction->_id ?>" data-note="<?= $transaction->meta->note ?>" data-verified="<?= $transaction->meta->verified ?>">
					<td class="actions"></td>
					<td class="date">
						<time datetime="<?= $dateOfRow; ?>" data-epoch="<?= $transaction->date->sec?>" data-format="none">	
								<div class="m"><?= date('M', $transaction->date->sec) ?></div>
								<div class="d"><?= date('d', $transaction->date->sec) ?></div>
						</time>
					</td>
					<td class="description">
						<?php
						foreach ($transaction->meta->tags as $tag) {
							?>
							<span class="tag"><?= $this->User->tagTitle($tag) ?></span>
							<?php
						}
						?>
						<span class="description">
							<?= $this->Html->link(
								$transaction->description,
								array(
									'controller' => 'transactions',
									'action' => 'view',
									'args' => (string) $transaction->_id
								)
							);?>
						</span>
					</td>
					<td class="category">
						<?php
						if (!empty($transaction->meta->category_id)) {
							?>
							<span class="category" data-id="<?= $transaction->meta->category_id ?>"><?= $this->Category->title($categories, $transaction->meta->category_id) ?></span>
							<?php
						}
						?>
					</td>
					<td class="amount number">
						<span class="credit">
							<?php
							$credit += $transaction->credit;
							echo $this->Bank->credit($transaction);
							?>
						</span>
						<span class="debit">
							<?php
							$debit += $transaction->debit;
							echo $this->Bank->debit($transaction);
							?>
						</span>
					</td>
				</tr>
				<?php
			}
		}
		?>
	</tbody>
	
	<tfoot>
		<tr>
			<td colspan="3" class="alignRight">TOTAL</td>
			<td id="totalCredit"><?= $credit ?></td>
			<td id="totalDebit"><?= $debit ?></td>
		</tr>
	</tfoot>
</table>