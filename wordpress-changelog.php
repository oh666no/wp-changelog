<?php
/**
 * Plugin Name: WordPress Changelog
 * Plugin URI: http://webolatory.com/
 * Description: WordPress changelog - logs any uploads, updates, installations/uninstallations, activations/deactivations of themes, plugins and WordPress core.
 * Author: Andrew Skochelias
 * Author URI: http://skoch.com.ua/
 * Text Domain: wordpress-changelog
 * Version: 0.0.2
 * Domain Path: /languages/
 * License: GPL v3
 */

/**
 * WordPress changelog Plugin
 * Copyright (C) 2016, Webolatory - a.skoch@webolatory.com
 *
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
 */

defined( 'ABSPATH' ) || die;

define( 'WP_CHANGELOG_PLUGIN', __FILE__ );

define( 'WP_CHANGELOG_BASENAME', plugin_basename( WP_CHANGELOG_PLUGIN ) );

define( 'WP_CHANGELOG_DOMAIN', trim( dirname( WP_CHANGELOG_BASENAME ), '/' ) );

define( 'WP_CHANGELOG_DIR', untrailingslashit( dirname( WP_CHANGELOG_PLUGIN ) ) );

define( 'WP_CHANGELOG_URL', plugins_url( WP_CHANGELOG_DOMAIN ) );

/**
 * Init
 * */
class WP_Changelog_Init {

	/**
	 * Constructor
	*/
	function __construct() {

		// Include classes
		include_once( 'classes/class.view-changelog.php' );
		include_once( 'classes/class.wordpress-changelog.php' );

		// Load translate
		add_action( 'plugins_loaded', array( &$this, 'load_translate' ) );
	}

	/**
	 * Load translate.
	 *
	 * @return void.
	 */
	public function load_translate() {

		load_plugin_textdomain( WP_CHANGELOG_DOMAIN, false, WP_CHANGELOG_DOMAIN.'/languages/' );
	}

	/**
	 * Plugin Activation.
	 *
	 * @return void.
	 */
	public static function activation() {

		// Setup WordPress Changelog module
		WordPress_Changelog_Class::setup( 'Wbl_WordPress_Changelog' );

		return null;
	}
}

$wp_changelog = new WP_Changelog_Init();

// Activation hook
register_activation_hook( __FILE__, array( 'WP_Changelog_Init', 'activation' ) );
