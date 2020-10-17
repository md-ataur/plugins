;(function($){
    $(document).ready(function(){
        PlainModal.closeByEscKey = false;
        PlainModal.closeByOverlay = false;
        var modalels = document.querySelectorAll(".modal-content");        
        for (i = 0; i < modalels.length; i++) {
            var content = modalels[i];
            modals = new PlainModal(content);            
            //console.log(modals);
            modals.closeButton = content.querySelector('.close-button');            
            modals.open();            
        }
    });
})(jQuery);