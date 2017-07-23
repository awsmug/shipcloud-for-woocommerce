<?php

/**
 * Labels bulk for shipcloud WooCommerce.
 *
 * @author  awesome.ug <support@awesome.ug>
 * @package shipcloudForWooCommerce
 * @version 1.0.0
 * @since   1.0.0
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
class WooCommerce_Shipcloud_Block_Order_Labels_Bulk {
	protected $allowed_carriers;

	/**
	 * @var Woocommerce_Shipcloud_API
	 */
	private $shipcloud_api;

	private $template_file;

	/**
	 * Create new bulk view.
	 *
	 * @param string                      $template_file    Path to the template.
	 * @param WC_Shipcloud_Order          $order
	 * @param \Shipcloud\Domain\Carrier[] $allowed_carriers List of carriers that can be selected.
	 * @param \Shipcloud\Api              $shipcloud_api    Connection to the API.
	 */
	public function __construct( $template_file, $order, $allowed_carriers, $shipcloud_api ) {
		$this->template_file = $template_file;

		$this->label_form = new WooCommerce_Shipcloud_Block_Labels_Form(
			WCSC_FOLDER . '/components/block/label-form.php',
			$order,
			$allowed_carriers,
			$shipcloud_api
		);
	}

	/**
	 * Associative array of carrier id and display name.
	 *
	 * @return string[]
	 */
	public function get_allowed_carriers() {
		return $this->allowed_carriers;
	}

	/**
	 * Associative array of service id and labels.
	 *
	 * @return string[]
	 */
	public function get_services() {
		$services = array();

		foreach ( $this->get_shipcloud_api()->get_services() as $id => $settings ) {
			$services[ $id ] = $settings['name'];
		}

		return $services;
	}

	/**
	 * @return Woocommerce_Shipcloud_API
	 */
	public function get_shipcloud_api() {
		return $this->shipcloud_api;
	}

	/**
	 * Pre-render content.
	 *
	 * @return string
	 */
	public function render() {
		ob_start();
		$this->dispatch();

		return ob_get_clean();
	}

	/**
	 * Send content to client.
	 */
	public function dispatch() {
		require $this->get_template_file();
	}

	/**
	 * @return mixed
	 */
	protected function get_template_file() {
		return $this->template_file;
	}
}
