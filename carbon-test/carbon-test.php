<?php
/*
Plugin Name: Carbon Test
Plugin URI: http://example.com
Description: 
Version: 1.0
Author: Ataur Rahman
Author URI: md-ataur.github.io
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: crbtest
*/

/**
 * First of all you need to download carbon files by composer
 */

use Carbon_Fields\Container;
use Carbon_Fields\Field;

/* Carbon file loaded */
function crbtest_load() {
    require_once( 'vendor/autoload.php' );
    \Carbon_Fields\Carbon_Fields::boot();
}
add_action( 'after_setup_theme', 'crbtest_load' );


/* Meta field add */
function crbtest_attach_theme_options() {
    Container::make( 'theme_options', __( 'Carbon Metabox', 'crbtest' ) )        
        ->add_fields( array(
            // Text field
            Field::make( 'text', 'crbtest_author', __( 'Author Name', 'crbtest' ) ),
            // Image field
            Field::make( 'image', 'crbtest_image', __( 'Image' , 'crbtest' ) ),
            // Gallery field
            Field::make( 'media_gallery', 'crbtest_media_gallery', __( 'Media Gallery' ) ),
            // Multiselect
            Field::make( 'multiselect', 'crbtest_available_colors', __( 'Available Colors' ) )
                ->add_options( array(
                    'red' => 'Red',
                    'green' => 'Green',
                    'blue' => 'Blue',
                ) ),            
            // Repeater Fields
            Field::make( 'complex', 'crbtest_repeater', __( 'Repeater Fields' ) )
                ->add_fields( array(
                    Field::make( 'text', 'title', __( 'Slide Title' ) ),
                    Field::make( 'image', 'photo', __( 'Slide Photo' ) ),
                ) ),           
                     
            // Checkbox    
            Field::make( 'checkbox', 'crbtest_show_content', __( 'Show Content', 'crbtest' ) )
            ->set_option_value( 'yes' ),
        ) )  
        // Tabs
        ->add_tab( __( 'Profile' ), array(
            Field::make( 'text', 'crbtest_first_name', __( 'First Name' ) ),
            Field::make( 'text', 'crbtest_last_name', __( 'Last Name' ) ),
            Field::make( 'text', 'crbtest_position', __( 'Position' ) ),
        ) )
        ->add_tab( __( 'Notification' ), array(
            Field::make( 'text', 'crbtest_email', __( 'Notification Email' ) )->set_width('50'),
            Field::make( 'text', 'crbtest_phone', __( 'Phone Number' ) )->set_width('50'),
        ) );

    /* Conditional type */
    Container::make( 'post_meta', __( 'Homepage Settings' ) )
        ->where( 'post_type', '=', 'page' )
        ->where( 'post_template', '=', 'templates/homepage.php' )
        ->set_context('side')
        ->add_fields( array( 
            Field::make( 'text', 'crbtest_homepage', __( 'Image Title', 'crbtest' ) ),
        ) );
}
add_action( 'carbon_fields_register_fields', 'crbtest_attach_theme_options' );



/**
 * How to show data
 * ================
 */
 
/*
// If this is post_meta
-----------------------
echo "Author: " .carbon_get_the_post_meta( 'crbtest_author' );			
echo "Author: " .carbon_get_post_meta('2','crbtest_author') ."</br>"; // Specific data by id
    
// If this is theme_options
---------------------------
if ( function_exists( 'carbon_field_exists' ) ) {
    if(carbon_get_theme_option('crbtest_show_content') == '1'){
        echo "Author: " .carbon_get_theme_option('crbtest_author') ."</br>";	
        // Image
        $img_source = wp_get_attachment_image_src(carbon_get_theme_option('crbtest_image'), 'thumbnail');
        echo "<img width='150' height='150' src='".esc_url($img_source[0])."'>";
        // Gallery	
        echo "<ul>";				
        foreach (carbon_get_theme_option('crbtest_media_gallery') as $image) {						
            echo "<li>".wp_get_attachment_image($image)."</li>";
        }	
        echo "</ul>";
        // Multi Color
        foreach (carbon_get_theme_option('crbtest_available_colors') as $color) {						
            echo strtoupper($color)."<br>";
        }
        // Repeater field
        foreach (carbon_get_theme_option('crbtest_repeater') as $data) {						
            echo $data['title']."<br>";
            echo wp_get_attachment_image($data['photo']);
        }
    }
}*/