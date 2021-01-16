<div class="wrap">
    <h1><?php _e( 'Update Address', 'wedevs-tutorial' ); ?></h1>
    <?php
    /* echo "<pre>";
    var_dump($address);
    echo "</pre>"; */
    ?>
    <?php if ( isset( $_GET['address-updated'] ) ) { ?>
        <div class="notice notice-success">
            <p><?php _e( 'Address has been updated successfully', 'wedevs-tutorial' ); ?></p>
        </div>
    <?php } ?>
    <form action="" method="post">
        <table class="form-table">
            <tbody>
                <tr class="<?php echo esc_attr( $this->set_error('name') ? 'form-invalid': '' );?>">
                    <th scope="row">
                        <label for="name"><?php _e( 'Name', 'wedevs-tutorial' ); ?></label>
                    </th>
                    <td>
                        <input type="text" name="name" id="name" class="regular-text" value="<?php echo esc_attr($address->name);?>">
                        <?php if($this->set_error('name')){ ?>
                            <p class="description error"><?php echo esc_html($this->get_error('name'));?></p>
                        <?php } ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="address"><?php _e( 'Address', 'wedevs-tutorial' ); ?></label>
                    </th>
                    <td>
                        <textarea class="regular-text" name="address" id="address"><?php echo esc_textarea($address->address);?></textarea>
                    </td>
                </tr>
                <tr class="<?php echo esc_attr( $this->set_error('phone') ? 'form-invalid': '' );?>">
                    <th scope="row">
                        <label for="phone"><?php _e( 'Phone', 'wedevs-tutorial' ); ?></label>
                    </th>
                    <td>
                        <input type="text" name="phone" id="phone" class="regular-text" value="<?php echo esc_attr($address->phone);?>">
                        <?php if($this->set_error('phone')){ ?>
                            <p class="description error"><?php echo esc_html($this->get_error('phone'));?></p>
                        <?php } ?>
                    </td>
                </tr>
            </tbody>
        </table>

        <input type="hidden" name="id" value="<?php echo esc_attr( $address->id ); ?>">
        <?php wp_nonce_field( 'new-address' ); ?>
        <?php submit_button( __( 'Update Address', 'wedevs-tutorial' ), 'primary', 'submit_address' ); ?>

    </form>
</div>