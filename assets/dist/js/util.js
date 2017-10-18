/*!
 * Torro Forms Version 1.0.0-beta.8 (http://torro-forms.com)
 * Licensed under GNU General Public License v3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
window.torro = window.torro || {};

( function( torro, $, _, wp, wpApiSettings ) {
	var apiPromise;

	torro.api = {
		collections: {},

		models: {},

		root: wpApiSettings.root || window.location.origin + '/wp-json/',

		versionString: 'torro/v1/',

		init: function() {
			var deferred;

			if ( ! apiPromise ) {
				deferred = $.Deferred();
				apiPromise = deferred.promise();

				wp.api.init({ versionString: torro.api.versionString })
					.done( function() {
						var origUrl = wp.api.collections.ElementsTypes.prototype.url;

						torro.api.collections = _.extend( torro.api.collections, {
							Forms: wp.api.collections.Forms,
							FormCategories: wp.api.collections.Form_categories,
							Containers: wp.api.collections.Containers,
							Elements: wp.api.collections.Elements,
							ElementTypes: wp.api.collections.ElementsTypes.extend({
								url: function() {
									/* Fix bug in element types URL. */
									return origUrl.call( this ).replace( 'elements//types', 'elements/types' );
								}
							}),
							ElementChoices: wp.api.collections.Element_choices,
							ElementSettings: wp.api.collections.Element_settings,
							Submissions: wp.api.collections.Submissions,
							SubmissionValues: wp.api.collections.Submission_values,
							Participants: wp.api.collections.Participants
						});

						torro.api.models = _.extend( torro.api.models, {
							Form: wp.api.models.Forms,
							FormCategory: wp.api.models.Form_categories,
							Container: wp.api.models.Containers,
							Element: wp.api.models.Elements,
							ElementType: wp.api.models.ElementsTypes,
							ElementChoice: wp.api.models.Element_choices,
							ElementSetting: wp.api.models.Element_settings,
							Submission: wp.api.models.Submissions,
							SubmissionValue: wp.api.models.Submission_values,
							Participant: wp.api.models.Participants
						});

						deferred.resolveWith( torro.api );
					})
					.fail( function() {
						deferred.rejectWith( torro.api );
					});
			}

			return apiPromise;
		}
	};

	torro.template = function( id ) {
		return wp.template( 'torro-' + id );
	};

	torro.isTempId = function( id ) {
		return _.isString( id ) && 'temp_id_' === id.substring( 0, 8 );
	};

	torro.generateTempId = function() {
		var random = Math.floor( Math.random() * ( 10000 - 10 + 1 ) ) + 10;

		random = random * ( new Date() ).getTime();
		random = random.toString();

		return ( 'temp_id_' + random ).substring( 0, 14 );
	};
}( window.torro, window.jQuery, window._, window.wp, window.wpApiSettings || {} ) );
