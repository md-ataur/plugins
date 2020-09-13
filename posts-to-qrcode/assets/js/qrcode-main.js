;(function($){
	$(document).ready(function(){		
		
		$('#toggle1').minitoggle();

		var current_value = $("#qrcode_switcher").val();
		if(current_value == 1){
			$("#toggle1 .minitoggle").addClass("active");
			$("#toggle1 .toggle-handle").attr("style","transform: translate3d(33px, 0px, 0px)");
		}
		
		$('#toggle1').on("toggle", function(e){
            if (e.isActive){
                $("#qrcode_switcher").val(1);
            }else{
                $("#qrcode_switcher").val(0);
            }
           
        });
	});
})(jQuery);