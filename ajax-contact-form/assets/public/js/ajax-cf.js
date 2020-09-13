;(function($){
	$(document).ready(function(){
		$('#cfSubmit').on('click', function() {
			$.post(curl.ajaxurl, {
				action: 'ajaxCForm',
				nonce: $('#nonce_field').val(),
				cFName: $('#cFName').val(),
				cLName: $('#cLName').val(),
				cSubject: $('#cSubject').val(),
				cPhone: $('#cPhone').val(),
				cEmail: $('#cEmail').val(),
				cMessage: $('#cMessage').val(),
			}, function(data) {				
				$('#msg').html(data);
				$("#cform")[0].reset();
			});
			return false;
		});
	});

})(jQuery);