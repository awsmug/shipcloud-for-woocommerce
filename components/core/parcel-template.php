<?php
/**
 * WooCommerce shipcloud.io parcel class
 *
 * Loading parcel functions
 *
 * @author  awesome.ug <very@awesome.ug>, Sven Wagener <sven@awesome.ug>
 * @package WooCommerceShipCloud/Core
 * @version 1.0.0
 * @since   1.0.0
 * @license GPL 2
 *
 * Copyright 2015 (very@awesome.ug)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

if( !defined( 'ABSPATH' ) )
	exit;

class WCSC_Parceltemplate_PostType
{
	/**
	 * @var The Single instance of the class
	 */
	protected static $_instance = NULL;

	/**
	 * Construct
	 */
	private function __construct()
	{
		self::init_hooks();
	}

	/**
	 * Main Instance
	 */
	public static function instance()
	{
		if( is_null( self::$_instance ) )
		{
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Initializing Post type
	 */
	private static function init_hooks()
	{
		add_action( 'init', array( __CLASS__, 'register_post_types' ) );
		add_action( 'admin_menu', array( __CLASS__, 'add_menu' ) );
		add_action( 'add_meta_boxes', array( __CLASS__, 'meta_boxes' ), 10 );
		add_action( 'save_post', array( __CLASS__, 'save' ) );

		add_action( 'admin_notices', array( __CLASS__, 'notice_area' ) );

		add_filter( 'post_updated_messages', array( __CLASS__, 'remove_all_messages' ) );
	}

	/**
	 * Registering Post type
	 */
	public static function register_post_types()
	{
		$labels = array(
			'name'               => _x( 'Parcel Templates', 'post type general name', 'woocommerce-shipcloud' ),
			'singular_name'      => _x( 'Parcel Template', 'post type singular name', 'woocommerce-shipcloud' ),
			'menu_name'          => _x( 'Parcel Templates', 'admin menu', 'woocommerce-shipcloud' ),
			'name_admin_bar'     => _x( 'Parcel Template', 'add new on admin bar', 'woocommerce-shipcloud' ),
			'add_new'            => _x( 'Add New', 'parcel', 'woocommerce-shipcloud' ),
			'add_new_item'       => __( 'Add New Parcel Template', 'woocommerce-shipcloud' ),
			'new_item'           => __( 'New Parcel Template', 'woocommerce-shipcloud' ),
			'edit_item'          => __( 'Edit Parcel Template', 'woocommerce-shipcloud' ),
			'view_item'          => __( 'View Parcel Template', 'woocommerce-shipcloud' ),
			'all_items'          => __( 'All Parcel Templates', 'woocommerce-shipcloud' ),
			'search_items'       => __( 'Search Parcel Templates', 'woocommerce-shipcloud' ),
			'parent_item_colon'  => __( 'Parent Parcel Templates:', 'woocommerce-shipcloud' ),
			'not_found'          => __( 'No Parcel Template found.', 'woocommerce-shipcloud' ),
			'not_found_in_trash' => __( 'No Parcel Templates found in Trash.', 'woocommerce-shipcloud' )
		);

		$args = array(
			'labels'             => $labels,
			'description'        => __( 'Description', 'woocommerce-shipcloud' ),
			'public'             => FALSE,
			'publicly_queryable' => FALSE,
			'show_ui'            => TRUE,
			'show_in_menu'       => 'edit.php?post_type=shop_order',
			'query_var'          => TRUE,
			'capability_type'    => 'post',
			'has_archive'        => FALSE,
			'hierarchical'       => FALSE,
			'menu_position'      => NULL,
			'supports'           => FALSE
		);

		register_post_type( 'sc_parcel_template', $args );
	}

	/**
	 * Adding Parcels to Woo Menu
	 */
	public static function add_menu()
	{
		add_submenu_page( 'edit.php?post_type=product', __( 'Parcel Templates', 'woocommerce-shipcloud' ), __( 'Parcel Templates', 'woocommerce-shipcloud' ), 'manage_options', 'edit.php?post_type=sc_parcel_template' );
	}

	/**
	 * Adding Metaboxes
	 */
	public static function meta_boxes()
	{
		add_meta_box( 'box-tools', __( 'Tools', 'woocommerce-shipcloud' ), array(
			                         __CLASS__,
			                         'box_settings'
		                         ), 'sc_parcel_template', 'normal' );
	}

	public static function box_settings()
	{
		global $post;

		if( 'sc_parcel_template' != $post->post_type )
		{
			return;
		}

		$options = get_option( 'woocommerce_shipcloud_settings' );
		$shipcloud_api = new Woocommerce_Shipcloud_API( $options[ 'api_key' ] );

		$carriers = wcsc_get_carriers();

		$selected_carrier = get_post_meta( $post->ID, 'carrier', TRUE );
		$width = get_post_meta( $post->ID, 'width', TRUE );
		$height = get_post_meta( $post->ID, 'height', TRUE );
		$length = get_post_meta( $post->ID, 'length', TRUE );
		$weight = get_post_meta( $post->ID, 'weight', TRUE );

		?>
		<div id="shipcloud-parcel-settings">
			<table class="form-table">
				<tbody>
				<tr>
					<th><label for="test"><?php _e( 'Width', 'woocommerce-shipcloud' ); ?></label></th>
					<td>
						<input type="text" name="width" value="<?php echo $width; ?>"/> <?php _e( 'cm', 'woocommerce-shipcloud' ); ?>
					</td>
				</tr>
				<tr>
					<th><label for="test"><?php _e( 'Height', 'woocommerce-shipcloud' ); ?></label></th>
					<td>
						<input type="text" name="height" value="<?php echo $height; ?>"/> <?php _e( 'cm', 'woocommerce-shipcloud' ); ?>
					</td>
				</tr>
				<tr>
					<th><label for="test"><?php _e( 'Length', 'woocommerce-shipcloud' ); ?></label></th>
					<td>
						<input type="text" name="length" value="<?php echo $length; ?>"/> <?php _e( 'cm', 'woocommerce-shipcloud' ); ?>
					</td>
				</tr>
				<tr>
					<th><label for="test"><?php _e( 'Weight', 'woocommerce-shipcloud' ); ?></label></th>
					<td>
						<input type="text" name="weight" value="<?php echo $weight; ?>"/> <?php _e( 'kg', 'woocommerce-shipcloud' ); ?>
					</td>
				</tr>
				<tr>
					<th><label for="carrier"><?php _e( 'Shipping Company', 'woocommerce-shipcloud' ); ?></label></th>
					<td>
						<select name="carrier">
							<option value="none"><?php _e( '[ Select a Carrier ]', 'woocommerce-shipcloud' ); ?></option>
							<?php foreach( $carriers AS $name => $display_name ): ?>
								<?php if( $selected_carrier == $name ): $selected = ' selected="selected"';
								else: $selected = ''; endif; ?>
								<option value="<?php echo $name; ?>"<?php echo $selected; ?>><?php echo $display_name; ?></option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
				</tbody>
			</table>
		</div>
		<?php
	}

	/**
	 * Saving data
	 *
	 * @param int $post_id
	 *
	 * @since 1.0.0
	 */
	public static function save( $post_id )
	{
		global $wpdb;

		if( wp_is_post_revision( $post_id ) )
		{
			return;
		}

		if( !array_key_exists( 'post_type', $_POST ) )
		{
			return;
		}

		if( 'sc_parcel_template' != $_POST[ 'post_type' ] )
		{
			return;
		}

		if( !array_key_exists( 'carrier', $_POST ) )
		{
			return;
		}

		$carrier = $_POST[ 'carrier' ];
		$width = $_POST[ 'width' ];
		$height = $_POST[ 'height' ];
		$length = $_POST[ 'length' ];
		$weight = $_POST[ 'weight' ];

		$post_title = wcsc_get_carrier_display_name( $carrier ) . ' - ' . $width . ' x ' . $height . ' x ' . $length . ' ' . __( 'cm', 'woocommerce-shipcloud' ) . ' ' . $weight . __( 'kg', 'woocommerce-shipcloud' );

		$where = array( 'ID' => $post_id );
		$wpdb->update( $wpdb->posts, array( 'post_title' => $post_title ), $where );

		update_post_meta( $post_id, 'carrier', $carrier );
		update_post_meta( $post_id, 'width', $width );
		update_post_meta( $post_id, 'height', $height );
		update_post_meta( $post_id, 'length', $length );
		update_post_meta( $post_id, 'weight', $weight );
	}

	public static function notice_area()
	{
		echo '<div class="shipcloud-message updated" style="display: none;"><p class="info"></p></div>';
	}

	public static function remove_all_messages( $messages )
	{
		global $post;

		if( get_class( $post ) != 'WP_Post' )
		{
			return $messages;
		}

		if( 'sc_parcel_template' == $post->post_type )
		{
			return array();
		}
	}
}

WCSC_Parceltemplate_PostType::instance();