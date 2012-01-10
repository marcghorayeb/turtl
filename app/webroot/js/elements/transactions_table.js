function setupTransactions() {
	$.getJSON(window.location.pathname+'.json', {}, function (data, textStatus, jqXHR) {
		turtl.transactions = data['transactions'];
		refreshTable();
	});
}

function refreshTable() {
	$('.transactions tbody').jqotesub('#transactionRow', turtl.transactions);
	
	refreshTableFooter();
	refreshTableCaption();
}

function refreshTableFooter() {
	var credit = 0,
		debit = 0;
	
	for (var i=0; i<turtl.transactions.length; i++) {
		credit += turtl.transactions[i].credit;
		debit += turtl.transactions[i].debit;
	}

	$('#totalDebit').text(debit.toFixed(2)+'€');
	$('#totalCredit').text(credit.toFixed(2)+'€');
}

function refreshTableCaption() {
	var count = turtl.transactions.length,
		unverifiedCount = 0;
	
	for (var i=0; i<count; i++) {
		if (turtl.transactions[i].meta.verified == false) {
			unverifiedCount++;
		}
	}

	$('.transactions caption').jqotesub('#transactionsCaption', {count: count, unverifiedCount: unverifiedCount});
}

function updateTransaction(newData) {
	var t;
	
	for (var i=0; i<turtl.transactions.length; i++) {
		t = turtl.transactions[i];
		if (t._id == newData._id) {
			turtl.transactions[i] = $.extend(t, newData);
			break;
		}
	}

	var selected = $('.transactions tbody tr[data-id="'+t._id+'"]').hasClass('selected');
	$('.transactions tbody tr[data-id="'+t._id+'"]').replaceWith($.jqote('#transactionRow', t));
	if (selected) {
		selectTransaction(t._id);
	}

	refreshTableFooter()
	refreshTableCaption();
}

function getTransactionById(id) {
	for (var i=0; i<turtl.transactions.length; i++) {
		if (turtl.transactions[i]._id === id) {
			return turtl.transactions[i];
		}
	};
}

function getTransactionRowById(id) {
	return $('.transactions tr[data-id="'+id+'"]');
}

function getSelectedTransactions() {
	var transactions = new Array();

	$('.transactions tbody tr.selected').each(function (i, row) {
		var id = $(row).data('id');
		transactions.push(getTransactionById(id));
	});

	return transactions;
}

function deselectAllTransactions() {
	$('.transactions tbody tr.selected input[type="checkbox"]').prop('checked', false);
	$('.transactions tbody tr.selected').removeClass('selected');
}

function selectAllTransactions() {
	$('.transactions tbody tr input[type="checkbox"]').prop('checked', true);
	$('.transactions tbody tr').addClass('selected');	
}

function deselectTransaction(id) {
	var row = getTransactionRowById(id);

	row.removeClass('selected');
	row.find('input[type=checkbox]').prop('checked', false);

	if (getSelectedTransactions().length < 1) {
		$('#toggleAllCheckbox').prop('checked', false);
	}
}

function selectTransaction(id) {
	var row = getTransactionRowById(id);

	row.addClass('selected');
	row.find('input[type=checkbox]').prop('checked', true);

	$('#toggleAllCheckbox').prop('checked', true);
}

jQuery(document).ready(function (){
	$.jqotec('#transactionRow');
	$.jqotec('#transactionsCaption');

	$('.transactions').bind('CategoriesReady', function (event) {
		setupTransactions();
	});

	$('.transactions').bind('TransactionDeselect', function (event, data) {
		deselectTransaction(data._id);
	});

	$('.transactions').bind('TransactionSelect', function (event, data) {
		selectTransaction(data._id);
	});

	$('.transactions').bind('TransactionUpdate', function (event, data) {
		updateTransaction(data);
	});

	$('#toggleAllCheckbox').bind('change', function (event) {
		if ($(this).prop('checked')) {
			selectAllTransactions();
			$('#details').trigger('TransactionSelect');
		}
		else {
			deselectAllTransactions();
			$('#details').trigger('TransactionDeselect');
		}
	});

	$('.transactions tbody tr').live('click', function (e) { // prevendefault pour tout sauf checkbox
		var row = $(this),
			rowId = $(this).data('id'),
			selected = row.hasClass('selected'),
			transaction = getTransactionById(rowId);
		
		if (!$(e.target).is('input[type="checkbox"]')) {
			e.preventDefault();
		}

		if (selected) {
			$('.transactions').trigger('TransactionDeselect', transaction);
			$('#details').trigger('TransactionDeselect', transaction);
		}
		else {
			$('.transactions').trigger('TransactionSelect', transaction);
			$('#details').trigger('TransactionSelect', transaction).trigger('TransactionVerify', transaction);
			$('time').trigger('DOMReady');
		}
	});
});