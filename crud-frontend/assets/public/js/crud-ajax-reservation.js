; (function ($) {
	$(document).ready(function () {
		$('#CrudReserveForm').on('click', function () {
			//alert(objurl.ajaxurl);			
			$.post(objurl.ajaxurl, {
				action: 'crudRSF',
				nf: $('#rsf_nonce_field').val(),
				CRFname: $('#RFname').val(),
				uid: $('#uid').val(),
				CRFemail: $('#RFemail').val(),
				CRFphone: $('#RFphone').val(),
				CRFperson: $('#RFperson').val(),
				CRFdate: $('#RFdate').val(),
				CRFtime: $('#RFtime').val(),
				CRFMessage: $('#RFMessage').val()
			}, function (data) {
				$('#message').html(data);
				$('#Rform')[0].reset();
				$('.usertable').load(location.href + ' .items');

			});
			return false;
		});


	});
})(jQuery);