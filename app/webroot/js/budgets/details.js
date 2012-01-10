var charts = new Array(),
	categories = new Array(),
	currentMonth = new Array(),
	history = new Array();

function setupCharts() {
	// *******************************
	// Pie chart for current month
	// *******************************
	charts.push(new Highcharts.Chart({
		chart: {
			renderTo: 'currentMonthChart',
			defaultSeriesType: $('#currentMonthChart').data('chart-type'),
			events: {
				load: function () {
					var series = this.series[0];

					$.each(currentMonth, function (key, val) {
						if (val['monthSum'] !== 0) {
							series.addPoint([val['category_title'], Math.abs(val['monthSum'])], true, false);
						}
					});
				}
			}
		},
		title: {
			text: 'Achats pour le mois courant.'
		},
		tooltip: {
			formatter: function() {
				return '<b>'+ this.point.name +'</b>: '+ this.y +' €';
			}
		},
		plotOptions: {
			pie: {
				allowPointSelect: true,
				cursor: 'pointer',
				dataLabels: {
					enabled: false,
					color: '#000000',
					connectorColor: '#000000',
					formatter: function() {
						return '<b>'+ this.point.name +'</b>: '+ this.y +' €';
					}
				},
				showInLegend: true
			}
		},
	    series: [{
	    	name: 'Mois courant',
	    	data: []
	    }],
	    credits: {
	    	enabled: false
	    }
	}));
	// *******************************

	// *******************************
	// Bar graph for current month
	// *******************************
	charts.push(new Highcharts.Chart({
		chart: {
			renderTo: 'currentLimits',
			defaultSeriesType: $('#currentLimits').data('chart-type'),
			events: {
				load: function () {
					var safeSpend = this.series[0],
						xAxis = this.xAxis[0],
						yAxis = this.yAxis[0],
						min = 0,
						max = 0,
						ext = 0;

					xAxis.setCategories(categories);

					$.each(currentMonth, function (key, val) {
						var a = val['limit'] + val['monthSum'];
						
						safeSpend.addPoint(a, false, false);

						if (a < min) {
							min = a;
						}

						if (a > max) {
							max = a;
						}
					});

					if (Math.abs(min) < Math.abs(max)) {
						ext = Math.abs(max) + 20;
					}
					else {
						ext = Math.abs(min) + 20;
					}

					yAxis.setExtremes(-ext, ext, true, true);
				}
			}
		},
		title: {
			text: 'Dépenses restantes.'
		},
		xAxis: {
			labels: {
				rotation: -45,
				align: 'right',
				style: {
					font: 'normal 10px Verdana, sans-serif'
				}
			}
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
				this.series.name +': '+ this.y+'€';
			}
		},
	    series: [{
	    	name: 'Montant restant',
	    	data: []
	    }],
	    credits: {
	    	enabled: false
	    }
	}));
	// *******************************

	// *******************************
	// Bar graph for current month tags
	// *******************************
	charts.push(new Highcharts.Chart({
		chart: {
			renderTo: 'currentLimitsTags',
			defaultSeriesType: $('#currentLimitsTags').data('chart-type'),
			events: {
				load: function () {
					var spent = this.series[0],
						xAxis = this.xAxis[0],
						newCat = new Array();
					
					$.each(tags, function (key, val) {
						spent.addPoint(Math.abs(val['monthSum']), true, false);
						newCat.push(val['tag_title']);
					});

					xAxis.setCategories(newCat);
				}
			}
		},
		title: {
			text: 'Dépenses restantes.'
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
				this.series.name +': '+ this.y +'€';
			}
		},
	    series: [{
	    	name: 'Montant restant',
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
         renderTo: 'historyMonthChart',
         defaultSeriesType: 'line',
         marginRight: 130,
         marginBottom: 25,
         events: {
				load: function () {
					var series = new Object(),
						xAxis = this.xAxis[0],
						chart = this,
						periods = new Array();

					$.each(history, function (period, data) {
						periods.unshift(period);

						$.each(data['categories'], function (key, category) {
							if (typeof series[category['category_id']] === 'undefined') {
								series[category['category_id']] = {
									data: new Array(),
									name: category['category_title']
								};
							}

							series[category['category_id']]['data'].unshift(Math.abs(category['monthSum']));
						});
					});

					xAxis.setCategories(periods);

					$.each(series, function (category_id, serie) {
						chart.addSeries(serie);
					});
				}
			}
      },
      title: {
         text: 'Historique des dépenses mensuelles',
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
      series: []
   }));

   	// *******************************
	// History graph for tags
	// *******************************
	charts.push(new Highcharts.Chart({
      chart: {
         renderTo: 'historyMonthChartTags',
         defaultSeriesType: 'line',
         marginRight: 130,
         marginBottom: 25,
         events: {
				load: function () {
					var series = new Object(),
						xAxis = this.xAxis[0],
						chart = this,
						periods = new Array();

					$.each(history, function (period, data) {
						periods.unshift(period);

						$.each(data['tags'], function (key, tag) {
							if (typeof series[tag['tag_title']] === 'undefined') {
								series[tag['tag_title']] = {
									data: new Array(),
									name: tag['tag_title']
								};
							}

							series[tag['tag_title']]['data'].unshift(Math.abs(tag['monthSum']));
						});
					});

					xAxis.setCategories(periods);

					$.each(series, function (category_id, serie) {
						chart.addSeries(serie);
					});
				}
			}
      },
      title: {
         text: 'Historique des dépenses mensuelles',
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
      series: []
   }));
}

function getChartData() {
	$.ajax(
		'/budgets/getMonthChart.json', {
			async: false,
			cache: false,
			type: 'GET',
			dataType: 'json',
			data: '',
			error: function (jqXHR, textStatus, errorThrown) {
				alert(errorThrown);
			},
			success: function (data, textStatus, jqXHR) {
				categories = data['categories'];
				tags = data['tags'];
				currentMonth = data['currentMonth'];
			}
		}
	);

	$.ajax(
		'/budgets/getHistory.json', {
			async: false,
			cache: false,
			type: 'GET',
			dataType: 'json',
			data: '',
			error: function (jqXHR, textStatus, errorThrown) {
				alert(errorThrown);
			},
			success: function (data, textStatus, jqXHR) {
				history = data;
			}
		}
	);
}

jQuery(document).ready(function(){
	getChartData();
	setupCharts();
});