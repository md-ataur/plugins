;(function($){
	$(document).ready(function(){		
		$('#reserveForm').on('click', function(){
			$.post(url.ajaxUrl, {
				action: 'ajaxRSF',
				rn: $('#rsf_nonce_field').val(),
				RFname: $('#RFname').val(),
				RFemail: $('#RFemail').val(),
				RFphone: $('#RFphone').val(),
				RFperson: $('#RFperson').val(),
				RFdate: $('#RFdate').val(),
				RFtime: $('#RFtime').val(),
				RFMessage: $('#RFMessage').val()
			}, function(data) {
				$('#msg').html(data);
				console.log(data);
				$('#Rform')[0].reset();
			});			
			return false;
		});
	});
})(jQuery);