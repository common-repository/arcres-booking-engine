<?php
/**
 * @package arcResBookingWidget
 * @version 1.0
 */
/*
Plugin Name: arcResBookingWidget
Plugin URI: http://wordpress.org/extend/plugins/arcResBookingWidget/
Description: Embeds the arcRes Booking Widget on a travel supplier website and ensures it is displayed for all arcRes-driven referrals.
Author: Axses Web Communications
Author URI: http://arcres.com
Version: 1.0
Tags: arcRes,booking widget,hotels,tourism,travel,suppliers,destinations,marketing,publishing
*/

$arcResBookingPlugin = new arcResBookingPlugin;

class arcResBookingPlugin{
		
	// set the properties for the class
	private $arcRes_BookingWidget_option_iconmap = 0;
	private $arcRes_BookingWidget_option_layout = '';
	private $arcRes_BookingWidget_option_arcResReferralOnly = '';
	private $arcRes_BookingWidget_option_cookieType = '';
		
	function __construct(){									
		$this->arcRes_BookingWidget_option_iconmap = get_option("arcRes_BookingWidget_option_iconmap", "100000");
		$this->arcRes_BookingWidget_option_layout = get_option( "arcRes_BookingWidget_option_layout", "vertical" );
		$this->arcRes_BookingWidget_option_arcResReferralOnly = get_option( "arcRes_BookingWidget_option_arcResReferralOnly", "0" );
		$this->arcRes_BookingWidget_option_cookieType = get_option( "arcRes_BookingWidget_option_cookieType", "permanent");
				
		// includes the javascript code in the header
		add_action('wp_head', array( &$this, 'arcRes_addJSHeader' ));
		
		// add the stylesheet		
		$this->arcRes_addStylesheet();
		
		// includes the admin menu for the plugin
		add_action('admin_menu', array( &$this, 'arcRes_menu' ) );
	}
	
	public function arcRes_addJSHeader(){	
		
		if ($this->arcRes_BookingWidget_option_arcResReferralOnly == 0){
			$this->arcRes_includeHeaderCode();
		}	elseif($this->arcRes_BookingWidget_option_cookieType === 'temporary'){
						
			if(array_key_exists("axsesReferrer",$_GET))
					$_SESSION['axsesReferrer'] =	$_GET['axsesReferrer'];
			
			if (isset($_SESSION['axsesReferrer']))		
				$this->arcRes_includeHeaderCode();	
							
		} else{
			if(array_key_exists("axsesReferrer",$_GET))
				setcookie("axsesReferrer","1",time()+(60*60*24*365), SITECOOKIEPATH, COOKIE_DOMAIN, false, true);
			
			if(isset($_COOKIE['axsesReferrer']) || array_key_exists("axsesReferrer",$_GET))
				$this->arcRes_includeHeaderCode();			
		}
	}
	
	private function arcRes_addStylesheet(){
		// set the stylesheet based on the plugin options		
		if (strcasecmp($this->arcRes_BookingWidget_option_layout,"horizontal") == 0)
			$stylesheet = "arcResBookingWidget-horizontal.css";
		else
			$stylesheet = "arcResBookingWidget-vertical.css";
	
		$stylesheetURL = site_url().'/wp-content/plugins/arcResBookingWidget/css/'.$stylesheet;
		wp_register_style('arcResStylesheet', $stylesheetURL);
        wp_enqueue_style( 'arcResStylesheet');		
	}
	
		private function arcRes_includeHeaderCode(){
		// include the booking widget javascript code
		echo '<script type="text/javascript" src="http://axses.com/arcResTracking/arcResTracking.js"></script>
	<script type="text/javascript">setAxsesCookies(window.location.search.substring(1),"'.$this->arcRes_BookingWidget_option_iconmap.'")</script>';
	}
	
	
	public function arcRes_showBookingWidget(){				
		if ($this->arcRes_BookingWidget_option_arcResReferralOnly == 1){
			$this->arcRes_DetermineWidget();
		} else {
			$this->arcRes_displayBookingWidget();		
		}
	}
	
	
	private function arcRes_displayBookingWidget(){
		echo '<script type="text/javascript">showAxsesBookingEngine("'.$this->arcRes_BookingWidget_option_iconmap.'")</script>';	
	}
	
	
	private function arcRes_DetermineWidget(){
		if (strcasecmp($this->arcRes_BookingWidget_option_cookieType,"temporary") == 0){
			$this->arcRes_DetermineWidgetTemporary();	
		} else{
			$this->arcRes_DetermineWidgetPermanent();
		}
	}
	
	private function arcRes_DetermineWidgetTemporary(){
	/**
	 * This function uses SESSION variables to temporarily store the axsesReferrer variable.
	 * Since it is a SESSION variable it is destroyed once the browser is closed.
	 * Returning visitors are therefore shown the alternative booking engine.
	 * To always show returning visitors the arcRes Booking Widget, set Visitors to Permanent in the plugin settings (found in the Settings > arcRes Booking Widget in WP Admin)
	 */
		
		if (isset($_SESSION['axsesReferrer']))
			$this->arcRes_displayBookingWidget();
		else
			$this->arcRes_alternativeBookingEngine();	
	}
	
	private function arcRes_DetermineWidgetPermanent(){
	/**
	 * This function uses COOKIES to permanently store the axsesReferrer variable.
	 * Since it is a COOKIE variable it persists after the browser is closed.
	 * Returning visitors are therefore shown the arcRes Booking Widget.
	 * To show returning visitors the alternative booking engine, set Visitors to Temporary in the plugin settings (found in the Settings > arcRes Booking Widget in WP Admin)
	 */
		
		if(isset($_COOKIE['axsesReferrer']) || array_key_exists("axsesReferrer",$_GET))		
			$this->arcRes_displayBookingWidget();			
		else
			$this->arcRes_alternativeBookingEngine();			
	}

	private function arcRes_alternativeBookingEngine(){
		/***** Place the code for the alternative BookingEngine here *****/
		echo 'The alternative booking engine will appear here.';
	}
	
	
	/*** Backend Functions ***/
	function arcRes_menu(){
		add_options_page('arcRes Booking Widget Options', 'arcRes Booking Widget', 'manage_options', 'arcRes-BookingWidget-options', array( &$this, 'arcRes_BookingWidget_options' ));
	}
	
	function arcRes_BookingWidget_options() {
		if (!current_user_can('manage_options'))  {
			wp_die( __('You do not have sufficient permissions to access this page.') );
		}
	
	$optionsUpdated = false;
	
		if( isset($_POST["formAction"]) && $_POST["formAction"] == 'Y' ) {					
				
			// update the options
			update_option("arcRes_BookingWidget_option_iconmap",$_POST["arcRes_BookingWidget_option_iconmap"]);
			update_option("arcRes_BookingWidget_option_layout",$_POST["arcRes_BookingWidget_option_layout"]);
			update_option("arcRes_BookingWidget_option_arcResReferralOnly",$_POST["arcRes_BookingWidget_option_arcResReferralOnly"]);
			update_option("arcRes_BookingWidget_option_cookieType",$_POST["arcRes_BookingWidget_option_cookieType"]);			
			
			// reset the object properties
			$this->arcRes_BookingWidget_option_iconmap = $_POST["arcRes_BookingWidget_option_iconmap"];
			$this->arcRes_BookingWidget_option_layout = $_POST["arcRes_BookingWidget_option_layout"];
			$this->arcRes_BookingWidget_option_arcResReferralOnly = $_POST["arcRes_BookingWidget_option_arcResReferralOnly"];
			$this->arcRes_BookingWidget_option_cookieType = $_POST["arcRes_BookingWidget_option_cookieType"];
			
			$optionsUpdated = true;		
		}
		
	?>
		<div class="wrap">
		<div id="icon-plugins" class="icon32"></div>
		<h2>arcRes Booking Widget Plugin Settings</h2>
		
		<?php
			if ($optionsUpdated){
				echo '<div class="updated">Your settings have been updated.</div>';
			}
		?>
			
		<form action="" method="post">
			<input type="hidden" name="formAction" value="Y">	
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><label for="arcRes_BookingWidget_option_iconmap">Iconmap</label></th>
					<td>
						<input id="arcRes_BookingWidget_option_iconmap" type="text" name="arcRes_BookingWidget_option_iconmap" value="<?php echo $this->arcRes_BookingWidget_option_iconmap; ?>" size="20">
						<div>This is the unique identifier for your business. Contact <a href="mailto:support@axses.com">support@axses.com</a> if you do not have this information.</div>	
					</td>
				</tr>
				
				<tr valign="top">
					<th scope="row"><label for="arcRes_BookingWidget_option_layout">Layout</label></th>
					<td>
						<input type="radio" name="arcRes_BookingWidget_option_layout" value="horizontal" <?php if (strcasecmp($this->arcRes_BookingWidget_option_layout,"horizontal") == 0){ echo 'checked';} ?>> Horizontal		
						<input type="radio" name="arcRes_BookingWidget_option_layout" value="vertical" <?php if (strcasecmp($this->arcRes_BookingWidget_option_layout,"vertical") == 0){ echo 'checked';} ?>> Vertical
						<div>Select the layout that best matches the design of your website.</div>	
					</td>
				</tr>
				
				<tr valign="top">
					<th scope="row"><label for="arcRes_BookingWidget_option_arcResReferralOnly">arcRes Referrers Only</label></th>
					<td>
						<input type="radio" name="arcRes_BookingWidget_option_arcResReferralOnly" value="1" <?php if (strcasecmp($this->arcRes_BookingWidget_option_arcResReferralOnly,"1") == 0){ echo 'checked';} ?>> Yes		
						<input type="radio" name="arcRes_BookingWidget_option_arcResReferralOnly" value="0" <?php if (strcasecmp($this->arcRes_BookingWidget_option_arcResReferralOnly,"0") == 0){ echo 'checked';} ?>> No
						<div>Select <strong>No</strong> if unsure.<br/> Select Yes if you wish the arcRes Booking Widget to be shown <strong>only</strong> to visitors referred through arcRes channels. </div>	
					</td>
				</tr>
				
				<tr valign="top">
					<th scope="row"><label for="arcRes_BookingWidget_option_cookieType">Visitors</label></th>
					<td>
						<input type="radio" name="arcRes_BookingWidget_option_cookieType" value="permanent" <?php if (strcasecmp($this->arcRes_BookingWidget_option_cookieType,"permanent") == 0){ echo 'checked';} ?>> Permanent		
						<input type="radio" name="arcRes_BookingWidget_option_cookieType" value="temporary" <?php if (strcasecmp($this->arcRes_BookingWidget_option_cookieType,"temporary") == 0){ echo 'checked';} ?>> Temporary
						<div>
						Select <strong>Permanent</strong> if unsure. <br/>This will give more accurate information on the original source of your bookings.
						<br/>With a setting of Permanent repeat visitors referred by arcRes channels see the arcRes booking widget. With a setting of Temporary, repeat visitors referred by arcRes channels see the alternative booking engine. <br/>
						<strong>Important:</strong> If using the <em>Temporary</em> setting, you must add <em>session_start();</em> at the top of your <strong>wp-config.php</strong> file.
						</div>	
					</td>
				</tr>
				
			</table>
						
			<p>
				<input type="submit" name="Submit" class="button-primary" value="Save Options">
			</p>	
		</form>
		</div>
	<?php
	}	
} //class

class arcResBookingWidget extends WP_Widget {
  function arcResBookingWidget() {
    parent::WP_Widget( false, $name = 'arcRes Bookings Widget' );
  }

  function widget( $args, $instance ) {
	$arcResBookingPlugin =  new arcResBookingPlugin;
	$arcResBookingPlugin->arcRes_showBookingWidget();
 }
 
 function form($instance) {
		echo 'See Settings > ArcRes Booking Widget to customize settings.';
	}
 
}
 
add_action( 'widgets_init', 'MyWidgetInit' );

function MyWidgetInit() {
  register_widget( 'arcResBookingWidget' );
}
?>