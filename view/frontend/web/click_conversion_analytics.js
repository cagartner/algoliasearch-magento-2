requirejs(['algoliaBundle', 'algoliaAnalytics'], function(algoliaBundle, algoliaAnalytics) {
	var clickedObjectIds = [];
	var convertedObjectIds = [];
	
	algoliaBundle.$(function ($) {
		algoliaAnalytics.init({
			applicationID: algoliaConfig.applicationId,
			apiKey: algoliaConfig.apiKey
		});
		
		$(algoliaConfig.autocomplete.selector).each(function () {
			$(this).on('autocomplete:selected', function (e, suggestion) {
				algoliaAnalytics.click({
					queryID: suggestion.__queryID,
					objectID: suggestion.objectID,
					position: suggestion.__position
				});
			});
		});
		
		// "Click" in list
		$(document).on('click', algoliaConfig.ccAnalytics.ISSelector, function() {
			var $this = $(this);
			
			trackClick($this.data('objectid'), $this.data('position'));
		});
		
		// "Add to cart" in list
		$(document).on('click', '.action.tocart.primary', function() {
			var objectId = $(this).data('objectid');
			
			setTimeout(function() {
				trackConversion(objectId);
			}, 0);
		});
		
		// "Add to cart" in detail
		$(document).on('click', '#product-addtocart-button', function () {
			trackConversion(algoliaConfig.productId);
		});
	});
	
	algolia.registerHook('beforeInstantsearchInit', function (instantsearchOptions) {
		instantsearchOptions.searchParameters['clickAnalytics'] = true;
		
		return instantsearchOptions;
	});
	
	algolia.registerHook('beforeInstantsearchStart', function (search) {
		search.once('render', function() {
			algoliaAnalytics.initSearch({
				getQueryID: function() {
					return search.helper.lastResults && search.helper.lastResults._rawResults[0].queryID
				}
			});
		});
		
		return search;
	});
	
	function trackClick(objectID, position) {
		objectID = objectID.toString();
		
		if (!clickedObjectIds[objectID]) {
			algoliaAnalytics.click({
				objectID: objectID.toString(),
				position: parseInt(position)
			});
			
			clickedObjectIds[objectID] = 1;
			delete convertedObjectIds[objectID];
		}
	}
	
	function trackConversion(objectID) {
		objectID = objectID.toString();
		
		if (!convertedObjectIds[objectID]) {
			algoliaAnalytics.conversion({
				objectID: objectID
			});
			
			convertedObjectIds[objectID] = 1;
			delete clickedObjectIds[objectID];
		}
	}
});