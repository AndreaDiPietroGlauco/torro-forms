<?php
/**
 * Plugin main class
 *
 * @package TorroForms
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Main class for Torro Forms.
 *
 * Takes care of initializing the plugin.
 *
 * This file must always be parseable by PHP 5.2.
 *
 * @since 1.0.0
 *
 * @method Leaves_And_Love\Plugin_Lib\Error_Handler          error_handler()
 * @method Leaves_And_Love\Plugin_Lib\Options                options()
 * @method Leaves_And_Love\Plugin_Lib\Cache                  cache()
 * @method Leaves_And_Love\Plugin_Lib\DB                     db()
 * @method Leaves_And_Love\Plugin_Lib\Meta                   meta()
 * @method Leaves_And_Love\Plugin_Lib\Assets                 assets()
 * @method Leaves_And_Love\Plugin_Lib\Template               template()
 * @method Leaves_And_Love\Plugin_Lib\AJAX                   ajax()
 * @method Leaves_And_Love\Plugin_Lib\Components\Admin_Pages admin_pages()
 * @method Leaves_And_Love\Plugin_Lib\Components\Extensions  extensions()
 */
class Torro_Forms extends Leaves_And_Love_Plugin {
	/**
	 * The error handler instance.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var Leaves_And_Love\Plugin_Lib\Error_Handler
	 */
	protected $error_handler;

	/**
	 * The Option API instance.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var Leaves_And_Love\Plugin_Lib\Options
	 */
	protected $options;

	/**
	 * The cache instance.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var Leaves_And_Love\Plugin_Lib\Cache
	 */
	protected $cache;

	/**
	 * The database instance.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var Leaves_And_Love\Plugin_Lib\DB
	 */
	protected $db;

	/**
	 * The Metadata API instance.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var Leaves_And_Love\Plugin_Lib\Meta
	 */
	protected $meta;

	/**
	 * The Assets manager instance.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var Leaves_And_Love\Plugin_Lib\Assets
	 */
	protected $assets;

	/**
	 * The Template instance.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var Leaves_And_Love\Plugin_Lib\Template
	 */
	protected $template;

	/**
	 * The AJAX handler instance.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var Leaves_And_Love\Plugin_Lib\AJAX
	 */
	protected $ajax;

	/**
	 * The Admin Pages instance.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var Leaves_And_Love\Plugin_Lib\Components\Admin_Pages
	 */
	protected $admin_pages;

	/**
	 * The Extensions instance.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var Leaves_And_Love\Plugin_Lib\Components\Extensions
	 */
	protected $extensions;

	/**
	 * Uninstalls the plugin.
	 *
	 * Drops all database tables and related content.
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 *
	 * @codeCoverageIgnore
	 */
	public static function uninstall() {
		//TODO: uninstall routine
	}

	/**
	 * Returns the uninstall callback.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return callable Uninstall callback.
	 */
	public function get_uninstall_hook() {
		return array( __CLASS__, 'uninstall' );
	}

	/**
	 * Loads the base properties of the class.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function load_base_properties() {
		$this->version = '1.0.0';
		$this->prefix = 'torro_';
		$this->vendor_name = 'awsmug';
		$this->project_name = 'Torro_Forms';
		$this->minimum_php = '5.4';
		$this->minimum_wp = '4.4';
	}

	/**
	 * Loads the plugin's textdomain.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function load_textdomain() {
		/** This filter is documented in wp-includes/l10n.php */
		$locale = apply_filters( 'plugin_locale', get_locale(), 'torro-forms' );

		$mofile = WP_LANG_DIR . '/plugins/torro-forms/torro-forms-' . $locale . '.mo';
		if ( file_exists( $mofile ) ) {
			return load_textdomain( 'torro-forms', $mofile );
		}

		$this->load_plugin_textdomain( 'torro-forms' );
	}

	/**
	 * Loads the class messages.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function load_messages() {
		$this->messages['cheatin_huh']  = __( 'Cheatin&#8217; huh?', 'torro-forms' );
		$this->messages['outdated_php'] = __( 'Torro Forms cannot be initialized because your setup uses a PHP version older than %s.', 'torro-forms' );
		$this->messages['outdated_wp']  = __( 'Torro Forms cannot be initialized because your setup uses a WordPress version older than %s.', 'torro-forms' );
	}

	/**
	 * Checks whether the dependencies have been loaded.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return bool True if the dependencies are loaded, false otherwise.
	 */
	protected function dependencies_loaded() {
		return class_exists( 'EDD_SL_Plugin_Updater' ) && class_exists( 'PHPExcel' );
	}

	/**
	 * Instantiates the plugin services.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function instantiate_services() {
		call_user_func( array( 'Leaves_And_Love\Plugin_Lib\Fields\Field_Manager', 'set_translations' ), $this->instantiate_plugin_class( 'Translations\Translations_Field_Manager' ) );

		$this->error_handler = $this->instantiate_library_service( 'Error_Handler', $this->prefix, $this->instantiate_plugin_class( 'Translations\Translations_Error_Handler' ) );
		$this->options       = $this->instantiate_library_service( 'Options', $this->prefix );
		$this->cache         = $this->instantiate_library_service( 'Cache', $this->prefix );
		$this->db            = $this->instantiate_library_service( 'DB', $this->prefix, array(
			'options'       => $this->options,
			'error_handler' => $this->error_handler,
		), $this->instantiate_plugin_class( 'Translations\Translations_DB' ) );
		$this->meta          = $this->instantiate_library_service( 'Meta', $this->prefix, array(
			'db'            => $this->db,
			'error_handler' => $this->error_handler,
		) );
		$this->assets        = $this->instantiate_library_service( 'Assets', $this->prefix, array(
			'path_callback' => array( $this, 'path' ),
			'url_callback'  => array( $this, 'url' ),
		) );
		$this->template      = $this->instantiate_library_service( 'Template', $this->prefix, array(
			'default_location' => $this->path( 'templates/' ),
		) );
		$this->ajax          = $this->instantiate_library_service( 'AJAX', $this->prefix, $this->instantiate_plugin_class( 'Translations\Translations_AJAX' ) );

		$this->admin_pages = $this->instantiate_library_service( 'Components\Admin_Pages', $this->prefix, array(
			'ajax'          => $this->ajax,
			'assets'        => $this->assets,
			'error_handler' => $this->error_handler,
		) );

		$this->extensions = $this->instantiate_library_service( 'Components\Extensions', $this->prefix, $this->instantiate_plugin_class( 'Translations\Translations_Extensions' ) );
		$this->extensions->set_plugin( $this );
	}

	/**
	 * Adds the necessary plugin hooks.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function add_hooks() {
		$this->options->add_hooks();
		$this->db->add_hooks();
		$this->ajax->add_hooks();
		$this->admin_pages->add_hooks();
		$this->extensions->add_hooks();
	}
}