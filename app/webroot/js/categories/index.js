var charts = new Array(),
	currentPeriod = '',
	categories = new Array(),
	currentMonth = new Array(),
	history = new Array();

function setupCharts() {
	// *******************************
	// Pie chart for current month
	// *******************************
	charts.push(new Highcharts.Chart({
		chart: {
			renderTo: 'categoriesMonthBar',
			defaultSeriesType: $('#categoriesMonthBar').data('chart-type'),
			events: {
				load: function () {
					var spent = this.series[0],
						labels = this.xAxis[0],
						newCat = new Array();
						newPoints = new Array();
					
					$.each(categories, function (i, category) {
						$.each(category['snapshots'], function (j, snapshot) {
							if (snapshot['period'] == currentPeriod && snapshot['debit'] < 0) {
								newPoints.push(Math.abs(snapshot['debit']));
								newCat.push(getCategoryById(category['parent']).title);
							}
						});
					});

					labels.setCategories(newCat, false);
					spent.setData(newPoints);
				}
			}
		},
		title: {
			text: 'Dépenses classées par catégories'
		},
		yAxis: {
			title: {
				text: 'Montant (€)'
			}
		},
		legend: false,
		tooltip: {
			formatter: function() {
				return '<b>'+ this.x +'</b><br/>'+
				'Dépenses: '+ this.y +'€';
				//this.series.name +': '+ this.y +'€';
			}
		},
	    series: [{
	    	name: 'Catégories',
	    	data: []
	    }],
	    credits: {
	    	enabled: false
	    }
	}));
	// *******************************

	// *******************************
	// History graph for categories
	// *******************************
	charts.push(new Highcharts.Chart({
      chart: {
         renderTo: 'categoryHistoryPlot',
         defaultSeriesType: $('#categoryHistoryPlot').data('chart-type'),
         marginRight: 130,
         marginBottom: 25,
         events: {
				load: function () {
					var series = new Object(),
						labels = this.xAxis[0],
						chart = this,
						periods = new Array();

					$.each(categories, function (i, category) {
						$.each(category['snapshots'], function (j, snapshot) {
							if ($.inArray(snapshot['period'], periods) == -1) {
								periods.unshift(snapshot['period']);
							}
						});
					});

					periods.sort(function (a, b) {
						return a.localeCompare(b);
					});

					labels.setCategories(periods, false);

					$.each(categories, function (i, category) {
						$.each(category['snapshots'], function (j, snapshot) {
							var pos = $.inArray(snapshot['period'], periods);

							if (typeof series[category['parent']] === 'undefined') {
								series[category['parent']] = {
									data: new Array(),
									name: getCategoryById(category['parent']).title
								};

								$.each(periods, function (i, period) {
									series[category['parent']]['data'].push(0);
								})
							}
							

							series[category['parent']]['data'][pos] = Math.abs(snapshot['debit']);
						});
					});
					
					$.each(series, function (category_id, data) {
						chart.addSeries(data, true, false);
					});
				}
			}
      },
      title: {
         text: 'Historique des dépenses mensuelles classées par catégories',
         x: -20 //center
      },
      xAxis: {},
      yAxis: {
         title: {
            text: 'Montant (€)'
         },
         plotLines: [{
            value: 0,
            width: 2,
            color: '#808080'
         }]
      },
      tooltip: {
         formatter: function() {
                   return '<b>'+ this.series.name +'</b><br/>'+
               this.x +': '+ this.y +'€';
         }
      },
      legend: {
         layout: 'vertical',
         align: 'right',
         verticalAlign: 'top',
         x: -10,
         y: 100,
         borderWidth: 0
      },
      series: [],
      credits: {
      	enabled: false
      }
   }));
   // *******************************
}

function getChartData() {
	$.getJSON(
		window.location.pathname+'.json',
		{},
		function (data, textStatus, jqXHR) {
			categories = data['categories'];
			currentPeriod = data['date'];


			$.each(categories, function (i, category) {
				categories[i]['snapshots'].sort(function (a, b) {
					return a.period.localeCompare(b.period);
					//return (a.period < b.period) ? 1 : (a.period > b.period) ? -1 : 0;
				});
			});

			setupCharts();
		}
	);
}

jQuery(document).ready(function(){
	$('body').bind('CategoriesReady', function (event) {
		getChartData();
	});
});