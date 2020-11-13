;(function($){
    $(document).ready(function () {       
        $('body').on('click', '#noticeninja .notice-dismiss', function(){
            console.log("Fired");
            setCookie('notice','1',20); // you can set time 
        });
    });
})(jQuery);

function setCookie(cookiName, cookiValue, expiryInSeconds) {
    var expiry = new Date();
    expiry.setTime(expiry.getTime() + 1000 * expiryInSeconds);
    document.cookie = cookiName + '=' + cookiValue + ';expires=' + expiry.toGMTString() + "; path=/";
}