<?php
/**
 * Plugin Name: WP File Download
 * Plugin URI: https://www.joomunited.com/wordpress-products/wp-file-download
 * Description: WP File Download, a new way to manage files in WordPress
 * Author: Joomunited
 * Version: 4.7.13
 * Text Domain: wpfd
 * Domain Path: /app/languages
 * Author URI: https://www.joomunited.com
 */

// Prohibit direct script loading
defined('ABSPATH') || die('No direct script access allowed!');

/*
 * Define WP File Download current version
 */
define('WPFD_VERSION', '4.7.13');

// Check plugin requirements
if (version_compare(PHP_VERSION, '5.6', '<')) {
    if (!function_exists('wpfdDisablePlugin')) {
        /**
         * Deactivate plugin
         *
         * @return void
         */
        function wpfdDisablePlugin()
        {
            if (current_user_can('activate_plugins') && is_plugin_active(plugin_basename(__FILE__))) {
                deactivate_plugins(__FILE__);
                // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Internal function used
                unset($_GET['activate']);
            }
        }
    }

    if (!function_exists('wpfdShowError')) {
        /**
         * Show notice
         *
         * @return void
         */
        function wpfdShowError()
        {
            echo '<div class="error"><p>';
            echo '<strong>WP File Download</strong>';
            echo ' needs at least PHP 5.6 version, please update php before installing the plugin.</p></div>';
        }
    }

    // Add actions
    add_action('admin_init', 'wpfdDisablePlugin');
    add_action('admin_notices', 'wpfdShowError');

    // Do not load anything more
    return;
}

//Include the jutranslation helpers
include_once('jutranslation' . DIRECTORY_SEPARATOR . 'jutranslation.php');
call_user_func(
    '\Joomunited\WPFileDownload\Jutranslation\Jutranslation::init',
    __FILE__,
    'wpfd',
    'WP File Download',
    'wpfd',
    'app' . DIRECTORY_SEPARATOR . 'languages' . DIRECTORY_SEPARATOR . 'wpfd-en_US.mo'
);

if (!class_exists('\Joomunited\WPFileDownload\JUCheckRequirements')) {
    include_once('app/requirements.php');
}
if (class_exists('\Joomunited\WPFileDownload\JUCheckRequirements')) {
    // Plugins name for translate
    $args = array(
        'plugin_name' => esc_html__('WP File Download', 'wpfd'),
        'plugin_path' => 'wp-file-download/wp-file-download.php',
        'plugin_textdomain' => 'wpfd',
        'requirements' => array(
            'php_version' => '5.6',
            'php_modules' => array(
                'xml' => 'error'
            ),
            // Minimum addons version
            'addons_version' => array(
                'wpfdCloudAddons' => '4.3.0'
            )
        ),
    );
    $wpfdCheck = call_user_func('\Joomunited\WPFileDownload\JUCheckRequirements::init', $args);
    if (!$wpfdCheck['success']) {
        // Do not load anything more
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Internal function used
        unset($_GET['activate']);
        return;
    }
}

include_once('framework' . DIRECTORY_SEPARATOR . 'ju-libraries.php');

if (!defined('WPFD_PLUGIN_FILE')) {
    define('WPFD_PLUGIN_FILE', __FILE__);
}
if (!defined('WPFD_PLUGIN_DIR_PATH')) {
    define('WPFD_PLUGIN_DIR_PATH', trailingslashit(realpath(dirname(__FILE__))));
}
if (!defined('WPFD_PLUGIN_URL')) {
    define('WPFD_PLUGIN_URL', plugin_dir_url(__FILE__));
}
// Define to use new ui
define('WPFD_ADMIN_UI', true);

include_once('app' . DIRECTORY_SEPARATOR . 'autoload.php');
include_once('app' . DIRECTORY_SEPARATOR . 'install.php');
include_once('app' . DIRECTORY_SEPARATOR . 'widget.php');
include_once('app' . DIRECTORY_SEPARATOR . 'functions.php');

//Initialise the application
$app = call_user_func('Joomunited\WPFramework\v1_0_5\Application::getInstance', 'Wpfd', __FILE__);
$app->init();

if (is_admin()) {
    //config section
    if (!defined('JU_BASE')) {
        define('JU_BASE', 'https://www.joomunited.com/');
    }

    $remote_updateinfo = JU_BASE . 'juupdater_files/wp-file-download.json';
    //end config

    require 'juupdater/juupdater.php';
    $UpdateChecker = Jufactory::buildUpdateChecker(
        $remote_updateinfo,
        __FILE__
    );
}
/**
 * Handle redirects to setup/welcome page after install and updates.
 *
 * For setup wizard, transient must be present, the user must have access rights, and we must ignore the network/bulk plugin updaters.
 *
 * @return void
 */
function wpfd_wizard_setup_redirect()
{
    // Setup wizard redirect
    if (is_null(get_option('_wpfd_installed', null)) || get_option('_wpfd_installed') === 'false') {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- View request, no action
        if ((!empty($_GET['page']) && in_array($_GET['page'], array('wpfd-setup')))) {
            return;
        }

        wp_safe_redirect(admin_url('index.php?page=wpfd-setup'));
        exit;
    }
}

/**
 * Includes WP File Download setup
 *
 * @return void
 */
function wpfd_wizard_setup_include()
{
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- View request, no action
    if (!empty($_GET['page'])) {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- View request, no action
        switch ($_GET['page']) {
            case 'wpfd-setup':
                require_once WPFD_PLUGIN_DIR_PATH . 'app/admin/classes/install-wizard/install-wizard.php';
                break;
        }
    }
}
add_action('init', 'wpfd_wizard_setup_include');
add_action('admin_init', 'wpfd_wizard_setup_redirect');

// Load Addons
if (isset($wpfdCheck) && !empty($wpfdCheck['load'])) {
    foreach ($wpfdCheck['load'] as $addonName) {
        if (function_exists($addonName . 'Init')) {
            call_user_func($addonName . 'Init');
        }
    }
}

// Setup cron interval for Statistics Storage be clear
add_filter('wpfd_get_schedules', 'wpfd_get_remove_statistics_schedule');
add_filter('wpfd_get_schedules', 'wpfd_get_clean_junks_schedule');

wpfd_schedules();

// Add hook for cronjob
add_action('wpfd_remove_statistics_tasks', 'wpfd_remove_statistics');
add_action('wpfd_remove_junks_tasks', 'wpfd_clean_junks');

// Install cronjob
wpfd_install_job('wpfd_remove_statistics_tasks', 'wpfd_remove_statistics');
wpfd_install_job('wpfd_remove_junks_tasks', 'wpfd_clean_junks');

// Reload schedule after main config saved
add_action('wpfd_after_main_setting_save', 'wpfd_after_main_setting_save');
