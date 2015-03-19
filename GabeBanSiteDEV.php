<?php
/*
 * Plugin Name: HotchDev_Template
 * Description: Purge's a site page when executed.
 * Version: 0.0.1
 * Author: Gabriel Hotchner
 */

	//Hook  to add an admin menu for the plugin
	add_action('admin_menu','plugin_setup_menu');
	
	//Function to create settings page
	function plugin_setup_menu(){
		add_options_page(
			'My Varnish Settings',
			'HotchDev_Template',
			'manage_options',
			'varnish-menu',
			'varnish_init_func');
	}
	
	//Hook to initialize settings page
	add_action( 'admin_init', 'my_plugin_settings' );
	
	
	//Register the settings we want on the settings page
	function my_plugin_settings() {
            register_setting( 'my-plugin-settings-group', 'ip_address' );
            register_setting( 'my-plugin-settings-group', 'port_number' );
            register_setting( 'my-plugin-settings-group', 'url_page' );
	}
	
	//Function to set up the style and look of the setting page
	function varnish_init_func() {
		?>
		
		<!-- This is the main Div -->
		<div class="wrap">
		<h2>Varnish Plugin Settings</h2>

		<form method="post" action="options.php">
			<?php settings_fields( 'my-plugin-settings-group' ); ?>
			<?php do_settings_sections( 'my-plugin-settings-group' ); ?>
		<table class="form-table">
			<tr valign="top">
			<th scope="row">IP Address</th>
			<td><input type="text" name="ip_address" value="<?php echo esc_attr( get_option('ip_address', '127.0.0.1') ); ?>" /></td>
			</tr>
		
			<tr valign="top">
			<th scope="row">Port</th>
			<td><input type="text" name="port_number" value="<?php echo esc_attr( get_option('port_number', '80') ); ?>" /></td>
			</tr>

			<tr valign="top">
			<th scope="row">URL</th>
			<td><input type="text" name="url_page" value="<?php echo esc_attr( get_option('url_page', 'https://www.mysite.com/some/page') ); ?>" /></td>
			</tr>
		</table>
		
		<!-- This is the save settings button -->
		<?php submit_button(); ?> 
		
		<!-- This is the Purge URL button -->
                <input type="submit" value="Purge URL" onclick="purge_varnish()" />
             
                <!-- This JS Script will grab the settings info and run the purge function-->
                <script type="text/javascript">
                        
                //Main URL Purge Function 
                //Should only work when puge button is clicked,
                // however the save settings button also seems to active it for some reason. 
                function purge_varnish(){ 
                    alert("Let the Purge Commence!");
                    <?php 
                    
                    //Set up the socket connection to varnish
                     $errno = (integer) "";
                     $errstr = (string) "";
                     $varnish_sock = fsockopen(get_option('ip_address'), get_option('port_number'), $errno, $errstr, 10);
                     
                    //Check if the settings provided connect to a varnish socket
                    if (!$varnish_sock) {
                        error_log("Varnish connect error: ". $errstr ."(". $errno .")");
                    } else {
                     
                       //Take the user's URL
                       $txtUrl = get_option('url_page');
                       
                       //We need the host name and page
                       //So we perform a few operations to get those bits of information from the URL
                       $txtUrl = str_replace("http://", "", $txtUrl); 
                       $hostname = substr($txtUrl, 0, strpos($txtUrl, '/'));
                       $url = substr($txtUrl, strpos($txtUrl, '/'), strlen($txtUrl));
                        
                        // Build the request
                        $cmd = "PURGE ". $url ." HTTP/1.0\r\n";
                        $cmd .= "Host: ". $hostname ."\r\n";
                        $cmd .= "Connection: Close\r\n";
                        $cmd .= "\r\n";
                     
                        // Send the request to the socket
                         fwrite($varnish_sock, $cmd);
                    
                        // Get the reply (I may just remove this since I'm not using it)
                        $response = "";
                        while (!feof($varnish_sock)) {
                            $response .= fgets($varnish_sock, 128);
                        }
                      }
                     
                     //Close socket connection
                     fclose($varnish_sock); 
                     
                     ?>
                      
                } 
                
                </script>
		
		</form>
		</div>
	<?php } //End of initialization function
	
      
	//Function to remove settings upon deactivation of the plugin
	function deactivate() {
		delete_option('ip_address');
		delete_option('port_number');
		delete_option('time_delay');
	}
	
	//Hook to run the deactivate function upon deactivation
	register_deactivation_hook(__FILE__, 'deactivate');
	
       
	//Might want to work on having a method to output purge results to user

	//EOF
	?>
