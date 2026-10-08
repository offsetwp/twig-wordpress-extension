<?php
/**
 * OffsetWP Twig WordPress Extension Tests
 *
 * The class of WordPress a user is, standing in for the platform a test process does not
 * have. It keeps the name of the class, the properties the user variable reads, with the
 * data of the user behind magic properties as WordPress keeps it, and the one method the
 * variable calls. A test builds the user it wants from an ID, data and roles.
 *
 * The declaration is guarded, so that a process which does have the platform loaded keeps
 * the platform's own. The analyser reads this file too, so it is also where the types of
 * those properties come from.
 *
 * @author Jérôme Wohlschlegel
 * @package OffsetWP\Twig\Extension\WordPressExtension\Tests
 */

declare( strict_types=1 );

if ( ! class_exists( 'WP_User', false ) ) {
	/**
	 * A user: an ID, data such as the display name, and roles. The user of ID 0 is the one
	 * WordPress gives when nobody is logged in.
	 *
	 * @property string $display_name
	 * @property string $user_email
	 */
	class WP_User {

		/**
		 * The ID of the user, and 0 for nobody.
		 *
		 * @var int
		 */
		public $ID = 0;

		/**
		 * The data of the user, read through magic properties.
		 *
		 * @var stdClass
		 */
		public $data;

		/**
		 * The roles of the user, by slug.
		 *
		 * @var string[]
		 */
		public $roles = array();

		/**
		 * A user, or nobody when no ID is given.
		 *
		 * @param int                  $id    The ID of the user.
		 * @param array<string, mixed> $data  The data of the user, such as display_name.
		 * @param string[]             $roles The roles of the user.
		 */
		public function __construct( int $id = 0, array $data = array(), array $roles = array() ) {
			$this->ID    = $id;
			$this->data  = (object) $data;
			$this->roles = $roles;
		}

		/**
		 * Whether a field of the data of the user is set.
		 *
		 * @param string $key The name of the field.
		 * @return bool
		 */
		public function __isset( $key ) {
			return isset( $this->data->$key );
		}

		/**
		 * A field of the data of the user, or an empty string, which is what WordPress gives
		 * for a field it does not know.
		 *
		 * @param string $key The name of the field.
		 * @return mixed
		 */
		public function __get( $key ) {
			return $this->data->$key ?? '';
		}

		/**
		 * Whether the user exists, which the user of ID 0 does not.
		 *
		 * @return bool
		 */
		public function exists() {
			return ! empty( $this->ID );
		}
	}
}
