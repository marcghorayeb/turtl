/**
 * Turtl theme for Highcharts JS
 */

Highcharts.theme = {
	colors: ["#F37F0E", "#D33030", "#95C45C", "#EACB3B", "#B3884F", "#8C64B4", "#4D75D2", 
		"#CCC", "#5CB9C4", "#CF70A1", "#FFF224", "#47760F"],
	chart: {
		borderColor: '#95C45C',
		borderWidth: 5,
		className: 'turtl-graphs',
		plotBorderColor: false,
		plotBackgroundColor: null,
		plotShadow: false,
		plotBorderWidth: 0
	},
	title: {
		style: {
			color: '#000',
			font: '20px "TitilliumBold", Verdana, sans-serif'
		}
	},
	subtitle: {
		style: { 
			color: '#ccc',
			font: '18px "TitilliumBold", Verdana, sans-serif'
		}
	},
	xAxis: {
		gridLineColor: '#ccc',
		gridLineWidth: 1,
		labels: {
			style: {
				color: '#000'
			}
		},
		lineColor: '#ccc',
		tickColor: '#ccc',
		title: {
			style: {
				color: '#000',
				fontWeight: 'normal',
				fontSize: '16px',
				fontFamily: '"TitilliumBold", Verdana, sans-serif'

			}				
		}
	},
	yAxis: {
		gridLineColor: '#ccc',
		labels: {
			style: {
				color: '#000'
			}
		},
		lineColor: '#ccc',
		minorTickInterval: null,
		tickColor: '#ccc',
		tickWidth: 1,
		title: {
			style: {
				color: '#000',
				fontWeight: 'normal',
				fontSize: '16px',
				fontFamily: '"TitilliumBold", Verdana, sans-serif'
			}				
		}
	},
	legend: {
		itemStyle: {
			font: '16px "TitilliumBold", Verdana, sans-serif',
			color: '#000'
		}
	},
	tooltip: {
		backgroundColor: 'rgba(0, 0, 0, 0.75)',
		style: {
			color: '#FFFFFF',
		}
	},
	toolbar: {
		itemStyle: { 
			color: 'silver'
		}
	},
	plotOptions: {
		line: {
			dataLabels: {
				color: '#CCC'
			},
			marker: {
				lineColor: '#333'
			}
		},
		spline: {
			marker: {
				lineColor: '#333'
			}
		},
		scatter: {
			marker: {
				lineColor: '#333'
			}
		},
		candlestick: {
			lineColor: 'white'
		}
	},		
	legend: {
		itemStyle: {
			color: '#000'
		},
		itemHoverStyle: {
			color: '#ccc'
		},
		itemHiddenStyle: {
			color: '#ccc'
		}
	},
	labels: {
		style: {
			color: '#000'
		}
	},

	exporting: {
		buttons: {
			exportButton: {
				symbolFill: '#55BE3B'
			},
			printButton: {
				symbolFill: '#7797BE'
			}
		}
	}
};

// Apply the theme
var highchartsOptions = Highcharts.setOptions(Highcharts.theme);