(function ($) {
    
    "use strict";
    
    $(document).ready(function () {
        $(document).on('widget-updated',function(event,widget){
            var widget_id = $(widget).attr('id');            
            if(widget_id.indexOf('advertisement_widget')!=-1){
                prefetch();
            }
        });

        /**
         * Event delegation
         * ----------------
         * The idea of event delegation is simple. Instead of attaching the event listeners directly to the buttons, you delegate listening to the parent <div id="buttons"> . When a button is clicked, the listener of the parent element catches the bubbling event
         */
        $("body").off("click",".widgetuploader"); // Event listener off for the first time
        $("body").on("click",".widgetuploader", function () {            
            var that = this; // Reference of the ".widgetuploader"
            
            var file_frame = wp.media.frames.file_frame = wp.media({
                frame: 'post',
                state: 'insert',
                multiple: false
            }); // Media popup on

            // "Insert" event listener
            file_frame.on('insert', function () { 
                var data = file_frame.state().get('selection');               
                var jdata = data.toJSON();
                var selected_ids = _.pluck(jdata, "id");
                var container = $(that).siblings("p.imgpreview");

                if (selected_ids.length > 0) {
                    $(that).css("marginTop", "10px");
                    $(that).val("Change Image");
                }
                $(that).prev('input').val(selected_ids.join(","));
                $(that).prev('input').trigger('change');
                container.html("");

                data.map(function (attachment) {
                    if (attachment.attributes.subtype == "png" || attachment.attributes.subtype == "jpeg" || attachment.attributes.subtype == "jpg") {
                        try {
                            //console.log(attachment.attributes.sizes);
                            container.append("<img src='" + attachment.attributes.sizes.thumbnail.url + "'/>");
                        } catch (e) {
                            
                        }
                    }
                });
            });

            // In media frame image file selected
            file_frame.on('open', function () {
                var selection = file_frame.state().get('selection');
                var ats = $(that).prev("input").val().split(",");

                for (var i = 0; i < ats.length; i++) {
                    if (ats[i] > 0)
                        selection.add(wp.media.attachment(ats[i]));
                }
            });

            file_frame.open();
        });

        // Image preview
        function prefetch(){
            $(".imgph").each(function(){
                var attid = $(this).val();
                var container = $(this).prev();
                container.html("");
                if(attid){
                    $(this).next().val("Change Image");
                    var attachment = new wp.media.model.Attachment.get(attid);
                    attachment.fetch({success: function (att) {
                        container.append("<img src='" + att.attributes.sizes.thumbnail.url + "'/>");
                    }});
                }
            });
        }

        // Customizer 
        if(wp.customize !== undefined){
            $(".customize-control").on("expand",function(e){
                var widget_id = $(this).attr('id');
                if(widget_id.indexOf('advertisement_widget')!==-1){
                    prefetch();
                }
            });
        }

        prefetch();
    });
})(jQuery);