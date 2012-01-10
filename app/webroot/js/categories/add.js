function getCategoriesCount() {
	return $('#categories li').length;
}

function createCategoryRow(id) {
	var listItem = $('<li/>').data('id', id);
	
	var categoryTitle = $('<label/>', {
		text: 'Titre'
	}).after($('<input/>', {
		type: 'text',
		name: 'categories['+id+'][title]',
		value: '',
		placeholder: 'ex: Loisirs'
	}));
	
	var categoryDelete = $('<button/>', {
		text: 'Supprimer',
		class: 'medium button red',
		click: function (e) {
			var li = $(this).parents('li');
			
			$(li).nextAll('li').map(function () {
				var newId = $(this).data('id') - 1;
				
				$(this).data('id', newId);
				$(this).find('input[type=text]').attr('name', 'categories['+newId+'][title]');
			});
			
			$(li).remove();
			e.preventDefault();
		}
	});
	
	return $(listItem).append(categoryTitle, categoryDelete);
}

jQuery(document).ready(function(){
	$('#addCategory').click(function (e) {
		var id = getCategoriesCount();
		var item = createCategoryRow(id);
		
		$('#categories').append(item);
		e.preventDefault();
	});
});