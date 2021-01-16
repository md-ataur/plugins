<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('Address book', 'wedevs-tutorial');?></h1>
    <a class="page-title-action" href="<?php echo admin_url('admin.php?page=wedevs-tutorial&action=new');?>">Add New</a>

    <!-- Data store message -->
    <?php if ( isset( $_GET['inserted'] ) ) { ?>
        <div class="notice notice-success">
            <p><?php _e( 'Address has been stored successfully', 'wedevs-tutorial' ); ?></p>
        </div>
    <?php } ?>

    <!-- Data delete message -->
    <?php if ( isset( $_GET['address-deleted'] ) && $_GET['address-deleted'] == 'true' ) { ?>
        <div class="notice notice-success">
            <p><?php _e( 'Address has been deleted successfully!', 'wedevs-academy' ); ?></p>
        </div>
    <?php } ?>

    <form action="" method="post">
        <?php
        // object create for Address_List class
        $table = new WeDevs\Tutorial\Admin\Address_List();

        // Call prepare_items() method
        $table->prepare_items();

        // Call display() method to show data
        $table->display();
        ?>
    </form>
</div>