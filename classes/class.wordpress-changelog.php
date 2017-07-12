<?php
/**
 * WordPress сhangelog
 *
 * @file
 * @package		Webolatory changelog
 * @author		Andrew Skochelias
 */

defined( 'ABSPATH' ) || die();

/**
 * Class Wbl_WordPress_Changelog.
 */
class WordPress_Changelog_Class extends WP_Changelog_Init {

	/**
	 * Constructor
	*/
	function __construct() {

		// Core actions
		add_action( '_core_updated_successfully', 			array( &$this, 'wordpress_upgrade_action' ), 					10, 1 ); // Upgrade core
		// Plugin actions
		add_action( 'activated_plugin', 					array( &$this, 'plugin_action' ), 								10, 2 ); // Activat plugin
		add_action( 'deactivate_plugin', 					array( &$this, 'plugin_action' ), 								10, 2 ); // Deactivate plugin
		add_action( 'delete_plugin', 						array( &$this, 'plugin_action' ), 								10, 1 ); // Delete plugin
		add_action( 'upgrader_process_complete', 			array( &$this, 'theme_and_plugin_install_and_update_action' ), 	10, 2 ); // Plugins and Themes install and update
		// Theme actions
		add_action( 'delete_site_transient_update_themes', 	array( &$this, 'delete_themes_action' ), 						10, 1 ); // Delete themes
		add_action( 'switch_theme', 						array( &$this, 'switch_theme_action' ), 						10, 3 ); // Switch theme
	}

	/**
	 * WordPress upgrade action.
	 *
	 * @param string $wp_version The current WordPress version.
	 *
	 * @return void.
	 */
	public function wordpress_upgrade_action( $version = null ) {

		// Get WordPress version
		if ( null === $version ) {
			global $wp_version;
			$version = $wp_version;
		}

		// Save data
		if ( ! empty( $version ) ) {

			$this->write_log( 'upgrade', 'wordpress/core', 'WordPress', 'wordpress-wordpress_upgrade_action', $version, 'core' );
		}

		return null;
	}

	/**
	 * Plugin action
	 *
	 * @param string $plugin_file  Plugin path to main plugin file with plugin data.
	 * @param bool   $network_wide Whether to enable the plugin for all sites in the network or just the current site. Multisite only. Default is false.
	 *
	 * @return void.
	 */
	public function plugin_action( $plugin_file, $network_wide = null ) {

		// Get cutern action
		$action = current_filter();

		// Actions list
		$actions = array(
			'activated_plugin'	=> 'activate',
			'deactivate_plugin'	=> 'deactivate',
			'delete_plugin'		=> 'delete',
		);

		if ( ! empty( $action ) ) {

			// Get plugin info
			$plugin_data = $this->get_plugin_info( $plugin_file );

			// Save data
			if ( is_array( $plugin_data ) && ! empty( $plugin_data ) ) {

				$this->write_log( $actions[ $action ], $plugin_file, $plugin_data['plugin_name'], $plugin_data['plugin_domain'], $plugin_data['plugin_version'], 'plugin' );
			}
		}

		return null;
	}

	/**
	 * Install & Update plugin and theme action.
	 *
	 * @param object $instance plugin\theme info.
	 * @param array $extra action data.
	 *
	 * @return void.
	 */
	public function theme_and_plugin_install_and_update_action( $instance, $extra ) {

		switch ( $extra['type'] ) {

			// Plugin actions
			case 'plugin':

				// Install
				if ( 'install' === $extra['action'] ) {

					// Get plugin info
					$plugin_data = $this->get_plugin_info( $instance->plugin_info() );

					// Save data
					if ( is_array( $plugin_data ) && ! empty( $plugin_data ) ) {

						$this->write_log( 'install', $instance->plugin_info(), $plugin_data['plugin_name'], $plugin_data['plugin_domain'], $plugin_data['plugin_version'], 'plugin' );
					}
				}

				// Update
				if ( 'update' === $extra['action'] ) {

					// Bulk action

					if ( isset( $extra['bulk'] ) && true === $extra['bulk'] ) {

						// Get plugins info
						if ( isset( $extra['plugins'] ) && ! empty( $extra['plugins'] ) && is_array( $extra['plugins'] ) ) {

							foreach ( $extra['plugins'] as $plugin ) {

								// Get plugin info
								$plugin_data = $this->get_plugin_info( $plugin );

								// Save data
								if ( is_array( $plugin_data ) && ! empty( $plugin_data ) ) {

									$this->write_log( 'update', $plugin, $plugin_data['plugin_name'], $plugin_data['plugin_domain'], $plugin_data['plugin_version'], 'plugin' );
								}
							}
						}
					} elseif ( ! empty( $instance->skin->plugin ) ) {

						// Single action

						// Get plugin info
						$plugin_data = $this->get_plugin_info( $instance->skin->plugin );

						// Save data
						if ( is_array( $plugin_data ) && ! empty( $plugin_data ) ) {

							$this->write_log( 'update', $instance->skin->plugin, $plugin_data['plugin_name'], $plugin_data['plugin_domain'], $plugin_data['plugin_version'], 'plugin' );
						}
					} else {
						return null;
					}
				}

				break;

			// Theme actions
			case 'theme':

				// Install
				if ( 'install' === $extra['action'] ) {

					$theme_info = $instance->theme_info();

					if ( $theme_info ) {

						// Get theme data
						$theme_data = array(
							'theme_name' 	=> $theme_info->get( 'Name' ),
							'theme_domain' 	=> $theme_info->get( 'TextDomain' ),
							'theme_version' => $theme_info->get( 'Version' ),
						);

						// Save data
						$this->write_log( 'install', $instance->result['destination_name'], $theme_data['theme_name'], $theme_data['theme_domain'], $theme_data['theme_version'], 'theme' );
					}
				}

				// Update
				if ( 'update' === $extra['action'] ) {

					if ( isset( $extra['bulk'] ) && true === $extra['bulk'] ) {

						// Bulk action

						// Get themes info
						if ( isset( $extra['themes'] ) && ! empty( $extra['themes'] ) && is_array( $extra['themes'] ) ) {

							foreach ( $extra['themes'] as $theme ) {

								// Get theme info
								$theme_info = wp_get_theme( $theme );

								if ( $theme_info ) {

									// Get theme data
									$theme_data = array(
										'theme_name' 	=> $theme_info->get( 'Name' ),
										'theme_domain' 	=> $theme_info->get( 'TextDomain' ),
										'theme_version' => $theme_info->get( 'Version' ),
									);

									// Save data
									$this->write_log( 'update', $theme, $theme_data['theme_name'], $theme_data['theme_domain'], $theme_data['theme_version'], 'theme' );
								}
							}
						}
					} elseif ( ! empty( $instance->skin->theme ) ) {

						// Single action

						// Get theme info
						$theme_info = wp_get_theme( $instance->skin->theme );

						// Save data
						if ( $theme_info ) {

							// Get theme data
							$theme_data = array(
								'theme_name' 	=> $theme_info->get( 'Name' ),
								'theme_domain' 	=> $theme_info->get( 'TextDomain' ),
								'theme_version' => $theme_info->get( 'Version' ),
							);

							// Save data
							$this->write_log( 'update', $instance->result['destination_name'], $theme_data['theme_name'], $theme_data['theme_domain'], $theme_data['theme_version'], 'theme' );
						}
					} else {
						return null;
					}
				}

				break;

		}

		return null;
	}

	/**
	 * Delete themes action.
	 *
	 * @param string $transient Transient name.
	 *
	 * @return void.
	 */
	public function delete_themes_action( $transient ) {

		// Check theme slug
		if ( ! isset( $_REQUEST['slug'] ) || empty( $_REQUEST['slug'] ) ) {
			return null;
		}

		// Check action
		if ( ! isset( $_REQUEST['action'] ) || 'delete-theme' !== $_REQUEST['action'] ) {
			return null;
		}

		global $wpdb;
		$slug = sanitize_text_field( $_REQUEST['slug'] );

		// Get theme info
		$theme = $wpdb->get_row(
			$wpdb->prepare(
				'SELECT * FROM ' . $wpdb->prefix . 'webolatory_changelog WHERE file = %s',
				$slug
			)
		);

		if ( $theme ) {

			// Save data
			$this->write_log( 'delete', $slug, $theme->name, $theme->domain, $theme->version, 'theme' );
		}

		return null;
	}

	/**
	 * Switch themes action.
	 *
	 *
	 * @param string   $new_name  Name of the new theme.
	 * @param WP_Theme $new_theme WP_Theme instance of the new theme.
	 * @param WP_Theme $old_theme WP_Theme instance of the old theme.
	 *
	 * @return void.
	 */
	public function switch_theme_action( $new_name, $new_theme, $old_theme ) {

		// Check theme data
		if ( empty( $new_theme ) || empty( $old_theme ) ) {
			return null;
		}

		global $wpdb;

		// Get old theme info
		$new_theme_info = $wpdb->get_row(
			$wpdb->prepare(
				'SELECT * FROM ' . $wpdb->prefix . 'webolatory_changelog WHERE name = %s',
				$new_theme->get( 'Name' )
			)
		);

		// Get old theme info
		$old_theme_info = $wpdb->get_row(
			$wpdb->prepare(
				'SELECT * FROM ' . $wpdb->prefix . 'webolatory_changelog WHERE name = %s',
				$old_theme->get( 'Name' )
			)
		);

		// Save data
		if ( $new_theme_info && $old_theme_info ) {

			// Save old theme status
			$this->write_log( 'deactivate', $old_theme_info->file, $old_theme_info->name, $old_theme_info->domain, $old_theme_info->version, 'theme' );

			// Save new theme status
			$this->write_log( 'activate', $new_theme_info->file, $new_theme_info->name, $new_theme_info->domain, $new_theme_info->version, 'theme' );
		}

		return null;
	}

	/**
	 * Get plugin info
	 *
	 * @param string $plugin_file Plugin path to main plugin file with plugin data.
	 *
	 * @return $array plugin info.
	 */
	public function get_plugin_info( $plugin_file ) {

		$plugin_data = get_file_data(
			ABSPATH . 'wp-content/plugins/' . $plugin_file,
			array(
				'plugin_name' 		=> 'Plugin Name',
				'plugin_domain'		=> 'Text Domain',
				'plugin_version' 	=> 'Version',
			)
		);

		$plugin_data['plugin_file'] = $plugin_file;

		return $plugin_data;
	}

	/**
	 * Write mysql log
	 *
	 * @param string $action action.
	 * @param string $plugin_file plugin file name.
	 * @param string $plugin_name plugin name.
	 * @param string $plugin_domain plugin domain.
	 * @param string $plugin_version plugin version.
	 *
	 * @return void.
	 */
	public static function write_log( $action, $file, $name, $domain = null, $version, $type ) {

		global $wpdb;

		// Insert row
		$wpdb->insert(
			$wpdb->prefix . 'webolatory_changelog',
			array(
				'date'		=> current_time( 'mysql' ),
				'action'	=> $action,
				'file'		=> $file,
				'name'		=> $name,
				'domain'	=> $domain,
				'version'	=> $version,
				'type'		=> $type,
			)
		);

		return null;
	}

	/**
	 * Setup module
	 *
	 * @return void.
	 */
	public static function setup() {

		global $wpdb;

		/**
		 * Create wordpress сhangelog table
		 */

		// Table params
		$table_name 		= $wpdb->prefix . 'webolatory_changelog';
		$charset_collate 	= $wpdb->get_charset_collate();

		// sql query
		$sql = 'CREATE TABLE ' . $table_name . ' (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			date datetime NOT NULL,
			action VARCHAR(20) NOT NULL,
			file text NOT NULL,
			name text NOT NULL,
			domain text NOT NULL,
			version text NOT NULL,
			type VARCHAR(20) NOT NULL,
			UNIQUE KEY id (id)
		) ' . $charset_collate . ';';

		// Create table
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

		/**
		 * Write core, plugin and theme statuses
		 */
		$count_rows = $wpdb->get_var(
			$wpdb->prepare(
				'SELECT COUNT(*) FROM %s',
				$table_name
			)
		);

		if ( 0 === (int) $count_rows ) {

			// Get WordPress version
			global $wp_version;

			if ( ! empty( $wp_version ) ) {

				// Save data
				self::write_log( 'activate', 'wordpress/core', 'WordPress', 'wordpress', $wp_version, 'core' );
			}

			// Get all plugins data
			$plugins = get_plugins();

			if ( is_array( $plugins ) && ! empty( $plugins ) ) {

				foreach ( $plugins as $path => $plugin ) {

					$status = is_plugin_active( $path ) ? 'activate' : 'deactivate';

					// Save data
					self::write_log( $status, $path, $plugin['Name'], $plugin['TextDomain'], $plugin['Version'], 'plugin' );
				}
			}

			// Get themes data
			$themes = wp_get_themes( true, true );
			$curent_theme = wp_get_theme();

			if ( is_array( $themes ) && ! empty( $themes ) ) {

				foreach ( $themes as $path => $theme ) {

					$status = $curent_theme->get( 'Name' ) === $theme->get( 'Name' ) ? 'activate' : 'deactivate';

					// Save data
					self::write_log( $status, $path, $theme->get( 'Name' ), $theme->get( 'TextDomain' ), $theme->get( 'Version' ), 'theme' );
				}
			}
		}

		return null;
	}
}

$wordpress_changelog_class = new WordPress_Changelog_Class();

