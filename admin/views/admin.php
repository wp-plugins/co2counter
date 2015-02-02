<?php
/**
 * Represents the view for the administration dashboard.
 *
 * This includes the header, options, and other information that should provide
 * The User Interface to the end user.
 *
 * @package   Plugin_Name
 * @author    Your Name <email@example.com>
 * @license   GPL-2.0+
 * @link      http://example.com
 * @copyright 2013 Your Name or Company Name
 */
 
$error_data = array(
  'Shop name is not set!', 
  'Shop email is not set!', 
  'Shop title is not set!', 
  'Shop phone is not set!', 
  'Shop mobile is not set!', 
  'Company name is not set!',
  'Login was not finish',
  'Email address must be unique and it already exists in the database'
); 
 
global $woocommerce;
/**
 *
 * Login to API interface
 * 
 */  
if(isset($_POST['login'])){

$shop = get_option('carbon');
if(empty($shop)){ $shop = array(); }
$error = false;

    if(isset($_POST['shop_name'])){ 
      $shop['shop_name'] = sanitize_text_field($_POST['shop_name']); 
    }
    if(isset($_POST['shop_email'])){ 
      $shop['shop_email'] = sanitize_text_field($_POST['shop_email']); 
    }
    if(isset($_POST['shop_customer_title'])){ 
      $shop['shop_customer_title'] = sanitize_text_field($_POST['shop_customer_title']); 
    }
    if(isset($_POST['shop_phone'])){ 
      $shop['shop_phone'] = sanitize_text_field($_POST['shop_phone']); 
    }
    if(isset($_POST['shop_mobile'])){ 
      $shop['shop_mobile'] = sanitize_text_field($_POST['shop_mobile']); 
    }
    if(isset($_POST['company_name'])){ 
      $shop['company_name'] = sanitize_text_field($_POST['company_name']); 
    }
 
if(empty($shop['shop_name'])||$shop['shop_name']==''){ 
  $error = 0; 
}
if(empty($shop['shop_email'])||$shop['shop_email']==''){ 
  $error = 1; 
}
if(empty($shop['shop_customer_title'])||$shop['shop_customer_title']==''){
  $error = 2; 
}
if(empty($shop['shop_phone'])||$shop['shop_phone']==''){
  $error = 3; 
}
if(empty($shop['shop_mobile'])||$shop['shop_mobile']==''){
  $error = 4; 
}
if(empty($shop['company_name'])||$shop['company_name']==''){
  $error = 5;
} 


if(!$error){
$api_key     = 2;
$api_hash    = 'register_only_$$qw.123';
        
$url  = '';
$url .= 'http://co2counter.com.au/api/api3.register.php?json=';
$url .= '{"key":"'.$api_key.'",';
$url .= '"hash":"'.$api_hash.'",';
$url .= '"name":"'.$shop['shop_name'].'",';
$url .= '"email":"'.$shop['shop_email'].'",';
$url .= '"output":"full",';
$url .= '"title":"'.$shop['shop_customer_title'].'",';
$url .= '"phoneOffice":"'.$shop['shop_phone'].'",';
$url .= '"mobile":"'.$shop['shop_mobile'].'",';
$url .= '"companyName":"'.$shop['company_name'].'"}';

        $c = curl_init();
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($c, CURLOPT_URL, $url);
        $contents = curl_exec($c);
        curl_close($c);
        
$gaia_result = json_decode($contents);        
if(!empty($gaia_result->co2counter->status) && $gaia_result->co2counter->status == '1'){
  if(!empty($gaia_result->co2counter->error008)){
    $error = 7;
  }else{
    $error = 6;
  }
  wp_redirect(admin_url( 'admin.php?page=co2-offset&error="'.$error.'"' ));
}elseif($gaia_result->co2counter->status == '0'){
  wp_redirect(admin_url( 'admin.php?page=co2-offset&api_login=ok' ));
}

}else{
  wp_redirect(admin_url( 'admin.php?page=co2-offset&error="'.$error.'"' ));
}
  
}


/**
 *
 * Save setting data
 *
 */  
if(isset($_POST['control'])){

  $location_string = '';
  $shop = get_option('carbon');
  if(empty($shop)){ $shop = array(); }
    
    if(isset($_POST['carbon_enabled'])){ 
      $shop['carbon_enabled'] = sanitize_text_field($_POST['carbon_enabled']); 
    }
    if(isset($_POST['carbon_mode'])){ 
      $shop['carbon_mode'] = sanitize_text_field($_POST['carbon_mode']); 
    }

    
    
    if(isset($_POST['shop_address'])){ 
      $location_string .= $_POST['shop_address']; }
      $shop['shop_address'] = sanitize_text_field($_POST['shop_address']);
    if(isset($_POST['shop_city'])){ 
      $location_string .= ', '.$_POST['shop_city'];
      $shop['shop_city'] = sanitize_text_field($_POST['shop_city']); 
    }
    if(isset($_POST['shop_state'])){ 
      $shop['shop_state'] = sanitize_text_field($_POST['shop_state']); 
    }
    if(isset($_POST['shop_zip'])){ 
      $location_string .= ', '.$_POST['shop_zip'];
      $shop['shop_zip'] = sanitize_text_field($_POST['shop_zip']); 
    }
    if(isset($_POST['shop_country'])){ 
      $shop['shop_country'] = sanitize_text_field($_POST['shop_country']); 
    }
    
    
    
    
   
    
    $location_string = gaia_iso2ascii($location_string);
    
    try {
    $location = new Geocoder();
    $address  = urlencode($location_string);
    $response = $location::getLocation($address);
    
    if(!($response)){ 
      $shop['error'] == 'Nepodařilo se získat souřadnice eshopu!'; 
    }else{
      if($response['lat'] == 1){
        $shop['lat'] = 'Cannot load data, probably wrong adress';
      }else{
        $shop['lat'] = $response['lat'];
      }
      if($response['lng'] == 1){
        $shop['lng'] = 'Cannot load data, probably wrong adress';
      }else{
        $shop['lng'] = $response['lng'];
      }
          
    }
    
    } catch (Exception $e) {
	    var_dump($e);	    
	    exit();
	}
   
   update_option('carbon',$shop);


wp_redirect(home_url().'/wp-admin/admin.php?page=co2-offset');

}

$shop = get_option('carbon');

?>


<div class="wrap">

	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
<?php if(!empty($_GET['api_login'])){ echo '<p style="color:blue;">Login was correct</p>'; } ?>
<?php if(!empty($_GET['error'])){ echo '<p style="color:red;">'.$error_data[$_GET['error']].'</p>'; } ?>
<?php if(!empty($shop['error'])){ echo '<p style="color:red;">'.$shop['error'].'</p>'; } ?>

<form method="post" style="margin-bottom:10px;">
  
  <h3><?php _e('Save your shop data and login into API','co2-offset') ?></h3>
  <table>
    <tr>
      <th>
        <label for=""><?php _e('Name','co2-offset'); ?></label>
      </th>
      <td>
        <input type="text" name="shop_name" id="shop_name" style="width:400px;" value="<?php if(!empty($shop['shop_name'])){ echo $shop['shop_name']; } ?>" />
      </td>
    </tr>
    <tr>
      <th>
        <label for=""><?php _e('E-mail','co2-offset'); ?></label>
      </th>
      <td>
        <input type="text" name="shop_email" id="shop_email" style="width:400px;" value="<?php if(!empty($shop['shop_email'])){ echo $shop['shop_email']; } ?>" />
      </td>
    </tr>
    <tr>
      <th>
        <label for=""><?php _e('Customer title','co2-offset'); ?></label>
      </th>
      <td>
        <input type="text" name="shop_customer_title" id="shop_customer_title" style="width:400px;" value="<?php if(!empty($shop['shop_customer_title'])){ echo $shop['shop_customer_title']; } ?>" />
      </td>
    </tr>
    <tr>
      <th>
        <label for=""><?php _e('Phone','co2-offset'); ?></label>
      </th>
      <td>
        <input type="text" name="shop_phone" id="shop_phone" style="width:400px;" value="<?php if(!empty($shop['shop_phone'])){ echo $shop['shop_phone']; } ?>" />
      </td>
    </tr>            
    <tr>
      <th>
        <label for=""><?php _e('Mobile','co2-offset'); ?></label>
      </th>
      <td>
        <input type="text" name="shop_mobile" id="shop_mobile" style="width:400px;" value="<?php if(!empty($shop['shop_mobile'])){ echo $shop['shop_mobile']; } ?>" />
      </td>
    </tr>
    <tr>
      <th>
        <label for=""><?php _e('Company name','co2-offset'); ?></label>
      </th>
      <td>
        <input type="text" name="company_name" id="company_name" style="width:400px;" value="<?php if(!empty($shop['company_name'])){ echo $shop['company_name']; } ?>" />
      </td>
    </tr>
  
  </table>
  
  <input type="hidden" name="login" value="ok" />
  <input type="submit" class="button" value="<?php _e('Sing Up - Register for production API Key','co2-offset'); ?>" />
</form>  

<p>&nbsp;</p>


  <form method="post" style="margin-bottom:10px;">
  
  <h3><?php _e('Save your shop address and API setting','co2-offset') ?></h3>
  <table>
    <tr>
      <th>
        <label for=""><?php _e('Enabled','co2-offset'); ?></label>
      </th>
      <td>
        <select name="carbon_enabled">
          <option value="no" <?php if(!empty($shop['carbon_enabled'])&&$shop['carbon_enabled']=='no'){ echo 'selected="selected"'; } ?>><?php _e('No','co2-offset'); ?></option>
          <option value="yes" <?php if(!empty($shop['carbon_enabled'])&&$shop['carbon_enabled']=='yes'){ echo 'selected="selected"'; } ?>><?php _e('Yes','co2-offset'); ?></option>
        </select>
      </td>
    </tr>
    <tr>
      <th>
        <label for=""><?php _e('Environment','co2-offset'); ?></label>
      </th>
      <td>
        <select name="carbon_mode">
          <option value="sandbox" <?php if(!empty($shop['carbon_mode'])&&$shop['carbon_mode']=='sandbox'){ echo 'selected="selected"'; } ?>><?php _e('Sandbox','co2-offset'); ?></option>
          <option value="production" <?php if(!empty($shop['carbon_mode'])&&$shop['carbon_mode']=='production'){ echo 'selected="selected"'; } ?>><?php _e('Production','co2-offset'); ?></option>
        </select>
      </td>
    </tr>
    <tr>
      <th>
        <label for=""><?php _e('Address','co2-offset'); ?></label>
      </th>
      <td>
        <input type="text" name="shop_address" id="shop_address" style="width:400px;" value="<?php if(!empty($shop['shop_address'])){ echo $shop['shop_address']; } ?>" />
      </td>
    </tr>
    <tr>
      <th>
        <label for=""><?php _e('City','co2-offset'); ?></label>
      </th>
      <td>
        <input type="text" name="shop_city" id="shop_city" style="width:400px;" value="<?php if(!empty($shop['shop_city'])){ echo $shop['shop_city']; } ?>" />
      </td>
    </tr>
    <tr>
      <th>
        <label for=""><?php _e('State','co2-offset'); ?></label>
      </th>
      <td>
        <input type="text" name="shop_state" id="shop_state" style="width:400px;" value="<?php if(!empty($shop['shop_state'])){ echo $shop['shop_state']; } ?>" />
      </td>
    </tr>
    <tr>
      <th>
        <label for=""><?php _e('ZIP','co2-offset'); ?></label>
      </th>
      <td>
        <input type="text" name="shop_zip" id="shop_zip" style="width:400px;" value="<?php if(!empty($shop['shop_zip'])){ echo $shop['shop_zip']; } ?>" />
      </td>       
    </tr>
    <tr>
      <th>
        <label for=""><?php _e('Country','co2-offset'); ?></label>
      </th>
      <td>
        <input type="text" name="shop_country" id="shop_country" style="width:400px;" value="<?php if(!empty($shop['shop_country'])){ echo $shop['shop_country']; } ?>" />
      </td>       
    </tr>
    <?php if(!empty($shop['lat'])){ ?>
    <tr> 
      <th>
        <label for=""><?php _e('Latitude','co2-offset'); ?></label>
      </th>
      <td>
        <?php echo $shop['lat']; ?>
      </td>       
    </tr>
    <?php } ?>
    <?php if(!empty($shop['lng'])){ ?>
    <tr> 
      <th>
        <label for=""><?php _e('Longitude','co2-offset'); ?></label>
      </th>
      <td>
        <?php echo $shop['lng']; ?>
      </td>       
    </tr>
    <?php } ?>
    <tr>
      <th>
        <label for=""><?php _e('API key','co2-offset'); ?></label>
      </th>
      <td>
        <input type="text" name="shop_api" id="shop_api" style="width:400px;" value="<?php if(!empty($shop['shop_api'])){ echo $shop['shop_api']; } ?>" />
      </td>       
    </tr>
    <tr>
      <th>
        <label for=""><?php _e('API hash','co2-offset'); ?></label>
      </th>
      <td>
        <input type="text" name="shop_hash" id="shop_hash" style="width:400px;" value="<?php if(!empty($shop['shop_hash'])){ echo $shop['shop_hash']; } ?>" />
      </td>       
    </tr>
  </table>  
    
    <input type="hidden" name="control" value="ok" />
    <input type="submit" class="button" value="<?php _e('Save','co2-offset'); ?>" />
  </form>	

</div>
