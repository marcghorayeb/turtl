var categories = new Array(),
	tags = new Array();

/*function getCategoriesCount() {
	return $('#categories tbody tr').length;
}*/

function getTagCount() {
	return tags.length;
}

function tagExists(tag) {
	var l = tags.length,
		i;
	
	for (i = 0; i < l; i++) {
		if (tags[i].tagName == tag) {
			return true;
		}
	}

	return false;
}

function indexTags() {
	var l = tags.length,
		i;

	for (i = 0; i < l; i++) {
		tags[i].tagId = i;
	}
}

jQuery(document).ready(function () {
	$('.toggleCategory').click(function (e) {
		e.preventDefault();

		var btn = $(this),
			tr = btn.closest('tr');
		
		$(tr).find("input").each(function (i) {
			var $this = $(this);

			if ($this.attr('disabled')) {
				$this.removeAttr('disabled');
				btn.text('Desactiver');
			}
			else {
				$this.attr('disabled', 'disabled');
				btn.text('Activer');
			}
		});
	});

	$('#addNewTag').click(function (e) {
		e.preventDefault();

		var btn = $(this),
			data = {
				tagId: getTagCount(),
				tagName: $('#tagName').val(),
				tagLimit: $('#tagLimit').val()
			};
		
		if (data.tagName !== '' && data.tagLimit > 0 && !tagExists(data.tagName)) {
			tags.push(data);
			$('#tags tbody').jqoteapp('#newTagRow', data);
		}
	});

	$('.removeTag').live('click', function (e) {
		e.preventDefault();

		var btn = $(this),
			tr = btn.closest('tr'),
			id = tr.data('id');
		
		tags.splice(id, 1);
		indexTags();
		$('#tags tbody').jqotesub('#newTagRow', tags);
	});
});