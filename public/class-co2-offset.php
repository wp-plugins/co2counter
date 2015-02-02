<?php
/**
 * @package   CO2 Offset
 * @author    Vladislav Musilek <support@czdigital.com.au>
 * @license   GPL-2.0+
 * @link      http://opensource.org/licenses/gpl-2.0.php
 * @copyright 2014 CZ Digital Pty Ltd
 */

class Co2_Offset {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   1.0.0
	 *
	 * @var     string
	 */
	const VERSION = '1.0.0';

	/**
	 * Plugin slug
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_slug = 'co2-offset';

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Activate plugin when new blog is added
		add_action( 'wpmu_new_blog', array( $this, 'activate_new_site' ) );

		// Load public-facing style sheet and JavaScript.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

    //Define ajax url
    add_action('wp_head', array( $this, 'carbon_ajaxurl' ) );

		//Calculate carbon as fee
    add_action( 'woocommerce_cart_calculate_fees' , array( $this, 'woo_calculate_total' ) );
    //Display carbon checkbox
    add_action( 'woocommerce_review_order_after_shipping' , array( $this, 'woo_after_shipping' ) );
    
    //Ajax calculate carbon
    add_action('wp_ajax_carbon_calculate', array( $this, 'carbon_calculate_ajax') );
    add_action('wp_ajax_nopriv_carbon_calculate', array( $this, 'carbon_calculate_ajax') );
    add_action('wp_ajax_carbon_delete', array( $this, 'carbon_delete_ajax') );
    add_action('wp_ajax_nopriv_carbon_delete', array( $this, 'carbon_delete_ajax') ); 
   
   
   
    add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'save_offset_value' ) );
    add_action( 'init', array( $this, 'output_buffer' ) );
    
    add_filter('woocommerce_checkout_get_value', array( $this, 'custom_checkout_value' ), 10, 2 );
    

	}


  
  public function custom_checkout_value($value,$input){
  
  $opt = get_option('trans_data');
    
    if(!empty($opt)){
    
      if($input == 'billing_first_name'){
        return $opt['name'];
      }
      if($input == 'billing_last_name'){
        return $opt['surname'];
      }
      if($input == 'billing_address_1'){
        return $opt['address'];
      }
      if($input == 'billing_city'){
        return $opt['city'];
      }
      if($input == 'billing_postcode'){
        return $opt['zip'];
      }
      if($input == 'billing_email'){
        return $opt['email'];
      }
      if($input == 'billing_phone'){
        return $opt['phone'];
      }
      
      
      if($input == 'shipping_first_name'){
        return $opt['name'];
      }
      if($input == 'shipping_last_name'){
        return $opt['surname'];
      }
      if($input == 'shipping_address_1'){
        return $opt['address'];
      }
      if($input == 'shipping_city'){
        return $opt['city'];
      }
      if($input == 'shipping_postcode'){
        return $opt['zip'];
      }
            
      }
      
  }

	/**
	 * Return the plugin slug.
	 *
	 * @since    1.0.0
	 *
	 * @return    Plugin slug variable.
	 */
	public function get_plugin_slug() {
		return $this->plugin_slug;
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Fired when the plugin is activated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Activate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       activated on an individual blog.
	 */
	public static function activate( $network_wide ) {

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide  ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_activate();
				}

				restore_current_blog();

			} else {
				self::single_activate();
			}

		} else {
			self::single_activate();
		}

	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Deactivate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       deactivated on an individual blog.
	 */
	public static function deactivate( $network_wide ) {

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_deactivate();

				}

				restore_current_blog();

			} else {
				self::single_deactivate();
			}

		} else {
			self::single_deactivate();
		}

	}

	/**
	 * Fired when a new site is activated with a WPMU environment.
	 *
	 * @since    1.0.0
	 *
	 * @param    int    $blog_id    ID of the new blog.
	 */
	public function activate_new_site( $blog_id ) {

		if ( 1 !== did_action( 'wpmu_new_blog' ) ) {
			return;
		}

		switch_to_blog( $blog_id );
		self::single_activate();
		restore_current_blog();

	}

	/**
	 * Get all blog ids of blogs in the current network that are:
	 * - not archived
	 * - not spam
	 * - not deleted
	 *
	 * @since    1.0.0
	 *
	 * @return   array|false    The blog ids, false if no matches.
	 */
	private static function get_blog_ids() {

		global $wpdb;

		// get an array of blog ids
		$sql = "SELECT blog_id FROM $wpdb->blogs
			WHERE archived = '0' AND spam = '0'
			AND deleted = '0'";

		return $wpdb->get_col( $sql );

	}

	/**
	 * Fired for each blog when the plugin is activated.
	 *
	 * @since    1.0.0
	 */
	private static function single_activate() {

	}

	/**
	 * Fired for each blog when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 */
	private static function single_deactivate() {

	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		$domain = $this->plugin_slug;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );

	}

	/**
	 * Register and enqueue public-facing style sheet.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_slug . '-plugin-styles', plugins_url( 'assets/css/public.css', __FILE__ ), array(), self::VERSION );
	}

	/**
	 * Register and enqueues public-facing JavaScript files.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_slug . '-plugin-script', plugins_url( 'assets/js/public.js', __FILE__ ), array( 'jquery' ), self::VERSION );
	}









  /**
   *
   * Save offset value into order meta
   */        
  public function save_offset_value($order_id){
    global $woocommerce;      
     
    foreach ( $woocommerce->session->cart as $key => $default ) {
		    $idecko = $key;
		}
    
    $carbon = get_option($idecko);
    
    update_post_meta( $order_id, '_carbon_value', esc_attr($carbon));
    
    delete_option($idecko);
    
    
         
  }







	public function woo_calculate_total(){
    global $woocommerce;
    foreach ( $woocommerce->session->cart as $key => $default ) {
		    $idecko = $key;
		}
    
    $carbon = get_option($idecko);
     
       if($carbon){ 
        
        		  WC()->cart->add_fee( 'Carbon Offset' , $carbon );
              
	     }   
	}
  
  
  
  public function woo_after_shipping(){
    global $woocommerce;    
    foreach ( $woocommerce->session->cart as $key => $default ) {
		    $idecko = $key;
		}
    
    $carbon = get_option($idecko);
    if($carbon){ $checked = 'checked="checked"'; }else{ $checked = ''; }
    
    echo '<tr id="carbon-item">
					<th>'.__('Carbon Offset','woocommerce').'<a class="question-mark tip" href="http://gaiapartnership.com/services/page7/carbon_offsets.html" target="_blank">
<span>The Gaia Partnership\'s CO2counter automatically calculates the carbon emissions from the transportation of your goods. The small cost of offsetting is then shown in within the cart.</span>
</a></th>
					<td><input type="checkbox" name="co2-carbon" id="co2-carbon" '.$checked.' /></td>
				</tr>';
        
  
  }
  /**
   * Calculate carbon price
   *
   * since 1.0  
   */         
   public function carbon_calculate_ajax(){
      global $woocommerce;
      foreach ( $woocommerce->session->cart as $key => $default ) {
		    $idecko = $key;
		}
    
    
    $location_string = '';
    if(isset($_POST['address'])){ $location_string .= $_POST['address']; }
    if(isset($_POST['city'])){ $location_string .= ', '.$_POST['city']; }
    if(isset($_POST['zip'])){ $location_string .= ', '.$_POST['zip']; }
    if(isset($_POST['country'])){ $location_string .= ', '.$_POST['country']; }
    
    
    
    
    
    
    
    
    $location_string = gaia_iso2ascii($location_string);
    
    try {
    $location = new Geocoder();
    $address  = urlencode($location_string);
    $end_location = $location::getLocation($address);
     } catch (Exception $e) {
	    var_dump($e);	    
	    exit();
	}
  
  //Adress error
  if($end_location == '1'){
    var_dump(__('Adress not exist','woocommerce'));
    exit();
  }
  
  $gaia_carbon = new GaiaCarbon();
  
  //Convert weight to kg
  $weight = wc_get_weight( $woocommerce->session->cart_contents_weight, 'kg' );
   
  
  $args = array(
        'weight' => $weight,
        'lat2'   => $end_location['lat'],
        'lng2'   => $end_location['lng'],
        'sale'   => false
  );
  $shop_option = get_option('carbon');
  if($shop_option['carbon_mode']=='sandbox'){
    $args['mode'] = 'sandbox';
  }else{
    $args['mode'] = 'production';
  }
  
  
  $woocommerce->session->__set('shipping_city',serialize($args));
  
  $gaia_result = $gaia_carbon::getCarbonPrice($args);
  $gaia_result = json_decode($gaia_result);
  
  
  if(!empty($gaia_result->co2counter->status) && $gaia_result->co2counter->status == '1'){
    //Some kind of error
    if(!empty($gaia_result->co2counter->error007)){
      $gaia_error = __('Weight must be > 0','WooCommerce');
      echo $gaia_error;
      exit();
    }else{
      
      var_dump($gaia_result);
      exit();
    }                                                            
  
  }else{
  
     $f_date = array();

    if(isset($_POST['name'])){    $f_date['name'] = $_POST['name']; }
    if(isset($_POST['surname'])){ $f_date['surname'] = $_POST['surname']; }
    if(isset($_POST['address'])){ $f_date['address'] = $_POST['address']; }
    if(isset($_POST['city'])){    $f_date['city'] = $_POST['city']; }
    if(isset($_POST['zip'])){     $f_date['zip'] = $_POST['zip']; }
    if(isset($_POST['email'])){   $f_date['email'] = $_POST['email']; }
    if(isset($_POST['phone'])){   $f_date['phone'] = $_POST['phone']; }
    if(isset($_POST['country'])){ $f_date['country'] = $_POST['country']; }
    
     update_option('trans_data',$f_date);
     $woocommerce->session->__set('carbon_args',serialize($args));
     $woocommerce->session->__set('final_balance',$gaia_result->co2counter->final_balance);
     update_option($idecko,$gaia_result->co2counter->final_balance);
     do_action( 'woocommerce_cart_calculate_fees' , array( $this, 'woo_calculate_total' ) );
     var_dump($gaia_result);
     
     exit();
  }
      
   }
   
  /**
   * Delete carbon price
   *
   * since 1.0  
   */         
   public function carbon_delete_ajax(){
      global $woocommerce;
      foreach ( $woocommerce->session->cart as $key => $default ) {
		    $idecko = $key;
		}
    
    delete_option($idecko);
    
    do_action( 'woocommerce_cart_calculate_fees' , array( $this, 'woo_calculate_total' ) );
    
    $opt = get_option('trans_data');
      if(!empty($opt)){   
        delete_option('trans_data');
      }
      
      exit();
   } 

          /*
  // Our hooked in function - $fields is passed via the filter!
  function custom_override_checkout_fields( $fields ) {
    $opt = get_option('trans_data');
    
    
     $fields['shipping']['shipping_first_name'] = $opt['name'];
     $fields['shipping']['shipping_last_name'] = $opt['surname'];
     $fields['shipping']['shipping_address_1'] = $opt['address'];
     $fields['shipping']['shipping_city'] = $opt['city'];
     $fields['shipping']['shipping_postcode'] = $opt['zip'];
     $fields['shipping']['shipping_email'] = $opt['email'];
     $fields['shipping']['shipping_phone'] = $opt['phone'];
     
     $fields['billing']['billing_first_name'] = $opt['name'];
     $fields['billing']['billing_last_name'] = $opt['surname'];
     $fields['billing']['billing_address_1'] = $opt['address'];
     $fields['billing']['billing_city'] = $opt['city'];
     $fields['billing']['billing_postcode'] = $opt['zip'];
     $fields['billing']['billing_email'] = $opt['email'];
     $fields['billing']['billing_phone'] = $opt['phone']; 
      
     return $fields;
  }     */

	/**
	 * Define ajaxurl
	 *
	 * @since    1.0.0
	 */
	public function carbon_ajaxurl() {
    ?>
    <script type="text/javascript">
    var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
    </script>
    <?php
  }
  
  /**
   * Remove czech from string
   *
   * @since 1.0 0   
   */        
  public function iso2ascii($text){
        return strtr($text,
            "áčďéěíľňóřšťúůýžÁČĎÉĚÍĽŇÓŘŠŤÚŮÝŽ",
            "acdeeilnorstuuyzACDEEILNORSTUUYZ");
    } 

	/**
	 * Headers allready sent fix
	 *
	 */        
  public function output_buffer() {
		ob_start();
  } 



}
