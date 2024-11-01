<?php
/**
 * Plugin Name: VoyageSMS
 * Plugin URI: https://voyagetext.com/wp-plugin
 * Description: This is a plugin for existing VoyageSMS clients on WooCommerce to add required scripts for modal display and purchase tracking.
 * Version: 1.1
 * Author: Voyage Mobile, Inc.
 * Author URI: https://voyagetext.com
 */

// Adding Voyage Script
add_action( 'wp_head', 'add_voyage_init_script' );
add_action( 'woocommerce_thankyou', 'voyage_checkout_analytics' );
// Adding Admin Setting menu
add_action( 'admin_menu', 'voyage_plugin_menu' );
add_action( 'admin_menu', 'voyage_add_admin_api' );
add_action( 'admin_init', 'voyage_settings_init' );

function add_voyage_init_script() {
    $voyage_pguid = get_option( 'voyage_settings' )['voyage_pguid_field'];
    echo "<script type='text/javascript'>
    /* <![CDATA[ */
    (function(){if(window.voyage)return;window.voyage={q:[]};var fns=['init', 'track'];
    for(var i=0;i<fns.length;i++){(function(fn){window.voyage[fn]=function(){
        this.q.push([fn,arguments]);}})(fns[i])}})();voyage.init('$voyage_pguid', { popup: true });
        /* ]]> */
        </script>
        <script async src='https://assets.voyagetext.com/voyage.production.js'></script>";
}

function voyage_checkout_analytics( $order_id ) {
    $order = new WC_Order( $order_id );
    $total = $order->get_total();
    ?>
    <script type='text/javascript'>
        voyage.track('Purchase', { amountCents: <?php echo $total*100; ?> });
        console.log('Voyage Purchased Amount: ', <?php echo $total*100; ?>)
    </script> 
    <?php
    
}

function voyage_add_admin_api(  ) {
    add_options_page( 'Voyage Setting', 'Voyage Setting', 'manage_options', 'voyage-setting-page', 'voyage_options_page' );
}

function voyage_settings_init(  ) {
    register_setting( 'voyagePlugin', 'voyage_settings' );
    add_settings_section(
        'voyage_sms_setting_section',
        __( 'Edit Voyage Setting', 'wordpress' ),
        'voyage_settings_section_callback',
        'voyagePlugin'
    );

    add_settings_field(
        'voyage_pguid_field',
        __( 'Your Voyage Public GUID', 'wordpress' ),
        'voyage_pguid_field_render',
        'voyagePlugin',
        'voyage_sms_setting_section'
    );
}

function voyage_pguid_field_render(  ) {
    $options = get_option( 'voyage_settings' );
    ?>
    <input type='text' name='voyage_settings[voyage_pguid_field]' value='<?php echo $options['voyage_pguid_field']; ?>'>
    <?php
}

function voyage_settings_section_callback(  ) {
    echo __( 'You can obtain your Public GUID from your Voyage contact.', 'wordpress' );
}

function voyage_options_page(  ) {
    ?>
    <form action='options.php' method='post'>
        <h2>Voyage Admin Settings</h2>
        <?php
        settings_fields( 'voyagePlugin' );
        do_settings_sections( 'voyagePlugin' );
        submit_button();
        ?>
    </form>
    <?php
}