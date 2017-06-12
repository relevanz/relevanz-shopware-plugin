(function($) {

$(function() {
	var workspace = $('#workspace');
	var canvas = new fabric.Canvas('canvas');
	var json = Ext.JSON.decode(document.getElementById('data-set').dataset.json);
	var showConversions = 1,
		showImpressions = 1,
		showClicks = 1,
		showCosts = 1,
		showTurnover = 1;
	var canvasMargin = 50,
		graphHeight = 260,
		tipWidth = 110,
		tipMargin = 10,
		tipBorderRadius = 3,
		graphWidth = workspace.outerWidth() - 2 * canvasMargin,
		canvasWidth = workspace.outerWidth(),
		canvasHeight = graphHeight + 30,
		graphStep,
		graphDiffHeight = 5
		graphTimeCounter = 5,
		initDatepicker = 1;
	var graphObjects = [],
		graphTimeObjects = [];
	var minDate = json.minDate,
		maxDate = json.maxDate;

	var fontSize = 12;

	var impressionsButton = $('#impressions'),
		conversionsButton = $('#conversions'),
		costsButton = $('#costs'),
		clicksButton = $('#clicks');
		turnoverButton = $('#turnover');
	var datepickerFrom = $('#datepicker-from'),
		datepickerTo = $('#datepicker-to');

	handlerCreateGraphCoords = function(polyData, ordersData) {
		var object = graphObjects[polyData];
		if(object.show == 1) {
			var polyCoordsData = object.data,
				polyMax = object.max,
				polyMin = object.min;
				polyHeight = object.height;

			var coords = {};

			if(object.data.length > 1) {
				graphStep = Math.round(graphWidth / (object.data.length - 1));
			} else {
				graphStep = graphWidth;
			}

			coords.x = canvasMargin;
			coords.y = graphHeight;

			var tips = [];
			var pathCoords = 'M ' + coords.x + ' ' + coords.y;

			$(polyCoordsData).each(function(key, value) {
				var coords = {};

				coords.x = canvasMargin + key * graphStep;
				var heightCorrect = 0;
				$(ordersData).each(function(k, v) {
					var diffObject = graphObjects[v];
					if(diffObject.show == 1) {
						heightCorrect = heightCorrect + Math.floor(diffObject.data[key] * diffObject.height) + graphDiffHeight;
					}
				});
				coords.y = graphHeight - Math.floor((value) * polyHeight + heightCorrect);
				pathCoords = pathCoords + ' L ' + coords.x + ' ' + coords.y;

				var tip = new fabric.Circle({
					left: coords.x,
					top: coords.y,
					radius: 3,
					fill: object.fill,
					stroke: object.stroke,
					strokeWidth: 1,
					originX: 'center',
					originY: 'center',
					selectable: false,
				});
				tips.push(tip);
			});

			coords.x = canvasMargin + (polyCoordsData.length - 1) * graphStep;
			coords.y = graphHeight;
			pathCoords = pathCoords + ' L ' + coords.x + ' ' + coords.y;

			var pathCoordsFill = new fabric.Path(pathCoords);
			pathCoordsFill.set({right: 0, bottom: 0});
			pathCoordsFill.set({selectable: false});
			pathCoordsFill.set({fill: object.fill, stroke: object.stroke, opacity: 1});
			canvas.add(pathCoordsFill);

			$(tips).each(function(k, v) {
				canvas.add(v);
			});
		}
	}

	handlerCreateGraphTimeTitle = function() {
		var nextCounter = 0,
			stepLength = graphTimeObjects.length / graphTimeCounter,
			graphLength = graphTimeObjects.length - 1;

		$(graphTimeObjects).each(function(key, value) {
			var tipText = [];
			tipText.push(dataSnippets.date + ': ' + value);
			if(showConversions) {
				tipText.push(dataSnippets.conversions + ': ' + graphObjects.conversions.data[key]);
			}
			if(showImpressions) {
				tipText.push(dataSnippets.impressions + ': ' + graphObjects.impressions.data[key]);
			}
			if(showClicks) {
				tipText.push(dataSnippets.clicks + ': ' + graphObjects.clicks.data[key]);
			}
			if(showCosts) {
				tipText.push(dataSnippets.costs + ': ' + (graphObjects.costs.data[key]).toFixed(2));
			}
			if(showTurnover) {
				tipText.push(dataSnippets.turnover + ': ' + graphObjects.turnover.data[key]);
			}

			var x = canvasMargin + key * graphStep,
				y = graphHeight + 10;

			handlerAddTipLine(x, y, tipText, key);

			if(key > nextCounter * stepLength || key == nextCounter * stepLength) {
				var text = new fabric.Text(value, {left: x, top: y, fontSize: fontSize, originX: 'center', selectable: false});
				canvas.add(text);
				nextCounter = nextCounter + 1;

				handlerAddTimeLine(x, y);
			}
		});

		var x = canvasMargin + (graphTimeObjects.length - 1) * graphStep,
			y = graphHeight + 10;

		var text = new fabric.Text(graphTimeObjects[graphTimeObjects.length - 1], {left: x, top: y, fontSize: fontSize, originX: 'center'});
		canvas.add(text);

		handlerAddTimeLine(x, y);
	}

	handlerAddTipLine = function(x, y, tipText, tipKey) {
		var tipRect = new fabric.Rect({
			left: x - tipBorderRadius,
			top: 5,
			width: tipBorderRadius * 2,
			fill: 'red',
			opacity: 0.0,
			height: graphHeight,
			selectable: false,
			tipText: tipText,
			tipKey: tipKey
		});
		canvas.add(tipRect);
	}

	handlerAddTimeLine = function(x, y) {
		var pathCoords = 'M ' + (x) + ' ' + (y - 7);

		pathCoords = pathCoords + ' L ' + (x + 2) + ' ' + (y - 2);
		pathCoords = pathCoords + ' L ' + (x - 2) + ' ' + (y - 2);

		var pathCoordsFill = new fabric.Path(pathCoords);

		pathCoordsFill.set({right: 0, bottom: 0});
		pathCoordsFill.set({selectable: false});
		pathCoordsFill.set({fill: '#7A92A3', stroke: '#7A92A3', opacity: 1});
		canvas.add(pathCoordsFill);

		var coords = [x, y - 10, x, 10];
		var timeLine = new fabric.Line(coords, {
			strokeDashArray: [10, 5],
			fill: '#AFC5D5',
			stroke: '#AFC5D5',
			strokeWidth: 1,
			selectable: false
		});
		canvas.add(timeLine);
	}

	handlerCreateGraphByOrder = function(order) {
		var inOrder = order;
		$(order).each(function(key, value) {
			inOrder.shift();
			handlerCreateGraphCoords(value, inOrder);
		});
	}

	handlerImpressionsChange = function() {
		if(showImpressions == 1) {
			$(impressionsButton).removeClass('graph-active');
			$(impressionsButton).addClass('graph-inactive');
			showImpressions = 0;
		} else {
			$(impressionsButton).addClass('graph-active');
			$(impressionsButton).removeClass('graph-inactive');
			showImpressions = 1;
		}
		handlerCreateGraph();
	}
	handlerConversionsChange = function() {
		if(showConversions == 1) {
			$(conversionsButton).removeClass('graph-active');
			$(conversionsButton).addClass('graph-inactive');
			showConversions = 0;
		} else {
			$(conversionsButton).addClass('graph-active');
			$(conversionsButton).removeClass('graph-inactive');
			showConversions = 1;
		}
		handlerCreateGraph();
	}
	handlerCostsChange = function() {
		if(showCosts == 1) {
			$(costsButton).removeClass('graph-active');
			$(costsButton).addClass('graph-inactive');
			showCosts = 0;
		} else {
			$(costsButton).addClass('graph-active');
			$(costsButton).removeClass('graph-inactive');
			showCosts = 1;
		}
		handlerCreateGraph();
	}
	handlerClicksChange = function() {
		if(showClicks == 1) {
			$(clicksButton).removeClass('graph-active');
			$(clicksButton).addClass('graph-inactive');
			showClicks = 0;
		} else {
			$(clicksButton).addClass('graph-active');
			$(clicksButton).removeClass('graph-inactive');
			showClicks = 1;
		}
		handlerCreateGraph();
	}
	handlerTurnoverChange = function() {
		if(showTurnover == 1) {
			$(turnoverButton).removeClass('graph-active');
			$(turnoverButton).addClass('graph-inactive');
			showTurnover = 0;
		} else {
			$(turnoverButton).addClass('graph-active');
			$(turnoverButton).removeClass('graph-inactive');
			showTurnover = 1;
		}
		handlerCreateGraph();
	}
	handlerDatapickerChange = function() {
		if(json.csrfToken) {
			$.ajax({
				url: '/backend/Relevanz?file=app&no-cache=1457108763+2+1',
				dataType: 'json',
				type: 'POST',
				data: 'dataFrom=' + $(datepickerFrom).val() + '&dataTo=' + $(datepickerTo).val() + '&dataAction=ajaxGetData',
				headers: {
					'X-CSRF-Token' : Ext.CSRFService.getToken()
				},
				beforeSend: function() {},
				complete: function() {},
				success: function(jsonData) {
					json = jsonData;
					handlerCreateGraph();
					document.getElementById('data-set').dataset.json = JSON.stringify(json);
					dataGrid.gridRefresh();
				},
				error: function() {}
			});
        } else {
			$.ajax({
				url: '/backend/Relevanz?file=app&no-cache=1457108763+2+1',
				dataType: 'json',
				type: 'POST',
				data: 'dataFrom=' + $(datepickerFrom).val() + '&dataTo=' + $(datepickerTo).val() + '&dataAction=ajaxGetData',
				beforeSend: function() {},
				complete: function() {},
				success: function(jsonData) {
					json = jsonData;
					handlerCreateGraph();
					document.getElementById('data-set').dataset.json = JSON.stringify(json);
					dataGrid.gridRefresh();
				},
				error: function() {}
			});
		}
	}

	handlerCreateTip = function(tipObject) {
		var x = tipObject.left - tipWidth / 2;
		if(x < tipMargin) {
			x = tipMargin;
		}
		if(x + tipWidth > canvasWidth) {
			x = canvasWidth - tipMargin - tipWidth;
		}
		var tipRect = new fabric.Rect({
			left: x,
			top: tipMargin,
			width: tipWidth,
			fill: '#D9534F',
			opacity: 0.2,
			rx: tipBorderRadius,
			ry: tipBorderRadius,
			height: tipObject.tipText.length * 15 + 2 * tipBorderRadius,
			tipKeyRemove: tipObject.tipKey,
			selectable: false
		});
		canvas.add(tipRect);

		$(tipObject.tipText).each(function(key, value) {
			var text = new fabric.Text(value, {
				fontSize: 12,
				left: x + tipMargin / 2 + tipBorderRadius,
				top: tipBorderRadius + tipMargin + key * 15,
				tipKeyRemove: tipObject.tipKey,
				selectable: false
			});
			canvas.add(text);
		});

		canvas.renderAll();
	}

	handlerCloseTip = function(tipObject) {
		var objects = [];
		$(canvas._objects).each(function(key, value) {
			if(value.tipKeyRemove == tipObject.tipKey) {
			} else {
				objects.push(value);
			}
		});
		canvas.clear().renderAll();

		$(objects).each(function(key, value) {
			canvas.add(value);
		});
		canvas.renderAll();
	}

	handlerCreateGraph = function() {
		canvas.clear().renderAll();
		if(json.graph) {
			var polyConversionsData = [],
				polyImpressionsData = [],
				polyClicksData = [],
				polyCostsData = [],
				polyTurnoverData = [],
				polyDateData = [];
			var minConversionsData = 99999999,
				minImpressionsData = 99999999,
				minClicksData = 99999999,
				minCostsData = 99999999,
				minTurnoverData = 99999999;
			var maxConversionsData = 0,
				maxImpressionsData = 0,
				maxClicksData = 0,
				maxCostsData = 0,
				maxTurnoverData = 0;

			$(json.graph).each(function(key, value) {
				polyDateData.push(value.dd);

				// Conversions
				polyConversionsData.push(value.conversions);
				if(minConversionsData > value.conversions) {
					minConversionsData = value.conversions;
				}
				if(maxConversionsData < value.conversions) {
					maxConversionsData = value.conversions;
				}

				// Impressions
				polyImpressionsData.push(value.impressions);
				if(minImpressionsData > value.impressions) {
					minImpressionsData = value.impressions;
				}
				if(maxImpressionsData < value.impressions) {
					maxImpressionsData = value.impressions;
				}

				// Clicks
				polyClicksData.push(value.clicks);
				if(minClicksData > value.clicks) {
					minClicksData = value.clicks;
				}
				if(maxClicksData < value.clicks) {
					maxClicksData = value.clicks;
				}

				// Costs
				polyCostsData.push(value.costs);
				if(minCostsData > value.costs) {
					minCostsData = value.costs;
				}
				if(maxCostsData < value.costs) {
					maxCostsData = value.costs;
				}

				// Turnover
				polyTurnoverData.push(value.turnover);
				if(minTurnoverData > value.turnover) {
					minTurnoverData = value.turnover;
				}
				if(maxTurnoverData < value.turnover) {
					maxTurnoverData = value.turnover;
				}
			});

			var graphCount = 0;
			if(showConversions) {
				graphCount = graphCount + 1;
			}
			if(showImpressions) {
				graphCount = graphCount + 1;
			}
			if(showClicks) {
				graphCount = graphCount + 1;
			}
			if(showCosts) {
				graphCount = graphCount + 1;
			}
			if(showTurnover) {
				graphCount = graphCount + 1;
			}

			if(graphCount > 1) {
				var graphPartHeight = Math.floor( 0.9 * graphHeight / graphCount);
			} else {
				var graphPartHeight = graphHeight;
			}

			if(maxConversionsData) {
				var heightModConversions = graphPartHeight / maxConversionsData;
			} else {
				var heightModConversions = graphPartHeight;
			}
			if(maxImpressionsData) {
				var heightModImpressions = graphPartHeight / maxImpressionsData;
			} else {
				var heightModImpressions = graphPartHeight;
			}
			if(maxClicksData) {
				var heightModClicks = graphPartHeight / maxClicksData;
			} else {
				var heightModClicks = graphPartHeight;
			}
			if(maxCostsData) {
				var heightModCosts = graphPartHeight / maxCostsData;
			} else {
				var heightModCosts = graphPartHeight;
			}
			if(maxTurnoverData) {
				var heightModTurnover = graphPartHeight / maxTurnoverData;
			} else {
				var heightModTurnover = graphPartHeight;
			}

			var conversions = {};
			conversions.data = polyConversionsData;
			conversions.min = minConversionsData;
			conversions.max = maxConversionsData;
			conversions.height = heightModConversions;
			conversions.fill = '#D9534F';
			conversions.stroke = '#C7254E';
			conversions.show = showConversions;

			graphObjects['conversions'] = conversions;

			var impressions = {};
			impressions.data = polyImpressionsData;
			impressions.min = minImpressionsData;
			impressions.max = maxImpressionsData;
			impressions.height = heightModImpressions;
			impressions.fill = '#7CB47C';
			impressions.stroke = '#4DA74D';
			impressions.show = showImpressions;

			graphObjects['impressions'] = impressions;

			var clicks = {};
			clicks.data = polyClicksData;
			clicks.min = minClicksData;
			clicks.max = maxClicksData;
			clicks.height = heightModClicks;
			clicks.fill = '#2577B5';
			clicks.stroke = '#0B62A4';
			clicks.show = showClicks;

			graphObjects['clicks'] = clicks;

			var costs = {};
			costs.data = polyCostsData;
			costs.min = minCostsData;
			costs.max = maxCostsData;
			costs.height = heightModCosts;
			costs.fill = '#A7B3BC';
			costs.stroke = '#7A92A3';
			costs.show = showCosts;

			graphObjects['costs'] = costs;

			var turnover = {};
			turnover.data = polyTurnoverData;
			turnover.min = minTurnoverData;
			turnover.max = maxTurnoverData;
			turnover.height = heightModTurnover;
			turnover.fill = '#8892BF';
			turnover.stroke = '#4F5B93';
			turnover.show = showTurnover;

			graphObjects['turnover'] = turnover;

			graphTimeObjects = polyDateData;

			var graphOrder = [
				'impressions',
				'conversions',
				'clicks',
				'costs',
				'turnover'
			];

			handlerCreateGraphByOrder(graphOrder);
			handlerCreateGraphTimeTitle();

			canvas.renderAll();

			$("#datepicker-from").datepicker({
				dateFormat: 'dd-mm-yy',
				defaultDate: setDate,
				onSelect: function(selected) {
					$("#datepicker-to").datepicker("option", "minDate", selected);
					handlerDatapickerChange();
				}
			});
			if(initDatepicker) {
				var setDate = new Date(minDate * 1000);
				$("#datepicker-from").datepicker("setDate", setDate);
			}

			$("#datepicker-to").datepicker({
				dateFormat: 'dd-mm-yy',
				defaultDate: setDate,
				onSelect: function(selected) {
					$("#datepicker-from").datepicker("option", "maxDate", selected)
					handlerDatapickerChange();
				}
			});
			if(initDatepicker) {
				var setDate = new Date(maxDate * 1000);
				$("#datepicker-to").datepicker("setDate", setDate);
			}

			initDatepicker = 0;
		}
	}

	/** init events **/
	canvas.setWidth(canvasWidth);
	canvas.setHeight(canvasHeight);

	handlerCreateGraph();

	canvas.on('mouse:over', function(e) {
		if(e.target.tipText) {
			handlerCreateTip(e.target);
		}
	});
	canvas.on('mouse:out', function(e) {
		if(e.target.tipText) {
			handlerCloseTip(e.target);
		}
	});

	impressionsButton.on('click', handlerImpressionsChange);
	conversionsButton.on('click', handlerConversionsChange);
	costsButton.on('click', handlerCostsChange);
	clicksButton.on('click', handlerClicksChange);
	turnoverButton.on('click', handlerTurnoverChange);
});

})(jQuery);
