function refreshTab(transactions) {
	var details = $('#details'),
		files,
		data;

	if (transactions.length < 2) {
		data = transactions[0];
		
		details.jqotesub('#transactionDetails', data);
		
		var	row = getTransactionRowById(data._id),
			t = (row.position().top + details.height() < $('.transactions').height()) ? 
				row.position().top : $('.transactions').height() - details.height();


		if (details.position().top != t) {
			details.animate({
					top: t
				},
				300,
				'easeInOutQuad'
			);
		}

		if (data.meta.file_id.length > 0) {
			$.getJSON(
				'/transactions/getAssociatedFiles/'+data._id+'.json',
				{},
				function (data, textStatus, jqXHR) {
					$.each(data['files'], function (i, file) {
						details.find('li[data-id="'+file._id+'"] a').html(file.filename);
					});
				},
				'json'
			);
		}
	}
	else {
		data = mergeData(transactions);
		
		details.jqotesub('#multipleTransactionDetails', data).animate(
			{
				top: $('.transactions thead').position().top
			},
			300,
			'easeInOutQuad'
		);
	}
}

function mergeData(transactions) {
	var data = {
			_id: new Array(),
			meta: {
				tags: transactions[0].meta.tags,
				category_id: transactions[0].meta.category_id,
				note: transactions[0].meta.note,
				verified: transactions[0].meta.verified
			}
		};
	
	$.each(transactions, function (i, transaction) {
		data._id.push(transaction._id);

		if (data.meta.category_id != transaction.meta.category_id) {
			data.meta.category_id = '';
		}

		if (data.meta.note != transaction.meta.note) {
			data.meta.note = '';
		}

		if (data.meta.tags.join(',') != transaction.meta.tags.join(',')) {
			data.meta.tags = new Array();
		}

		if (data.meta.verified != transaction.meta.verified) {
			data.meta.verified = '';
		}
	});

	return data;
}

function verifyTransaction() {
	var verif = $('#details input[name="meta.verified"]');
	if (!verif.prop('checked')) {
		verif.prop('checked', true).trigger('change');
	}
}

function clearTab() {
	$('#details').jqotesub('#noTransactionDetails').animate(
		{
			top: $('.transactions thead').position().top
		},
		300,
		'easeInOutQuad'
	);
}

function sendUpdate(updateData, updateUrl) {
	$.ajax({
		url: updateUrl,
		data: updateData,
		type: 'POST',
		enctype:  'multipart/form-data',
		datatType: 'json',
		async: true,
		success: function(data, textStatus, jqXHR){
			var transactions = $('.transactions');

			if ($.isArray(data['transaction'])) {
				$.each(data['transaction'], function (i, transaction) {
					transactions.trigger('TransactionUpdate', transaction);
				});
			}
			else {
				transactions.trigger('TransactionUpdate', data['transaction']);
			}

			if (data['propagationResults'] && $.isArray(data['propagationResults'])) {
				$.each(data['propagationResults'], function (key, value) {
					transactions.trigger('TransactionUpdate', value);
				});
			}
		}
	});
	/*$.post(
		url,
		updateData,
		function (data, textStatus, jqXHR) {
			var transactions = $('.transactions');

			if ($.isArray(data['transaction'])) {
				$.each(data['transaction'], function (i, transaction) {
					transactions.trigger('TransactionUpdate', transaction);
				});
			}
			else {
				transactions.trigger('TransactionUpdate', data['transaction']);
			}

			if (data['propagationResults'] && $.isArray(data['propagationResults'])) {
				$.each(data['propagationResults'], function (key, value) {
					transactions.trigger('TransactionUpdate', value);
				});
			}
		},
		'json'
	);*/
}

jQuery(document).ready(function (){
	clearTab();

	$.jqotec('#transactionDetails');
	$.jqotec('#multipleTransactionDetails');
	$.jqotec('#noTransactionDetails');

	$('#details input[name="meta.verified"]').live('change', function () {
		var details = $('#details'),
			url = details.find('form').attr('action');
			updateData = {
				_id: details.find('input[name="_id"]').val().split(','),
				meta: {
					verified: details.find('input[name="meta.verified"]').prop('checked') ? '1' : ''
				},
				propagateChanges: 'false'
			};

		sendUpdate(updateData, url);
	});

	$('#details input[name="meta.tags"]').live('change', function () {
		var details = $('#details'),
			url = details.find('form').attr('action');
			updateData = {
				_id: details.find('input[name="_id"]').val().split(','),
				meta: {
					tags: details.find('input[name="meta.tags"]').val()
				},
				propagateChanges: 'true'
			};

		sendUpdate(updateData, url);
	});

	$('#details input[name="meta.note"]').live('change', function () {
		var details = $('#details'),
			url = details.find('form').attr('action');
			updateData = {
				_id: details.find('input[name="_id"]').val().split(','),
				meta: {
					note: details.find('input[name="meta.note"]').val()
				},
				propagateChanges: 'true'
			};

		sendUpdate(updateData, url);
	});

	$('#details select[name="meta.category_id"]').live('change', function () {
		var details = $('#details'),
			url = details.find('form').attr('action');
			updateData = {
				_id: details.find('input[name="_id"]').val().split(','),
				meta: {
					category_id: $(this).val()
				},
				propagateChanges: 'true'
			};

		sendUpdate(updateData, url);
	});

	$('#details input[name="fileUpload"]').live('change', function () {
		var details = $('#details'),
			url = details.find('form').attr('action');
			updateData = {
				_id: details.find('input[name="_id"]').val(),
				fileUpload: $(this).val(),
				propagateChanges: 'false'
			};

		sendUpdate(updateData, url);
	});

	$('#details').bind('TransactionVerify', function (event, data) {
		verifyTransaction();
	});

	$('#details').bind('TransactionSelect', function (event, data) {
		refreshTab(getSelectedTransactions());
	});

	$('#details').bind('TransactionDeselect', function (event, data) {
		var transactions = getSelectedTransactions();

		if (transactions.length > 0) {
			refreshTab(transactions);
		}
		else {
			clearTab();
		}
	});

	$(window).on('scroll', function () {
		var pos = ($(window).scrollTop() < $('.transactions thead').position().top) ? $('.transactions thead').position().top : $(window).scrollTop();

		$('#details').stop().animate({
			top: pos + 'px'
		});
	});
});