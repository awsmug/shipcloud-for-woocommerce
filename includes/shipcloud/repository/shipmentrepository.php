<?php

namespace Shipcloud\Repository;

class ShipmentRepository {
    public function availableAdditionalServices() {
        return array (
            'dhl' => array(
                array(
                    'name' => 'visual_age_check'
                ),
                array(
                    'name' => 'premium_international'
                ),
                array(
                    'name' => 'delivery_time'
                ),
                array(
                    'name' => 'cash_on_delivery'
                ),
                array(
                    'name' => 'advance_notice'
                )
            ),
            'dhl_express' => array(
                array(
                    'name' => 'saturday_delivery'
                )
            ),
            'dpd' => array(
                array(
                    'name' => 'drop_authorization'
                ),
                array(
                    'name' => 'saturday_delivery'
                ),
                array(
                    'name' => 'advance_notice'
                )
            ),
            'gls' => array(
                array(
                    'name' => 'cash_on_delivery'
                ),
                array(
                    'name' => 'gls_guaranteed24service'
                ),
                array(
                    'name' => 'advance_notice'
                )
            ),
            'ups' => array(
                array(
                    'name' => 'cash_on_delivery'
                ),
                array(
                    'name' => 'ups_adult_signature'
                )
            ),
        );
    }

    public function additionalServicesForCarrier( $carrier ) {
        return $this->availableAdditionalServices()[$carrier];
    }

    /**
	 * @param $shipment_id
	 *
	 * @return null|\WC_Order
	 */
	public function findOrderByShipmentId( $shipment_id ) {
		$orders = get_posts(
			array(
				'post_type'    => ['order', 'shop_order'],
				'post_status'  => 'any',
				'meta_key'     => 'shipcloud_shipment_data',
				'meta_value'   => $shipment_id,
				'meta_compare' => 'LIKE',
			)
		);

		if ( ! $orders || is_wp_error( $orders ) ) {
			return null;
		}

		// Iterate matching orders and check for the exact match.
		foreach ( $orders as $order ) {
			if ( $this->findByShipmentId( $order->ID, $shipment_id ) ) {
				// This order has the shipment we are searching for.
				return wc_get_order( $order );
			}
		}

		return null;
	}

	/**
	 * @param $order_id
	 * @param $shipment_id
	 *
	 * @return array
	 */
	public function findByShipmentId( $order_id, $shipment_id ) {
		foreach ( get_post_meta( $order_id, 'shipcloud_shipment_data' ) as $shipment ) {
			if ( $shipment['id'] === $shipment_id ) {
				$converter = new \Shipcloud\ShipmentAdapter( $shipment );

				return $converter->toArray();
			}
		}

		return array();
	}

	public function update( $order_id, $shipment ) {
        $order = \WooCommerce::instance()->order_factory->get_order( $order_id );
        $translated_data = $this->translate_data( $shipment, $order_id );

        //  update shipment at shipcloud
        _wcsc_api()->shipment()->update( $shipment['id'], $shipment );

        // update shipment meta data
        if (method_exists($order, 'get_meta_data')) {
            foreach ( $order->get_meta_data() as $meta_value ) {
                if ( 'shipcloud_shipment_data' !== $meta_value->key ) {
                    continue;
                }

                if ( $meta_value->value['id'] !== $shipment['id'] ) {
                    continue;
                }

                $order->update_meta_data(
                    'shipcloud_shipment_data',
                    array_merge( $meta_value->value, $translated_data ),
                    $meta_value->id
                );

                $order->save_meta_data();

                break;
            }
        } else {
            $shipment_meta_data_entries = get_post_meta( $order->id, 'shipcloud_shipment_data' );
            foreach ( $shipment_meta_data_entries as $meta_key => $meta_value) {
                if ( $meta_value['id'] !== $shipment['id'] ) {
                    continue;
                }

                update_post_meta(
                    $order->id,
                    'shipcloud_shipment_data',
                    array_merge( $meta_value, $translated_data ),
                    $meta_value
                );

                break;
            }
        }
    }

	public function translate_to_api_data( $old_structured_data, $order_id = null ) {
		$data = array(
			'id'                  => isset($old_structured_data['id']) ? $old_structured_data['id'] : '',
			'from'                => array(
				'company'    => isset($old_structured_data['sender_company']) ? $old_structured_data['sender_company'] : '',
				'first_name' => isset($old_structured_data['sender_first_name']) ? $old_structured_data['sender_first_name'] : '',
				'last_name'  => isset($old_structured_data['sender_last_name']) ? $old_structured_data['sender_last_name'] : '',
				'street'     => isset($old_structured_data['sender_street']) ? $old_structured_data['sender_street'] : '',
				'street_no'  => isset($old_structured_data['sender_street_no']) ? $old_structured_data['sender_street_no'] : '',
				'zip_code'   => isset($old_structured_data['sender_zip_code']) ? $old_structured_data['sender_zip_code'] : '',
				'city'       => isset($old_structured_data['sender_city']) ? $old_structured_data['sender_city'] : '',
				'country'    => isset($old_structured_data['country']) ? $old_structured_data['country'] : $old_structured_data['sender_country'],
				'phone'      => isset($old_structured_data['sender_phone']) ? $old_structured_data['sender_phone'] : '',
			),
			'to'                  => array(
				'company'    => isset($old_structured_data['recipient_company']) ? $old_structured_data['recipient_company'] : '',
				'first_name' => isset($old_structured_data['recipient_first_name']) ? $old_structured_data['recipient_first_name'] : '',
				'last_name'  => isset($old_structured_data['recipient_last_name']) ? $old_structured_data['recipient_last_name'] : '',
                'care_of'    => isset($old_structured_data['recipient_care_of']) ? $old_structured_data['recipient_care_of'] : '',
				'street'     => isset($old_structured_data['recipient_street']) ? $old_structured_data['recipient_street'] : '',
				'street_no'  => isset($old_structured_data['recipient_street_no']) ? $old_structured_data['recipient_street_no'] : '',
				'zip_code'   => isset($old_structured_data['recipient_zip_code']) ? $old_structured_data['recipient_zip_code'] : '',
				'city'       => isset($old_structured_data['recipient_city']) ? $old_structured_data['recipient_city'] : '',
				'country'    => isset($old_structured_data['recipient_country']) ? $old_structured_data['recipient_country'] : '',
				'phone'      => isset($old_structured_data['recipient_phone']) ? $old_structured_data['recipient_phone'] : '',
			),
			'package'             => array(
				'width'  => wc_format_decimal( $old_structured_data['width'] ),
				'height' => wc_format_decimal( $old_structured_data['height'] ),
				'length' => wc_format_decimal( $old_structured_data['length'] ),
				'weight' => wc_format_decimal( $old_structured_data['weight'] ),
				//'type' => $_POST['package']['type'],
			),
			'label_url'           => isset($old_structured_data['label_url']) ? $old_structured_data['label_url'] : '',
			'tracking_url'        => isset($old_structured_data['tracking_url']) ? $old_structured_data['tracking_url'] : '',
			'price'               => isset($old_structured_data['price']) ? $old_structured_data['price'] : '',
			'carrier'             => isset($old_structured_data['carrier']) ? $old_structured_data['carrier'] : '',
            'service'             => isset($old_structured_data['service']) ? $old_structured_data['service'] : '',
			'carrier_tracking_no' => isset($old_structured_data['carrier_tracking_no']) ? $old_structured_data['carrier_tracking_no'] : '',
			'reference_number'    => isset($old_structured_data['reference_number']) ? $old_structured_data['reference_number'] : '',
			'additional_services' => isset($old_structured_data['additional_services']) ? $this->handleAdditionalServices( $old_structured_data ) : '',
            'customs_declaration' => isset($old_structured_data['customs_declaration']) ? $old_structured_data['customs_declaration'] : '',
		);

		if ( $order_id ) {
			$data['shipment_status'] = wcsc_get_shipment_status_string(
				get_post_meta( $order_id, 'shipment_' . $old_structured_data['id'] . '_status', true )
			);
		}

        if (isset($old_structured_data['pickup_request'])) {
            $data['pickup_request'] = $old_structured_data['pickup_request'];
        } elseif (isset($old_structured_data['pickup'])) {
            $data['pickup'] = $old_structured_data['pickup'];
        }

		return $data;
	}

	public function translate_data( $data, $order_id ) {
        $order = \WC_Shipcloud_Order::create_order( $order_id );
		$data = array_replace_recursive(
			array(
				'to'   => array(
					'company'    => '',
					'first_name' => '',
					'last_name'  => '',
					'street'     => '',
					'street_no'  => '',
					'care_of'    => '',
					'city'       => '',
					'zip_code'   => '',
					'country'    => '',
				),
				'from' => array(
					'company'    => '',
					'first_name' => '',
					'last_name'  => '',
					'street'     => '',
					'street_no'  => '',
					'care_of'    => '',
					'city'       => '',
					'zip_code'   => '',
					'country'    => '',
				),
				'additional_services' => '',
                'customs_declaration' => '',
			),
			$data
		);

		return array(
			// Sender
			'sender_company'       => $data['from']['company'],
			'sender_first_name'    => $data['from']['first_name'],
			'sender_last_name'     => $data['from']['last_name'],
			'sender_street'        => $data['from']['street'],
			'sender_street_no'     => $data['from']['street_no'],
			'sender_care_of'       => $data['from']['care_of'],
			'sender_city'          => $data['from']['city'],
			'sender_zip_code'      => $data['from']['zip_code'],
			'sender_state'         => $data['from']['state'],
			'country'              => $data['from']['country'],
			// Recipient
			'recipient_company'    => $data['to']['company'],
			'recipient_first_name' => $data['to']['first_name'],
			'recipient_last_name'  => $data['to']['last_name'],
			'recipient_street'     => $data['to']['street'],
			'recipient_street_no'  => $data['to']['street_no'],
			'recipient_care_of'    => $data['to']['care_of'],
			'recipient_city'       => $data['to']['city'],
			'recipient_zip_code'   => $data['to']['zip_code'],
			'recipient_state'      => $data['to']['state'],
			'recipient_country'    => $data['to']['country'],
			'additional_services'  => $data['additional_services'],
            'customs_declaration' => $data['customs_declaration']
		);
	}

	/**
	 * Handles additional services content in api form and returns the hash
	 *
	 * @return string
	 * @since 1.8.0
	 */
	private function handleAdditionalServices( $data ) {
		$submitted_additional_services = $data['additional_services'];
		$additional_services = array();

		foreach ( $submitted_additional_services as $additional_service ) {
			switch ( $additional_service['name'] ) {
				case 'visual_age_check':
					if (array_key_exists( 'minimum_age', $additional_service['properties'])) {
						$additional_services[] = array(
							'name' => 'visual_age_check',
							'properties' => array(
								'minimum_age' => $additional_service['properties']['minimum_age']
							)
						);
					}
					break;
				case 'ups_adult_signature':
					$additional_services[] = array(
						'name' => 'ups_adult_signature'
					);
					break;
				case 'saturday_delivery':
					$additional_services[] = array(
						'name' => 'saturday_delivery'
					);
					break;
				case 'premium_international':
					$additional_services[] = array(
						'name' => 'premium_international'
					);
					break;
				case 'delivery_time':
					$additional_services[] = array(
						'name' => 'delivery_time',
						'properties' => array(
							'time_of_day_earliest' => $additional_service['properties']['time_of_day_earliest'],
							'time_of_day_latest' => $additional_service['properties']['time_of_day_latest']
						)
					);
					break;
				case 'drop_authorization':
					if ( array_key_exists( 'message', $additional_service['properties'] ) ) {
						$additional_services[] = array(
							'name' => 'drop_authorization',
							'properties' => array(
								'message' => $additional_service['properties']['message']
							)
						);
					}
					break;
				case 'cash_on_delivery':
                    $additional_services[] = array(
                        'name' => 'cash_on_delivery',
                        'properties' => array(
                            'amount' => $additional_service['properties']['amount'],
                            'currency' => $additional_service['properties']['currency'],
                            'bank_account_holder' => array_key_exists( 'bank_account_holder', $additional_service['properties'] ) ? $additional_service['properties']['bank_account_holder'] : '',
                            'bank_name' => array_key_exists( 'bank_account_holder', $additional_service['properties'] ) ? $additional_service['properties']['bank_name'] : '',
                            'bank_account_number' => array_key_exists( 'bank_account_holder', $additional_service['properties'] ) ? $additional_service['properties']['bank_account_number'] : '',
                            'bank_code' => array_key_exists( 'bank_account_holder', $additional_service['properties'] ) ? $additional_service['properties']['bank_code'] : '',
                            'reference1' => array_key_exists( 'bank_account_holder', $additional_service['properties'] ) ? $additional_service['properties']['reference1'] : ''
                        )
                    );
                    break;
				case 'gls_guaranteed24service':
                    $additional_services[] = array(
                        'name' => 'gls_guaranteed24service'
                    );
                    break;
			}
		}

		return $additional_services;
	}

	/**
	 * Returns parses additional services from request form and returns them in an api hash
	 *
	 * @return string
	 * @since 1.8.0
	 */
	public function additional_services_from_request($data, $order_total, $currency, $bank_information, $reference, $carrier) {
		$additional_services = array();

        foreach ( $data as $additional_service_key => $additional_service_value ) {
			switch ( $additional_service_key ) {
				case 'visual_age_check':
					if (array_key_exists( 'age_based_delivery', $data ) &&
                        array_key_exists( 'checked', $data['age_based_delivery'] ) &&
                        array_key_exists( 'minimum_age', $additional_service_value ) &&
                        !empty($additional_service_value['minimum_age'])
					) {
						$additional_services[] = array(
							'name' => 'visual_age_check',
							'properties' => array(
								'minimum_age' => $additional_service_value['minimum_age']
							)
						);
					}
					break;
				case 'ups_adult_signature':
					if (array_key_exists( 'age_based_delivery', $data ) &&
                        array_key_exists( 'checked', $data['age_based_delivery'] ) &&
						array_key_exists( 'checked', $additional_service_value )
					) {
						$additional_services[] = array(
							'name' => 'ups_adult_signature'
						);
					}
					break;
				case 'saturday_delivery':
					if (array_key_exists( 'checked', $additional_service_value )) {
						$additional_services[] = array(
							'name' => 'saturday_delivery'
						);
					}
					break;
				case 'premium_international':
					$additional_services[] = array(
						'name' => 'premium_international'
					);
					break;
				case 'delivery_time':
					if (array_key_exists( 'checked', $additional_service_value ) &&
						array_key_exists( 'timeframe', $additional_service_value )
					) {
						$selected_option = $additional_service_value['timeframe'];
						$time_of_day_earliest = substr($selected_option, 0, 2).':00';
						$time_of_day_latest = substr($selected_option, 2, 2).':00';

						$additional_services[] = array(
							'name' => 'delivery_time',
							'properties' => array(
								'time_of_day_earliest' => $time_of_day_earliest,
								'time_of_day_latest' => $time_of_day_latest
							)
						);
					}
					break;
				case 'drop_authorization':
					if (array_key_exists( 'checked', $additional_service_value ) &&
						array_key_exists( 'message', $additional_service_value ) &&
						isset($additional_service_value['message'])
					) {
						$additional_services[] = array(
							'name' => 'drop_authorization',
							'properties' => array(
								'message' => $additional_service_value['message']
							)
						);
					}
					break;
				case 'cash_on_delivery':
                    if (array_key_exists( 'checked', $additional_service_value )) {
                        $cod_array = array(
                            'name' => 'cash_on_delivery',
                            'properties' => array(
                                'amount' => $order_total,
                                'currency' => $currency,
                            )
                        );
                        switch($carrier) {
                            case 'dhl':
                                $cod_array['properties']['reference1'] = $reference;
                                $cod_array['properties']['bank_account_holder'] = $bank_information->getAccountHolder();
                                $cod_array['properties']['bank_name'] = $bank_information->getBankName();
                                $cod_array['properties']['bank_account_number'] = $bank_information->getIban();
                                $cod_array['properties']['bank_code'] = $bank_information->getBankSwift();
                                break;
                            case 'gls':
                                $cod_array['properties']['reference1'] = $reference;
                                break;
                        }
                        $additional_services[] = $cod_array;
                    }
                    break;
                case 'gls_guaranteed24service':
                    if (array_key_exists( 'checked', $data['gls_guaranteed24service'] ) &&
                        array_key_exists( 'checked', $additional_service_value )
                    ) {
                        $additional_services[] = array(
                            'name' => 'gls_guaranteed24service'
                        );
                    }
                    break;
			}
		}

        return $additional_services;
	}
}
