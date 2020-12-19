<?php
/*
Plugin Name: TranslatePress - SEO Pack Add-on
Plugin URI: https://translatepress.com/
Description: Extends the functionality of TranslatePress by allowing the translation of the page's meta information, including the url slug.
Version: 1.3.0
Author: Cozmoslabs, Razvan Mocanu
Author URI: https://translatepress.com/
License: GPL2

== Copyright ==
Copyright 2020 Cozmoslabs (www.cozmoslabs.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/



function trp_sp_run(){
	
	/** Initialize update here in the main plugin file. It is a must **/
	// this is the URL our updater / license checker pings. This should be the URL of the site with EDD installed
	define( 'TRP_SP_STORE_URL', 'https://translatepress.com' );
	// the name of your product. This should match the download name in EDD exactly
	define( 'TRP_SP_ITEM_NAME', 'SEO Pack' );
	if( !class_exists( 'TRP_EDD_SL_Plugin_Updater' ) ) {
		// load our custom updater
		include( dirname( __FILE__ ) . '/includes/class-edd-sl-plugin-updater.php' );
	}
	// retrieve our license key from the DB
	$license_key = trim( get_option( 'trp_license_key' ) );
	// setup the updater
	$edd_updater = new TRP_EDD_SL_Plugin_Updater( TRP_SP_STORE_URL, __FILE__, array(
						'version' 	=> '1.3.0', 		// current version number
						'license' 	=> $license_key, 	// license key (used get_option above to retrieve from DB)
						'item_name' => TRP_SP_ITEM_NAME, 	// name of this plugin
						'item_id'   => '226',							
						'author' 	=> 'Cozmoslabs',  // author of this plugin
						'beta' 		=> false
						)
			);
	/** End the update initialization here **/
	
	
	
    require_once plugin_dir_path( __FILE__ ) . 'class-seo-pack.php';
    if ( class_exists( 'TRP_Translate_Press' ) ) {
        new TRP_Seo_Pack();
    }

}
add_action( 'plugins_loaded', 'trp_sp_run', 0 );

// Clear Yoast SEO Sitemap on plugin activation and deactivation. 
require_once plugin_dir_path( __FILE__ ) . 'class-seo-pack.php';
register_activation_hook( __FILE__, array( 'TRP_Seo_Pack', 'wpseo_clear_sitemap' ) );
register_deactivation_hook( __FILE__, array( 'TRP_Seo_Pack', 'wpseo_clear_sitemap' ) );
