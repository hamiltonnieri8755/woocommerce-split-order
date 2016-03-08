<?php
/**
 * Plugin Name: WooCommerce Split Order
 * Plugin URI: https://www.wplab.com/
 * Description: An e-commerce toolkit that helps you split woocommerce order
 * Version: 1.0
 * Author: Hamilton Nieri
 * Author URI: https://www.wplab.com/
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * ----------------------------------------------------------------------
 * Copyright (C) 2016  Hamilton Nieri  (Email: hamiltonnieri8755@yahoo.com)
 * ----------------------------------------------------------------------
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * ----------------------------------------------------------------------
 */

// Including WP core file
if ( ! function_exists( 'get_plugins' ) )
    require_once ABSPATH . 'wp-admin/includes/plugin.php';

// Including base class
if ( ! class_exists( 'WC_Split_Order' ) )
    require_once plugin_dir_path( __FILE__ ) . 'classes/class-wc-split-order.php';

// Whether plugin active or not
if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) :

	/**
	 * Display Metabox Shippment Tracking on order admin page
	 **/

	add_action( 'add_meta_boxes', 'wso_add_meta_boxes' );

	function wso_add_meta_boxes(){

	    add_meta_box(
	        'woocommerce-split-order',
	        'Split current order',
	        'split_order_meta',
	        'shop_order',
	        'side',
	        'default'
	    );

	}

	// global 
	$wso = NULL;

	/**
	 * Outputs the content of the meta box
	 */
	function split_order_meta( $post ) {
		
		// The object
		global $wso;
	    $wso = new WC_Split_Order( $post );
	    $wso->output_metabox_content();
	}

else :

	/**
     * Getting notice if WooCommerce not active
     * 
     * @return string
     */
	function wso_notice() {
        global $current_screen;
        if ( $current_screen->parent_base == 'plugins' ) {
            echo '<div class="error"><p>'.__( 'The <strong>WooCommerce Split Order</strong> plugin requires the <a href="http://wordpress.org/plugins/woocommerce" target="_blank">WooCommerce</a> plugin to be activated in order to work. Please <a href="'.admin_url( 'plugin-install.php?tab=search&type=term&s=WooCommerce' ).'" target="_blank">install WooCommerce</a> or <a href="'.admin_url( 'plugins.php' ).'">activate</a> first.' ).'</p></div>';
        }
    }
    add_action( 'admin_notices', 'wso_notice' );

endif;
