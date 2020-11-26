<?php
/*
Plugin Name: WC Product Owner Menu Filter
Plugin URI: http://example.com
Description: WC Product Owner Menu Filter
Version: 1.0
Author: Ataur Rahman
Author URI: md-ataur.github.io
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: wpomf
*/

use Carbon_Fields\Container;
use Carbon_Fields\Field;

/* Carbon file loaded */
function wpomf_load() {
    require_once( 'vendor/autoload.php' );
    \Carbon_Fields\Carbon_Fields::boot();
}
add_action( 'after_setup_theme', 'wpomf_load' );

/* Carbon fields */
function wpomf_attach_theme_options() {
    if ( !class_exists( 'WooCommerce' ) ) {
        return;
    }

    // Get products
    global $wpdb;    
    $products = $wpdb->get_results( " SELECT ID, post_name as name from {$wpdb->prefix}posts WHERE post_status='publish' and post_type='product' ", ARRAY_A);            
    $_products = [0 => 'Always Display'];
    foreach($products as $product){
        $_products[$product['ID']] = $product['name'];
    }
    
    Container::make( 'nav_menu_item', __( 'Menu Settings', 'wpomf' ) )
        ->add_fields( array(
            Field::make( 'select', 'wpomf_product', __( 'Display menu to the prodcut owners ' ))
            ->set_options($_products),        

    ));
}
add_action( 'carbon_fields_register_fields', 'wpomf_attach_theme_options' );


/* Menu show based on the product owner */
add_filter('wp_get_nav_menu_items', function($items){
    if ( !class_exists( 'WooCommerce' ) ) {
        return $items;
    }

    $to_hide = [];
    foreach ($items as $key => $item) {
        /* Get Product id */
        $product_id = carbon_get_nav_menu_item_meta($item->ID, 'wpomf_product');
        /* echo "<pre>";
        print_r("{$key}: {$item->ID}: {$product_id}");
        echo "</pre>"; */        
        
        if (! is_admin()) {
            if ($product_id != 0) {
                /* Current user */
                $current_user = wp_get_current_user();
                if ($current_user) {
                    /* The owner of the product */
                    $is_owner = wc_customer_bought_product($current_user->user_email, $current_user->ID, $product_id);
                    
                    /* If the user is not the owner of the product  */
                    if (! $is_owner) {                    
                        $to_hide[$key] = $item->ID;
                    }
                }
            }
        }
    }
   
    // Menu unset
    foreach ($to_hide as $key => $value) {
        unset($items[$key]);
    }

    return $items;
});

/* 
// Get products info
add_action('wp_footer', function(){
    $products = wc_get_products([
        'posts_per_page' => -1,
        'post_status'   => 'publish'
    ]);
    
    $_products = [];
    foreach($products as $product){
        $_products[] = [$product->name, $product->get_id()];
    }
    
    echo "<pre>";
    print_r($_products);
    echo "</pre>";
}); */