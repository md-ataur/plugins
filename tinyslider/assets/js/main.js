;(function($){
	$(document).ready(function(){
		var slider = tns({
		    container: '.slider',
		    mode:'gallery',
		    slideBy: 'page',
            autoplayTimeout:4000,
            items: 1,
            autoplay: true,
            autoHeight:false,
            controls:true,            
            nav:false,
            autoplayButtonOutput:false
		});
	});
})(jQuery);