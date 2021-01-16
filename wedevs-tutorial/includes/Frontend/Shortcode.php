<?php
namespace WeDevs\Tutorial\Frontend;

class Shortcode {

    function __construct() {
        add_shortcode( 'wedevs-tutorial', [$this, 'render_shortcode'] );
    }

    /**
     * Shortcode Callback function
     */
    public function render_shortcode( $atts, $content = '' ) {
        return "Hello from shortcode";
    }
}
