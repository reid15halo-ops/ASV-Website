function clmns_setMessage( msg ) {
	(function($) {
		$( ".error" ).hide();
		$( ".clmns_image_update_message" ).html( msg ).show();
	})(jQuery);
}

function clmns_setError( msg ) {
	(function($) {
		$( ".clmns_image_update_message" ).hide();
		$( ".error" ).html( msg ).show();
	})(jQuery);
}

(function($) {
	$(document).ready( function() {

		if ( 0 < $( '#clmns-add-column' ).length  ) {
			var clmnsCurrentColumn = $( '#clmns_current_column' ).val();

			$( '#clmns-add-column' ).click( function() {
				clmnsCurrentColumn = $( '#clmns_current_column' ).val();
				clmnsCurrentColumn++;
				child = $( '.clmns-clone.hidden' ).clone().removeClass( 'clmns-clone' ).removeClass( 'hidden' );
				child.find( '.clmns_display_button' ).attr( 'name', 'clmns[display_button][' + ( clmnsCurrentColumn - 1 ) + ']' )
				child.insertBefore( '.clmns-clone.hidden' ).find( '.clmns-column-number' ).text( clmnsCurrentColumn );
				$( '#clmns_current_column' ).val( clmnsCurrentColumn );
				return false;
			});
		}

		if ( 0 < $( '.clmns-delete-column' ).length ) {
			$( document ).on( 'click', '.clmns-delete-column', function() {
				if ( 0 < $( this ).parents( '.clmns-single-column' ).find( 'input[name^="clmns[id]"]' ).length ) {	
					$( '#clmns-delete-column-ids' ).append( '<input type="hidden" name="clmns[delete][]" value="' + $( this ).parents( '.clmns-single-column ' ).find( 'input[name^="clmns[id]"]' ).val() + '" />' );
				}
				$( this ).parents( '.clmns-single-column' ).remove();
				return false;
			});
		}

		if ( 0 < $( '.clmns_display_button' ).length  ) {

			$( '.clmns_display_button' ).each( function() {
				if ( $( this ).is( ':checked' ) ) {
					$( this ).parents( 'table' ).find( '.clmns_button' ).show();
				} else {
					$( this ).parents( 'table' ).find( '.clmns_button' ).hide();
				}
			});

			$( document ).on( 'click', '.clmns_display_button', function() {
				if ( $( this ).is( ':checked' ) ) {
					$( this ).parents( 'table' ).find( '.clmns_button' ).show();
				} else {
					$( this ).parents( 'table' ).find( '.clmns_button' ).hide();
				}
			});
		}

		if ( 0 < $( '#clmns_widget_background_color' ).length ) {
			$( '#clmns_widget_background_color' ).wpColorPicker();
		}
		if ( 0 < $( '#clmns_border_color' ).length ) {
			$( '#clmns_border_color' ).wpColorPicker();
		}
		if ( 0 < $( '#clmns_border_hover_color' ).length ) {
			$( '#clmns_border_hover_color' ).wpColorPicker();
		}
		if ( 0 < $( '#clmns_background_color' ).length ) {
			$( '#clmns_background_color' ).wpColorPicker();
		}
		if ( 0 < $( '#clmns_background_hover_color' ).length ) {
			$( '#clmns_background_hover_color' ).wpColorPicker();
		}		
		if ( 0 < $( '#clmns_shadow_hover_color' ).length ) {
			$( '#clmns_shadow_hover_color' ).wpColorPicker();
		}

		if( 0 < $( '#clmns_slider' ).length ) {
			$( '#clmns_slider' ).slider({
				range: 'min',
				min: 0.1,
				max: 1,
				step: 0.1,
				value: $( '#clmns_widget_background_opacity' ).val(),
				slide: function( event, ui ) {
					$( '#clmns_widget_background_opacity' ).val( ui.value );
				}
			});
		}

		$( '.clmns_settings_form' ).on( 'click', '.add_media', function open_media_window() {
			var currentParent = $( this ).parents( 'td' );
			if ( this.window === undefined ) {
				this.window = wp.media({
					title: clmns_vars.wp_media_title,
					library: { type: 'image' },
					multiple: true,
					button: { text: clmns_vars.wp_media_button }
				});

				var self = this; /* Needed to retrieve our variable in the anonymous function below */
				this.window.on( 'select', function() {
					var all = self.window.state().get( 'selection' ).toJSON();
					all.forEach( function( item, i, arr ) {
						if ( currentParent.find( '.clmns-image-block' ).length > 0 ) {
							currentParent.find( '.clmns-image-block .clmns-image' ).html( '<img src="' + item.url + '" /><span class="clmns-delete-image"><span class="dashicons dashicons-no-alt"></span></span>' );
							currentParent.find( '.clmns-image-block .clmns-image-id' ).val( item.id );
						} else if( currentParent.find( '.clmns-background-block' ).length > 0 ) {
							currentParent.find( '.clmns-background-block .clmns-image' ).html( '<img src="' + item.url + '" /><span class="clmns-delete-image"><span class="dashicons dashicons-no-alt"></span></span>' );
							currentParent.find( '.clmns-background-block .clmns-background-image' ).val( item.id );
						}
					});
				});
			}

			this.window.open();
			return false;
		});

		$( '.clmns_settings_form' ).on( 'click', '.clmns-delete-image', function(){
			$( this ).parent().next().val( '' );
			$( this ).parent().html( '' );
		});

		$( '.clmns-column-style' ).on( 'change', function(){
			$( '.clmns-column-style-child' ).addClass( 'hidden' );
			$( '.clmns_' + $( this ).val() ).removeClass( 'hidden' );
		});		

		$( document ).on( 'click', '.clmns-media-actions-delete', function() {
			if ( window.confirm( clmns_vars.warnSingleDelete ) ) {
				var attachment_id = $( this ).parent().find( '.clmns_attachment_id' ).val(),
					column_id = $( this ).parent().find( '.clmns_column_id' ).val();

				$.ajax({
					url: '../wp-admin/admin-ajax.php',
					type: "POST",
					data: "action=clmns_delete_image&delete_id_array=" + attachment_id + "&column_id=" + column_id + "&clmns_ajax_nonce_field=" + clmns_vars.clmns_nonce,
					success: function( result ) {
						$( '#post-' + attachment_id ).remove();
						tb_remove();
						if ( ! $( '.attachments li' ).length )
							$( '.clmns-media-bulk-select-button' ).hide();
					}
				});
			}
		});

		$( '.clmns-media-bulk-delete-selected-button' ).on( 'click', function() {
			if ( 'disabled' != $( this ).attr( 'disabled' ) ) {
				if ( window.confirm( clmns_vars.warnBulkDelete ) ) {
					var delete_id_array = '';
					$( '.attachments li.selected' ).each( function() {
						delete_id_array += $( this ).attr( 'id' ).replace( 'post-', '' ) + ',';
					});
					var column_id = $( '.clmns_column_id' ).val();
					$( '.clmns-media-spinner' ).css( 'display', 'inline-block' );
					$( '.attachments' ).attr( 'disabled', 'disabled' );
					$.ajax({
						url: '../wp-admin/admin-ajax.php',
						type: "POST",
						data: "action=clmns_delete_image&delete_id_array=" + delete_id_array + "&column_id=" + column_id + "&clmns_ajax_nonce_field=" + clmns_vars.clmns_nonce,
						success: function( result ) {
							if ( result == 'updated' ) {
								$( '.clmns-media-attachment.selected' ).remove();
								$( '.clmns-media-bulk-delete-selected-button' ).attr( 'disabled', 'disabled' );
								if ( ! $( '#post-body-content .attachments li' ).length ) {
									$( '.clmns-media-bulk-cansel-select-button' ).trigger( 'click' );
									$( '.clmns-media-bulk-select-button' ).hide();
								}
							}
							$( '.clmns-media-spinner' ).css( 'display', 'none' );
							$( '.attachments' ).removeAttr( 'disabled' );
						}
					});
				}
			}
			return false;
		});
	});
})(jQuery);

/* Create notice on a gallery page */
function clmns_notice_view( data_id ) {
	(function( $ ) {
		/*	function to send Ajax request to gallery notice */
		clmns_notice_media_attach = function( thumb_id ) {
			$.ajax({
				url: "../wp-admin/admin-ajax.php",
				type: "POST",
				data: "action=clmns_media_check&thumbnail_id=" + thumb_id + "&clmns_ajax_nonce_field=" + clmns_vars.clmns_nonce,
				success: function( html ) {
					if ( undefined != html.data ) {
						$( ".media-frame-content" ).find( "#clmns_media_notice" ).html( html.data );
						$( '.button.media-button-select' ).attr( 'disabled', 'disabled' );
					} else {
						$( '.button.media-button-select' ).removeAttr( 'disabled' );
					}
				}
			});
		}
		clmns_notice_media_attach( data_id );
	})( jQuery );
}