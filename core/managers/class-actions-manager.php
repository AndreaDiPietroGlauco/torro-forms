<?php
/**
 * Core: Torro_Actions_Manager class
 *
 * @package TorroForms
 * @subpackage CoreManagers
 * @version 1.0.0beta1
 * @since 1.0.0beta1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Torro Forms action manager class
 *
 * @since 1.0.0beta1
 */
final class Torro_Actions_Manager extends Torro_Manager {
	/**
	 * Instance
	 *
	 * @var null|Torro_Actions_Manager
	 * @since 1.0.0
	 */
	private static $instance = null;

	/**
	 * Singleton
	 *
	 * @return Torro_Actions_Manager
	 * @since 1.0.0
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	protected function __construct() {
		parent::__construct();
	}

	/**
	 * Setting allowed mudules
	 *
	 * @return array
	 * @since 1.0.0
	 */
	protected function allowed_modules(){
		$allowed = array(
			'actions' => 'Torro_Action'
		);
		return $allowed;
	}

	/**
	 * Getting category
	 *
	 * @return string
	 * @since 1.0.0
	 */
	protected function get_category() {
		return 'actions';
	}
}
