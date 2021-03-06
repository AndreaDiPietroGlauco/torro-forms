/*!
 * Torro Forms Version 1.0.0-beta.10 (https://torro-forms.com)
 * Licensed under GNU General Public License v2 (or later) (http://www.gnu.org/licenses/gpl-2.0.html)
 */
( function( $ ) {
	'use strict';

	var $sideSortables = $( '#postbox-container-1 #side-sortables' );
	var extraMargin = 20;
	var height, offsetTop, offsetBottom, windowHeight, toolbarOffset, lastScroll = 0;

	if ( ! $sideSortables.length ) {
		return;
	}

	function checkRightContainerOffset() {
		var scrollTop = $( window ).scrollTop();

		if ( height < windowHeight - toolbarOffset - offsetTop ) {
			if ( scrollTop + toolbarOffset >= offsetTop - extraMargin ) {
				$sideSortables.css({
					position: 'fixed',
					top: toolbarOffset + extraMargin,
					bottom: 'auto'
				});
			} else {
				$sideSortables.css({
					position: 'relative',
					top: 'auto',
					bottom: 'auto'
				});
			}
		} else {
			if ( scrollTop > lastScroll ) {
				if ( scrollTop + windowHeight >= $( document ).height() - offsetBottom + extraMargin ) {
					$sideSortables.css({
						position: 'fixed',
						top: 'auto',
						bottom: scrollTop + windowHeight - $( document ).height() + offsetBottom - extraMargin
					});
				} else if ( scrollTop + windowHeight >= offsetTop + height ) {
					$sideSortables.css({
						position: 'fixed',
						top: 'auto',
						bottom: extraMargin
					});
				} else {
					$sideSortables.css({
						position: 'relative',
						top: 'auto',
						bottom: 'auto'
					});
				}
			} else {
				if ( scrollTop + toolbarOffset >= $( document ).height() - offsetBottom - height - extraMargin ) {
					$sideSortables.css({
						position: 'fixed',
						top: 'auto',
						bottom: scrollTop + windowHeight - $( document ).height() + offsetBottom - extraMargin
					});
				} else if ( scrollTop + toolbarOffset >= offsetTop ) {
					$sideSortables.css({
						position: 'fixed',
						top: toolbarOffset + extraMargin,
						bottom: 'auto'
					});
				} else {
					$sideSortables.css({
						position: 'relative',
						top: 'auto',
						bottom: 'auto'
					});
				}
			}
		}

		lastScroll = scrollTop;
	}

	function refreshParams() {
		height = $sideSortables.height();
		offsetTop = $sideSortables.offset().top;
		offsetBottom = $( document ).height() - $( '#poststuff' ).offset().top - $( '#poststuff' ).outerHeight();

		windowHeight = $( window ).height();

		toolbarOffset = 0;
		if ( $( 'body' ).hasClass( 'admin-bar' ) ) {
			if ( document.documentElement.clientWidth <= 782 ) {
				toolbarOffset = 46;
			} else {
				toolbarOffset = 32;
			}
		}

		checkRightContainerOffset();
	}

	refreshParams();

	$( window ).on( 'resize', refreshParams );
	$( window ).on( 'scroll', checkRightContainerOffset );

}( window.jQuery ) );
