<?php
/**
 * Textarea element type class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\DB_Objects\Elements\Element_Types\Base;

use awsmug\Torro_Forms\DB_Objects\Elements\Element_Types\Element_Type;
use awsmug\Torro_Forms\DB_Objects\Elements\Element;
use awsmug\Torro_Forms\DB_Objects\Submissions\Submission;
use WP_Error;

/**
 * Class representing a textarea element type.
 *
 * @since 1.0.0
 */
class Textarea extends Element_Type {

	/**
	 * Filters the array representation of a given element of this type.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array           $data       Element data to filter.
	 * @param Element         $element    The element object to get the data for.
	 * @param Submission|null $submission Optional. Submission to get the values from, if available. Default null.
	 * @return array Array including all information for the element type.
	 */
	public function filter_json( $data, $element, $submission = null ) {
		$settings = $this->get_settings( $element );

		if ( ! empty( $settings['max_length'] ) ) {
			$data['input_attrs']['maxlength'] = (int) $settings['max_length'];
		}

		if ( ! empty( $settings['rows'] ) ) {
			$data['input_attrs']['rows'] = (int) $settings['rows'];
		}

		if ( ! empty( $settings['cols'] ) ) {
			$data['input_attrs']['cols'] = (int) $settings['cols'];
		}

		$data = parent::filter_json( $data, $element, $submission );

		$limits_text = '';
		if ( ! empty( $settings['min_length'] ) && ! empty( $settings['max_length'] ) ) {
			/* translators: 1: minimum length, 2: maximum length */
			$limits_text = sprintf( __( 'Between %1$s and %2$s characters are required.', 'torro-forms' ), number_format_i18n( $settings['min_length'] ), number_format_i18n( $settings['max_length'] ) );
		} elseif ( ! empty( $settings['min_length'] ) ) {
			/* translators: %s: minimum length */
			$limits_text = sprintf( __( 'At least %s characters are required.', 'torro-forms' ), number_format_i18n( $settings['min_length'] ) );
		} elseif ( ! empty( $settings['max_length'] ) ) {
			/* translators: %s: maximum length */
			$limits_text = sprintf( __( 'A maximum of %s characters are allowed.', 'torro-forms' ), number_format_i18n( $settings['max_length'] ) );
		}

		if ( ! empty( $limits_text ) ) {
			if ( ! empty( $data['description'] ) ) {
				$data['description'] .= '<br>';
			} else {
				$data['description'] = '';
				$data['input_attrs']['aria-describedby'] = $data['description_attrs']['id'];
			}

			$data['description'] .= $limits_text;
		}

		return $data;
	}

	/**
	 * Validates a field value for an element.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param mixed      $value      The value to validate. It is already unslashed when it arrives here.
	 * @param Element    $element    Element to validate the field value for.
	 * @param Submission $submission Submission the value belongs to.
	 * @return mixed|WP_Error Validated value, or error object on failure.
	 */
	public function validate_field( $value, $element, $submission ) {
		$settings = $this->get_settings( $element );

		$value = trim( (string) $value );

		if ( ! empty( $settings['required'] ) && 'no' !== $settings['required'] && empty( $value ) ) {
			return $this->create_error( 'value_required', __( 'You must enter something here.', 'torro-forms' ), $value );
		}

		if ( ! empty( $settings['min_length'] ) && strlen( $value ) < (int) $settings['min_length'] ) {
			return $this->create_error( 'value_too_short', __( 'The value you entered is too short.', 'torro-forms' ), $value );
		}

		if ( ! empty( $settings['max_length'] ) && strlen( $value ) > (int) $settings['max_length'] ) {
			return $this->create_error( 'value_too_long', __( 'The value you entered is too long.', 'torro-forms' ), $value );
		}

		return $value;
	}

	/**
	 * Bootstraps the element type by setting properties.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function bootstrap() {
		$this->slug        = 'textarea';
		$this->title       = __( 'Textarea', 'torro-forms' );
		$this->description = __( 'A textarea element.', 'torro-forms' );
		$this->icon_url    = $this->manager->assets()->get_full_url( 'assets/dist/img/icon-textarea.png' );

		$this->add_placeholder_settings_field();
		$this->add_description_settings_field();
		$this->add_required_settings_field();
		$this->settings_fields['min_length'] = array(
			'section'     => 'settings',
			'type'        => 'number',
			'label'       => __( 'Minimum length', 'torro-forms' ),
			'description' => __( 'The minimum number of chars which can be typed in.', 'torro-forms' ),
			'min'         => 0,
			'step'        => 1,
		);
		$this->settings_fields['max_length'] = array(
			'section'     => 'settings',
			'type'        => 'number',
			'label'       => __( 'Maximum length', 'torro-forms' ),
			'description' => __( 'The maximum number of chars which can be typed in.', 'torro-forms' ),
			'min'         => 0,
			'step'        => 1,
		);
		$this->settings_fields['rows'] = array(
			'section'     => 'settings',
			'type'        => 'number',
			'label'       => __( 'Rows', 'torro-forms' ),
			'description' => __( 'Number of rows for typing in  (can be overwritten by CSS).', 'torro-forms' ),
			'default'     => 10,
			'min'         => 0,
			'step'        => 1,
		);
		$this->settings_fields['cols'] = array(
			'section'     => 'settings',
			'type'        => 'number',
			'label'       => __( 'Columns', 'torro-forms' ),
			'description' => __( 'Number of columns for typing in (can be overwritten by CSS).', 'torro-forms' ),
			'default'     => 75,
			'min'         => 0,
			'step'        => 1,
		);
		$this->add_css_classes_settings_field();
	}
}