<?php
/**
 * WooCommerce shipcloud.io postboxes
 * Loading postboxes
 *
 * @author  awesome.ug <support@awesome.ug>, Sven Wagener <sven@awesome.ug>
 * @package WooCommerceShipCloud/Woo
 * @version 1.0.0
 * @since   1.2.1
 * @license GPL 2
 *          Copyright 2017 (support@awesome.ug)
 *          This program is free software; you can redistribute it and/or modify
 *          it under the terms of the GNU General Public License, version 2, as
 *          published by the Free Software Foundation.
 *          This program is distributed in the hope that it will be useful,
 *          but WITHOUT ANY WARRANTY; without even the implied warranty of
 *          MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *          GNU General Public License for more details.
 *          You should have received a copy of the GNU General Public License
 *          along with this program; if not, write to the Free Software
 *          Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WC_Shipcloud_Order_Bulk
 *
 * Bulk functionalities for Label creation
 *
 * @since   1.2.1
 */
class WC_Shipcloud_Order_Bulk {

  const FORM_BULK = 'wcsc_order_bulk_label';
  const FORM_PICKUP_REQUEST = 'shipcloud_create_pickup_request';
  const BUTTON_PDF = 'wscs_order_bulk_pdf';
  const BUTTON_PICKUP_REQUEST = 'shipcloud_order_bulk_pickup_request';

	/**
	 * WC_Shipcloud_Order_Bulk constructor.
	 *
	 * @since   1.2.1
	 */
	public function __construct() {
		$this->init_hooks();
	}

	/**
     * Backward compatibility to WC2.
     *
	 * The current WC3 has almost anything covered by getter methods
	 * while the old WC2 used simple fields for that.
	 * This layer allows using the old syntax
	 * and makes it compatible with the new one.
     *
	 * @param $name
	 *
	 * @return bool
	 */
	public function __isset( $name ) {
		return property_exists( '\\WC_Order', $name ) || method_exists( $this->get_wc_order(), 'get_' . $name );
	}

	/**
	 * Initializing hooks and filters
	 *
	 * @since   1.2.1
	 */
	private function init_hooks() {
		add_action( 'admin_print_footer_scripts', array( $this, 'admin_print_footer_scripts' ) );
		add_action( 'admin_print_footer_scripts', array( $this, 'attach_downloads' ) );
    add_action( 'load-edit.php', array( $this, 'handle_wcsc_order_bulk' ) );
		add_action( 'load-edit.php', array( $this, 'load_edit' ) );

		add_filter( 'bulk_actions-edit-shop_order', array( $this, 'add_bulk_actions' ) );
	}

	/**
	 * Handling submit after bulk action
	 *
	 * @since   1.2.1
	 */
	public function handle_wcsc_order_bulk() {
    WC_Shipcloud_Shipping::log('handle_wcsc_order_bulk', ['format' => 'full']);

    if ( ! is_admin() || ! get_current_screen() || 'edit-shop_order' !== get_current_screen()->id ) {
      WC_Shipcloud_Shipping::log('We are not on the edit-shop_order page. So leaving now.');
      // None of our business.
			return;
    }

    $request = $_GET; // XSS: OK.
    WC_Shipcloud_Shipping::log('request:');
    WC_Shipcloud_Shipping::log(json_encode($request));

    if (isset($request['action']) || isset($request['action2'])) {
      if ("-1" == $request['action']) {
        if ("-1" == $request['action2']) {
          WC_Shipcloud_Shipping::log('action and action2 were -1. Leaving.');
          return;
        } else {
          $action = $request['action2'];
        }
      } else {
        $action = $request['action'];
      }
    } else {
      WC_Shipcloud_Shipping::log('Neither action nor action2 were set. Leaving.');
      return;
    }

    WC_Shipcloud_Shipping::log('action: '.$action);
    if ( self::FORM_BULK == $action ) {
      WC_Shipcloud_Shipping::log('creating labels in bulk');
      $this->create_pdf( $request );
      return;
    } elseif ( self::FORM_PICKUP_REQUEST == $action ) {
      WC_Shipcloud_Shipping::log('creating a pickup request');
      $this->create_pickup_request( $request );
      return;
    } else {
      WC_Shipcloud_Shipping::log(sprintf( __( 'Unknown bulk action called. Request: %s', 'shipcloud-for-woocommerce' ), json_encode($request) ));
      return;
    }
	}

	/**
	 * Adding bulk action to dropdown
	 *
	 * @since   1.2.1
	 *
	 * @param array $actions Bulk actions
	 *
	 * @return array $actions Bulk actions with own Actions
	 */
	public function add_bulk_actions( $actions ) {
		$actions['wcsc_order_bulk_label'] = __( 'Create shipping labels', 'shipcloud-for-woocommerce' );

        // only applicable for WooCommerce 3
        if (class_exists('WC_DateTime')) {
            $actions[self::FORM_PICKUP_REQUEST] = __( 'Create pickup request', 'shipcloud-for-woocommerce' );
        }

		return $actions;
	}

	/**
	 * Adding Footer Scripts
	 *
	 * @since   1.2.1
	 */
	public function admin_print_footer_scripts() {

		if (
			false === get_current_screen() instanceof \WP_Screen
			|| 'edit-shop_order' !== get_current_screen()->id
		) {
			// Not the context for bulk action so we won't print the bulk template.
			return;
		}

		require_once WCSC_FOLDER . '/includes/shipcloud/block-order-labels-bulk.php';

		$block = new WooCommerce_Shipcloud_Block_Order_Labels_Bulk(
			WCSC_COMPONENTFOLDER . '/block/order-labels-bulk.php',
			WC_Shipcloud_Order::create_order( null ),
			_wcsc_carriers_get(),
			wcsc_api()
		);

		$block->dispatch();

        require WCSC_COMPONENTFOLDER . '/block/pickup-request-form.php';
        require WCSC_COMPONENTFOLDER . '/block/bulk-action-template.php';
	}

	/**
	 * Loading Scripts
	 *
	 * @since   1.2.1
	 */
	public function load_edit() {
        wp_register_script(
            'shipcloud_bulk_actions',
            WCSC_URLPATH . '/includes/js/bulk-actions.js',
            array( 'jquery', 'wcsc-multi-select' )
        );
        wp_enqueue_script( 'shipcloud_bulk_actions', false, array(), false, true );
	}

	/**
     * Create multiple labels.
     *
	 * @param array $request
	 */
	protected function create_label( $request ) {
		$succeeded = 0;
		foreach ( $request['post'] as $order_id ) {
			if ( $this->create_label_for_order( $order_id, $request ) ) {
				$succeeded ++;
			}
		}

		WooCommerce_Shipcloud::admin_notice(
			sprintf( 'Created %d labels.', $succeeded ), 'updated'
		);
	}

	/**
	 * Download the label PDF.
	 *
	 * @param int $order_id
	 * @param string $url URL to the PDF as given by the API.
     * @param string $prefix prefix that will be added to the filename
	 *
	 * @return string
	 */
	protected function save_pdf_to_storage( $order_id, $url, $prefix ) {
		$path = $this->get_storage_path( 'order' . DIRECTORY_SEPARATOR . $order_id ).
            DIRECTORY_SEPARATOR.
            $prefix.
            md5( $url ).
            '.pdf';

		if ( file_exists( $path ) ) {
			WC_Shipcloud_Shipping::log('pdf file already exists');
			// Might be already downloaded, so we won't overwrite it.
			return $path;
		}

		$pdf_content = wp_remote_retrieve_body( wp_remote_get( $url ) );

		if ( ! $pdf_content ) {
			WC_Shipcloud_Shipping::log('Couldn\'t download pdf');
			// No content, so we refuse to continue.
			throw new \RuntimeException( 'Could not download PDF - no content delivered.' );
		}

		if ( ! $this->get_filesystem()->put_contents( $path, $pdf_content ) ) {
			WC_Shipcloud_Shipping::log('Couldn\'t store downloaded PDF contents.');
			throw new \RuntimeException( 'Could not store downloaded PDF contents.' );
		}

		return $path;
	}

	/**
	 * Get the URL to some Shipcloud files.
	 *
	 * @param null|string $suffix Path and name of the file.
	 *
	 * @return string
	 */
	protected function get_storage_url( $suffix = null ) {
		$wp_upload_dir = wp_upload_dir();
		$url           = $wp_upload_dir['baseurl'] . '/' . 'shipcloud-woocommerce';

		if ( null !== $suffix && $suffix ) {
			// Add suffix but disallow hopping in other path.
			$url .= '/' . str_replace( '..', '', $suffix );
		}

		return $url;
	}

  /**
   * Add a new download for admin.
   *
   * @param $url
   */
  public static function admin_download( $url ) {
    $shipcloud_downloads = get_transient( 'shipcloud_downloads' );
    if ( !isset($shipcloud_downloads) ) {
      $shipcloud_downloads = array();
    }

    $shipcloud_downloads[ md5( $url ) ] = $url;
    set_transient( 'shipcloud_downloads', $shipcloud_downloads );
  }

  /**
   * Dispatch downloads to frontend.
   */
  public function attach_downloads() {
    if (shipcloud_admin_is_on_order_overview_page()) {
      $shipcloud_downloads = get_transient( 'shipcloud_downloads' );

      foreach ( (array) $shipcloud_downloads as $key => $download ) {
    ?>
        <script type="application/javascript">
          (window.open('<?php echo $download ?>', '_blank')).focus();
        </script>
    <?php
      }
      set_transient( 'shipcloud_downloads', array() );
    }
	}

	/**
	 * Ask API for labels and merge their PDF into one.
	 *
	 * @param $request
	 */
	protected function create_pdf( $request ) {
    WC_Shipcloud_Shipping::log('create_pdf called');

    if ( ! $request['post'] ) {
      // Nothing selected or no post given, so we don't have anything to do.
      return;
    }

    $pdf_basename = sha1( implode( ',', $request['post'] ) ) . '.pdf';

    WooCommerce_Shipcloud::load_fpdf();

    /** @var WP_Filesystem_Base $wp_filesystem */
    global $wp_filesystem;

    if ( ! $wp_filesystem ) {
        WP_Filesystem();
    }

    $shipping_label_count = $customs_declaration_count = 0;
    $shipping_label_merger = new \iio\libmergepdf\Merger();
    $customs_declaration_merger = new \iio\libmergepdf\Merger();

    foreach ( $request['post'] as $order_id ) {
      $shipments = get_post_meta( $order_id, 'shipcloud_shipment_data' );

      if ( isset($request['shipcloud_bulk_only_one_shipping_label'] )) {
        // check to see if there's already a shipping label present
        foreach ( $shipments as $shipment ) {
          if ( !is_null($shipment['label_url'])) {
            WC_Shipcloud_Shipping::log(sprintf('found shipment with label_url for order #%d - skipping', $order_id));
            WooCommerce_Shipcloud::admin_notice(
              sprintf('Skipped label creation for order #%d, because there was already a shipping label.', $order_id),
              'error' );
            continue(2);
          }
        }
      }

      $current       = $this->create_label_for_order( $order_id, $request );

      if ( ! $current || ! $current->getLabelUrl() ) {
        WC_Shipcloud_Shipping::log(
          sprintf( 'There was an error while trying to create the label for order #%d.', $order_id )
        );

        continue;
      }

      try {
        // Storing label.
        WC_Shipcloud_Shipping::log('Trying to store shipping labels');
        $path_to_shipping_label = $this->save_pdf_to_storage(
                    $order_id,
                    $current->getLabelUrl(),
                    'shipping_label'
                );
        $shipping_label_merger->addFromFile( $path_to_shipping_label );
        $shipping_label_count++;
      } catch ( \RuntimeException $e ) {
        $error_message = sprintf(
          __( 'Couldn’t save label for order #%d to disk: %s' ),
          $order_id,
          str_replace( "\n", ', ', $e->getMessage() )
        );

        WC_Shipcloud_Shipping::log( json_encode($error_message) );
        WooCommerce_Shipcloud::admin_notice( $error_message, 'error' );

        continue;
      }

      if (null !== $current->getCustomsDeclarationDocumentUrl()) {
        // Storing customs declaration.
        try {
            WC_Shipcloud_Shipping::log('Trying to store customs declaration documents');
            $path_to_customs_declaration = $this->save_pdf_to_storage(
                $order_id,
                $current->getCustomsDeclarationDocumentUrl(),
                'customs_declaration'
            );
            $customs_declaration_merger->addFromFile( $path_to_customs_declaration );
            $customs_declaration_count++;
        } catch ( \RuntimeException $e ) {
          $error_message = sprintf(
            __( 'Couldn’t save customs declaration documents for order #%d to disk: %s' ),
            $order_id,
            str_replace( "\n", ', ', $e->getMessage() )
          );

          WC_Shipcloud_Shipping::log( json_encode($error_message) );
          WooCommerce_Shipcloud::admin_notice( $error_message, 'error' );

          continue;
        }
      }
    }
    WC_Shipcloud_Shipping::log('Done looping through order ids and creating labels');

    if (0 !== $shipping_label_count) {
      WC_Shipcloud_Shipping::log('Merging shipping labels');

      $shipping_labels_pdf_content = '';
      $shipping_labels_pdf_file =
          $this->get_storage_path( 'labels' ).
          DIRECTORY_SEPARATOR.
          'merged_shipping_labels_'.
          $pdf_basename;
      $shipping_labels_pdf_url =
          $this->get_storage_url( 'labels' ).
          DIRECTORY_SEPARATOR.
          'merged_shipping_labels_'.
          $pdf_basename;

      try {
          $shipping_labels_pdf_content = $shipping_label_merger->merge();
      } catch (\Exception $e) {
          WC_Shipcloud_Shipping::log('Couldn\'t merge shipping label pdf files.');
          WC_Shipcloud_Shipping::log(print_r($e, true));
      }

      if ( !$shipping_labels_pdf_content ) {
          WooCommerce_Shipcloud::admin_notice( __( 'Could not compose labels into one PDF.', 'shipcloud-for-woocommerce' ), 'error' );
          return;
      }

      $wp_filesystem->put_contents( $shipping_labels_pdf_file, $shipping_labels_pdf_content );
      static::admin_download( $shipping_labels_pdf_url );

      $download_message = sprintf(
          'Shipping labels can be downloaded using this URL: %s',
          '<a href="' . esc_attr( $shipping_labels_pdf_url ) . '" target="_blank">' . esc_html( $shipping_labels_pdf_url ) . '</a>'
      );
      WooCommerce_Shipcloud::admin_notice( $download_message, 'updated' );
    }

    if ($customs_declaration_count > 0) {
      WC_Shipcloud_Shipping::log('Merging customs declarations');

      $customs_declarations_pdf_content = '';
      $customs_declarations_pdf_file =
          $this->get_storage_path( 'labels' ).
          DIRECTORY_SEPARATOR.
          'merged_customs_declarations_'.
          $pdf_basename;
      $customs_declaration_pdf_url =
          $this->get_storage_url( 'labels' ).
          DIRECTORY_SEPARATOR.
          'merged_customs_declarations_'.
          $pdf_basename;

      try {
          $customs_declaration_pdf_content = $customs_declaration_merger->merge();
      } catch (\Exception $e) {
          WC_Shipcloud_Shipping::log('Couldn\'t merge customs declaration documents pdf files.');
          WC_Shipcloud_Shipping::log(print_r($e, true));
      }

      if ( !$customs_declaration_pdf_content ) {
          WooCommerce_Shipcloud::admin_notice( __( 'Could not compose customs declaration documents into one PDF.', 'shipcloud-for-woocommerce' ), 'error' );
          return;
      }

      $wp_filesystem->put_contents( $customs_declarations_pdf_file, $customs_declaration_pdf_content );
      static::admin_download( $customs_declaration_pdf_url );

      $download_message = sprintf(
          'Customs declaration documents can be downloaded using this URL: %s',
          '<a href="' . esc_attr( $customs_declaration_pdf_url ) . '" target="_blank">' . esc_html( $customs_declaration_pdf_url ) . '</a>'
      );
      WC_Shipcloud_Shipping::log('Sending admin notice: '.$download_message);
      WooCommerce_Shipcloud::admin_notice( $download_message, 'updated' );
    }
	}

	/**
	 * Ask API for a new label.
	 *
	 * @param $order_id
	 * @param $request
	 *
	 * @return array|\Shipcloud\Domain\Shipment
	 */
	protected function create_label_for_order( $order_id, $request ) {
    WC_Shipcloud_Shipping::log('create_label_for_order called');
    WC_Shipcloud_Shipping::log('order_id: '.$order_id);

    $order = WC_Shipcloud_Order::create_order( $order_id );
    $request = $order->sanitize_reference_number($request);
    $carrier = $request['shipcloud_carrier'];

		$use_calculated_weight = isset($request['shipcloud_use_calculated_weight']) ? $request['shipcloud_use_calculated_weight'] : '';
		if ( $use_calculated_weight == 'use_calculated_weight' ) {
			$request['parcel_weight'] = $order->get_calculated_weight();
		}
		$package = new \Shipcloud\Domain\Package(
			wc_format_decimal( $request['parcel_length'] ),
			wc_format_decimal( $request['parcel_width'] ),
			wc_format_decimal( $request['parcel_height'] ),
			wc_format_decimal( $request['parcel_weight'] ),
			$request['shipcloud_carrier_package']
		);

        $shipment_repo = _wcsc_container()->get( '\\Shipcloud\\Repository\\ShipmentRepository' );

        if (!empty($request['shipment']['additional_services']['cash_on_delivery']['currency'])) {
            $currency = $request['shipment']['additional_services']['cash_on_delivery']['currency'];
        } elseif (method_exists($order, 'get_currency')) {
            $currency = $order->get_currency();
        } elseif (method_exists($order->get_wc_order(), 'get_currency')) {
            $currency = $order->get_wc_order()->get_currency();
        } else {
            $currency = $order->get_wc_order()->get_order_currency();
        }

        $reference_number = '';
        if (!empty($request['shipment']['additional_services']['cash_on_delivery']['reference1'])) {
            $reference_number = $request['shipment']['additional_services']['cash_on_delivery']['reference1'];
        } elseif (!empty($request['reference_number'])) {
            $reference_number = $request['reference_number'];
        }

        $bank_information = new \Shipcloud\Domain\ValueObject\BankInformation(
            $request['shipment']['additional_services']['cash_on_delivery']['bank_name'],
            $request['shipment']['additional_services']['cash_on_delivery']['bank_code'],
            $request['shipment']['additional_services']['cash_on_delivery']['bank_account_holder'],
            $request['shipment']['additional_services']['cash_on_delivery']['bank_account_number']
        );

        $additional_services = $shipment_repo->additional_services_from_request(
            $request['shipment']['additional_services'],
            $carrier,
            $order
        );

        $customs_declaration = '';
        if ($request['customs_declaration']['shown'] === 'true') {
            unset($request['customs_declaration']['shown']);

            $customs_declaration = $request['customs_declaration'];

            $customs_declaration['currency'] = 'EUR';
            $customs_declaration['total_value_amount'] =
                $order->get_wc_order()->get_total() - $order->get_wc_order()->get_shipping_total();

            if (array_key_exists('invoice_number', $request['customs_declaration'])) {
                $invoice_number = $request['customs_declaration']['invoice_number'];

                if ( has_shortcode( $invoice_number, 'shipcloud_orderid' ) ) {
                    $customs_declaration['invoice_number'] = str_replace('[shipcloud_orderid]', $order_id, $invoice_number);
                }
            }

            $customs_declaration['items'] = array();
            $order_data = $order->get_wc_order()->get_data();

            foreach ( $order_data['line_items'] as $line_item ) {
                $product = $line_item->get_product();

                $hs_tariff_number = get_post_meta( $product->get_id(), 'shipcloud_hs_tariff_number', true );
                $origin_country = get_post_meta( $product->get_id(), 'shipcloud_origin_country', true );

                $item = array(
                    'description' => $product->get_title(),
                    'origin_country' => isset($origin_country) ? $origin_country : '',
                    'quantity' => $line_item->get_quantity(),
                    'value_amount' => $line_item->get_total(),
                    'hs_tariff_number' => isset($hs_tariff_number) ? $hs_tariff_number : '',
                );

                if( $product->has_weight() ) {
                    $item['net_weight'] = $product->get_weight();
                }

                array_push($customs_declaration['items'], $item);
            }
        }

        $notification_email = '';
        if ( isset($request['shipcloud_notification_email_checkbox'] )) {
          if ( isset($request['shipcloud_notification_email'] ) && '' != $request['shipcloud_notification_email']) {
            $notification_email = $request['shipcloud_notification_email'];
          } else {
            $notification_email = $order->get_email_for_notification();
          }
        }

    $data = array(
      'to'                    => $order->get_recipient(),
      'from'                  => $order->get_sender(),
      'package'               => $package,
      'carrier'               => $carrier,
      'service'               => $request['shipcloud_carrier_service'],
      'reference_number'      => $reference_number,
      'description'           => $request['other_description'],
      'notification_email'    => $notification_email,
      'additional_services'   => $additional_services,
      'customs_declaration'   => $customs_declaration,
      'create_shipping_label' => true,
    );

		try {
      $pickup = WC_Shipcloud_Order::handle_pickup_request($request);
      if (!empty($pickup)) {
          $data['pickup'] = $pickup;
      }

      WC_Shipcloud_Shipping::log('calling shipcloud api to create label with the following data: '.json_encode($data));
			$shipment = _wcsc_api()->shipment()->create( array_filter( $data ) );

			$order->get_wc_order()->add_order_note( __( 'shipcloud.io label was created.', 'woocommerce-shipcloud' ) );

			WC_Shipcloud_Shipping::log( 'Order #' . $order->get_wc_order()->get_order_number() . ' - Created shipment successful (' . wcsc_get_carrier_display_name( $carrier ) . ')' );

			$parcel_title = wcsc_get_carrier_display_name( $carrier )
							. ' - '
							. $request['parcel_width']
							. __( 'x', 'woocommerce-shipcloud' )
							. $request['parcel_height']
							. __( 'x', 'woocommerce-shipcloud' )
							. $request['parcel_length']
							. __( 'cm', 'woocommerce-shipcloud' )
							. ' '
							. $request['parcel_weight']
							. __( 'kg', 'woocommerce-shipcloud' );

			$label_for_order = array(
				'id'                  => $shipment->getId(),
				'carrier_tracking_no' => $shipment->getCarrierTrackingNo(),
				'tracking_url'        => $shipment->getTrackingUrl(),
				'label_url'           => $shipment->getLabelUrl(),
				'price'               => $shipment->getPrice(),
				'parcel_id'           => $shipment->getId(),
				'parcel_title'        => $parcel_title,
				'carrier'             => $carrier,
				'width'               => wc_format_decimal( $request['parcel_width'] ),
				'height'              => wc_format_decimal( $request['parcel_height'] ),
				'length'              => wc_format_decimal( $request['parcel_length'] ),
				'weight'              => wc_format_decimal( $request['parcel_weight'] ),
				'additional_services' => $additional_services,
                'customs_declaration' => $shipment->getCustomsDeclaration(),
				'date_created'        => time(),
			);

            if (!empty($pickup)) {
                $label_for_order['pickup'] = $pickup;
            }

			$label_for_order = array_merge( $label_for_order, $order->get_sender( 'sender_' ) );
			$label_for_order = array_merge( $label_for_order, $order->get_recipient( 'recipient_' ) );

			add_post_meta( $order_id, 'shipcloud_shipment_ids', $label_for_order['id'] );
			add_post_meta( $order_id, 'shipcloud_shipment_data', $label_for_order );
		} catch ( \Exception $e ) {
			$error_message = sprintf(
				__( 'No label for order #%d created: %s' ),
				$order_id,
				str_replace( "\n", ', ', $e->getMessage() )
			);

      WC_Shipcloud_Shipping::log( json_encode($error_message) );
			WooCommerce_Shipcloud::admin_notice( $error_message, 'error' );

			return array();
		}

		return $shipment;
	}

    /*
     * Create pickup request at shipcloud
     *
     * @since 1.9.0
     *
     * @param $request
     */
    protected function create_pickup_request($request) {
      try {
        $pickup_time = self::handle_pickup_request($request);
      } catch ( \Exception $e ) {
        $error_message = sprintf(
          __( 'Pickup request couldn\'t be created: %s' ),
          str_replace( "\n", ', ', $e->getMessage() )
        );

        WC_Shipcloud_Shipping::log( json_encode($error_message) );
        WooCommerce_Shipcloud::admin_notice( $error_message, 'error' );
        return;
      }

        $pickup_time = array_shift($pickup_time);
        $pickup_request_params = array();

        foreach ( $request['post'] as $order_id ) {
            $order = WC_Shipcloud_Order::create_order( $order_id );
            $shipments = get_post_meta( $order->ID, 'shipcloud_shipment_data' );
            foreach ( $shipments as $shipment ) {
                $shipment_id = $shipment['id'];
                $carrier = $shipment['carrier'];

                if ( !array_key_exists('pickup_request', $shipment) ) {
                    if ( !array_key_exists($carrier, $pickup_request_params) ) {
                        $pickup_request_params[$carrier] = array();
                    }
                    array_push($pickup_request_params[$carrier], $shipment_id);
                } else {
                    WooCommerce_Shipcloud::admin_notice( sprintf( __( 'No pickup request for shipment with id %s created, because there was already one', 'shipcloud-for-woocommerce' ), $shipment_id ), 'error' );
                }
            }
        }

        foreach ( $pickup_request_params as $carrier => $shipment_ids) {
            $shipment_id_hashes = array();
            foreach ( $shipment_ids as $shipment_id ) {
                array_push($shipment_id_hashes, array(
                    'id' => $shipment_id
                ));
            }

            $data = array(
                'carrier' => $carrier,
                'pickup_time' => $pickup_time,
                'shipments' => $shipment_id_hashes,
            );

            $pickup_address = array_filter($request['pickup_address']);
            // check to see if there was anything more send than the country code
            if ( count($pickup_address) > 1 ) {
                $data['pickup_address'] = $pickup_address;
            }

            $pickup_request = _wcsc_container()->get( '\\Woocommerce_Shipcloud_API' )->create_pickup_request($data);
            if ( is_wp_error( $pickup_request ) ) {
                WC_Shipcloud_Shipping::log( sprintf( __( 'Error while creating the pickup request: %s', 'shipcloud-for-woocommerce' ), $pickup_request->get_error_message() ) );
                WooCommerce_Shipcloud::admin_notice( sprintf( __( 'Error while creating the pickup request: %s', 'shipcloud-for-woocommerce' ), $pickup_request->get_error_message() ), 'error' );
            } else {
                WC_Shipcloud_Shipping::log( sprintf( __( 'Pickup request created with id %s for shipment with id %s', 'shipcloud-for-woocommerce' ), $pickup_request['id'], $shipment['id']) );
                WooCommerce_Shipcloud::admin_notice( __( 'Pickup requests created', 'shipcloud-for-woocommerce') );

                // let's update the shipment_data with the pickup requests
                foreach ( $request['post'] as $order_id ) {
                    $order = WC_Shipcloud_Order::create_order( $order_id );
                    $shipments = get_post_meta( $order->ID, 'shipcloud_shipment_data' );

                    // remove shipments element from pickup_request
                    unset($pickup_request['shipments']);

                    foreach ( $shipments as $shipment ) {
                        if (in_array($shipment['id'], $shipment_ids)) {
                            $new_data = array_merge(
                                $shipment,
                                array(
                                    'pickup_request' => $pickup_request
                                )
                            );
                            update_post_meta( $order->ID, 'shipcloud_shipment_data', $new_data, $shipment );
                        }
                    }
                }
            }
        }
    }

        /**
    * Create a single pickup request
    *
    * @since 1.9.0
    */
    public static function handle_pickup_request( $data ) {
      WC_Shipcloud_Shipping::log('function bulk handle_pickup_request called');
      WC_Shipcloud_Shipping::log('with data: '.json_encode($data));

      if (
        !empty($data['pickup']['pickup_earliest_date']) && !empty($data['pickup']['pickup_latest_date']) && (
          empty($data['pickup']['pickup_earliest_time_hour']) || empty($data['pickup']['pickup_earliest_time_minute']) ||
          empty($data['pickup']['pickup_latest_time_hour']) || empty($data['pickup']['pickup_latest_time_minute'])
        )
      ) {
        $error_message = __( 'Please provide a pickup time', 'shipcloud-for-woocommerce' );
        \WC_Shipcloud_Shipping::log( $error_message );

        throw new \UnexpectedValueException( $error_message );
      }

      $pickup = array();
      $pickup_earliest_date = isset($data['pickup']['pickup_earliest_date']) ? $data['pickup']['pickup_earliest_date'] : '';
      $pickup_earliest_time_hour = isset($data['pickup']['pickup_earliest_time_hour']) ? $data['pickup']['pickup_earliest_time_hour'] : '';
      $pickup_earliest_time_minute = isset($data['pickup']['pickup_earliest_time_minute']) ? $data['pickup']['pickup_earliest_time_minute'] : '';
      $pickup_latest_date = isset($data['pickup']['pickup_latest_date']) ? $data['pickup']['pickup_latest_date'] : '';
      $pickup_latest_time_hour = isset($data['pickup']['pickup_latest_time_hour']) ? $data['pickup']['pickup_latest_time_hour'] : '';
      $pickup_latest_time_minute = isset($data['pickup']['pickup_latest_time_minute']) ? $data['pickup']['pickup_latest_time_minute'] : '';

      $pickup_earliest = $pickup_earliest_date.' '.$pickup_earliest_time_hour.':'.$pickup_earliest_time_minute;
      $pickup_latest = $pickup_latest_date.' '.$pickup_latest_time_hour.':'.$pickup_latest_time_minute;

      WC_Shipcloud_Shipping::log('pickup_earliest: '.json_encode($pickup_earliest));
      WC_Shipcloud_Shipping::log('pickup_latest: '.json_encode($pickup_latest));
      try {
          $pickup_earliest = new WC_DateTime( $pickup_earliest, new DateTimeZone( 'Europe/Berlin' ) );
          $pickup_latest = new WC_DateTime( $pickup_latest, new DateTimeZone( 'Europe/Berlin' ) );

          $pickup['pickup_time']['earliest'] = $pickup_earliest->format(DateTime::ATOM);
          $pickup['pickup_time']['latest'] = $pickup_latest->format(DateTime::ATOM);
      } catch (Exception $e) {
          WC_Shipcloud_Shipping::log(sprintf( __( 'Couldn\'t prepare pickup: %s', 'shipcloud-for-woocommerce' ), $e->getMessage() ));
      }

      return $pickup;
    }
	/**
	 * Sanitize package data.
	 *
	 * User enter package data that can:
	 *
	 * - Have local decimal separator.
	 *
	 * @since 1.5.1
	 *
	 * @param array $package_data
	 *
	 * @return array
	 */
	protected function sanitize_package( $package_data ) {
		$package_data['width']  = wc_format_decimal( $package_data['width'] );
		$package_data['height'] = wc_format_decimal( $package_data['height'] );
		$package_data['length'] = wc_format_decimal( $package_data['length'] );
		$package_data['weight'] = wc_format_decimal( $package_data['weight'] );

		return $package_data;
	}

	/**
	 * @param $request
	 *
	 * @return array
	 */
	public function get_package_data( $request ) {
		$package_data = array(
			'width'  => $request['wcsc_width'],
			'height' => $request['wcsc_height'],
			'length' => $request['wcsc_length'],
			'weight' => $request['wcsc_weight'],
			'type'   => 'parcel',
		);

		if ( isset( $request['wcsc_type'] ) ) {
			$package_data['type'] = $request['wcsc_type'];
		}

		return $package_data;
	}

	/**
	 * @param null $order_id
	 *
	 * @return string
	 * @throws \RuntimeException
	 */
	protected function get_storage_path( $suffix = null ) {
		$wp_upload_dir = wp_upload_dir();
		$path          = $wp_upload_dir['basedir']
		                 . DIRECTORY_SEPARATOR . 'shipcloud-woocommerce';

		if ( null !== $suffix && $suffix ) {
			$path .= DIRECTORY_SEPARATOR . trim( $suffix, '\\/' );
		}

		if ( is_dir( $path ) ) {
			// Already created, nothing to do.
			return $path;
		}

		// Directory not present - we try to create it.
		if ( ! wp_mkdir_p( $path ) ) {
			WC_Shipcloud_Shipping::log('Couldn\'t create sub-directories for shipcloud storage.');
			throw new \RuntimeException(
				'Couldn\'t create sub-directories for shipcloud storage.'
			);
		}

		return $path;
	}

	/**
	 * Get filesystem adapter.
	 *
	 * @return WP_Filesystem_Base
	 * @throws \RuntimeException
	 */
	protected function get_filesystem() {
		global $wp_filesystem;

		if ( $wp_filesystem ) {
			// Aready connectec / instantiated, so we won't do it again.
			return $wp_filesystem;
		}

		if ( ! WP_Filesystem() ) {
			WC_Shipcloud_Shipping::log('Can\'t access file system to download created shipping labels.');
			throw new \RuntimeException(
				'Can\'t access file system to download created shipping labels.'
			);
		}

		return $wp_filesystem;
	}

	/*
	 * Check to see if it's a return shipment
	 *
	 * @return array
	 */
	 protected function handle_return_shipments( $order, $data ) {
		if ( 'returns' == $data['shipcloud_carrier_service'] ) {
			WC_Shipcloud_Shipping::log('Detected returns shipment. Switching from and to entries.');
			$from = $order->get_recipient();
			$to = $order->get_sender();
		} else {
			$to = $order->get_recipient();
			$from = $order->get_sender();
		}

		$adresses = array(
			'from' => $from,
			'to' => $to,
		);

		return array_filter( $adresses );
	}
}

$wc_shipcloud_order_bulk = new WC_Shipcloud_Order_Bulk();
