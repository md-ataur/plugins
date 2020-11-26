;(function($){
    $(document).ready(function () {
        $('.accordion-container .body').hide();
        $('.accordion-container .body').first().show();
        $('.accordion-container .title').on('click', function(){
            $(this).parents('.accordion-container').find('.body').hide();
            $(this).next('.body').show('slow');
        });
    });
})(jQuery);