<?php 

class GaiaCarbon{

  /**
   *  Service url
   */
  public $service_url;
  /**
   *  Api key
   */
  public $api_key;
  /**
   *  Api hash
   */
  public $api_hash;
 
 
  /**
   *
   *
   */ 
  public function _construct($service){
      
      
      
     
      
  }
    
              
    static public function getCarbonPrice($args){
       $shop_option = get_option('carbon');
       if($args['mode'] == 'sandbox'){
      
        $service_url = 'http://test.co2counter.com.au/api/api3.php';
        $api_key    = 2;
        $api_hash    = 'sandbox';
        $orn = '3333';
      
      }else{
      
        $service_url  = 'http://co2counter.com.au/api/api3.php';
        $api_key      = $shop_option['shop_api'];
        $api_hash     = $shop_option['shop_hash'];
        $orn = '3333';
      
      }  

$currencies = array('AUD','USD','GBP','EUR');      
$cur = get_woocommerce_currency();
if(!in_array($cur,$currencies)){ $cur = 'AUD'; }

      
      
$url  = '';
$url .= $service_url.'?json=';
$url .= '{"key":"'.$api_key.'",';
$url .= '"hash":"'.$api_hash.'",';
$url .= '"orn":"'.$orn.'",';
$url .= '"sale":"'.$args['sale'].'",';
$url .= '"currency":"'.$cur.'",';
$url .= '"mass_kg":"'.$args['weight'].'",';
$url .= '"output":"full",';
$url .= '"hops":[{"seq":0,"freight_type":"GeneralRoad","type":"latlong",';
$url .= '"lat1":'.$shop_option['lat'].',';
$url .= '"lon1":'.$shop_option['lng'].',';
$url .= '"lat2":'.$args['lat2'].',';
$url .= '"lon2":'.$args['lng2'].'}]}';
      
        $c = curl_init();
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($c, CURLOPT_URL, $url);
        $contents = curl_exec($c);
        curl_close($c);
        
        if ($contents) return $contents;
            else return FALSE;
            
    }


    
    
    
    static private function curl_file_get_contents($URL){
        $c = curl_init();
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($c, CURLOPT_URL, $URL);
        $contents = curl_exec($c);
        curl_close($c);

        if ($contents) return $contents;
            else return FALSE;
    }
    
    
    

}//End class