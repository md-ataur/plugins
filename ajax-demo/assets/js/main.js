; (function ($) {
    $(document).ready(function () {
        $(".action-button").on('click', function () {
            let task = $(this).data('task');
            window[task]();
        });
    });

})(jQuery);


function simple_ajax_call() {
    let $ = jQuery;
    let name = prompt("What is your Name?");
    $.post(plugindata.ajax_url, { 'action': 'ajd_simple', 'data': name }, function (data) {
        console.log(data);
    });
}

function unp_ajax_call() {
    let $ = jQuery;
    let name = prompt("What is your Name?");
    $.post(plugindata.ajax_url, { 'action': 'ajd_priv', 'data': name }, function (data) {
        console.log(data);
    });
}

function ajd_localize_script() {
    let $ = jQuery;
    console.log(bucket);
    $.post(plugindata.ajax_url, { 'action': 'ajd_process_user', 'person': bucket }, function (data) {
        console.log(data);
    });
}

function ajd_secure_ajax_call() {
    let $ = jQuery;
    $.post(plugindata.ajax_url, { 'action': 'ajd_protected', 'secret': 'secret code', 'ajd_nonce': plugindata.ajd_nonce }, function (data) {
        console.log(data);
    });
}