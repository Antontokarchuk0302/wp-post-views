<?php
/**
 * Plugin Name
 *
 * @package           WP Post Views
 * @author            Ronak J Vanpariya
 * @copyright         Creole Studios
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       WP Post Views
 * Plugin URI:        https://github.com/vanpariyar/wp-post-views
 * Description:       WP Post Views.
 * Version:           0.0.1
 * Requires at least: 5.0
 * Requires PHP:      7.2
 * Author:            Ronak J Vanpariya
 * Author URI:        https://example.com
 * Text Domain:       wp-post-views
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt

 WP Post Views is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 2 of the License, or any later version.
 
 WP Post Views is distributed in the hope that it will be useful,but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 
 You should have received a copy of the GNU General Public License along with WP Post Views. If not, see  * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt

*/

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

/* Plugin Constants */
if (!defined('WP_POST_VIEW_URL')) {
    define('WP_POST_VIEW_URL', plugin_dir_url(__FILE__));
}

if (!defined('WP_POST_VIEW_PLUGIN_PATH')) {
    define('WP_POST_VIEW_PLUGIN_PATH', plugin_dir_path(__FILE__));
}

require_once (WP_POST_VIEW_PLUGIN_PATH . '/includes/settings.php');


/**
 * MAIN CLASS
 */
class Post_Views 
{
	private $stored_ip_addresses;
	private $post_id;

	function __construct()
	{
		add_action( 'wp_head', array( $this , 'counter'), 10, 1 );
		Wp_post_view_settings::settings_init();
	}
	
	
	// add_action( , $function_to_add, 10, 1 );

	function get_ip_address() 
	{
	    // check for shared internet/ISP IP
	    if (!empty($_SERVER['HTTP_CLIENT_IP']) && validate_ip($_SERVER['HTTP_CLIENT_IP']))
	        return $_SERVER['HTTP_CLIENT_IP'];
	    // check for IPs passing through proxies
	    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
	        // check if multiple ips exist in var
	        $iplist = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
	        foreach ($iplist as $ip) {
	            if (validate_ip($ip))
	                return $ip;
	        }
	    }
	    if (!empty($_SERVER['HTTP_X_FORWARDED']) && validate_ip($_SERVER['HTTP_X_FORWARDED']))
	        return $_SERVER['HTTP_X_FORWARDED'];
	    if (!empty($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']) && validate_ip($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']))
	        return $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
	    if (!empty($_SERVER['HTTP_FORWARDED_FOR']) && validate_ip($_SERVER['HTTP_FORWARDED_FOR']))
	        return $_SERVER['HTTP_FORWARDED_FOR'];
	    if (!empty($_SERVER['HTTP_FORWARDED']) && validate_ip($_SERVER['HTTP_FORWARDED']))
	        return $_SERVER['HTTP_FORWARDED'];
	    // return unreliable ip since all else failed
	    return $_SERVER['REMOTE_ADDR'];
	}

	function validate_ip($ip) {
	     if (filter_var($ip, FILTER_VALIDATE_IP, 
	                         FILTER_FLAG_IPV4 | 
	                         FILTER_FLAG_IPV6 |
	                         FILTER_FLAG_NO_PRIV_RANGE | 
	                         FILTER_FLAG_NO_RES_RANGE) === false)
	        return false;
	    return true;
	}

	function counter(){
		$stored_ip_addresses = get_post_meta(get_the_ID(),'view_ip',true);
		$new_viewed_count = 0;
		if($stored_ip_addresses)
		{
			if(sizeof($stored_ip_addresses))
			{
			  $current_ip = $this->get_ip_address();
			  if(!in_array($current_ip, $stored_ip_addresses))
			  {
			    $meta_key         = 'entry_views';
			    $view_post_meta   = get_post_meta(get_the_ID(), $meta_key, true);
			    $new_viewed_count = $view_post_meta + 1;
			    update_post_meta(get_the_ID(), $meta_key, $new_viewed_count);
			    $stored_ip_addresses[] = $current_ip;
			    update_post_meta(get_the_ID(),'view_ip',$stored_ip_addresses);
			  }
			}
		}
		else {
			$meta_key         = 'entry_views';
			$view_post_meta   = get_post_meta(get_the_ID(), $meta_key, true);	
			$new_viewed_count = $view_post_meta + 1;
			update_post_meta(get_the_ID(), $meta_key, $new_viewed_count);
			$ip_arr[] = $this->get_ip_address();
			update_post_meta(get_the_ID(),'view_ip',$ip_arr);
		}

	}

	 
}

$post_view = new Post_Views();