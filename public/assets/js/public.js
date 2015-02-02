(function ( $ ) {
	"use strict";

	$(function () {

		//Use carbon checkbox
    jQuery('body').on('click','#co2-carbon',function(){

        if(jQuery('body #co2-carbon').is(":checked")) {
          //If is, checked create session value and calculate fee
          
          if(jQuery('#ship-to-different-address-checkbox').is(":checked")) {
            var name    = jQuery('#shipping_first_name').val();
            var surname = jQuery('#shipping_last_name').val();
            var address = jQuery('#shipping_address_1').val();
            var city    = jQuery('#shipping_city').val();
            var zip     = jQuery('#shipping_postcode').val();
            var email   = jQuery('#shipping_email').val();
            var phone   = jQuery('#shipping_phone').val(); 
            var country = jQuery('#shipping_country option:selected').text();
          }else{
            var name    = jQuery('#billing_first_name').val();
            var surname = jQuery('#billing_last_name').val();
            var address = jQuery('#billing_address_1').val();
            var city    = jQuery('#billing_city').val();
            var zip     = jQuery('#billing_postcode').val();
            var email   = jQuery('#billing_email').val();
            var phone   = jQuery('#billing_phone').val();  
            var country = jQuery('#billing_country option:selected').text();
          }
           
            var control = true;
          
          if ( address  == '' ){
            var control = false;
          }else if (  city == '' ){
            var control = false;
          }else if ( zip == '' ){
            var control = false;
          }
          
          if( control === false ){ 
            alert('Address is not complete!');
            return false;
          }
          
          var data = {
                        action  : 'carbon_calculate',
                        name    : name,
                        surname : surname,
                        address : address,
                        city    : city,
                        zip     : zip,
                        email   : email,
                        phone   : phone,  
                        country : country                    
                      };
          jQuery.post(ajaxurl, data, function(response){
              if(response != ''){
                //alert(response);
              } 
              location.reload();
          });        
        }else{
          //jQuery('#carbon-item').remove();
          //If is unchecked, remove session value
          var data = {
                        action: 'carbon_delete'
                      };
          jQuery.post(ajaxurl, data, function(response){
              location.reload();
          });
        }
        
        //jQuery('body').trigger('update_checkout');
        
		    
    
    });
    
	});

}(jQuery));