<?php

namespace Shipcloud\Domain;

/**
 * Class Carrier
 *
 * @package Shipcloud\Domain
 */
class Carrier implements \ArrayAccess, \JsonSerializable {
	/**
	 * @var string
	 */
	private $displayName;

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var array
	 */
	private $packageTypes;

	/**
	 * @var array
	 */
	private $services;

	/**
	 * Create new carrier.
	 *
	 * @param string $name
	 * @param string $displayName
	 * @param array  $services
	 * @param array  $packageTypes
	 */
	public function __construct( $name, $displayName, array $services = array(), array $packageTypes = array() ) {
		$this->name         = $name;
		$this->displayName  = $displayName;
		$this->services     = $services;
		$this->packageTypes = $packageTypes;
	}

	/**
	 * Retrieve the internal name.
	 *
	 * @since 1.4.0
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Get a list of services.
	 *
	 * The unordered array contains the internal names of services which the carrier offers.
	 *
	 * @since 1.4.0
	 * @return array
	 */
	public function getServices() {
		return $this->services;
	}

	/**
	 * Offset to retrieve
	 *
	 * @since 1.4.0
	 * @deprecated 2.0.0 Use getter instead.
	 */
	public function offsetGet( $offset ) {
		if ( ! $this->offsetExists( $offset ) ) {
			return null;
		}

		if ( 'display_name' == $offset ) {
			return $this->getDisplayName();
		}

		if ( 'package_types' === $offset ) {
			return $this->getPackageTypes();
		}

		$getter = 'get' . ucfirst( $offset );

		return $this->$getter();
	}

	/**
	 * Whether a offset exists
	 *
	 * @since 1.4.0
	 * @deprecated 2.0.0 Use getter instead.
	 */
	public function offsetExists( $offset ) {
		return in_array( $offset, array( 'name', 'display_name', 'services', 'package_types' ), true );
	}

	/**
	 * @since 1.4.0
	 * @return string
	 */
	public function getDisplayName() {
		return $this->displayName;
	}

	/**
	 * @return array
	 */
	public function getPackageTypes() {
		return $this->packageTypes;
	}

	/**
	 * Offset to set
	 *
	 * @since 1.4.0
	 * @deprecated 2.0.0 Refuse changing an object state.
	 */
	public function offsetSet( $offset, $value ) {
		if ( ! $this->offsetExists( $offset ) ) {
			return null;
		}

		if ( 'display_name' === $offset ) {
			$this->displayName = $value;

			return;
		}

		if ( 'package_types' === $offset ) {
			$this->packageTypes = $value;

			return;
		}

		$this->$offset = $value;
	}

	/**
	 * Offset to unset
	 *
	 * @deprecated 2.0.0 Refuse to change offsets.
	 */
	public function offsetUnset( $offset ) {
		// Not provided.
	}

	/**
	 * Specify data which should be serialized to JSON.
	 *
	 * This is done for using the original snake_case keys instead of the camelCase properties.
	 *
	 * @since 1.4.0
	 * @return array
	 */
	public function jsonSerialize() {
		return array(
			'name'          => $this->getName(),
			'display_name'  => $this->getDisplayName(),
			'services'      => $this->getServices(),
			'package_types' => $this->getPackageTypes(),
		);
	}
}
