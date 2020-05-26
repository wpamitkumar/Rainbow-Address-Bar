jQuery( document ).ready( function( $ ) {
	// Appear Color picker on rab-post-color class.
	$( '.rab-post-color' ).wpColorPicker();

	$.fn.swichColorButton = function( thisObj ) { 
		if( thisObj.is( ':checked' ) ) {
			thisObj.parents().siblings().children().children( 'button' ).removeAttr( 'disabled' );
		} else {
			thisObj.parents().siblings().children().children( 'button' ).attr( 'disabled', 'disabled' );
		}
	}

	// If post-type not selected then disabled color picker.
	$( 'input[type=checkbox].rab-post-switch' ).each( function( index ) {
		$.fn.swichColorButton( $( this ) );
	} );

	$( '.rab-post-switch' ).change( function() {
		$.fn.swichColorButton( $( this ) );
	} );
} );
