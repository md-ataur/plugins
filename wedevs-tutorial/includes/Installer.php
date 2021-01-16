<?php
namespace WeDevs\Tutorial;

class Installer {

    public function run() {
        $this->add_version();
        $this->create_tables();
    }

    /**
     * Plugin version add
     */
    public function add_version() {
        /* plugin version add to the database */
        update_option( 'wd_tutorial_version', WD_TUTORIAL_VERSION );

        /* plugin installed time add to the database */
        $installed = get_option( 'wd_tutorial_installed_time' );
        if ( !$installed ) {
            update_option( 'wd_tutorial_installed_time', time() );
        }
    }

    /**
     * Create Database Table
     */
    public function create_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        $schema = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}address_book` (
          `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `name` varchar(100) NOT NULL DEFAULT '',
          `address` varchar(255) DEFAULT NULL,
          `phone` varchar(30) DEFAULT NULL,
          `created_by` bigint(20) unsigned NOT NULL,
          `created_at` datetime NOT NULL,
          PRIMARY KEY (`id`)
        ) $charset_collate";

        if ( !function_exists( 'dbDelta' ) ) {
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        }

        dbDelta( $schema );
    }
}
