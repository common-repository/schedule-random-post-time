<?php
/*
Plugin Name: Schedule-Random-Post-Time
Plugin URI: http://www.jamespegram.com/schdule-random-post-time/
Description: Schedule Random Post Time adds a button to the post, page and comments that adds a button allowing you to generate a random date. This plugin is useful for us lazy people that would rather not take the time trying to decide what date and time in the future to schedule a post for. Simply click the button and let it decide for you. A shoutout goes to radiok for his <a href="http://wordpress.org/extend/plugins/datetime-now-button/">Date/Time Now Button</a> plugin which served as a basis for this plugin.
Author: James Pegram
Version: 1.0
Author URI: http://www.jamespegram.com
*/

/*  Copyright 2011 
	
    James Pegram (email : jwpegram [make-an-at] gmail [make-a-dot] com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
define('srpt_VERSION', '1.0');	// Current version of the Plugin
define('srpt_NAME', 'Schedule Random Post Time');	// Name of the Plugin

$srpt_path       = preg_replace('/^.*wp-content[\\\\\/]plugins[\\\\\/]/', '', __FILE__);
$srpt_path       = str_replace('\\','/',$srpt_path);
$srpt_fullpath   = $srpt_siteurl.'/wp-content/plugins/'.substr($srpt_path,0,strrpos($srpt_path,'/')).'/';

add_action( 'init', 'srpt_init' );
add_action('admin_menu', "srpt_admin_options");		
add_action("admin_head", "AddRandomButton");	

if ($_GET['page'] == 'srpt') {
	wp_register_style('srpt.css', $srpt_fullpath . 'srpt.css');
	wp_enqueue_style('srpt.css');
}

			
register_activation_hook( __FILE__, 'srpt_activate' );
register_uninstall_hook(__FILE__, 'srpt_uninstall' );

if ( isset( $_POST['srpt_uninstall'], $_POST['srpt_uninstall_confirm'] ) ) {
	srpt_uninstall();
}	

// Initialize plugin
function srpt_init() {
	if ( function_exists( 'load_plugin_textdomain' ) ) {
		load_plugin_textdomain( 'schedule-random-post-time', PLUGINDIR.'/'.dirname(plugin_basename(__FILE__)) );
	}
	
}		

function AddRandomButton()	{
			?>
	<script type="text/javascript">
	jQuery(document).ready(function() {
		if (jQuery('#timestampdiv').length > 0) {
			jQuery('#timestampdiv').find('div')
				.append('&nbsp;')
				.append(jQuery('<a>')
					.attr('class', 'now button')
					.append('rDate')
				);
		}

		if (jQuery('.inline-edit-date').length > 0) {
			jQuery('.inline-edit-date').find('div')
				.append('&nbsp;')
				.append(jQuery('<a>')
					.attr('class', 'now button')
					.append('rDate')
				);
		}

		jQuery('.now.button').bind('click', function() {
			<?php
			$options = get_option('srpt_options');
			$now = current_time('timestamp');
			
			$newhour_x = mt_rand(0,$options['srpt_futurehours']);
			$h_x = mt_rand(1,24);
			$m_x = mt_rand(1,59);
			$s_x = mt_rand(1,59);
			
			$newdate = date('Y-m-d', strtotime('+'.$newhour_x.' hours'));
			$newtime = $h_x.':'.$m_x.':'.$s_x;

			$datetime = strtotime($newdate.' '.$newtime);

			$cur_mm = gmdate( 'm', $datetime );
			$cur_jj = gmdate( 'd', $datetime );
			$cur_aa = gmdate( 'Y', $datetime );
			$cur_hh = gmdate( 'H', $datetime );
			$cur_mn = gmdate( 'i', $datetime );
			?>
			if (jQuery('select[name="mm"]').length > 0) jQuery('select[name="mm"]').val('<?php echo $cur_mm; ?>');
			if (jQuery('input[name="jj"]').length > 0) jQuery('input[name="jj"]').val('<?php echo $cur_jj; ?>');
			if (jQuery('input[name="aa"]').length > 0) jQuery('input[name="aa"]').val('<?php echo $cur_aa; ?>');
			if (jQuery('input[name="hh"]').length > 0) jQuery('input[name="hh"]').val('<?php echo $cur_hh; ?>');
			if (jQuery('input[name="mn"]').length > 0) jQuery('input[name="mn"]').val('<?php echo $cur_mn; ?>');
		});
	});
	</script>
	<?php
}
	
	
/*
============================================
ADMIN
============================================
*/

function srpt_activate() {

	$default_options = array('srpt_futurehours' => '48');
		
	add_option('srpt_options', $default_options);
	update_option('srpt_version', srpt_VERSION);

	return true;	
}


function srpt_admin_options() {
	if ( function_exists('add_management_page') ) {
		add_options_page('Schedule Random Post Time', 'Schedule Random Post Time', 'manage_options', 'srpt', 'srpt_admin_settings');

		//call register settings function
		add_action( 'admin_init', 'srpt_register_settings' );
		
	}
}		
	
	// Let's do a bit of validation on the submitted values, just in case something strange got submitted
	function srpt_options_validate($input) {
		
		$options = get_option('srpt_options');
		$options['srpt_futurehours'] = intval($input['srpt_futurehours']);
		return $options;
	}
	
	
	// Administration menu
	function srpt_admin_settings() {
		

	    // Check that the user has the required permission level 
	    if (!current_user_can('manage_options')) { wp_die( __('You do not have sufficient permissions to access this page.') ); }
	
	    
	    //$options = get_option('srpt_options');
	?>
<div class="wrap">
<div id="icon-options-general" class="icon32"><br /></div>
<h2><?php echo srpt_NAME .' ( v.'. srpt_VERSION .' )'; ?></h2>
<?php

   // srpt_admin_message();
     
    
    ?>

	<div class="postbox-container" style="width: 70%;">
	<div class="metabox-holder">	
	<div class="meta-box-sortables">
	
	<?php if ( $_GET['module'] == 'help')  { include('help.php'); get_help(); } else {	?>
		<form method="post" id="srpadmin" action="options.php">
		<?php settings_fields( 'srpt_admin_options' ); ?>
			<div class="postbox"><?php do_settings_sections( 'srpt_form' ); ?></div>
		</form>
	<?php } ?>
	</div></div>
	</div>

	<div class="postbox-container" style="width:26%;">
	<div class="metabox-holder">	
	<div class="meta-box-sortables">
		
		<?php srpt_postbox_support(); ?>	
		<?php srpt_postbox_uninstall(); ?>	
	
	</div></div>
	</div>
</div>


			
<?php


}

function srpt_register_settings() {
	register_setting( 'srpt_admin_options', 'srpt_options','srpt_options_validate');
	add_settings_section('srpt_form', 'Time Settings', 'srpt_form_settings', 'srpt_form');	
}

function srpt_form_settings() { 
	
	$options = get_option('srpt_options');
	
	echo '<div class="inside"><div class="intro"><p>Select the number of hours into the future to create posts.</p></div>'; 
	
	echo '<fieldset>';
	echo '<dl><dt><label>Max Hours Into The Future:</label><p>ex: To randomly select dates between now and 2 days from now enter 48. <br />Default value is 48 hours (2 days)</p></dt>
	<dd><input name="srpt_options[srpt_futurehours]" type="text" size="5" maxlength="5" value="'. $options['srpt_futurehours'] .'" /></dd></dl>';	

	
	echo '</fieldset><div style="clear:both;"></div>';
	if (get_bloginfo('version') >= '3.1') { submit_button('Save Changes'); } else { echo '<input type="submit" name="submit" id="submit" class="button-primary" value="Save Changes"  />'; }
	echo '</div>';
} 



		 // On uninstall all Block Spam By Math Reloaded options will be removed from database
		function srpt_uninstall() {
		
			delete_option( 'srpt_version' );
			delete_option( 'srpt_futurehours' );
		
			$current = get_option('active_plugins');
			array_splice($current, array_search( $_POST['plugin'], $current), 1 ); // Array-function!
			update_option('active_plugins', $current);
			header('Location: plugins.php?deactivate=true');
		}
		
		function srpt_build_postbox( $id, $title, $content, $ech = TRUE ) {
		
			$output  = '<div id="srpt_' . $id . '" class="postbox">';
			$output .= '<div class="handlediv" title="Click to toggle"><br /></div>';
			$output .= '<h3 class="hndle"><span>' . $title . '</span></h3>';
			$output .= '<div class="inside">';
			$output .= $content;
			$output .= '</div></div>';
		
			if ( $ech === TRUE )
				echo $output;
			else
				return $output;
		
		}



function srpt_postbox_support() {
	
$output  = '<p>' . __( 'If you require support, or would like to contribute to the further development of this plugin, please choose one of the following;', 'srpt' ) . '</p>';
	$output .= '<ul style="list-style:circle;margin-left:25px;">';
	$output .= '<li><a href="http://www.jamespegram.com/">' . __( 'Author Homepage', 'srpt' ) . '</a></li>';
	$output .= '<li><a href="http://www.jamespegram.com/schedule-random-post-time/">' . __( 'Plugin Homepage', 'srpt' ) . '</a></li>';
	$output .= '<li><a href="http://wordpress.org/extend/plugins/schedule-random-post-time/">' . __( 'Rate This Plugin', 'srpt' ) . '</a></li>';
	$output .= '<li><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=10845671">' . __( 'Donate To The Cause', 'srpt' ) . '</a></li>';
	$output .= '</ul>';	
	
	srpt_build_postbox( 'display_options', __( 'Support', 'srpt' ), $output );	
}


function srpt_postbox_uninstall() {
	
	$output  = '<form action="" method="post">';
	$output .= '<input type="hidden" name="plugin" id="plugin" value="schedule-random-post-time/schedule-random-post-time.php" />';

	if ( isset( $_POST['srpt_uninstall'] ) && ! isset( $_POST['srpt_uninstall_confirm'] ) ) {
		$output .= '<p class="error">' . __( 'You must check the confirm box before continuing.', 'srpt' ) . '</p>';
	}

	$output .= '<p>' . __( 'The options for this plugin are not removed on deactivation to ensure that no data is lost unintentionally.', 'srpt' ) . '</p>';
	$output .= '<p>' . __( 'If you wish to remove all plugin information for your database be sure to run this uninstall utility first.', 'srpt' ) . '</p>';
	$output .= '<p class="aside"><input type="checkbox" name="srpt_uninstall_confirm" value="1" /> ' . __( 'Please confirm before proceeding.', 'srpt' ) . '</p>';
	$output .= '<p class="srpt_submit center"><input type="submit" name="srpt_uninstall" class="button-secondary" value="' . __( 'Uninstall', 'srpt' ) . '" /></p>';

	$output .= '</form>';
	
	srpt_build_postbox( 'display_options', __( 'Uninstall Plugin', 'srpt' ), $output );	
}

?>