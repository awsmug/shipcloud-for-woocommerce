jQuery( function( $ ) {

	var edit_address = function(){
		$( '.btn_edit_address' ).click( function ( e ){
			var div_address = $( this ).parent().parent().find( ".address" );
			var div_edit_address = $( this ).parent().parent().find( ".edit_address" );
			var div_edit_address_inputs = div_edit_address.find( 'input' );
			var div_edit_address_selects = div_edit_address.find( 'select' );

			if( div_edit_address.hasClass( 'disabled' ) ){
				console.log( div_edit_address_inputs );

				div_edit_address_inputs.each(function() {
					$( this ).removeAttr( 'disabled' );
				});

				div_edit_address_selects.each(function() {
					$( this ).removeAttr( 'disabled' );
				});

				div_edit_address.removeClass( 'disabled' )

			}else{
				div_edit_address_inputs.each(function() {
					$( this).attr( 'disabled', 'disabled' );
				});

				div_edit_address_selects.each(function() {
					$( this ).attr( 'disabled', 'disabled' );
				});

				div_edit_address.addClass( 'disabled' );
			}
		});
	}
	edit_address();
	
	var carrier_select = function(){
		$( '.carrier_select' ).click( function (){
			
			var template = $( this ).parent();
			
			var carrier = template.find( "input[name='carrier']" ).val();
			var width = template.find( "input[name='width']" ).val();
			var height = template.find( "input[name='height']" ).val();
			var length = template.find( "input[name='length']" ).val();
			var weight = template.find( "input[name='weight']" ).val();

			$( '.shipcloud-tabs' ).tabs( "option", "active", 0 );

			var template_value = carrier + ';' + width + ';' + height + ';' + length + ';' + weight;

			$( "select[name='parcel_template']" ).val( template_value );

		});
	}
	carrier_select();


	$( '#check_parcel_settings' ).click( function(){

		// We need to give any address to test parcel settings

		// From Cologne Cathedral
		var sender_street 	= 'Domkloster';
		var sender_street_nr= '4';
		var sender_postcode = '50667';
		var sender_city 	= 'Köln';
		var sender_country 	= 'DE';

		// From Cologne Main Station
		var recipient_street 	= 'Trankgasse';
		var recipient_street_nr	= '11';
		var recipient_postcode 	= '50667';
		var recipient_city 		= 'Köln';
		var recipient_country 	= 'DE';

		var carrier 	= $( "select[name='carrier']" ).val( );
		var width 		= $( "input[name='width']" ).val();
		var height 		= $( "input[name='height']" ).val();
		var length 		= $( "input[name='length']" ).val();
		var weight 		= $( "input[name='weight']" ).val();

		var data = {
			'action': 'shipcloud_calculate_shipping',
			'sender_street' : sender_street,
			'sender_street_nr' : sender_street_nr,
			'sender_postcode' : sender_postcode,
			'sender_city' : sender_city,
			'sender_country' : sender_country,
			'recipient_street' : recipient_street,
			'recipient_street_nr': recipient_street_nr,
			'recipient_postcode' : recipient_postcode,
			'recipient_city' : recipient_city,
			'recipient_country' : recipient_country,
			'carrier': carrier,
			'width': width,
			'height': height,
			'length': length,
			'weight': weight
		};

		var button = $( this );
		button.addClass( 'button-loading' );

		$.post( ajaxurl, data, function( response ) {

			var result = jQuery.parseJSON( response );

			if( result.errors ){

				var html = '';
				result.errors.forEach( function( entry ){
					html+= entry + '<br />';
				});

				$( '.shipcloud-message').removeClass( 'updated' ).addClass( 'error' ).fadeIn();;
				$( '.shipcloud-message .info' ).html( html );
				$( '.shipcloud-message').delay( 3000 ).fadeOut();

			}if( result.price ){

				var html = wcsc_translate.parcel_dimensions_check_yes;

				$( '.shipcloud-message').removeClass( 'error' ).addClass( 'updated' ).fadeIn();
				$( '.shipcloud-message .info' ).html( html );
				$( '.shipcloud-message').delay( 3000 ).fadeOut();
			}

			button.removeClass( 'button-loading' );
		});
	});
	
	$( '#shipcloud_calculate_price' ).click( function(){
		
		var sender_street 	= $( "input[name='sender_address[street]']" ).val( );
		var sender_street_nr= $( "input[name='sender_address[street_nr]']" ).val( );
		var sender_postcode = $( "input[name='sender_address[postcode]']" ).val( );
		var sender_city 	= $( "input[name='sender_address[city]']" ).val( );
		var sender_country 	= $( "select[name='sender_address[country]']" ).val( );
		
		var recipient_street 	= $( "input[name='recipient_address[street]']" ).val( );
		var recipient_street_nr	= $( "input[name='recipient_address[street_nr]']" ).val( );
		var recipient_postcode 	= $( "input[name='recipient_address[postcode]']" ).val( );
		var recipient_city 		= $( "input[name='recipient_address[city]']" ).val( );
		var recipient_country 	= $( "select[name='recipient_address[country]']" ).val( );

		var parcel_id 	= $( "select[name=parcel_id" ).val( );

		var carrier 	= $( "input[name='parcel[" + parcel_id + "][carrier]']" ).val( );
		var width 		= $( "input[name='parcel[" + parcel_id + "][width]']" ).val( );
		var height 		= $( "input[name='parcel[" + parcel_id + "][height]']" ).val( );
		var length 		= $( "input[name='parcel[" + parcel_id + "][length]']" ).val( );
		var weight 		= $( "input[name='parcel[" + parcel_id + "][weight]']" ).val( );
		
		var data = {
			'action': 'shipcloud_calculate_shipping',
			'sender_street' : sender_street,
			'sender_street_nr' : sender_street_nr,
			'sender_postcode' : sender_postcode,
			'sender_city' : sender_city,
			'sender_country' : sender_country,
			'recipient_street' : recipient_street,
			'recipient_street_nr': recipient_street_nr,
			'recipient_postcode' : recipient_postcode,
			'recipient_city' : recipient_city,
			'recipient_country' : recipient_country,
			'carrier': carrier,
			'width': width,
			'height': height,
			'length': length,
			'weight': weight
		};

		var button = $( this );
		button.addClass( 'button-loading' );
		
		$.post( ajaxurl, data, function( response ) {
			
			var result = jQuery.parseJSON( response );
			
			if( result.errors ){
				var html = '<ul class="errors">';
				result.errors.forEach( function( entry ){
					html+= '<li>' + entry + '</li>';
				});
				html+= '</ul>';
				
				$( '#parcel_templates .info' ).fadeIn().html( html );
				$( '#shipcloud_create_label').fadeOut();

			}if( result.price ){
				var html = '<div class="notice">';
				html+= wcsc_translate.price_text + ' ' +  result.price;
				html+= '</div>';
				
				$( '#parcel_templates .info' ).fadeIn().html( html );
				$( '#shipcloud_create_label').fadeIn();
			}
			button.removeClass( 'button-loading' );
		});


	});

	var shipcloud_create_label = function(){
		var order_id = $( "#post_ID" ).val();

		var sender_first_name 	= $( "input[name='sender_address[first_name]']" ).val( );
		var sender_last_name 	= $( "input[name='sender_address[last_name]']" ).val( );
		var sender_company 	= $( "input[name='sender_address[company]']" ).val( );
		var sender_street 	= $( "input[name='sender_address[street]']" ).val( );
		var sender_street_nr= $( "input[name='sender_address[street_nr]']" ).val( );
		var sender_postcode = $( "input[name='sender_address[postcode]']" ).val( );
		var sender_city 	= $( "input[name='sender_address[city]']" ).val( );
		var sender_country 	= $( "select[name='sender_address[country]']" ).val( );

		var recipient_first_name 	= $( "input[name='recipient_address[first_name]']" ).val( );
		var recipient_last_name 	= $( "input[name='recipient_address[last_name]']" ).val( );
		var recipient_company 	= $( "input[name='recipient_address[company]']" ).val( );
		var recipient_street 	= $( "input[name='recipient_address[street]']" ).val( );
		var recipient_street_nr= $( "input[name='recipient_address[street_nr]']" ).val( );
		var recipient_postcode = $( "input[name='recipient_address[postcode]']" ).val( );
		var recipient_city 	= $( "input[name='recipient_address[city]']" ).val( );
		var recipient_country 	= $( "select[name='recipient_address[country]']" ).val( );

		var parcel_id 	= $( "select[name=parcel_id" ).val( );

		var carrier 	= $( "input[name='parcel[" + parcel_id + "][carrier]']" ).val( );
		var width 		= $( "input[name='parcel[" + parcel_id + "][width]']" ).val( );
		var height 		= $( "input[name='parcel[" + parcel_id + "][height]']" ).val( );
		var length 		= $( "input[name='parcel[" + parcel_id + "][length]']" ).val( );
		var weight 		= $( "input[name='parcel[" + parcel_id + "][weight]']" ).val( );

		var parcel_title = '';

		var data = {
			'action': 'shipcloud_create_label',
			'order_id' : order_id,
			'sender_first_name' : sender_first_name,
			'sender_last_name' : sender_last_name,
			'sender_company' : sender_company,
			'sender_street' : sender_street,
			'sender_street_nr' : sender_street_nr,
			'sender_postcode' : sender_postcode,
			'sender_city' : sender_city,
			'sender_country' : sender_country,
			'recipient_first_name' : recipient_first_name,
			'recipient_last_name' : recipient_last_name,
			'recipient_company' : recipient_company,
			'recipient_street' : recipient_street,
			'recipient_street_nr': recipient_street_nr,
			'recipient_postcode' : recipient_postcode,
			'recipient_city' : recipient_city,
			'recipient_country' : recipient_country,
			'parcel_id' : parcel_id,
			'carrier': carrier,
			'width': width,
			'height': height,
			'length': length,
			'weight': weight
		};

		var button = $( '#shipcloud_create_label' );
		button.addClass( 'button-loading-blue' );

		$.post( ajaxurl, data, function( response ) {
			try
			{
				var result = JSON.parse( response );

				if( result.errors != null ){
					var html = '<ul class="errors">';
					result.errors.forEach( function( entry ){
						html+= '<li>' + entry + '</li>';
					});
					html+= '</ul>';

					$( '.parcel .info' ).fadeIn().html( html ).delay( 5000 ).fadeOut( 2000 );
				}else{
					shipcloud_order_pickup();
				}
			}
			catch( e )
			{
				$( '.shipment_labels' ).prepend( response );
			}

			button.removeClass( 'button-loading-blue' );
		});
	}

	var shipcloud_order_pickup = function(){
		$( '.shipcloud-order-pickup' ).click( function() {

			var carrier = $( this ).parent().find( "input[name='carrier']" ).val();
			var shipment_id = $( this ).parent().find( "input[name='shipment_id']" ).val();

			var data = {
				'action': 'shipcloud_request_pickup',
				'carrier': carrier,
				'shipment_id': shipment_id,
			};

			var button = $( this );
			button.addClass( 'button-loading-blue' );

			$.post(ajaxurl, data, function (response) {
				try {
					var result = JSON.parse(response);

					if (result.errors != null) {
						var html = '<ul class="errors">';
						result.errors.forEach(function (entry) {
							html += '<li>' + entry + '</li>';
						});
						html += '</ul>';

						$('.parcel .info').fadeIn().html(html).delay(5000).fadeOut(2000);
					} else {

					}
				}
				catch (e) {
					$('.shipment_labels').prepend(response);
					$('#no_label_created').fadeOut();
				}

				button.removeClass('button-loading-blue');
			});
		});
	}
	shipcloud_order_pickup();

	$( '#shipcloud_create_label' ).click( function(){
		var ask_create_label = $( '#ask_create_label' );

		ask_create_label.dialog({                   
            'dialogClass'   : 'wcsc-dialog wp-dialog',
            'modal'         : true,
            'autoOpen'      : false, 
            'closeOnEscape' : true,
            'minHeight'     : 80,
            'buttons'       : [{
                    text: wcsc_translate.yes,
                    click: function() {
                            shipcloud_create_label();
                            $( this ).dialog( "close" );
                        }
                    },
                    {
                    text: wcsc_translate.no,
                    click: function() {
                        	$( this ).dialog( "close" );
                        }
                    },
                ],
                
        });
        
        ask_create_label.dialog( "open" );
	});

	/**
	 * Hiding parcel template adding button if value in form has changed
	 */
	$( "input[name='parcel[width]'],  input[name='parcel[height]'],  input[name='parcel[length]'],  input[name='parcel[weight]']" ).focusin(function(){
		$( '.parcel .info' ).fadeOut();
		$( '#shipcloud_add_parcel_template').fadeOut();
	});

	$( "select[name='parcel[carrier]']" ).change(function(){
		$( '.parcel .info' ).fadeOut();
		$( '#shipcloud_add_parcel_template').fadeOut();
	});

	$( "select[name='parcel_template']" ).change(function(){
		$( '#parcel_templates .info' ).fadeOut();
	});

	/**
	 * Function to switch to parcel templates
	 */
    $('.shipcloud-switchto-parcel-tamplates').click( function () {
        $( '.shipcloud-tabs' ).tabs( "option", "active", 1 );
    });

	/**
	 * CSS Corrections
	 */

	$( '#shipcloud' ).find( '.ui-tabs' ).removeClass( 'ui-tabs' );
	$( '#shipcloud' ).find( '.ui-widget' ).removeClass( 'ui-widget' );
	$( '#shipcloud' ).find( '.ui-widget-content' ).removeClass( 'ui-widget-content' );
	$( '#shipcloud' ).find( '.ui-corner-all' ).removeClass( 'ui-corner-all' );
	$( '#shipcloud' ).find( '.ui-tabs-nav' ).removeClass( 'ui-tabs-nav' );
	$( '#shipcloud' ).find( '.ui-helper-reset' ).removeClass( 'ui-helper-reset' );
	$( '#shipcloud' ).find( '.ui-helper-clearfix' ).removeClass( 'ui-helper-clearfix' );
	$( '#shipcloud' ).find( '.ui-widget-header' ).removeClass( 'ui-widget-header' );
	$( '#shipcloud' ).find( '.ui-state-default' ).removeClass( 'ui-state-default' );
	$( '#shipcloud' ).find( '.ui-corner-top' ).removeClass( 'ui-corner-top' );
	$( '#shipcloud' ).find( '.ui-state-active' ).removeClass( 'ui-state-active' );
	$( '#shipcloud' ).find( '.ui-tabs-panel' ).removeClass( 'ui-tabs-panel' );
	$( '#shipcloud' ).find( '.ui-corner-bottom' ).removeClass( 'ui-corner-bottom' );
	 
});