<?php

/**
 * transit days 
 */
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists("EnWweSmallTransitDays")) {

    class EnWweSmallTransitDays{
       
        public function __construct() 
        {
        }
        
        /**
         * 
         * @param array type $result
         * @return json_encode type
         */
        public function wwe_small_enable_disable_ups_ground($result)
        {
          
            $transit_day_type   =   get_option('restrict_calendar_transit_wwe_small_packages');
            $response           =   (isset($result->q)) ? $result->q : array();
            $days_to_restrict   =   get_option('ground_transit_wwe_small_packages');
            
            $package = apply_filters('wwe_quests_quotes_plans_suscription_and_features' , 'transit_days');
            if(!is_array($package) && strlen($days_to_restrict) > 0 && strlen($transit_day_type) > 0)
            {
                foreach($response as $row => $service)
                {
                    if($service->serviceCode == "GND" && 
                            (isset($service->$transit_day_type)) && 
                            ($service->$transit_day_type >= $days_to_restrict)) 

                        unset($result->q[$row]);
                }

            }

            return json_encode($result);
        }
    }
}
        

