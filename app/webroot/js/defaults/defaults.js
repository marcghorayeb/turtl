var turtl = new Object();

/**
 * Function : dump()
 * Arguments: The data - array,hash(associative array),object
 *    The level - OPTIONAL
 * Returns  : The textual representation of the array.
 * This function was inspired by the print_r function of PHP.
 * This will accept some data as the argument and return a
 * text that will be a more readable version of the
 * array/hash/object that is given.
 * Docs: http://www.openjs.com/scripts/others/dump_function_php_print_r.php
 */
function dump(arr,level) {
	var dumped_text = "";
	if(!level) level = 0;

	//The padding given at the beginning of the line.
	var level_padding = "";
	for(var j=0;j<level+1;j++) level_padding += "    ";

	if(typeof(arr) == 'object') { //Array/Hashes/Objects
		for(var item in arr) {
			var value = arr[item];

			if(typeof(value) == 'object') { //If it is an array,
				dumped_text += level_padding + "'" + item + "' ...\n";
				dumped_text += dump(value,level+1);
			} else {
				dumped_text += level_padding + "'" + item + "' => \"" + value + "\"\n";
			}
		}
	} else { //Stings/Chars/Numbers etc.
		dumped_text = "===>"+arr+"<===("+typeof(arr)+")";
	}
	return dumped_text;
}

function epochToDateTime(epoch, format) {
	if (format === undefined) {
		format = 'DD, d MM, yy';
	}

	return $.datepicker.formatDate(
		format,
		new Date(epoch * 1000)
	);
}

function notifyUser(txt) {
	$('#notifications').fadeIn(300).qtip({
		content: {
			text: txt
		},
		position: {
			my: 'bottom left',
			at: 'top center',
			container: $('#notifications')
		},
		show: {
			event: false,
			delay: 0,
			ready: true
		},
		hide: {
			event: false,
			inactive: 5000
		},
		events: {
			show: function (event, api) {
			},
			hide: function (event, api) {
				$('#notifications').fadeOut(500);
			}
		}
	});
}

jQuery(document).ready(function(){
	$.datepicker.regional['fr'] = {
		closeText: 'Fermer',
		prevText: '&#x3c;Préc',
		nextText: 'Suiv&#x3e;',
		currentText: 'Courant',
		monthNames: ['Janvier','Février','Mars','Avril','Mai','Juin',
		'Juillet','Août','Septembre','Octobre','Novembre','Décembre'],
		monthNamesShort: ['Jan','Fév','Mar','Avr','Mai','Jun',
		'Jul','Aoû','Sep','Oct','Nov','Déc'],
		dayNames: ['Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'],
		dayNamesShort: ['Dim','Lun','Mar','Mer','Jeu','Ven','Sam'],
		dayNamesMin: ['Di','Lu','Ma','Me','Je','Ve','Sa'],
		weekHeader: 'Sm',
		dateFormat: 'dd/mm/yy',
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''
	};

	$.datepicker.setDefaults($.datepicker.regional['fr']);

	$('.flash-message').hide();

	if ($('.flash-message').text() !== '') {
		notifyUser($('.flash-message').text());
	}

	$('time').live('DOMReady', function () {
		epoch = $(this).data('epoch');
		format = $(this).data('format');

		if (epoch !== undefined && format !== "none") {
			$(this).text(epochToDateTime(epoch, format));
		}
	});

	$('time').trigger('DOMReady');

	$('body').bind('TransactionUpdate', function (event) {
		notifyUser('Transaction mise à jour.');
	});

	$.localScroll({duration: 300, hash: true});
});