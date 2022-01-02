/*jslint browser: true, plusplus: true */
(function ($, window, document) {
	'use strict';
	// execute when the DOM is ready
	$(document).ready(function () {
		$('#enhance').on('click', function (event) {
			
			event.preventDefault();

            wp.ajax.post( "enhance_action", { qid: "Q47102", post_id: "1950" } ).done(function(response) {
                console.log(response);
            });
            
		});
	});
})(jQuery, window, document);
