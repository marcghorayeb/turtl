<?php
$this->Html->style('elements/transactionTab', array('inline' => false));

$this->Html->script('defaults/jquery.jqote2.min', array('inline' => false));
$this->Html->script('elements/transaction_tab', array('inline' => false));
?>

<script type="javascript/x-jqote-template" id="noTransactionDetails">
<![CDATA[
<article class="transaction">
	<header>
		<h3>Aucune transaction sélectionnée</h3>
	</header>
		
	<p>Sélectionnez une ou plusieurs transactions sur la gauche pour avoir plus d'informations.</p>
</article>
]]>
</script>

<script type="javascript/x-jqote-template" id="multipleTransactionDetails">
<![CDATA[
<article class="transaction">
	<form action="/transactions/multipleEdit.json">
		<h2><%= this._id.length %> transactions sélectionnées</h2>
		<p>Les modifications que vous effectuerez ci-dessous seront appliquées à toutes les transactions sél
		ectionnées.</p>

		<input type="hidden" name="_id" value="<%= this._id.join(',') %>"/>

		<section>
			<h3>Transactions vérifiées</h3>

			<% if (this.meta.verified) { %>
				<input type="checkbox" name="meta.verified" checked="checked" value="1"/>
			<% } else { %>
				<input type="checkbox" name="meta.verified" value="1"/>
			<% } %>
		</section>

		<section>
			<h3>Catégorie</h3>

			<select name="meta.category_id">
				<% if (!this.meta.category_id) { %>
					<option value="" selected="selected">Aucune</option>
				<% } else { %>
					<option value="">Aucune</option>
				<% } %>

				<% for (var i=0; i<turtl.categories.length; i++) { %>
					<% if (turtl.categories[i]._id === this.meta.category_id) { %>
						<option value="<%= turtl.categories[i]._id%>" selected="selected"><%= turtl.categories[i].title %></option>
					<% } else { %>
						<option value="<%= turtl.categories[i]._id%>"><%= turtl.categories[i].title %></option>
					<% } %>
				<% } %>
			</select>
		</section>

		<!--section>
			<h3>Tags</h3>

			<input type="text" name="meta.tags" value="<%= this.meta.tags.join(',') %>"/>
		</section-->

		<section>
			<h3>Note</h3>

			<% if (this.meta.note) { %>
				<input type="text" name="meta.note" value="<%= this.meta.note %>" placeholder="Note personelle." />
			<% } else { %>
				<input type="text" name="meta.note" value="" placeholder="Note personelle." />
			<% } %>
		</section>
	</form>
</article>
]]>
</script>

<script type="javascript/x-jqote-template" id="transactionDetails">
<![CDATA[
<article class="transaction">
	<form action="/transactions/edit/<%= this._id %>.json">
		<input type="hidden" name="_id" value="<%= this._id %>"/>

		<section>
			<h3>Transaction vérifiée</h3>

			<% if (this.meta.verified) { %>
				<input type="checkbox" name="meta.verified" checked="checked" value="1"/>
			<% } else { %>
				<input type="checkbox" name="meta.verified" value="1"/>
			<% } %>
		</section>

		<section>
			<h3>Catégorie</h3>

			<select name="meta.category_id">
				<% if (!this.meta.category_id) { %>
					<option value="" selected="selected">Aucune</option>
				<% } else { %>
					<option value="">Aucune</option>
				<% } %>

				<% for (var i=0; i<turtl.categories.length; i++) { %>
					<% if (turtl.categories[i]._id === this.meta.category_id) { %>
						<option value="<%= turtl.categories[i]._id%>" selected="selected"><%= turtl.categories[i].title %></option>
					<% } else { %>
						<option value="<%= turtl.categories[i]._id%>"><%= turtl.categories[i].title %></option>
					<% } %>
				<% } %>
			</select>
		</section>

		<!--section>
			<h3>Tags</h3>

			<input type="text" name="meta.tags" value="<%= this.meta.tags.join(',') %>"/>
		</section-->

		<section>
			<h3>Note</h3>

			<% if (this.meta.note) { %>
				<input type="text" name="meta.note" value="<%= this.meta.note %>" placeholder="Note personelle." />
			<% } else { %>
				<input type="text" name="meta.note" value="" placeholder="Note personelle." />
			<% } %>
		</section>

		<section>
			<h3>Fichiers associés</h3>

			<ul>
			<% for (var i=0; i<this.meta.file_id.length; i++) { %>
				<li data-id="<%= this.meta.file_id[i] %>">
					<a href="/files/view/<%= this.meta.file_id[i] %>"><%= this.meta.file_id[i] %></a>
				</li>
			<% } %>
			</ul>

			<a href="/transactions/edit/<%= this._id %>">Nouveau fichier</a>
		</section>
	</form>
</article>
]]>
</script>

<section id="details">
</section>