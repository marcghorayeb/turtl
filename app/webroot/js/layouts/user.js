var turtl = {
	transactions: new Array(),
	categories: new Array(),
	budget: new Array()
}

function setupCategories() {
	$.getJSON('/categories/getCategories.json', {}, function (data, textStatus, jqXHR) {
		turtl.categories = data['categories'];
		
		$('body').trigger('CategoriesReady');
		$('.transactions').trigger('CategoriesReady');
	});
}

function getCategories() {
	return turtl.categories;
}

function getCategoryById(id) {
	for (var i=0; i<turtl.categories.length; i++) {
		if (turtl.categories[i]._id === id) {
			return turtl.categories[i];
		}
	};
}

function getCategoryByTitle(id) {
	for (var i=0; i<turtl.categories.length; i++) {
		if (turtl.categories[i]._id === id) {
			return turtl.categories[i];
		}
	}
}

jQuery(document).ready(function (){
	setupCategories();
});