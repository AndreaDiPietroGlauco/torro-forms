window.torro = window.torro || {};

( function( torro, $, _, Backbone, wp, i18n ) {
	'use strict';

	var instanceCount = 0,
		initialized = [],
		callbacks = {},
		builder;

	function Builder( selector ) {
		instanceCount++;
		callbacks[ 'builder' + instanceCount ] = [];

		this.instanceNumber = instanceCount;
		this.$el = $( selector );

		this.elementTypes;

		this.form;
		this.containers;
		this.elements;
		this.elementChoices;
		this.elementSettings;
	}

	_.extend( Builder.prototype, {
		init: function() {
			if ( ! this.$el.length ) {
				console.error( i18n.couldNotInitCanvas );
				return;
			}

			torro.api.init()
				.done( _.bind( function() {
					( new torro.api.models.Form({
						id: parseInt( $( '#post_ID' ).val(), 10 )
					}) ).fetch({
						data: {
							context: 'edit',
							_embed: true
						},
						context: this,
						success: function( form ) {
							( new torro.api.collections.ElementTypes() ).fetch({
								data: {
									context: 'edit'
								},
								context: this,
								success: function( elementTypes ) {
									$( document ).ready( _.bind( function() {
										var i;

										initialized.push( this.instanceCount );

										this.elementTypes = torro.Builder.ElementTypes.fromApiCollection( elementTypes );

										this.addHooks();
										this.setupInitialData( form );

										for ( i in callbacks[ 'builder' + this.instanceCount ] ) {
											callbacks[ 'builder' + this.instanceCount ][ i ]( this );
										}

										delete callbacks[ 'builder' + this.instanceCount ];
									}, this ) );
								},
								error: function() {
									$( document ).ready( _.bind( function() {
										this.fail( i18n.couldNotLoadData );
									}, this ) );
								}
							});
						},
						error: function() {
							$( document ).ready( _.bind( function() {
								this.fail( i18n.couldNotLoadData );
							}, this ) );
						}
					});
				}, this ) )
				.fail( _.bind( function() {
					$( document ).ready( _.bind( function() {
						this.fail( i18n.couldNotLoadData );
					}, this ) );
				}, this ) );
		},

		addHooks: function() {
			if ( ! _.contains( initialized, this.instanceCount ) ) {
				return;
			}
		},

		setupInitialData: function( form ) {
			if ( ! _.contains( initialized, this.instanceCount ) ) {
				return;
			}
		},

		onLoad: function( callback ) {
			if ( _.isUndefined( callbacks[ 'builder' + this.instanceCount ] ) ) {
				callback( this );
				return;
			}

			callbacks[ 'builder' + this.instanceCount ].push( callback );
		},

		fail: function( message ) {
			var compiled = torro.template( 'failure' );

			this.$el.find( '.drag-drop-area' ).addClass( 'is-empty' ).html( compiled({ message: message }) );
		}
	});

	torro.Builder = Builder;
	torro.Builder.getInstance = function() {
		if ( ! builder ) {
			builder = new Builder( '#torro-form-canvas' );
			builder.init();
		}

		return builder;
	};

}( window.torro, window.jQuery, window._, window.Backbone, window.wp, window.torroBuilderI18n ) );
