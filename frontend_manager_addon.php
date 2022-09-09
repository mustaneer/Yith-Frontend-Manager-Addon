<?php
/**
 * Plugin Name: Frontend Manager Addon
 * Plugin URI:
 * Description: Frontend Manager Addon
 * Version:     1.0.1
 * Author:      Mustaneer Abdullah
 * Author URI:  https://mustaneer.pro
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: frontend_manager_addon
 */

if (! class_exists('frontend_manager_addon')) {
    class frontend_manager_addon
    {
        var $plugin_name = "";
        public function __construct()
        {  
	        // Yith Frontend Manager Plugin custommizations
	        add_action('yith_wcfm_load_skin1_header',array($this, 'check_user_role'),99);
	        
	        add_filter( 'woocommerce_admin_order_actions', array($this, 'remove_actions_from_order_section'), 10, 2 );
	        add_filter( 'yith_wcfm_get_subsections_in_print_navigation', array($this, 'remove_navigation_sidebar'), 10, 2 ); 
	        add_filter( 'yith_wcfm_orders_reports_type',array($this, 'remove_order_reports_tabs'),99,1);
	        add_filter( 'yith_wcfm_section_url',array($this, 'yith_wcfm_change_report_url'),99,4 );
	                  
	        add_action('monthly_recurring_amount_hook',array($this, 'add_recurring_amount'));
	        add_action('yearly_recurring_amount_hook',array($this, 'add_recurring_amount'));
	        add_action('quarterly_recurring_amount_hook',array($this, 'add_recurring_amount'));
	                       
	        //add_action('init',array($this, 'add_recurring_amount'));
	        	        
	        add_filter( 'cron_schedules', array($this, 'cron_new_schedules'));
	        
	        add_action( 'user_new_form',  array($this, 'wk_custom_user_profile_fields') );
	        add_action( 'edit_user_profile',  array($this, 'wk_custom_user_profile_fields') );
	        add_action( 'show_user_profile',  array($this, 'wk_custom_user_profile_fields') );
	        
	        add_action( 'user_register', array($this, 'wk_save_custom_user_profile_fields') );
	        add_action( 'edit_user_profile_update', array($this, 'wk_save_custom_user_profile_fields') );
	        add_action( 'personal_options_update', array($this, 'wk_save_custom_user_profile_fields') );
	        
            add_action('admin_menu', array($this, 'frontend_manager_addon_admin_menu'));
            add_action('admin_enqueue_scripts', array( $this, 'admin_style' ));
            add_action('wp_enqueue_scripts', array( $this, 'admin_style' ));
            
            register_uninstall_hook(__FILE__, array( $this, 'deactivate_frontend_manager_addon'));
            
            require_once( ABSPATH . '/wp-content/plugins/FS_WooCommerce_Wallet/includes/classes/Wallet.php');	
			require_once( ABSPATH . '/wp-content/plugins/FS_WooCommerce_Wallet/includes/classes/FS_WC_Wallet.php');	
        } 
        /**
         * cron_new_schedules function.
         * 
         * @access public
         * @return void
         */
        public function cron_new_schedules(){
	        // Adds once weekly to the existing schedules.
		    $schedules['quarterly'] = array(
		       'interval' => 10540800,
		       'display' => __( 'quarterly' )
		    );
		    $schedules['yearly'] = array(
		       'interval' => 31622400,
		       'display' => __( 'Yearly' )
		    );
		   return $schedules;
        }
        /**
         * add_recurring_amount function.
         * 
         * @access public
         * @return void
         */
        public function add_recurring_amount(){
	        
	        $users 						= get_users(array('fields'=>array('ID')));
			$override_previous_amount   = get_option('override_previous_amount');
			$user_roles_not_allowed 	= get_option('user_roles_not_allowed');
			$recurring_duration 		= get_option('recurring_duration');
			
			if($recurring_duration == "monthly"){
			    $cron_timestamp = wp_next_scheduled( 'monthly_recurring_amount_hook' );
				$date_utc   = gmdate( 'Y/m/d\TH:i:s+00:00', $cron_timestamp );
				$time = get_date_from_gmt( date( 'Y/m/d H:i', $cron_timestamp ), 'Y/m/d H:i' );
				update_option('recurring_data_time',$time);
			} else if($recurring_duration == "quarterly"){
				$cron_timestamp = wp_next_scheduled( 'quarterly_recurring_amount_hook' );
				$date_utc   = gmdate( 'Y/m/d\TH:i:s+00:00', $cron_timestamp );
				$time = get_date_from_gmt( date( 'Y/m/d H:i', $cron_timestamp ), 'Y/m/d H:i' );
				update_option('recurring_data_time',$time);
			} else if($recurring_duration == "yearly"){
				$cron_timestamp = wp_next_scheduled( 'yearly_recurring_amount_hook' );
				$date_utc   = gmdate( 'Y/m/d\TH:i:s+00:00', $cron_timestamp );
				$time = get_date_from_gmt( date( 'Y/m/d H:i', $cron_timestamp ), 'Y/m/d H:i' );
				update_option('recurring_data_time',$time);
			}
			
	        foreach($users as $user){
		        $user_profile_recurring_amount 	= get_user_meta( $user->ID, 'user_recurring_amount',true);
		        $no_recurring_funds 			= get_user_meta( $user->ID, 'no_recurring_funds',true);
		        $user_info = get_userdata($user->ID);
		        $current_user_role =  $user_info->roles[0];
				if(!in_array($current_user_role,$user_roles_not_allowed)){
					if($no_recurring_funds == ""){
				        if($user_profile_recurring_amount != ""){
					        $user_recurring_amount = $user_profile_recurring_amount;
				        } else {
					        $user_recurring_amount = get_option('user_recurring_amount');
				        }
				        
				        $balance        = Wallet::get_balance($user->ID);
				        $subject    = __('Funds Have been added to your account', 'fsww');
				        $heading    = __('Funds Have been added to your account', 'fsww');
				        $message    = '<p>' . fsww_price($user_recurring_amount) . ' ' . __('Have been added to your account balance', 'fsww') . '</p><br>';
						if($override_previous_amount == "yes"){					
							if($balance > 0 ){
								Wallet::withdraw_funds($user->ID, $balance, 0, __('Reset Funds to Nill', 'fsww'));		
							} 
							Wallet::add_funds($user->ID, $user_recurring_amount, 0, 'Admin Added Funds');
							FS_WC_Wallet::send_email($user->ID, $message, $subject, $heading);
							
						} else{
							Wallet::add_funds($user->ID, $user_recurring_amount, 0, 'Admin Added Funds');
							FS_WC_Wallet::send_email($user->ID, $message, $subject, $heading);
						}
				    }
			    }      
	        }
        }
		public function wk_custom_user_profile_fields( $user ){
			$user_recurring_amount = (get_user_meta($user->ID,'user_recurring_amount',true))?get_user_meta($user->ID,'user_recurring_amount',true):'';
			$no_recurring_funds = (get_user_meta($user->ID,'no_recurring_funds',true))?get_user_meta($user->ID,'no_recurring_funds',true):'';
			
		?>	    
		    <table class="form-table">
				<tr>
		            <th scope="row"><label for="user_recurring_amount"><?php  _ex( 'Recurring Amount', 'yith-frontend-manager-for-woocommerce' ); ?></label></th>		 
					<td><input type="text"  class="regular-text" name="user_recurring_amount" id="user_recurring_amount" value="<?php echo $user_recurring_amount; ?>" /> </td> 
				</tr>
				<tr>
		            <th scope="row"><label for="no_recurring_funds"><?php  _ex( "Don't Add Recurring Funds", 'yith-frontend-manager-for-woocommerce' ); ?></label></th>		 
					<td><input type="checkbox"  class="regular-text" name="no_recurring_funds" id="no_recurring_funds" value="yes"  <?php echo ($no_recurring_funds == "yes") ? "checked" : "" ;?> /> </td> 
				</tr>
		    </table>	
		<?php
		}
		
		
		/**
		 * wk_save_custom_user_profile_fields function.
		 * 
		 * @access public
		 * @param mixed $user_id
		 * @return void
		 */
		function wk_save_custom_user_profile_fields( $user_id ){			
		    $user_recurring_amount 	= $_POST['user_recurring_amount'];	
		    $no_recurring_funds 	= $_POST['no_recurring_funds'];	
		    update_user_meta( $user_id, 'user_recurring_amount', $user_recurring_amount );	
		    update_user_meta( $user_id, 'no_recurring_funds', $no_recurring_funds );	
		}
		/**
		 * check_user_role function.
		 * 
		 * @access public
		 * @return void
		 */
		public function check_user_role(){
			$frontend_manager_access	= get_option( 'frontend_manager_access');
			$not_authorized_title 		= get_option( 'yith_wcfm_not_authorized_title');
			$not_authorized_message 	= get_option( 'yith_wcfm_not_authorized_message');
			$current_user_roles 		= $this->wcmo_get_current_user_roles();
			$finded = false;
			if(!empty($current_user_roles)){
				foreach($current_user_roles as $roled){
					if(in_array($roled, $frontend_manager_access)){
						$finded = true;
						break;
					}
				}
			}
			if( $finded === false ){ ?>
				<div id="yith_wcfm-main-content" class="">
				    <div class="yith_wcfm-container">
				        <div class="yith_wcfm-main-content-wrap responsive-nav-closed">
							<div class="yith-wcfm-content woocommerce-MyAccount-content">
							    <div id="yith-wcfm-dashboard">		
							        <h1><?php echo $not_authorized_title; ?></h1>
									<p><?php echo $not_authorized_message; ?> </p>     
							    </div>
							</div>
				        </div>
				    </div>
				</div>
			<?php 
				die;
			} // End of if conditionn
		}
		/**
		 * remove_actions_from_order_section function.
		 * 
		 * @access public
		 * @param mixed $actions
		 * @param mixed $order
		 * @return void
		 */
		public function remove_actions_from_order_section($actions, $order){
			unset($actions['complete']);
			unset($actions['processing']);
			return $actions;
			
		}
		/**
		 * remove_navigation_sidebar function.
		 * 
		 * @access public
		 * @param mixed $subsections
		 * @param mixed $section
		 * @return void
		*/
		public function remove_navigation_sidebar($subsections, $section){
			//print_r($section);
			unset($subsections['product_orders']);
			unset($subsections['product_order']);
			unset($subsections['stock-report']);
			//print_r($subsections);
			//die;
			return $subsections;
			
		}
		public function remove_order_reports_tabs($allowed_reports){
			unset($allowed_reports['sales_by_category']);
			unset($allowed_reports['coupon_usage']);
			unset($allowed_reports['downloads']);
			return $allowed_reports;	
		}
		
		/**
		 * yith_wcfm_change_report_url function.
		 * 
		 * @access public
		 * @param mixed $endpoint_uri
		 * @param mixed $slug
		 * @param mixed $subsection
		 * @param mixed $id
		 * @return void
		 */
		public function yith_wcfm_change_report_url($endpoint_uri, $slug, $subsection, $id){
			if($subsection == "customers-report"){
				$endpoint_uri = add_query_arg( array( 'reports' =>'customers-report','report'=>'customer_list'), $endpoint_uri );
			}
				return $endpoint_uri;
			
		}		
        // Update CSS within in Admin
        public function admin_style()
        {
			wp_enqueue_style('css-select-2', 'https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css', array(), null, 'all');
			wp_enqueue_style('css-datetime', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.20/jquery.datetimepicker.min.css', array(), null, 'all');
			wp_enqueue_style('custom-fm', plugin_dir_url(__FILE__) . 'assets/custom-fm-style.css', array(), time('now'), 'all');
            // admin js
            wp_enqueue_script('select-2', 'https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js', array( 'jquery' ), null, true);
            wp_enqueue_script('datetime-js', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.20/jquery.datetimepicker.full.min.js', array( 'jquery' ), null, true);
            wp_enqueue_script('ab-admin', plugin_dir_url(__FILE__) . 'assets/ab-admin.js', array( 'jquery' ), time('now'), true);
        }
        public function frontend_manager_addon_admin_menu()
        {
            add_options_page(__('Frontend Manager Settings', ''), __('Frontend Manager Settings', ''), 'manage_options', 'frontend-manager-backend', array($this, 'frontend_manager_addon_backend'));
        }
        public function frontend_manager_addon_backend()
        {
            $data = [];
            if (isset($_POST['fm_submit'])) {
                $data['frontend_manager_access'] = (isset($_POST['frontend_manager_access']) ? $_POST['frontend_manager_access'] : '');
                $data['user_roles_not_allowed'] = (isset($_POST['user_roles_not_allowed']) ? $_POST['user_roles_not_allowed'] : '');
                $data['override_previous_amount'] = (isset($_POST['override_previous_amount']) ? $_POST['override_previous_amount'] : '');
                $data['user_recurring_amount'] = (isset($_POST['user_recurring_amount']) ? $_POST['user_recurring_amount'] : '');
                $data['recurring_duration'] = (isset($_POST['recurring_duration']) ? $_POST['recurring_duration'] : '');                           
				$data['recurring_data_time'] = (isset($_POST['recurring_data_time']) ? $_POST['recurring_data_time'] : '');
				
                $current_timestamp  		= strtotime(date('Y/m/d H:i'));
				$recurring_time_stamp 		= strtotime($data['recurring_data_time']); 
				
				/* **** SAving data into db **** */
				foreach ($data as $key => $value) {
                    update_option($key, $value);
                }
                if($recurring_time_stamp > $current_timestamp ){

					if(wp_next_scheduled( 'monthly_recurring_amount_hook' )){
		            	wp_clear_scheduled_hook( 'monthly_recurring_amount_hook' );
	                }
	                
	                if( wp_next_scheduled( 'quarterly_recurring_amount_hook' ) ){
						wp_clear_scheduled_hook( 'quarterly_recurring_amount_hook' );
	                }
	                
	                if( wp_next_scheduled( 'yearly_recurring_amount_hook' ) ){
						wp_clear_scheduled_hook( 'yearly_recurring_amount_hook' );
	                }				
								
					$monthly_timestamp = wp_next_scheduled( 'monthly_recurring_amount_hook' );
					wp_unschedule_event( $monthly_timestamp, 'monthly_recurring_amount_hook' );
							
					$quarterly_timestamp = wp_next_scheduled( 'quarterly_recurring_amount_hook' );
					wp_unschedule_event($quarterly_timestamp, 'quarterly_recurring_amount_hook' );
					
					$yearly_timestamp = wp_next_scheduled( 'yearly_recurring_amount_hook' );
					wp_unschedule_event( $yearly_timestamp, 'yearly_recurring_amount_hook' );
			
	                if( $data['recurring_duration'] == "monthly" ) {
						// Schedule the event
						wp_schedule_event( $recurring_time_stamp, 'monthly', 'monthly_recurring_amount_hook' );
											
					} else if( $data['recurring_duration'] == "quarterly"){
						// Schedule the event
						wp_schedule_event( $recurring_time_stamp, 'quarterly', 'quarterly_recurring_amount_hook' );
					} else {
						// Schedule the event
						wp_schedule_event( $recurring_time_stamp, 'yearly', 'yearly_recurring_amount_hook' );
					}
				}
            }
            ob_start();
            include_once('admin/frontend_manager_addon.php');
            $content = ob_get_clean();
            echo $content;
        }
       
        public function deactivate_frontend_manager_addon()
        {
            $data = [];
            $data['frontend_manager_access'] = '';
            $data['user_roles_not_allowed'] = '';
            $data['override_previous_amount'] = '';
            $data['user_recurring_amount'] = '';
            $data['recurring_duration'] = '';
            foreach ($data as $key => $value) {
                delete_option($key);
            }
            
            $monthly_timestamp = wp_next_scheduled( 'monthly_recurring_amount_hook' );
			wp_unschedule_event( $monthly_timestamp, 'monthly_recurring_amount_hook' );
			wp_clear_scheduled_hook( 'monthly_recurring_amount_hook' );
					
			$quarterly_timestamp = wp_next_scheduled( 'quarterly_recurring_amount_hook' );
			wp_unschedule_event( $quarterly_timestamp, 'quarterly_recurring_amount_hook' );
			wp_clear_scheduled_hook( 'quarterly_recurring_amount_hook' );
			
			$yearly_timestamp = wp_next_scheduled( 'yearly_recurring_amount_hook' );
			wp_unschedule_event( $yearly_timestamp, 'yearly_recurring_amount_hook' );
			wp_clear_scheduled_hook( 'yearly_recurring_amount_hook' );
        }
        /**
		 * Get the user's roles
		 * @since 1.0.0
		 */
		public function wcmo_get_current_user_roles() {
		  if( is_user_logged_in() ) {
		    $user = wp_get_current_user();
		    $roles = ( array ) $user->roles;
		    return $roles; // This returns an array

		  } else {
		    return array();
		  }
		}
    }

    $frontend_manager_addon = new frontend_manager_addon();
}
