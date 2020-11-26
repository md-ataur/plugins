<?php
/*
Plugin Name: Gutenberg Blocks
Plugin URI: 
Description: 
Version: 1.0
Author: Ataur Rahman
Author URI: md-ataur.github.io
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: gutenberg-blocks
*/

use Carbon_Fields\Container;
use Carbon_Fields\Field;
use Carbon_Fields\Block;

/* Carbon file loaded */
function gb_load() {
    require_once( 'vendor/autoload.php' );
    \Carbon_Fields\Carbon_Fields::boot();
}
add_action( 'after_setup_theme', 'gb_load' );


function gb_attach_theme_options() {
    /* Enqueue Styles */
    wp_enqueue_style('gb-style', plugins_url('assets/css/gb-style.css', __FILE__), null, time());    
    wp_enqueue_style('flickity', plugins_url('assets/css/flickity.min.css', __FILE__));    
    wp_enqueue_script( 'gb-accrodion-js', plugins_url('assets/js/gb-accordion.js', __FILE__), ['jquery'], time(), true );
    wp_enqueue_script( 'flickity.pkgd-js', plugins_url('assets/js/flickity.pkgd.min.js', __FILE__), ['jquery'], null, true );

    Block::make( __( 'Text and Image', 'gutenberg-blocks' ) )
        ->set_description( __( 'Block content', 'gutenberg-blocks' ) )        
        ->set_category( 'carbon', __('Carbon Fields', 'gutenberg-blocks') )
        ->set_icon('format-status')
        ->add_fields( array(
            Field::make( 'text', 'heading', __( 'Block Heading', 'gutenberg-blocks' ) ),
            Field::make( 'image', 'image', __( 'Block Image', 'gutenberg-blocks' ) ),
            Field::make( 'rich_text', 'content', __( 'Block Content', 'gutenberg-blocks' ) ),
        ) )
        // Accordion Fields
        ->add_fields( array(
            Field::make( 'complex', 'gb_accordion', __( 'Accordion Fields', 'gutenberg-blocks' ) )
                ->add_fields( array(
                    Field::make( 'text', 'ac_title', __( 'Title', 'gutenberg-blocks' ) ),
                    Field::make( 'textarea', 'ac_body', __( 'Body', 'gutenberg-blocks' ) ),
                ) ),        
            
        ))
        // Gallery Carousel
        ->add_fields( array(
            Field::make( 'media_gallery', 'gb_gallery', __( 'Carousel' ) )
        ))
        ->set_render_callback( function ( $fields, $attributes, $inner_blocks ) {
            ?>
            <div class="block">
                <div class="block__heading">
                    <h1><?php echo esc_html( $fields['heading'] ); ?></h1>
                </div><!-- /.block__heading -->

                <div class="block__image">
                    <?php echo wp_get_attachment_image( $fields['image'], 'large' ); ?>
                </div><!-- /.block__image -->

                <div class="block__content">
                    <?php echo apply_filters( 'the_content', $fields['content'] ); ?>
                </div><!-- /.block__content -->

                <div class="accordion-container">
                    <?php
                    foreach ($fields['gb_accordion'] as $accordion) {
                        ?>
                        <div class="accordion">
                            <h3 class="title"><?php echo esc_html($accordion['ac_title']);?></h3>
                            <div class="body">
                                <?php echo apply_filters('the_content',$accordion['ac_body']);?>
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                </div><!-- Accordion -->
                <div class="carousel-container" data-flickity='{ "cellAlign": "left", "contain": true }'>
                    <?php
                    foreach ($fields['gb_gallery'] as $image) {
                        ?>
                        <div class="carousel-item">
                            <?php echo wp_get_attachment_image($image, 'large');?>                     
                        </div>
                        <?php
                    }
                    ?>
                </div><!-- Carrousel -->
            </div><!-- /.block -->

        <?php
    } );
    
}
add_action( 'carbon_fields_register_fields', 'gb_attach_theme_options' );
