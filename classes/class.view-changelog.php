<?php
/**
 * View сhangelog
 *
 * @file
 * @package		Webolatory changelog
 * @author		Andrew Skochelias
 */

defined( 'ABSPATH' ) || die();

/**
 * Class WordPress_View_Changelog_Class.
 */
class WordPress_View_Changelog_Class extends WP_Changelog_Init {

	/**
	 * Constructor
	*/
	function __construct() {

		add_action( 'admin_menu', 				array( &$this, 'register_view_page' ) );
		add_action( 'admin_enqueue_scripts', 	array( &$this, 'register_scripts' ) );
	}

	/**
	 * Register setting page
	 *
	 * @return void.
	 */
	public function register_view_page() {

		add_submenu_page(
			'tools.php',
			'WordPress Changelog',
			'WordPress Changelog',
			'manage_options',
			'wordpress-changelog',
			array( &$this, 'view_changelog' ),
			plugins_url( WP_CHANGELOG_DOMAIN . '/dist/media/favicon.ico' ),
			80
		);
	}

	/**
	 * View changelog.
	 *
	 * @return void.
	 */
	public function view_changelog() {

		if ( ! $this->is_webolatory_pack_setting_page() ) {

			return false;
		}

		// Table params
		global $wpdb;

		$table_name = $wpdb->prefix . 'webolatory_changelog';

		// Get results
		$rows = $wpdb->get_results( "SELECT * FROM `$table_name` ORDER BY `id` DESC" );

		// Show heaber
		printf( '
			<div class="wbl_container">
				<div class="wbl_main">
					<div class="wbl_main_header">
						<div class="wbl_left">
							<div class="wbl_logo">
								<a href="http://webolatory.com/" rel="nofollow" target="blank">
									<img src="%s/dist/media/webolatory_logo.svg" class="wbl_hide_mobile" alt="logo">
									<img src="%s/dist/media/mobile_logo.svg" class="wbl_hide_desc" alt="logo">
								</a>
							</div>
						</div>
						
						<div class="wbl_right">
							<h1>%s</1>
						</div>
						
					</div>

					<div class="wbl_main_section">
						<div id="changelog-list" class="wbl_subsection" style="display:block;">
							<input type="text" class="search" placeholder="%s" style="width:250px;margin-bottom:25px;"/>
							<select class="list-filter" data-target="type" style="width:250px;">
								<option value="all" selected>%s</option>
							</select>
							<select class="list-filter" data-target="action" style="width:250px;">
								<option value="all" selected>%s</option>
							</select>
							<span class="wbl_reset">reset filter</span>
							<table class="wbl_table">
								<thead>
									<tr>
										<th><span class="sort" data-sort="id">%s</span></th>
										<th><span class="sort" data-sort="date">%s</span></th>
										<th><span class="sort" data-sort="action">%s</span></th>
										<th><span class="sort" data-sort="name">%s</span></th>
										<th><span class="sort" data-sort="ver">%s</span></th>
										<th><span class="sort" data-sort="type">%s</span></th>
									</tr>
								</thead>
								<tbody class="list">
			',
			esc_url( WP_CHANGELOG_URL ),							// Logo
			esc_url( WP_CHANGELOG_URL ),							// Mobile logo
			esc_html( ' WordPress Changelog' ),						// Page title
			esc_html__( 'Search changes', WP_CHANGELOG_DOMAIN ),	// Search input placeholder
			esc_html__( 'Show All Types', WP_CHANGELOG_DOMAIN ),	// Type filter default value
			esc_html__( 'Show All Actions', WP_CHANGELOG_DOMAIN ),	// Action filter default value
			esc_html__( 'ID', WP_CHANGELOG_DOMAIN ),				// Row IDs
			esc_html__( 'Date', WP_CHANGELOG_DOMAIN ),				// Action Date
			esc_html__( 'Action', WP_CHANGELOG_DOMAIN ),			// Action Name
			esc_html__( 'Name', WP_CHANGELOG_DOMAIN ),				// Object Name
			esc_html__( 'Ver.', WP_CHANGELOG_DOMAIN ),				// Object Version
			esc_html__( 'Type', WP_CHANGELOG_DOMAIN )				// Object type
		);

		// Table body
		if ( is_array( $rows ) && ! empty( $rows ) ) {

			foreach ( $rows as $row ) {
				printf( '
									<tr>
										<td class="id">%d</td>
										<td class="date">%s</td>
										<td class="action">%s</td>
										<td class="name">%s</td>
										<td class="ver">%s</td>
										<td class="type">%s</td>
									</tr>',
					absint( $row->id ),
					esc_html__( $row->date ),
					esc_html__( $row->action ),
					esc_html__( $row->name ),
					esc_html__( $row->version ),
					esc_html__( $row->type )
				);
			}
		}

		// Show footer
		printf( '
								</tbody>
							</table>
						</div>

						<h3 class="wbl_copyright" >
							© WordPress Changelog — Made with ♥ by <a href="http://webolatory.com/" rel="nofollow" target="blank">Webolatory</a>
						</h3>
					</div>
				</div>
			</div>
		');
	}

	/**
	 * Register scripts
	 *
	 * @return void.
	 */
	public function register_scripts() {

		if ( ! $this->is_webolatory_pack_setting_page() ) {

			return false;
		}

		// Register style
		wp_register_style( 'webolatory-style', plugins_url( WP_CHANGELOG_DOMAIN . '/dist/css/style.css' ), array(), null, 'all' );

		// Enqueue style
		wp_enqueue_style( 'webolatory-style' );

		// Enqueue JS
		wp_enqueue_script( 'webolatory-js', plugins_url( WP_CHANGELOG_DOMAIN . '/dist/js/script.js' ), array(), null, true );
		wp_enqueue_script( 'list-js', plugins_url( WP_CHANGELOG_DOMAIN . '/dist/js/list.js' ), array(), null, true );
	}

	/**
	 * Cheack webolatory pack setting page.
	 *
	 * @return void.
	 */
	public function is_webolatory_pack_setting_page() {

		global $pagenow;

		// Check page.
		if ( 'tools.php' === $pagenow && isset( $_GET['page'] ) && 'wordpress-changelog' === sanitize_text_field( $_GET['page'] ) ) {
			return true;
		}

		return false;
	}
}

$view_changelog = new WordPress_View_Changelog_Class();
