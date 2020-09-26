<?php
/**
 * Plugin Name:  Options Demo
 * Plugin URI:
 * Description:  Demonstration for plugin option page
 * Version:      1.0
 * Author:       Ataur Rahman
 * Author URI:
 * License:      GPL2
 * License URI:  https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:  options-demo
 * Domain Path:  /languages
 */


/**
 * We generated this code from https://www.wp-hasty.com/
 */


if (!class_exists("OptionsDemo")) {
	
	class OptionsDemo{
				
		public function __construct() {
			add_action( 'plugins_loaded', array( $this, 'optionsdemo_bootstrap' ) );			
			add_action( 'admin_menu', array( $this, 'optionsdemo_create_settings' ) );
			add_action( 'admin_init', array( $this, 'optionsdemo_setup_sections' ) );
			add_action( 'admin_init', array( $this, 'optionsdemo_setup_fields' ) );			
			
			/* Hook for plugin link */
			add_filter( 'plugin_action_links_'.plugin_basename(__FILE__), array( $this, 'optionsdemo_settings_link' ) );
		}

		/* Link add */
		public function optionsdemo_settings_link($links) {
			$newlink = sprintf("<a href='%s'>%s</a>",'options-general.php?page=optionsdemo',__('Settings','options-demo'));
			$links[] = $newlink;
			return $links;
		}

		public function optionsdemo_bootstrap() {
			load_plugin_textdomain( 'options-demo', false, plugin_dir_path( __FILE__ ) . "/languages" );
		}

		public function optionsdemo_create_settings() {
			$page_title = __( 'Options Demo', 'options-demo' );
			$menu_title = __( 'Options Demo', 'options-demo' );
			$capability = 'manage_options';
			$slug       = 'optionsdemo';
			$callback   = array( $this, 'optionsdemo_settings_content' );
			/* add option page under settings */
			add_options_page( $page_title, $menu_title, $capability, $slug, $callback );			
			//add_media_page( $page_title, $menu_title, $capability, $slug, $callback );
			//add_plugins_page( $page_title, $menu_title, $capability, $slug, $callback );
		}
		
		/* This call back function for showing the content */
		public function optionsdemo_settings_content() { ?>
			<div class="wrap">				
				<form method="POST" action="options.php">
					<?php
					settings_fields( 'optionsdemo' );
					do_settings_sections( 'optionsdemo' );
					submit_button();
					?>
				</form>
			</div> <?php
		}

		/* Setup sections */
		public function optionsdemo_setup_sections() {
			add_settings_section( 'optionsdemo_section', 'Demonstration of plugin settings page', array(), 'optionsdemo' );
		}

		/* Setup fields */
		public function optionsdemo_setup_fields() {
			$fields = array(
				array(
					'label'       => __( 'Latitude', 'options-demo' ),
					'id'          => 'optionsdemo_latitude',
					'type'        => 'text',
					'section'     => 'optionsdemo_section',
					'placeholder' => __( 'Latitude', 'options-demo' ),
				),
				array(
					'label'       => __( 'Longitude', 'options-demo' ),
					'id'          => 'optionsdemo_longitude',
					'type'        => 'text',
					'section'     => 'optionsdemo_section',
					'placeholder' => __( 'Longitude', 'options-demo' ),
				),
				array(
					'label'   => __( 'Zoom Level', 'options-demo' ),
					'id'      => 'optionsdemo_zoomlevel',
					'type'    => 'text',
					'section' => 'optionsdemo_section',
				),
				array(
					'label'   => __( 'API Key', 'options-demo' ),
					'id'      => 'optionsdemo_apikey',
					'type'    => 'text',
					'section' => 'optionsdemo_section',
				),
				array(
					'label'   => __( 'External CSS', 'options-demo' ),
					'id'      => 'optionsdemo_externalcss',
					'type'    => 'textarea',
					'section' => 'optionsdemo_section',
				),
				array(
					'label'   => __( 'Expiry Date', 'options-demo' ),
					'id'      => 'optionsdemo_expirydate',
					'type'    => 'date',
					'section' => 'optionsdemo_section',
				),
			);
			foreach ( $fields as $field ) {
				add_settings_field( $field['id'], $field['label'], array($this,'optionsdemo_field_callback'), 'optionsdemo', $field['section'], $field );
				register_setting( 'optionsdemo', $field['id'] );
			}
		}

		/* Textarea and Input field */
		public function optionsdemo_field_callback( $field ) {
			$value = get_option( $field['id'] );
			switch ( $field['type'] ) {
				case 'textarea':
					printf( '<textarea name="%1$s" id="%1$s" placeholder="%2$s" rows="5" cols="50">%3$s</textarea>',
						$field['id'],
						isset( $field['placeholder'] ) ? $field['placeholder'] : '',
						$value
					);
					break;
				default:
					printf( '<input name="%1$s" id="%1$s" type="%2$s" placeholder="%3$s" value="%4$s" />',
						$field['id'],
						$field['type'],
						isset( $field['placeholder'] ) ? $field['placeholder'] : '',
						$value
					);
			}
			if ( isset( $field['desc'] ) ) {
				if ( $desc = $field['desc'] ) {
					printf( '<p class="description">%s </p>', $desc );
				}
			}
		}
	}

	new OptionsDemo();
}

/**
 * How to get these field value
 * echo esc_attr(get_option('optionsdemo_latitude'));
 */