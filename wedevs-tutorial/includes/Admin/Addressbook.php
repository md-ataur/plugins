<?php
namespace WeDevs\Tutorial\Admin;

use WeDevs\Tutorial\Traits\Form_Error;

class Addressbook {

    // Trait call
    use Form_Error;

    /**
     * Call back function for Address Book menu (Menu.php)
     */
    public function addressbook_page() {
        // Null coalescing operator
        $action = $_GET['action'] ?? 'list';
        $id     = $_REQUEST['id'] ?? 0;

        switch ( $action ) {
            case 'new':
                $template = __DIR__ . '/views/address-new.php';
                break;

            case 'edit':
                $address  = get_address_by_id( $id );
                $template = __DIR__ . '/views/address-edit.php';
                break;

            case 'view':
                $template = __DIR__ . '/views/address-view.php';
                break;

            default:
                $template = __DIR__ . '/views/address-list.php';
                break;
        }

        if ( file_exists( $template ) ) {
            include $template;
        }

    }

    /**
     * Form submission handler
     */
    public function form_handler() {
        // Check submit value
        if ( !isset( $_POST['submit_address'] ) ) {
            return;
        }

        // Nonce check
        if ( !wp_verify_nonce( $_POST['_wpnonce'], 'new-address' ) ) {
            wp_die( 'You are not accessible!' );
        }

        // User permission check
        if ( !current_user_can( 'manage_options' ) ) {
            wp_die( 'You are not permissible!' );
        }

        // User data
        $name    = isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : '';
        $address = isset( $_POST['address'] ) ? sanitize_textarea_field( $_POST['address'] ) : '';
        $phone   = isset( $_POST['phone'] ) ? sanitize_text_field( $_POST['phone'] ) : '';

        // Check your field is empty or not
        if ( empty( $name ) ) {
            $this->errors['name'] = __( 'Please provide a name', 'wedevs-tutorial' );
        }

        if ( empty( $phone ) ) {
            $this->errors['phone'] = __( 'Please provide a phone number.', 'wedevs-tutorial' );
        }

        if ( $this->errors ) {
            return;
        }

        $args = [
            'name'    => $name,
            'address' => $address,
            'phone'   => $phone,
        ];

        // Id for user data update
        $id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;

        if ( $id ) {
            $args['id'] = $id;
        }

        $insert_id = insert_address( $args );

        if ( is_wp_error( $insert_id ) ) {
            wp_die( $insert_id->get_error_message() );
        }

        // Redirected to
        if ( $id ) {
            $redirected_to = admin_url( 'admin.php?page=wedevs-tutorial&action=edit&address-updated=true&id=' . $id );
        } else {
            $redirected_to = admin_url( 'admin.php?page=wedevs-tutorial&inserted=true' );
        }

        wp_redirect( $redirected_to );
        exit;

        // Call insert_address method
        /* print_r(insert_address());
        echo "<pre>";
        print_r($_POST);
        echo "</pre>";
        exit; */
    }

    /**
     * Delete address
     */
    public function delete_address() {
        // Nonce URL check
        if ( !wp_verify_nonce( $_REQUEST['_wpnonce'], 'delete-address' ) ) {
            wp_die( 'Are you cheating?' );
        }

        // User capability check
        if ( !current_user_can( 'manage_options' ) ) {
            wp_die( 'Are you cheating?' );
        }

        // Get ID
        $id = isset( $_REQUEST['id'] ) ? intval( $_REQUEST['id'] ) : 0;

        if ( delete_address_by_id( $id ) ) {
            $redirected_to = admin_url( 'admin.php?page=wedevs-tutorial&address-deleted=true' );
        } else {
            $redirected_to = admin_url( 'admin.php?page=wedevs-tutorial&address-deleted=false' );
        }

        wp_redirect( $redirected_to );
        exit;
    }
}
