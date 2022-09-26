(function ($) {  // Avoid conflicts with other libraries

	'use strict';

	setTimeout(()=> $(".js-useractivity").each(function() {
		$( this ).click(function () {
			const action_href = $(this).data("href");

			$.ajax({
				type: 'post',
				url: action_href,
				datatype: 'json',
				data: {},
				success: function (response) {
					if (response.success && response.title) {
						$('.js-user_activity_report_title').html(response.title);
						$('.js-user_activity_report_content').html(response.content);
						$('.js-user_activity_report_panel').removeClass('d-none');
					}
				}
			});
		});
	}),
	1000);

})(jQuery); // Avoid conflicts with other libraries
