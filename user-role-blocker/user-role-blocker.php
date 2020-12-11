<?php
/*
Plugin Name: User role blocker
Plugin URI: http://example.com
Description: User role blocker
Version: 1.0
Author: Ataur Rahman
Author URI: md-ataur.github.io
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: user-role-blocker
*/

/* Add Role and rewrite_rule */
add_action('init', function(){
    add_role( 'urb_user_blocked', __( 'Blocked', 'user-role-blocker' ), array( 'blocked' => true ) );    
    add_rewrite_rule( 'blocked/?$', 'index.php?blocked=1', 'top' );    
});

/* Register query string */
add_filter('query_vars', function($query_string){
    $query_string[] = 'blocked';
    return $query_string;
});

/* If user is blocked redirect to blocked page */
add_action('template_redirect', function(){
    /**
     * intval() Return integer value of a variable
     * get_query_var() Get the query var value
     */
    $is_blocked = intval(get_query_var('blocked'));

    if ($is_blocked || current_user_can('blocked')) {
        ?>
        <!DOCTYPE html>
        <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title><?php _e( 'Blocked User', 'user-role-blocker' );?></title>
                <?php wp_head();?>
            </head>
            <body>
                <h2 style="text-align: center"><?php _e( 'You are blocked', 'user-role-blocker' );?></h2>
                <?php wp_footer();?>
            </body>
        </html>
        <?php
        die();
    }
});

/* If user is blocked don't' get to access to the Admin */
add_action('init', function(){
    /* $user = wp_get_current_user();
    if ($user->has_cap('blocked')) {
        # code...
    } */

    if (is_admin() && current_user_can('blocked') ) {
        wp_redirect( get_home_url() . '/blocked' );
        die();
    }
});
