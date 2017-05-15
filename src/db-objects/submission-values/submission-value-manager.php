<?php
/**
 * Submission value manager class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\DB_Objects\Submission_Values;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Manager;
use Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\Capability_Manager_Trait;
use Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\REST_API_Manager_Trait;
use awsmug\Torro_Forms\DB_Objects\Manager_With_Parents_Trait;
use awsmug\Torro_Forms\Translations\Translations_Submission_Value_Manager;
use awsmug\Torro_Forms\DB;
use Leaves_And_Love\Plugin_Lib\Cache;
use Leaves_And_Love\Plugin_Lib\Error_Handler;

/**
 * Manager class for submission values.
 *
 * @since 1.0.0
 *
 * @method Submission_Value_Capabilities capabilities()
 * @method DB                            db()
 * @method Cache                         cache()
 * @method Error_Handler                 error_handler()
 */
class Submission_Value_Manager extends Manager {
	use Capability_Manager_Trait, REST_API_Manager_Trait, Manager_With_Parents_Trait;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string                                $prefix       The instance prefix.
	 * @param array                                 $services     {
	 *     Array of service instances.
	 *
	 *     @type Submission_Value_Capabilities $capabilities  The capabilities instance.
	 *     @type DB                            $db            The database instance.
	 *     @type Cache                         $cache         The cache instance.
	 *     @type Error_Handler                 $error_handler The error handler instance.
	 * }
	 * @param Translations_Submission_Value_Manager $translations Translations instance.
	 */
	public function __construct( $prefix, $services, $translations ) {
		$this->class_name                 = Submission_Value::class;
		$this->collection_class_name      = Submission_Value_Collection::class;
		$this->query_class_name           = Submission_Value_Query::class;
		$this->rest_controller_class_name = REST_Submission_Values_Controller::class;

		$this->singular_slug = 'submission_value';
		$this->plural_slug   = 'submission_values';

		$this->table_name  = $this->plural_slug;
		$this->cache_group = $this->plural_slug;

		$this->primary_property = 'id';

		parent::__construct( $prefix, $services, $translations );
	}

	/**
	 * Adds the database table.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function add_database_table() {
		$this->db()->add_table( $this->table_name, array(
			"id int(11) unsigned NOT NULL auto_increment",
			"submission_id int(11) unsigned NOT NULL",
			"element_id int(11) unsigned NOT NULL",
			"field char(100) NOT NULL default ''",
			"value text NOT NULL",
			"PRIMARY KEY  (id)",
			"KEY submission_id (submission_id)",
			"KEY element_id (element_id)",
		) );
	}
}