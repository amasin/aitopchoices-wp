/**
 * Admin JavaScript for AI Tools plugin
 */

(function($) {
	'use strict';

	$(document).ready(function() {
		// Toggle price type fields
		$('#price_type').on('change', function() {
			var priceType = $(this).val();

			$('.aitc-price-single, .aitc-price-range, .aitc-price-tiers').hide();

			if (priceType === 'single') {
				$('.aitc-price-single').show();
			} else if (priceType === 'range') {
				$('.aitc-price-range').show();
			} else if (priceType === 'tiers') {
				$('.aitc-price-tiers').show();
			}
		});

		// Trigger on page load
		$('#price_type').trigger('change');
	});

})(jQuery);
