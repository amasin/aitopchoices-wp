/**
 * Frontend ratings and review submission
 */

(function($) {
	'use strict';

	$(document).ready(function() {
		// Handle review form submission
		$('#aitc-review-form').on('submit', function(e) {
			e.preventDefault();

			var $form = $(this);
			var $submitButton = $form.find('button[type="submit"]');
			var $message = $form.find('.aitc-form-message');

			// Get form data
			var formData = {
				action: 'aitc_submit_rating',
				nonce: aitcRatings.nonce,
				post_id: $form.find('input[name="post_id"]').val(),
				rating: $form.find('input[name="rating"]:checked').val(),
				review_title: $form.find('input[name="review_title"]').val(),
				review_text: $form.find('textarea[name="review_text"]').val(),
				website: $form.find('input[name="website"]').val() // Honeypot
			};

			// Validate rating
			if (!formData.rating) {
				$message.removeClass('success').addClass('error').text('Please select a rating.').show();
				return;
			}

			// Disable submit button
			$submitButton.prop('disabled', true).text('Submitting...');
			$message.hide();

			// Submit via AJAX
			$.ajax({
				url: aitcRatings.ajaxurl,
				type: 'POST',
				data: formData,
				success: function(response) {
					if (response.success) {
						$message.removeClass('error').addClass('success').text(response.data.message).show();
						$form[0].reset();

						// Reload page after 2 seconds to show new review
						setTimeout(function() {
							location.reload();
						}, 2000);
					} else {
						$message.removeClass('success').addClass('error').text(response.data.message).show();
					}
				},
				error: function() {
					$message.removeClass('success').addClass('error').text('An error occurred. Please try again.').show();
				},
				complete: function() {
					$submitButton.prop('disabled', false).text('Submit Review');
				}
			});
		});
	});

})(jQuery);
