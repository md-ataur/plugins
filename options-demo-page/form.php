<h1>Admin Options page</h1>
<form method="post" action="<?php echo admin_url('admin-post.php') ?>">
	<?php
		wp_nonce_field("odp");
		/* get the data */
		$optionsdemo_longitude = get_option('optionsdemo_longitude2');
	?>
	<label for="optionsdemo_longitude2"><?php _e('Longitude','options-demo-page'); ?></label>
	<input type="text" id="optionsdemo_longitude2" name="optionsdemo_longitude2" value="<?php echo esc_attr($optionsdemo_longitude); ?>">
	<input type="hidden" name="action" value="optionsdemo_admin_page">
	<?php submit_button('Save'); ?>
</form>