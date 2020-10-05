<?php
/**
 * Plugin Name: Rainbow Address Bar
 * Plugin URI: https://wordpress.org/plugins/rainbow-address-bar/
 * Description: Rainbow address bar change the color of the browser address bar on your mobile devices.
 * Author: Amit Dudhat, Dhruv Pandya, hiddeninfotech
 * Author URI: http://disruptivebulb.com/
 * Version: 1.0.3
 * Text Domain: rainbow-address-bar
 * Domain Path: languages
 *
 * Rainbow Address Bar is distributed under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * Rainbow Address Bar is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Rainbow Address Bar. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package RainbowAddressBar
 * @author Amit Dudhat, Dhruv Pandya
 * @version 1.0.0
 */

/**
 * Rainbow_Address_Bar
 *
 * @package    RainbowAddressBar
 * @author     Amit Dudhat, Dharuv Pandya
 */
class Rainbow_Address_Bar {
	/**
	 * Init.
	 */
	public function __construct() {
		register_activation_hook( __FILE__, array( $this, 'rab_active_function' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'rab_assets' ) );

		// If AMP Plugin is enable.
		if ( ! has_action( 'amp_post_template_head' ) && ! empty( get_option( 'rab-amp-switch' ) ) ) {
			add_action( 'amp_post_template_head', array( $this, 'rab_add_head_tag' ) );
		}
		add_action( 'wp_head', array( $this, 'rab_add_head_tag' ) );

		// Adding setting page for plugin.
		add_action( 'admin_menu', array( $this, 'rab_add_menu' ) );

		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'rab_add_link' ) );

		add_action( 'admin_init', array( $this, 'rab_setting_display' ) );

		// If you select Show Meta Box on Post Types.
		if ( 0 !== (int) get_option( 'rab-switch' ) ) {
			add_action( 'add_meta_boxes', array( $this, 'rab_add_color_metaboxes' ) );
		}

		// Save the metabox data.
		add_action( 'save_post', array( $this, 'rab_save_meta' ), 1, 2 );
	}

	/**
	 * When Plugin activate that time set default option fields.
	 *
	 * @return void
	 */
	public function rab_active_function() {
		add_option( 'rab-switch', 1 );
		add_option( 'rab-amp-switch', 0 );
	}

	/**
	 * Enqueue plugin assets.
	 *
	 * @return void
	 */
	public function rab_assets() {
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_style( 'rab-admin-css', plugin_dir_url( __FILE__ ) . 'css/rab-admin.css', array(), filemtime( plugin_dir_path( __FILE__ ) . '/css/rab-admin.css' ), false );
		wp_enqueue_script( 'rab-admin-js', plugin_dir_url( __FILE__ ) . 'js/rab-admin.js', array( 'wp-color-picker' ), filemtime( plugin_dir_path( __FILE__ ) . '/js/rab-admin.js' ), false );
	}

	/**
	 * Add Meta tag in HTML page head.
	 *
	 * @return void
	 */
	public function rab_add_head_tag() {
		global $post;
		$enable_rab = get_option( 'rab-switch' );
		if ( empty( $post ) ) {
			$rab_color = get_option( 'rab-color' );
		} else {
			$rab_post      = get_post_type( $post->ID );
			$rab_post_type = get_option( 'rab-post-type' );

			if ( null !== $rab_post_type && is_array( $rab_post_type ) ) {
				foreach ( $rab_post_type as $key => $value ) {
					if ( in_array( $rab_post, $value, true ) ) {
						$color = get_post_meta( $post->ID, 'rab-color', true );
						if ( ! empty( $color ) && null !== $color ) {
							$rab_color = $color;
						} else {
							$rab_color = $value['rab_color'];
						}
					}
				}
			}
		}

		if ( ( empty( $rab_color ) ) || ( is_home() ) || ( function_exists( 'is_shop' ) && is_shop() ) ) {
			$rab_color = get_option( 'rab-color' );
		}

		if ( '0' !== $enable_rab ) {
			printf(
				'<meta name="theme-color" content="%1$s"><meta name="apple-mobile-web-app-capable" content="yes"><meta name="apple-mobile-web-app-status-bar-style" content="black">',
				esc_html( $rab_color )
			);
		}
	}

	/**
	 * Add theme menu for setting page.
	 *
	 * @return void
	 */
	public function rab_add_menu() {
		add_theme_page(
			__( 'Rainbow Address Bar', 'rainbow-address-bar' ),
			__( 'Rainbow Address Bar', 'rainbow-address-bar' ),
			'manage_options',
			'rainbow-address-bar',
			array( $this, 'rab_menu_page' ),
			60
		);
	}

	/**
	 * Add Plugin action link for setting.
	 *
	 * @param [string[]] $links An array of plugin action links.
	 * @return array
	 */
	public function rab_add_link( $links ) {
		return array_merge(
			array(
				'settings' => sprintf(
					'<a href="%1$s">%2$s</a>',
					esc_url( admin_url( 'themes.php?page=rainbow-address-bar' ) ),
					__( 'Settings', 'rainbow-address-bar' )
				),
			),
			$links
		);
	}

	/**
	 * Page for Plugin.
	 */
	public function rab_menu_page() {
		printf( '<div class="wrap">' );
		printf( '<div class="bgcard">' );
		settings_errors();
		printf( '<form method="post" class="rab-option-page" action="options.php">' );
		// add_settings_section callback is displayed here. For every new section we need to call settings_fields.
		settings_fields( 'rab_setting_section' );
		// all the add_settings_field callbacks is displayed here.
		do_settings_sections( 'rainbow-address-bar' );
		// Add the submit button to serialize the options.
		submit_button( __( 'Save Settings', 'rainbow-address-bar' ) );
		printf( '</form></div></div>' );
	}

	/**
	 * Display Settins on setting page.
	 *
	 * @return void
	 */
	public function rab_setting_display() {
		add_settings_section( 'rab_setting_section', esc_html__( 'Rainbow Address Bar', 'rainbow-address-bar' ), array( $this, 'rab_content_callback' ), 'rainbow-address-bar' );

		add_settings_field( 'rab-switch', esc_html__( 'Enable Address Color Bar', 'rainbow-address-bar' ), array( $this, 'rab_color_switch_element' ), 'rainbow-address-bar', 'rab_setting_section' );
		$rab_switch_args = array(
			'type'              => 'string',
			'sanitize_callback' => array( $this, 'rab_sanitize_checkbox' ),
			'default'           => 0,
		);
		register_setting( 'rab_setting_section', 'rab-switch', $rab_switch_args );

		add_settings_field( 'rab-amp-switch', esc_html__( 'Enable Address Color Bar for AMP', 'rainbow-address-bar' ), array( $this, 'rab_amp_switch_element' ), 'rainbow-address-bar', 'rab_setting_section' );
		$rab_amp_switch_args = array(
			'type'              => 'string',
			'sanitize_callback' => array( $this, 'rab_sanitize_checkbox' ),
			'default'           => 0,
		);
		register_setting( 'rab_setting_section', 'rab-amp-switch', $rab_amp_switch_args );

		$setting_args = array(
			'class' => 'rab-post-type-section',
		);
		add_settings_field( 'rab-post-type', esc_html__( 'Enable Meta Box in Post Type', 'rainbow-address-bar' ), array( $this, 'rab_post_type_element' ), 'rainbow-address-bar', 'rab_setting_section', $setting_args );
		$rab_post_type_args = array(
			'type'              => 'array',
			'sanitize_callback' => array( $this, 'rab_sanitize_array' ),
			'default'           => 0,
		);
		register_setting( 'rab_setting_section', 'rab-post-type', $rab_post_type_args );

		add_settings_field( 'rab-color', esc_html__( 'Choose Global Address Color', 'rainbow-address-bar' ), array( $this, 'rab_color_field_element' ), 'rainbow-address-bar', 'rab_setting_section' );
		$rab_color_args = array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'default'           => '#ffffff',
		);
		register_setting( 'rab_setting_section', 'rab-color', $rab_color_args );
	}

	/**
	 * Top Content Callback.
	 *
	 * @return void
	 */
	public function rab_content_callback() {
		esc_html_e( 'Settings to change color of your browser address bar on your mobile devices', 'rainbow-address-bar' );
	}

	/**
	 * Add Post Color field element.
	 *
	 * @return void
	 */
	public function rab_color_field_element() {
		// id and name of form element should be same as the setting name.
		printf( '<input type="text" name="rab-color" id="rab-color" class="rab-post-color" value="%1$s" />', esc_html( get_option( 'rab-color' ) ) );
	}

	/**
	 * Callback for enable Rainbow Address BAr.
	 *
	 * @return void
	 */
	public function rab_color_switch_element() {
		$rab_enable = get_option( 'rab-switch' );
		// id and name of form element should be same as the setting name.
		printf( '<label class="switch"><input type="checkbox" name="rab-switch" id="rab-switch" value="1" %1$s /><span class="slider round"></span></label>', ( ( '0' !== $rab_enable ) ? ( esc_attr( 'checked' ) ) : '' ) );
	}

	/**
	 * Callback for enable AMP Pages.
	 *
	 * @return void
	 */
	public function rab_amp_switch_element() {
		$amp_enable = get_option( 'rab-amp-switch' );
		// id and name of form element should be same as the setting name.
		printf( '<label class="switch"><input type="checkbox" name="rab-amp-switch" id="rab-amp-switch" value="1" %1$s /><span class="slider round"></span></label>', ( ( '0' !== $amp_enable ) ? ( esc_attr( 'checked' ) ) : '' ) );
	}

	/**
	 * Callback for all post types.
	 *
	 * @return void
	 */
	public function rab_post_type_element() {
		$all_post_type = get_post_types(
			array(
				'public' => true,
			)
		);

		// id and name of form element should be same as the setting name.
		$post_selected = get_option( 'rab-post-type' );

		$temp_var = 0;
		foreach ( $all_post_type as  $post_type ) {
			$is_checked = '';
			$post_color = '';
			if ( is_array( $post_selected ) ) {
				foreach ( $post_selected as $key => $value ) {
					if ( in_array( $post_type, $value, true ) ) {
						$is_checked = esc_attr( 'checked' );
						$post_color = $value['rab_color'];
					}
				}
			}
			printf( '<div class="rab-post-list">' );
			printf( '<div class="rab-post-list-input"><label class="switch"><input type="checkbox" name="rab-post-type[%1$s][post_type]" class="rab-post-switch" value="%2$s" %3$s ><span class="slider round"></span></label> %2$s </div>', esc_html( $temp_var ), esc_attr( ( $post_type ) ), esc_html( $is_checked ) );
			printf( '<div class="rab-post-list-button"><input type="text" name="rab-post-type[%1$s][rab_color]" id="rab-post-color" class="rab-post-color" value="%2$s" /></div><br>', esc_html( $temp_var ), esc_html( $post_color ) );
			printf( '</div>' );
			$temp_var++;
		}
		$temp_var = 0;
	}

	/**
	 * Sanitize checkbox value.
	 *
	 * @param [string] $input checkbox value.
	 * @return integer
	 */
	public function rab_sanitize_checkbox( $input ) {
		return ( '1' !== $input ) ? 0 : 1;
	}

	/**
	 * Sanitize Array.
	 *
	 * @param [array] $input array.
	 * @return array
	 */
	public function rab_sanitize_array( $input ) {
		return ( ! is_array( $input ) ) ? '' : $input;
	}

	/**
	 * Add metabox for Address Color Bar.
	 *
	 * @return void
	 */
	public function rab_add_color_metaboxes() {
		$rab_post_type = get_option( 'rab-post-type' );
		foreach ( $rab_post_type as $post_type ) {
			if ( ! empty( $post_type['post_type'] ) && post_type_exists( $post_type['post_type'] ) ) {
				add_meta_box(
					'rab_color_meta',
					__( 'Rainbow Address Bar Color', 'rainbow-address-bar' ),
					array( $this, 'rab_color_meta' ),
					$post_type['post_type'] ,
					'side',
					'high'
				);
			}
		}
	}

	/**
	 * Get color meta field from post.
	 *
	 * @return void
	 */
	public function rab_color_meta() {
		global $post;

		// Nonce field to validate form request came from current site.
		wp_nonce_field( basename( __FILE__ ), 'rab-nonce' );

		// Get the rab_color data if it's already been entered.
		$rab_color = get_option( 'rab-color' );
		if ( null !== get_post_meta( $post->ID, 'rab-color', true ) && '' !== get_post_meta( $post->ID, 'rab-color', true ) ) {
			$rab_color = get_post_meta( $post->ID, 'rab-color', true );
		}

		// Output the field.
		printf( '<input type="text" name="rab-color" id="rab-color" class="rab-post-color" value="%1$s" />', esc_html( $rab_color ) );
	}

	/**
	 * Save the metabox data.
	 *
	 * @param [int]     $post_id Post ID.
	 * @param [WP_Post] $post Post object.
	 * @return Mixed
	 */
	public function rab_save_meta( $post_id, $post ) {
		// Return if the user doesn't have edit permissions.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;
		}

		// Verify this came from the our screen and with proper authorization,
		// because save_post can be triggered at other times.
		if ( ! isset( $_POST['rab-color'] ) || ! isset( $_POST['rab-nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['rab-nonce'] ) ), basename( __FILE__ ) ) ) {
			return $post_id;
		}

		// Now that we're authenticated, time to save the data.
		// This sanitizes the data from the field and saves it into an array $rab_meta.
		$rab_meta['rab-color'] = esc_textarea( sanitize_textarea_field( wp_unslash( $_POST['rab-color'] ) ) );
		// Cycle through the $rab_meta array.
		// Note, in this example we just have one item, but this is helpful if you have multiple.
		foreach ( $rab_meta as $key => $value ) :
			// Don't store custom data twice.
			if ( 'revision' === $post->post_type ) {
				return;
			}

			if ( get_post_meta( $post_id, $key, false ) ) {
				// If the custom field already has a value, update it.
				update_post_meta( $post_id, $key, $value );
			} else {
				// If the custom field doesn't have a value, add it.
				add_post_meta( $post_id, $key, $value );
			}

			if ( ! $value ) {
				// Delete the meta key if there's no value.
				delete_post_meta( $post_id, $key );
			}
		endforeach;
	}
}

new Rainbow_Address_Bar();
