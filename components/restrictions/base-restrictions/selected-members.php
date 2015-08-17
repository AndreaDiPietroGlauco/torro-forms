<?php
/**
 * Restrict form to all selected members
 *
 * Motherclass for all Restrictions
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package Questions/Restrictions
 * @version 1.0.0
 * @since   1.0.0
 * @license GPL 2
 *
 * Copyright 2015 awesome.ug (support@awesome.ug)
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

if( !defined( 'ABSPATH' ) ){
	exit;
}

class Questions_Restriction_SelectedMembers extends Questions_Restriction
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->title = __( 'Selected Members', 'questions-locale' );
		$this->slug = 'selectedmembers';

		$this->option_name = __( 'Selected Members of site', 'questions-locale' );

		add_action( 'questions_functions', array( $this, 'invite_buttons' ) );

		add_action( 'questions_save_form', array( $this, 'save' ), 10, 1 );

		add_action( 'wp_ajax_questions_add_participiants_allmembers', array( $this,
		                                                                     'ajax_add_participiants_allmembers'
		) );
		add_action( 'wp_ajax_questions_invite_participiants', array( $this, 'ajax_invite_participiants' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), 15 );
		add_action( 'admin_print_styles', array( $this, 'register_admin_styles' ) );
	}

	/**
	 * Adds content to the option
	 */
	public function option_content()
	{
		global $wpdb, $post, $questions_global;

		$form_id = $post->ID;

		/**
		 * Add participiants functions
		 */
		$html = '<div id="questions-add-participiants">';

		$options = apply_filters( 'questions_add_participiants_options', array( 'allmembers' => esc_attr__( 'Add all actual Members', 'questions-locale' ),
		                                                               ) );

		$add_participiants_option = get_post_meta( $form_id, 'add_participiants_option', TRUE );

		$html .= '<div id="questions-add-participiants-options">';
		$html .= '<select id="questions-add-participiants-option" name="questions_add_participiants_option">';
		foreach( $options AS $slug => $value ):
			$selected = '';
			if( $slug == $add_participiants_option ){
				$selected = ' selected="selected"';
			}
			$html .= '<option value="' . $slug . '"' . $selected . '>' . $value . '</option>';
		endforeach;
		$html .= '</select>';
		$html .= '</div>';

		$html .= '<div id="questions-add-participiants-content-allmembers" class="questions-add-participiants-content-allmembers questions-add-participiants-content">';
		$html .= '<input type="button" class="questions-add-participiants-allmembers-button button" id="questions-add-participiants-allmembers-button" value="' . esc_attr__( 'Add all members as Participiants', 'questions-locale' ) . '" />';
		$html .= '<a class="questions-remove-all-participiants">' . esc_attr__( 'Remove all Participiants', 'questions-locale' ) . '</a>';
		$html .= '</div>';

		// Hooking in
		ob_start();
		do_action( 'questions_add_participiants_content' );
		$html .= ob_get_clean();

		$html .= '</div>';

		/**
		 * Getting all users which have been added to participiants list
		 */
		$sql = $wpdb->prepare( "SELECT user_id FROM {$questions_global->tables->participiants} WHERE survey_id = %s", $form_id );
		$user_ids = $wpdb->get_col( $sql );

		$users = array();

		if( is_array( $user_ids ) && count( $user_ids ) > 0 ){
			$users = get_users( array( 'include' => $user_ids, 'orderby' => 'ID'
			                    ) );
		}
		/**
		 * Participiants Statistics
		 */
		$html .= '<div id="questions-participiants-status" class="questions-participiants-status">';
		$html .= '<p>' . count( $users ) . ' ' . esc_attr__( 'participiant/s', 'questions-locale' ) . '</p>';
		$html .= '</div>';

		/**
		 * Participiants list
		 */

		// Head
		$html .= '<div id="questions-participiants-list">';
		$html .= '<table class="wp-list-table widefat">';
		$html .= '<thead>';
		$html .= '<tr>';
		$html .= '<th>' . esc_attr__( 'ID', 'questions-locale' ) . '</th>';
		$html .= '<th>' . esc_attr__( 'User nicename', 'questions-locale' ) . '</th>';
		$html .= '<th>' . esc_attr__( 'Display name', 'questions-locale' ) . '</th>';
		$html .= '<th>' . esc_attr__( 'Email', 'questions-locale' ) . '</th>';
		$html .= '<th>' . esc_attr__( 'Status', 'questions-locale' ) . '</th>';
		$html .= '<th>&nbsp</th>';
		$html .= '</tr>';
		$html .= '</thead>';

		$html .= '<tbody>';

		$questions_participiants_value = '';

		if( is_array( $users ) && count( $users ) > 0 ){

			// Content
			foreach( $users AS $user ):
				if( qu_user_has_participated( $form_id, $user->ID ) ):
					$user_css = ' finished';
					$user_text = esc_attr__( 'finished', 'questions-locale' );
				else:
					$user_text = esc_attr__( 'new', 'questions-locale' );
					$user_css = ' new';
				endif;

				$html .= '<tr class="participiant participiant-user-' . $user->ID . $user_css . '">';
				$html .= '<td>' . $user->ID . '</td>';
				$html .= '<td>' . $user->user_nicename . '</td>';
				$html .= '<td>' . $user->display_name . '</td>';
				$html .= '<td>' . $user->user_email . '</td>';
				$html .= '<td>' . $user_text . '</td>';
				$html .= '<td><a class="button questions-delete-participiant" rel="' . $user->ID . '">' . esc_attr__( 'Delete', 'questions-locale' ) . '</a></td>';
				$html .= '</tr>';
			endforeach;

			$questions_participiants_value = implode( ',', $user_ids );
		}
		$html .= '<tr class="no-users-found">';
		$html .= '<td colspan="6">' . esc_attr( 'No Users found.', 'questions-locale' ) . '</td>';
		$html .= '</tr>';

		$html .= '</tbody>';

		$html .= '</table>';

		$html .= '<input type="hidden" id="questions-participiants" name="questions_participiants" value="' . $questions_participiants_value . '" />';
		$html .= '<input type="hidden" id="questions-participiants-count" name="questions-participiants-count" value="' . count( $users ) . '" />';

		$html .= '</div>';

		return $html;
	}

	/**
	 * Invitations box
	 *
	 * @since 1.0.0
	 */
	public static function invite_buttons()
	{

		global $post;

		$questions_invitation_text_template = qu_get_mail_template_text( 'invitation' );
		$questions_reinvitation_text_template = qu_get_mail_template_text( 'reinvitation' );

		$questions_invitation_subject_template = qu_get_mail_template_subject( 'invitation' );
		$questions_reinvitation_subject_template = qu_get_mail_template_subject( 'reinvitation' );

		$html = '';

		if( 'publish' == $post->post_status ):
			$html .= '<div class="questions-function-element">';
			$html .= '<input id="questions-invite-subject" type="text" name="questions_invite_subject" value="' . $questions_invitation_subject_template . '" />';
			$html .= '<textarea id="questions-invite-text" name="questions_invite_text">' . $questions_invitation_text_template . '</textarea>';
			$html .= '<input id="questions-invite-button" type="button" class="button" value="' . esc_attr__( 'Invite Participiants', 'questions-locale' ) . '" /> ';
			$html .= '<input id="questions-invite-button-cancel" type="button" class="button" value="' . esc_attr__( 'Cancel', 'questions-locale' ) . '" />';
			$html .= '</div>';

			$html .= '<div class="questions-function-element">';
			$html .= '<input id="questions-reinvite-subject" type="text" name="questions_invite_subject" value="' . $questions_reinvitation_subject_template . '" />';
			$html .= '<textarea id="questions-reinvite-text" name="questions_reinvite_text">' . $questions_reinvitation_text_template . '</textarea>';
			$html .= '<input id="questions-reinvite-button" type="button" class="button" value="' . esc_attr__( 'Reinvite Participiants', 'questions-locale' ) . '" /> ';
			$html .= '<input id="questions-reinvite-button-cancel" type="button" class="button" value="' . esc_attr__( 'Cancel', 'questions-locale' ) . '" />';

			$html .= '</div>';
		else:
			$html .= '<p>' . esc_attr__( 'You can invite Participiants to this form after it is published.', 'questions-locale' ) . '</p>';
		endif;

		echo $html;
	}

	/**
	 * Checks if the user can pass
	 */
	public function check()
	{

		if( !is_user_logged_in() ){
			$this->add_message( 'error', esc_attr( 'You have to be logged in to participate.', 'questions-locale' ) );

			return FALSE;
		}

		if( !$this->is_participiant() ){
			$this->add_message( 'error', esc_attr( 'You can\'t participate this survey.', 'questions-locale' ) );

			return FALSE;
		}

		return TRUE;
	}

	/**
	 * Checks if a user can participate
	 *
	 * @param int $form_id
	 * @param int $user_id
	 *
	 * @return boolean $can_participate
	 * @since 1.0.0
	 */
	public function is_participiant( $user_id = NULL )
	{
		global $wpdb, $current_user, $questions_global, $questions_form_id;

		$is_participiant = FALSE;

		// Setting up user ID
		if( NULL == $user_id ):
			get_currentuserinfo();
			$user_id = $user_id = $current_user->ID;
		endif;

		$sql = $wpdb->prepare( "SELECT user_id FROM {$questions_global->tables->participiants} WHERE survey_id = %d", $questions_form_id );
		$user_ids = $wpdb->get_col( $sql );

		if( !in_array( $user_id, $user_ids ) ){
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * Saving data
	 *
	 * @param int $form_id
	 *
	 * @since 1.0.0
	 */
	public static function save( $form_id )
	{
		global $wpdb, $questions_global;

		/**
		 * Saving restriction options
		 */
		$add_participiants_option = $_POST[ 'questions_add_participiants_option' ];
		update_post_meta( $form_id, 'add_participiants_option', $add_participiants_option );

		/**
		 * Saving participiants
		 */
		$questions_participiants = $_POST[ 'questions_participiants' ];
		$questions_participiant_ids = explode( ',', $questions_participiants );

		$sql = "DELETE FROM {$questions_global->tables->participiants} WHERE survey_id = %d";
		$sql = $wpdb->prepare( $sql, $form_id );
		$wpdb->query( $sql );

		if( is_array( $questions_participiant_ids ) && count( $questions_participiant_ids ) > 0 ):
			foreach( $questions_participiant_ids AS $user_id ):
				$wpdb->insert( $questions_global->tables->participiants, array( 'survey_id' => $form_id,
				                                                                'user_id'   => $user_id
				                                                       ) );
			endforeach;
		endif;
	}

	/**
	 * Adding user by AJAX
	 *
	 * @since 1.0.0
	 */
	public static function ajax_add_participiants_allmembers()
	{

		$users = get_users( array( 'orderby' => 'ID'
		                    ) );

		$return_array = array();

		foreach( $users AS $user ):
			$return_array[] = array( 'id'           => $user->ID, 'user_nicename' => $user->user_nicename,
			                         'display_name' => $user->display_name, 'user_email' => $user->user_email,
			);
		endforeach;

		echo json_encode( $return_array );

		die();
	}

	/**
	 * Invite participiants AJAX
	 *
	 * @since 1.0.0
	 */
	public static function ajax_invite_participiants()
	{

		global $wpdb, $questions_global;

		$return_array = array( 'sent' => FALSE
		);

		$form_id = $_POST[ 'form_id' ];
		$subject_template = $_POST[ 'subject_template' ];
		$text_template = $_POST[ 'text_template' ];

		$sql = "SELECT user_id FROM {$questions_global->tables->participiants} WHERE survey_id = %d";
		$sql = $wpdb->prepare( $sql, $form_id );
		$user_ids = $wpdb->get_col( $sql );

		if( 'reinvite' == $_POST[ 'invitation_type' ] ):
			$user_ids_new = '';
			if( is_array( $user_ids ) && count( $user_ids ) > 0 ):
				foreach( $user_ids AS $user_id ):
					if( !qu_user_has_participated( $form_id, $user_id ) ):
						$user_ids_new[] = $user_id;
					endif;
				endforeach;
			endif;
			$user_ids = $user_ids_new;
		endif;

		$post = get_post( $form_id );

		if( is_array( $user_ids ) && count( $user_ids ) > 0 ):
			$users = get_users( array( 'include' => $user_ids, 'orderby' => 'ID',
			                    ) );

			$content = str_replace( '%site_name%', get_bloginfo( 'name' ), $text_template );
			$content = str_replace( '%survey_title%', $post->post_title, $content );
			$content = str_replace( '%survey_url%', get_permalink( $post->ID ), $content );

			$subject = str_replace( '%site_name%', get_bloginfo( 'name' ), $subject_template );
			$subject = str_replace( '%survey_title%', $post->post_title, $subject );
			$subject = str_replace( '%survey_url%', get_permalink( $post->ID ), $subject );

			foreach( $users AS $user ):
				if( '' != $user->data->display_name ){
					$display_name = $user->data->display_name;
				}else{
					$display_name = $user->data->user_nicename;
				}

				$user_nicename = $user->data->user_nicename;
				$user_email = $user->data->user_email;

				$subject_user = str_replace( '%displayname%', $display_name, $subject );
				$subject_user = str_replace( '%username%', $user_nicename, $subject_user );

				$content_user = str_replace( '%displayname%', $display_name, $content );
				$content_user = str_replace( '%username%', $user_nicename, $content_user );

				qu_mail( $user_email, $subject_user, stripslashes( $content_user ) );
			endforeach;

			$return_array = array( 'sent' => TRUE
			);
		endif;

		echo json_encode( $return_array );

		die();
	}

	/**
	 * Enqueue Scripts
	 */
	public function enqueue_scripts()
	{
		$translation = array( 'delete'                              => esc_attr__( 'Delete', 'questions-locale' ),
		                      'yes'                                 => esc_attr__( 'Yes', 'questions-locale' ),
		                      'no'                                  => esc_attr__( 'No', 'questions-locale' ),
		                      'just_added'                          => esc_attr__( 'just added', 'questions-locale' ),
		                      'invitations_sent_successfully'       => esc_attr__( 'Invitations sent successfully!', 'questions-locale' ),
		                      'invitations_not_sent_successfully'   => esc_attr__( 'Invitations could not be sent!', 'questions-locale' ),
		                      'reinvitations_sent_succes  sfully'   => esc_attr__( 'Renvitations sent successfully!', 'questions-locale' ),
		                      'reinvitations_not_sent_successfully' => esc_attr__( 'Renvitations could not be sent!', 'questions-locale' ),
		                      'added_participiants'                 => esc_attr__( 'participiant/s', 'questions-locale' ),
		);

		wp_enqueue_script( 'questions-selected-members', QUESTIONS_URLPATH . '/components/restrictions/base-restrictions/includes/js/selected-members.js' );
		wp_localize_script( 'questions-selected-members', 'translation_sm', $translation );
	}

	/**
	 * Registers and enqueues admin-specific styles.
	 *
	 * @since 1.0.0
	 */
	public static function register_admin_styles()
	{
		wp_enqueue_style( 'questions-selected-member-styles', QUESTIONS_URLPATH . '/components/restrictions/base-restrictions/includes/css/selected-members.css' );
	}
}

qu_register_restriction( 'Questions_Restriction_SelectedMembers' );
