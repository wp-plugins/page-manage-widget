<?php
/*
 * Plugin Name:   Page Manage Widget
 * Version:       1.0
 * Plugin URI:    http://wordpress.org/extend/plugins/page-manage-widget/
 * Description:   This plugin gives the flebility to effectively manage pages in a sidebar with multiple instances possible. Adjust your settings <a href="options-general.php?page=page-manage-widget/page-manage-widget.php">here</a>.
 * Author:        MaxBlogPress
 * Author URI:    http://www.maxblogpress.com
 *
 * License:       GNU General Public License
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * 
 * Copyright (C) 2007 www.maxblogpress.com
 *
 * This is the improved version of "Breukie's Pages Widget" plugin by Arnold Breukhoven
 *
 */
$mbppmw_path      = preg_replace('/^.*wp-content[\\\\\/]plugins[\\\\\/]/', '', __FILE__);
$mbppmw_path      = str_replace('\\','/',$mbppmw_path);
$mbppmw_dir       = substr($mban_path,0,strrpos($mbppmw_path,'/'));
$mbppmw_siteurl   = get_bloginfo('wpurl');
$mbppmw_siteurl   = (strpos($mbppmw_siteurl,'http://') === false) ? get_bloginfo('siteurl') : $mbppmw_siteurl;
$mbppmw_fullpath  = $mbppmw_siteurl.'/wp-content/plugins/'.$mbppmw_dir.'';
$mbppmw_fullpath  = $mbppmw_fullpath.'page-manage-widget/';
$mbppmw_abspath   = str_replace("\\","/",ABSPATH); 

define('MBP_PMW_ABSPATH', $mbppmw_path);
define('MBP_PMW_LIBPATH', $mbppmw_fullpath);
define('MBP_PMW_SITEURL', $mbppmw_siteurl);
define('MBP_PMW_NAME', 'Page Manage Widget');
define('MBP_PMW_VERSION', '1.0');  
define('MBP_PMW_LIBPATH', $mbppmw_fullpath);
global $wp_version;

if ($wp_version > '2.3') {
	

	function mbp_pmw_options() {
		add_options_page('Page Manage Widget', 'Page Manage Widget', 10, __FILE__, 'mbp_pmw_activate');
	} 
	
	function mbp_pmw_activate() {
		$mbp_pmw_activate = get_option('mbp_pmw_activate');
		$reg_msg = '';
		$mbp_pmw_msg = '';
		$form_1 = 'mbp_pmw_reg_form_1';
		$form_2 = 'mbp_pmw_reg_form_2';
			// Activate the plugin if email already on list
		if ( trim($_GET['mbp_onlist']) == 1 ) {
			$mbp_pmw_activate = 2;
			update_option('mbp_pmw_activate', $mbp_pmw_activate);
			$reg_msg = 'Thank you for registering the plugin. It has been activated'; 
		} 
		// If registration form is successfully submitted
		if ( ((trim($_GET['submit']) != '' && trim($_GET['from']) != '') || trim($_GET['submit_again']) != '') && $mbp_pmw_activate != 2 ) { 
			update_option('mbp_pmw_name', $_GET['name']);
			update_option('mbp_pmw_email', $_GET['from']);
			$mbp_pmw_activate = 1;
			update_option('mbp_pmw_activate', $mbp_pmw_activate);
		}
		if ( intval($mbp_pmw_activate) == 0 ) { // First step of plugin registration
			global $userdata;
			mbp_pmwRegisterStep1($form_1,$userdata);
		} else if ( intval($mbp_pmw_activate) == 1 ) { // Second step of plugin registration
			$name  = get_option('mbp_pmw_name');
			$email = get_option('mbp_pmw_email');
			mbp_pmwRegisterStep2($form_2,$name,$email);
		} else if ( intval($mbp_pmw_activate) == 2 ) { // Options page
				if ( trim($reg_msg) != '' ) {
					echo '<div id="message" class="updated fade"><p><strong>'.$reg_msg.'</strong></p></div>';
				}			
			}
		
		if($mbp_pmw_activate != '' && !$_GET['submit']) {
		?>
			
		<div class="wrap">
			<h2><?php echo MBP_PMW_NAME.' '.MBP_PMW_VERSION; ?></h2>
		<strong><img src="<?php echo MBP_AIT_LIBPATH;?>image/how.gif" border="0" align="absmiddle" /> <a href="http://wordpress.org/extend/plugins/page-manage-widget/other_notes/" target="_blank">How to use it</a>&nbsp;&nbsp;&nbsp;
				<img src="<?php echo MBP_AIT_LIBPATH;?>image/comment.gif" border="0" align="absmiddle" /> <a href="http://www.maxblogpress.com/forum/forumdisplay.php?f=35" target="_blank">Community</a></strong>
		<br/><br/>				
				
				<div id="message" class="updated fade">
					<p>
						<strong>You have already registered. Please go to the <a href="<?php echo MBP_PMW_SITEURL;?>/wp-admin/widgets.php">Widgets</a> section to enable and configure the widget.</strong>
					</p>
				</div>
		</div>	
		<?php
		}	
	}
	function widget_pmw( $args, $widget_args = 1 ) {
		extract( $args, EXTR_SKIP );
		if ( is_numeric($widget_args) )
			$widget_args = array( 'number' => $widget_args );
		$widget_args = wp_parse_args( $widget_args, array( 'number' => -1 ) );
		extract( $widget_args, EXTR_SKIP );
	
		$options 			= get_option('widget_pmw');
		
		if ( !isset($options[$number]) )
			return;			
			
		//check if registered or not
		$mbp_pmw_activate 	= get_option('mbp_pmw_activate');				
		if ($mbp_pmw_activate == '') {
			echo "Please register in the admin panel to activate the `Page Manage Widget` widget";
		} else {			
			
	?>
		<?php echo $before_widget; ?>
		<div class="page_manage">
			
			<?php
			//for page output
				$title 				= empty($options[$number]['title']) ? __('Pages') : $options[$number]['title'];
				$orderby 			= empty($options[$number]['orderby']) ? 'ID' : $options[$number]['orderby'];
				$order 				= empty($options[$number]['order']) ? 'asc' : $options[$number]['order'];
				$child_of 			= empty($options[$number]['child_of']) ? '' : $options[$number]['child_of'];

				$exclude 			= empty($options[$number]['exclude']) ? '' : $options[$number]['exclude'];

				$title_li 			= empty($options[$number]['title_li']) ? '' : $options[$number]['title_li'];
				$depth 				= empty($options[$number]['depth']) ? '' : $options[$number]['depth'];				

				echo "<div class='" . $title . "'>" .  $title . "</div>";
				echo '<ul>';
				if ( function_exists('gdm_list_selected_pages') )
				{
					gdm_list_selected_pages("sort_column="  
											. $orderby 
											. "&title_li=" 
											. $title_li 
											. "&exclude=" 
											. $exclude 
											. "&sort_order=" 
											. $order 
											. "&depth=" 
											. $depth 
											. "&child_of=" 
											. $child_of);
				}
				else
				{
					wp_list_pages("sort_column="  
								. $orderby 
								. "&title_li=" 
								. $title_li 
								. "&exclude=" 
								. $exclude 
								. "&sort_order=" 
								. $order 
								. "&depth=" 
								. $depth 
								. "&child_of=" 
								. $child_of);
				}
				echo '</ul>';	
			?>
		</div>
		<?php echo $after_widget; ?>
	<?php
		}//user registered or not
	}
	
	function widget_pmw_control( $widget_args = 1 ) {
		global $wp_registered_widgets;
		static $updated = false; // Whether or not we have already updated the data after a POST submit
	
		if ( is_numeric($widget_args) )
			$widget_args = array( 'number' => $widget_args );
		$widget_args = wp_parse_args( $widget_args, array( 'number' => -1 ) );
		extract( $widget_args, EXTR_SKIP );
	
		// Data is stored as array:	 array( number => data for that instance of the widget, ... )
		$options = get_option('widget_pmw');
		if ( !is_array($options) )
			$options = array();
	
		// We need to update the data
		if ( !$updated && !empty($_POST['sidebar']) ) {
			// Tells us what sidebar to put the data in
			$sidebar = (string) $_POST['sidebar'];
	
			$sidebars_widgets = wp_get_sidebars_widgets();
			if ( isset($sidebars_widgets[$sidebar]) )
				$this_sidebar =& $sidebars_widgets[$sidebar];
			else
				$this_sidebar = array();
	
			foreach ( $this_sidebar as $_widget_id ) {
				if ( 'widget_pmw' == $wp_registered_widgets[$_widget_id]['callback'] && isset($wp_registered_widgets[$_widget_id]['params'][0]['number']) ) {
					$widget_number = $wp_registered_widgets[$_widget_id]['params'][0]['number'];
					if ( !in_array( "pmw-$widget_number", $_POST['widget-id'] ) ) // the widget has been removed
						unset($options[$widget_number]);
				}
			}
	
			foreach ( (array) $_POST['widget-pmw'] as $widget_number => $widget_pmw ) {
				if ( !isset($widget_pmw['title']) && isset($options[$widget_number]) ) // user clicked cancel
					continue;
				
				$title 						= wp_specialchars( $widget_pmw['title'] );
				$orderby 					= $widget_pmw['orderby'] ;
				$order 						= $widget_pmw['order'] ;
				$child_of 					= $widget_pmw['child_of'];
				$exclude 					= implode(",", $_POST['exclude' . $widget_number]);
				$title_li 					= $widget_pmw['title_li'];
				$depth						= $widget_pmw['depth'];
				
				$image 		= wp_specialchars( $widget_pmw['image'] );
				$alt 		= wp_specialchars( $widget_pmw['alt'] );
				$link 		= wp_specialchars( $widget_pmw['link'] );
				$new_window = isset( $widget_pmw['new_window'] );
				$options[$widget_number] 	= compact('image', 
														'alt', 
														'link', 
														'new_window',
														'title', 
														'orderby',
														'order', 
														'child_of',
														'exclude',
														'title_li',
														'depth'
														);			
			}
	
			update_option('widget_pmw', $options);
			$updated = true; // So that we don't go through this more than once
		}
		
		//print_r($options);
		if ( -1 == $number ) { 
			$title 						= '';
			$orderby					= '';
			$order 						= '';
			$child_of 					= '';
			$exclude 					= '';
			$title_li 					= '';
			$depth						= '';
			$image = '';
			$alt = '';
			$link = '';
			$new_window = '';
			$number = '%i%';
		} else {
			$title 						= attribute_escape($options[$number]['title']);
			$orderby 					= attribute_escape($options[$number]['orderby']);
			$order 						= attribute_escape($options[$number]['order']);
			$child_of 					= attribute_escape($options[$number]['child_of']);
			$exclude 					= attribute_escape($options[$number]['exclude']);
			$title_li 					= attribute_escape($options[$number]['title_li']);		
			$depth 						= attribute_escape($options[$number]['depth']);
			
			$image 		= attribute_escape($options[$number]['image']);
			$alt 		= attribute_escape($options[$number]['alt']);
			$link 		= attribute_escape($options[$number]['link']);
			$new_window = attribute_escape($options[$number]['new_window']);
		}
	?>
			<p>
				<label for="pmw-title-<?php echo $number; ?>">
					<?php _e('Title:'); ?>
					<input class="widefat" id="pmw-title-<?php echo $number; ?>" name="widget-pmw[<?php echo $number; ?>][title]" type="text" value="<?php echo $title; ?>" />
				</label>
			</p>		
			
			<p>
				<label for="pmw-title_li-<?php echo $number; ?>">
					<?php _e('Title li:'); ?>
					<input class="widefat" id="pmw-title_li-<?php echo $number; ?>" name="widget-pmw[<?php echo $number; ?>][title_li]" type="text" value="<?php echo $title_li; ?>" />
				</label>
			</p>			
			
			<p>
				<label for="pmw-orderby-<?php echo $number; ?>">
					<?php _e('Sort Options:'); ?><br/>
					<select id="widget-pmw-orderby-<?php echo $number; ?>" name="widget-pmw[<?php echo $number; ?>][orderby]">
					<?php echo "<option value=\"\">Select</option>"; ?>
					<?php echo "<option value=\"id\"" . ($orderby=='id' ? " selected='selected'" : '') .">ID</option>"; ?>
					<?php echo "<option value=\"post_title\"" . ($orderby=='post_title' ? " selected='selected'" : '') .">Title</option>"; ?>
					
					<?php echo "<option value=\"menu_order\"" . ($orderby=='menu_order' ? " selected='selected'" : '') .">Menu Order</option>"; ?>
					
					<?php echo "<option value=\"post_date\"" . ($orderby=='post_date' ? " selected='selected'" : '') .">Date</option>"; ?>
					
					<?php echo "<option value=\"post_modified\"" . ($orderby=='post_modified' ? " selected='selected'" : '') .">Modified</option>"; ?>
					
					<?php echo "<option value=\"post_author\"" . ($orderby=='post_author' ? " selected='selected'" : '') .">Author</option>"; ?>	
					
					<?php echo "<option value=\"post_name\"" . ($orderby=='post_name' ? " selected='selected'" : '') .">Name</option>"; ?>																									
					
					</select>&nbsp;<select id="widget-pmw-order-<?php echo $number; ?>" name="widget-pmw[<?php echo $number; ?>][order]" value="<?php echo $order; ?>">
					<?php echo "<option value=\"\">Select</option>"; ?>
					<?php echo "<option value=\"asc\"" . ($order=='asc' ? " selected='selected'" : '') .">ASC</option>"; ?>
					<?php echo "<option value=\"desc\"" . ($order=='desc' ? " selected='selected'" : '') .">DESC</option>"; ?>
					</select>				
				</label>
			</p>		
			
<style type="text/css">
<!--
#wpcontent select {
	height:auto;
}
-->
</style>		
			<p>
				<label for="pmw-child_of-<?php echo $number; ?>">
					<?php _e('Child Of:'); ?>
					<select id="widget-pmw-child_of-<?php echo $number; ?>" name="widget-pmw[<?php echo $number; ?>][child_of]">
					<option value="">Select</option>
					<?php 
						global $wpdb;
						$query_cat = "SELECT
											ID,
											post_title
									   FROM 
									   		" . $wpdb->posts 
									    . " WHERE
											post_type='page'
											AND post_status='publish'";
						$sql_cat   = mysql_query($query_cat);
						while($rs_cat	    = mysql_fetch_array($sql_cat)) {	 
					?>
					
					<option <?php if ($rs_cat['ID'] == $child_of) { echo 'selected';}?> value="<?php echo $rs_cat['ID'];?>">
						<?php echo $rs_cat['post_title'];?>
					</option>
					<?php } ?>
					</select>
				</label>
			</p>	
				
			<p>
				<label style="height:20px;" for="pmw-exclude-<?php echo $number; ?>">
					<?php _e('Exclude Pages:'); ?>	<br/>	
					<?php
						//tweak for breaking cat id
						$exclude_vals = explode(",",$exclude);
						foreach($exclude_vals as $key=>$val) {
							$arr_exclude[] = $val;
						}
					?>
					
					<select id="pmw-exclude-<?php echo $number; ?>" name="exclude<?php echo $number;?>[]" multiple="multiple">
					<?php
						$query_cat = "SELECT
											ID,
											post_title
									   FROM 
									   		" . $wpdb->posts 
									    . " WHERE 
											post_type='page'
											AND post_status='publish'";
						$sql_cat   = mysql_query($query_cat);
						while($rs_cat	    = mysql_fetch_array($sql_cat)) {
							$sel = (in_array($rs_cat['ID'], $arr_exclude)) ? ' selected="selected"':'';				
					?>						
						<option <?php echo $sel;?> value="<?php echo $rs_cat['ID'];?>">
							<?php echo $rs_cat['post_title'];?>
						</option>
					<?php } ?>						
					</select>
				</label>
			</p>		
	
<p>
				<label for="pmw-depth-<?php echo $number; ?>">
					<?php _e('Depth:'); ?>
					<select id="widget-pmw-depth-<?php echo $number; ?>" name="widget-pmw[<?php echo $number; ?>][depth]">
					<option value="">Select</option>
					<option <?php if ($depth == 'h') { echo 'selected';}?> value="h">
						Hierarchy 
					</option>
					
					<option <?php if ($depth == '-1') { echo 'selected';}?> value="-1">
						Flat 
					</option>
					
					<option <?php if ($depth == 1) { echo 'selected';}?> value="1">
						Top Levels 
					</option>
					
					<option <?php if ($depth == 2) { echo 'selected';}?> value="2">
						Level 2 
					</option>	
					
					<option <?php if ($depth == 3) { echo 'selected';}?> value="3">
						Level 3 
					</option>	
					
					<option <?php if ($depth == 4) { echo 'selected';}?> value="4">
						Level 4 
					</option>	
					
					<option <?php if ($depth == 5) { echo 'selected';}?> value="5">
						Level 5 
					</option>	
					
					<option <?php if ($depth == 6) { echo 'selected';}?> value="6">
						Level 6 
					</option>	
					
					<option <?php if ($depth == 7) { echo 'selected';}?> value="7">
						Level 7 
					</option>	
					
					<option <?php if ($depth == 8) { echo 'selected';}?> value="8">
						Level 8 
					</option>	
					
					<option <?php if ($depth == 9) { echo 'selected';}?> value="9">
						Level 9 
					</option>	
					
					<option <?php if ($depth == 10) { echo 'selected';}?> value="10">
						Level 10 
					</option>																																																							
					</select>
				</label>
			</p>			
				
			<input type="hidden" id="widget-pmw-submit-<?php echo $number; ?>" name="widget-pmw[<?php echo $number; ?>][submit]" value="1" />
	<?php
	}
	
	// Registers each instance of widget on startup
	function widget_pmw_register() {
		if ( !$options = get_option('widget_pmw') )
			$options = array();
	
		$widget_ops = array('classname' => 'widget_pmw', 'description' => __('Page Management'));
		$control_ops = array( 'id_base' => 'pmw');
		$name = __(MBP_PMW_NAME);
	
		$registered = false;
		foreach ( array_keys($options) as $o ) {
			// Old widgets can have null values for some reason
			if ( !isset($options[$o]['image']) )
				continue;
	
			$id = "pmw-$o"; // Never never never translate an id
			$registered = true;
			wp_register_sidebar_widget( $id, $name, 'widget_pmw', $widget_ops, array( 'number' => $o ) );
			wp_register_widget_control( $id, $name, 'widget_pmw_control', $control_ops, array( 'number' => $o ) );
		}
	
		// If there are none, we register the widget's existance with a generic template
		if ( !$registered ) {
			wp_register_sidebar_widget( 'pmw-1', $name, 'widget_pmw', $widget_ops, array( 'number' => -1 ) );
			wp_register_widget_control( 'pmw-1', $name, 'widget_pmw_control', $control_ops, array( 'number' => -1 ) );
		}
	}
	
	
// Srart Registration.

/**
 * Plugin registration form
 */
function mbp_pmwRegistrationForm($form_name, $submit_btn_txt='Register', $name, $email, $hide=0, $submit_again='') {
	$wp_url = get_bloginfo('wpurl');
	$wp_url = (strpos($wp_url,'http://') === false) ? get_bloginfo('siteurl') : $wp_url;
	$plugin_pg    = 'options-general.php';
	$thankyou_url = $wp_url.'/wp-admin/'.$plugin_pg.'?page='.$_GET['page'];
	$onlist_url   = $wp_url.'/wp-admin/'.$plugin_pg.'?page='.$_GET['page'].'&amp;mbp_onlist=1';
	if ( $hide == 1 ) $align_tbl = 'left';
	else $align_tbl = 'center';
	?>
	
	<?php if ( $submit_again != 1 ) { ?>
	<script><!--
	function trim(str){
		var n = str;
		while ( n.length>0 && n.charAt(0)==' ' ) 
			n = n.substring(1,n.length);
		while( n.length>0 && n.charAt(n.length-1)==' ' )	
			n = n.substring(0,n.length-1);
		return n;
	}
	function mbp_pmwValidateForm_0() {
		var name = document.<?php echo $form_name;?>.name;
		var email = document.<?php echo $form_name;?>.from;
		var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
		var err = ''
		if ( trim(name.value) == '' )
			err += '- Name Required\n';
		if ( reg.test(email.value) == false )
			err += '- Valid Email Required\n';
		if ( err != '' ) {
			alert(err);
			return false;
		}
		return true;
	}
	//-->
	</script>
	<?php } ?>
	<table align="<?php echo $align_tbl;?>">
	<form name="<?php echo $form_name;?>" method="post" action="http://www.aweber.com/scripts/addlead.pl" <?php if($submit_again!=1){;?>onsubmit="return mbp_pmwValidateForm_0()"<?php }?>>
	 <input type="hidden" name="unit" value="maxbp-activate">
	 <input type="hidden" name="redirect" value="<?php echo $thankyou_url;?>">
	 <input type="hidden" name="meta_redirect_onlist" value="<?php echo $onlist_url;?>">
	 <input type="hidden" name="meta_adtracking" value="mr-posr-ordering">
	 <input type="hidden" name="meta_message" value="1">
	 <input type="hidden" name="meta_required" value="from,name">
	 <input type="hidden" name="meta_forward_vars" value="1">	
	 <?php if ( $submit_again == 1 ) { ?> 	
	 <input type="hidden" name="submit_again" value="1">
	 <?php } ?>		 
	 <?php if ( $hide == 1 ) { ?> 
	 <input type="hidden" name="name" value="<?php echo $name;?>">
	 <input type="hidden" name="from" value="<?php echo $email;?>">
	 <?php } else { ?>
	 <tr><td>Name: </td><td><input type="text" name="name" value="<?php echo $name;?>" size="25" maxlength="150" /></td></tr>
	 <tr><td>Email: </td><td><input type="text" name="from" value="<?php echo $email;?>" size="25" maxlength="150" /></td></tr>
	 <?php } ?>
	 <tr><td>&nbsp;</td><td><input type="submit" name="submit" value="<?php echo $submit_btn_txt;?>" class="button" /></td></tr>
	 </form>
	</table>
	<?php
}

/**
 * Register Plugin - Step 2
 */
function mbp_pmwRegisterStep2($form_name='frm2',$name,$email) {
	$msg = 'You have not clicked on the confirmation link yet. A confirmation email has been sent to you again. Please check your email and click on the confirmation link to activate the plugin.';
	if ( trim($_GET['submit_again']) != '' && $msg != '' ) {
		echo '<div id="message" class="updated fade"><p><strong>'.$msg.'</strong></p></div>';
	}
	?>
	<style type="text/css">
	table, tbody, tfoot, thead {
		padding: 8px;
	}
	tr, th, td {
		padding: 0 8px 0 8px;
	}
	</style>
	<div class="wrap"><h2> <?php echo MBP_PMW_NAME.' '.MBP_PMW_VERSION; ?></h2>
	 <center>
	 <table width="100%" cellpadding="3" cellspacing="1" style="border:1px solid #e3e3e3; padding: 8px; background-color:#f1f1f1;">
	 <tr><td align="center">
	 <table width="650" cellpadding="5" cellspacing="1" style="border:1px solid #e9e9e9; padding: 8px; background-color:#ffffff; text-align:left;">
	  <tr><td align="center"><h3>Almost Done....</h3></td></tr>
	  <tr><td><h3>Step 1:</h3></td></tr>
	  <tr><td>A confirmation email has been sent to your email "<?php echo $email;?>". You must click on the link inside the email to activate the plugin.</td></tr>
	  <tr><td><strong>The confirmation email will look like:</strong><br /><img src="http://www.maxblogpress.com/images/activate-plugin-email.jpg" vspace="4" border="0" /></td></tr>
	  <tr><td>&nbsp;</td></tr>
	  <tr><td><h3>Step 2:</h3></td></tr>
	  <tr><td>Click on the button below to Verify and Activate the plugin.</td></tr>
	  <tr><td><?php mbp_pmwRegistrationForm($form_name.'_0','Verify and Activate',$name,$email,$hide=1,$submit_again=1);?></td></tr>
	 </table>
	 </td></tr></table><br />
	 <table width="100%" cellpadding="3" cellspacing="1" style="border:1px solid #e3e3e3; padding:8px; background-color:#f1f1f1;">
	 <tr><td align="center">
	 <table width="650" cellpadding="5" cellspacing="1" style="border:1px solid #e9e9e9; padding:8px; background-color:#ffffff; text-align:left;">
	   <tr><td><h3>Troubleshooting</h3></td></tr>
	   <tr><td><strong>The confirmation email is not there in my inbox!</strong></td></tr>
	   <tr><td>Dont panic! CHECK THE JUNK, spam or bulk folder of your email.</td></tr>
	   <tr><td>&nbsp;</td></tr>
	   <tr><td><strong>It's not there in the junk folder either.</strong></td></tr>
	   <tr><td>Sometimes the confirmation email takes time to arrive. Please be patient. WAIT FOR 6 HOURS AT MOST. The confirmation email should be there by then.</td></tr>
	   <tr><td>&nbsp;</td></tr>
	   <tr><td><strong>6 hours and yet no sign of a confirmation email!</strong></td></tr>
	   <tr><td>Please register again from below:</td></tr>
	   <tr><td><?php mbp_pmwRegistrationForm($form_name,'Register Again',$name,$email,$hide=0,$submit_again=2);?></td></tr>
	   <tr><td><strong>Help! Still no confirmation email and I have already registered twice</strong></td></tr>
	   <tr><td>Okay, please register again from the form above using a DIFFERENT EMAIL ADDRESS this time.</td></tr>
	   <tr><td>&nbsp;</td></tr>
	   <tr>
		 <td><strong>Why am I receiving an error similar to the one shown below?</strong><br />
			 <img src="http://www.maxblogpress.com/images/no-verification-error.jpg" border="0" vspace="8" /><br />
		   You get that kind of error when you click on &quot;Verify and Activate&quot; button or try to register again.<br />
		   <br />
		   This error means that you have already subscribed but have not yet clicked on the link inside confirmation email. In order to  avoid any spam complain we don't send repeated confirmation emails. If you have not recieved the confirmation email then you need to wait for 12 hours at least before requesting another confirmation email. </td>
	   </tr>
	   <tr><td>&nbsp;</td></tr>
	   <tr><td><strong>But I've still got problems.</strong></td></tr>
	   <tr><td>Stay calm. <strong><a href="http://www.maxblogpress.com/contact-us/" target="_blank">Contact us</a></strong> about it and we will get to you ASAP.</td></tr>
	 </table>
	 </td></tr></table>
	 </center>		
	<p style="text-align:center;margin-top:3em;"><strong><?php echo MBP_PMW_NAME.' '.MBP_PMW_VERSION; ?> by <a href="http://www.maxblogpress.com/" target="_blank" >MaxBlogPress</a></strong></p>
	</div>
	<?php
}

/**
 * Register Plugin - Step 1
 */
function mbp_pmwRegisterStep1($form_name='frm1',$userdata) {
	$name  = trim($userdata->first_name.' '.$userdata->last_name);
	$email = trim($userdata->user_email);
	?>
	<style type="text/css">
	tabled , tbody, tfoot, thead {
		padding: 8px;
	}
	tr, th, td {
		padding: 0 8px 0 8px;
	}
	</style>
	<div class="wrap"><h2> <?php echo MBP_PMW_NAME.' '.MBP_PMW_VERSION; ?></h2>
	 <center>
	 <table width="100%" cellpadding="3" cellspacing="1" style="border:2px solid #e3e3e3; padding: 8px; background-color:#f1f1f1;">
	  <tr><td align="center">
		<table width="548" align="center" cellpadding="3" cellspacing="1" style="border:1px solid #e9e9e9; padding: 8px; background-color:#ffffff;">
		  <tr><td align="center"><h3>Please register the plugin to activate it. (Registration is free)</h3></td></tr>
		  <tr><td align="left">In addition you'll receive complimentary subscription to MaxBlogPress Newsletter which will give you many tips and tricks to attract lots of visitors to your blog.</td></tr>
		  <tr><td align="center"><strong>Fill the form below to register the plugin:</strong></td></tr>
		  <tr><td align="center"><?php mbp_pmwRegistrationForm($form_name,'Register',$name,$email);?></td></tr>
		  <tr><td align="center"><font size="1">[ Your contact information will be handled with the strictest confidence <br />and will never be sold or shared with third parties ]</font></td></tr>
		</table>
	  </td></tr></table>
	 </center>
	<p style="text-align:center;margin-top:3em;"><strong><?php echo MBP_PMW_NAME.' '.MBP_PMW_VERSION; ?> by <a href="http://www.maxblogpress.com/" target="_blank" >MaxBlogPress</a></strong></p>
	</div>
	<?php
}	
	
	// add a option page
	add_action('admin_menu', 'mbp_pmw_options');
	// Hook for the registration
	add_action( 'widgets_init', 'widget_pmw_register' );
} else if ($wp_version < '2.5') {
function widget_pmwold_init()
{
	// Check for the required API functions
	if ( !function_exists('register_sidebar_widget') || !function_exists('register_widget_control') )
		return;

function widget_pmwold($args, $number = 1) {
	extract($args);
	$options = get_option('widget_pmwold');
	$title = empty($options[$number]['title']) ? __('Pages') : $options[$number]['title'];
// Extraatjes
	$sort_column = empty($options[$number]['sort_column']) ? 'post_title' : $options[$number]['sort_column'];
	$sort_order = empty($options[$number]['sort_order']) ? 'ASC' : $options[$number]['sort_order'];
	$title_li = empty($options[$number]['title_li']) ? '' : $options[$number]['title_li'];
	$exclude = empty($options[$number]['exclude']) ? '' : $options[$number]['exclude'];
	$depth = empty($options[$number]['depth']) ? '0' : $options[$number]['depth'];
	$child_of  = empty($options[$number]['child_of ']) ? '0' : $options[$number]['child_of '];
	echo $before_widget . $before_title . $title . $after_title . "<ul>\n";
//	WORDPRESS CODE wp_list_pages("title_li=");
//  REPLACED BY gdm_list_selected_pages
//  CHECKJE OF FUNCTIE BESTAAT DUS ;-)
	if ( function_exists('gdm_list_selected_pages') )
	{
		gdm_list_selected_pages("sort_column="  . $sort_column . "&title_li=" . $title_li . "&exclude=" . $exclude . "&sort_order=" . $sort_order . "&depth=" . $depth . "&child_of=" . $child_of);
	}
	else
	{
		wp_list_pages("sort_column="  . $sort_column . "&title_li=" . $title_li . "&exclude=" . $exclude . "&sort_order=" . $sort_order . "&depth=" . $depth . "&child_of=" . $child_of);
	}
	echo "</ul>\n" . $after_widget;
}

function widget_pmwold_control($number) {
	$options = $newoptions = get_option('widget_pmwold');
	if ( $_POST["pmw-submit-$number"] ) {
		$newoptions[$number]['title'] = strip_tags(stripslashes($_POST["pmw-title-$number"]));
// Extraatjes
		$newoptions[$number]['sort_column'] = strip_tags(stripslashes($_POST["pmw-sort_column-$number"]));
		$newoptions[$number]['sort_order'] = strip_tags(stripslashes($_POST["pmw-sort_order-$number"]));
		$newoptions[$number]['title_li'] = stripslashes($_POST["pmw-title_li-$number"]);
		$newoptions[$number]['exclude'] = strip_tags(stripslashes($_POST["pmw-exclude-$number"]));
		$newoptions[$number]['depth'] = strip_tags(stripslashes($_POST["pmw-depth-$number"]));
		$newoptions[$number]['child_of'] = strip_tags(stripslashes($_POST["pmw-child_of-$number"]));
	}
	if ( $options != $newoptions ) {
		$options = $newoptions;
		update_option('widget_pmwold', $options);
	}
	$title = htmlspecialchars($options[$number]['title'], ENT_QUOTES);
// Extraatjes
	$sort_column = htmlspecialchars($options[$number]['sort_column'], ENT_QUOTES);
	$sort_order = htmlspecialchars($options[$number]['sort_order'], ENT_QUOTES);
	$title_li = htmlspecialchars($options[$number]['title_li'], ENT_QUOTES);
	$exclude = htmlspecialchars($options[$number]['exclude'], ENT_QUOTES);
	$depth = htmlspecialchars($options[$number]['depth'], ENT_QUOTES);
	$child_of = htmlspecialchars($options[$number]['child_of'], ENT_QUOTES);
?>
<center>Check <a href="http://codex.wordpress.org/wp_list_pages" target="_blank">wp_list_pages</a> for help with these parameters.</center>
<br />
<table align="center" cellpadding="1" cellspacing="1" width="400">
<tr>
<td align="left" valign="middle" width="90" nowrap="nowrap">
Title Widget:
</td>
<td align="left" valign="middle">
<input style="width: 300px;" id="pmw-title-<?php echo "$number"; ?>" name="pmw-title-<?php echo "$number"; ?>" type="text" value="<?php echo $title; ?>" />
</td>
</tr>
<tr>
<tr>
<td align="left" valign="middle" width="90" nowrap="nowrap">
Title li:
</td>
<td align="left" valign="middle">
<input style="width: 300px;" id="pmw-title_li-<?php echo "$number"; ?>" name="pmw-title_li-<?php echo "$number"; ?>" type="text" value="<?php echo $title_li; ?>" />
</td>
</tr>
<tr>
<td align="left" valign="middle" width="90" nowrap="nowrap">
Sort Options:
</td>
<td align="left" valign="middle">
<select id="pmw-sort_column-<?php echo "$number"; ?>" name="pmw-sort_column-<?php echo "$number"; ?>" value="<?php echo $options[$number]['sort_column']; ?>">
<?php echo "<option value=\"\">Select</option>"; ?>
<?php echo "<option value=\"post_title\"" . ($options[$number]['sort_column']=='post_title' ? " selected='selected'" : '') .">Title</option>"; ?>
<?php echo "<option value=\"menu_order\"" . ($options[$number]['sort_column']=='menu_order' ? " selected='selected'" : '') .">Menu Order</option>"; ?>
<?php echo "<option value=\"post_date\"" . ($options[$number]['sort_column']=='post_date' ? " selected='selected'" : '') .">Date</option>"; ?>
<?php echo "<option value=\"post_modified\"" . ($options[$number]['sort_column']=='post_modified' ? " selected='selected'" : '') .">Modified</option>"; ?>
<?php echo "<option value=\"ID\"" . ($options[$number]['sort_column']=='id' ? " selected='selected'" : '') .">ID</option>"; ?>
<?php echo "<option value=\"post_author\"" . ($options[$number]['sort_column']=='post_author' ? " selected='selected'" : '') .">Author</option>"; ?>
<?php echo "<option value=\"post_name\"" . ($options[$number]['sort_column']=='post_name' ? " selected='selected'" : '') .">Name</option>"; ?>
</select>&nbsp; <select id="pmw-sort_order-<?php echo "$number"; ?>" name="pmw-sort_order-<?php echo "$number"; ?>" value="<?php echo $options[$number]['sort_order']; ?>">
<?php echo "<option value=\"\">Select</option>"; ?>
<?php echo "<option value=\"ASC\"" . ($options[$number]['sort_order']=='ASC' ? " selected='selected'" : '') .">ASC</option>"; ?>
<?php echo "<option value=\"DESC\"" . ($options[$number]['sort_order']=='DESC' ? " selected='selected'" : '') .">DESC</option>"; ?>
</select>
</td>
</tr>
<tr>
<td align="left" valign="middle" width="90" nowrap="nowrap">
Exclude Pages:
</td>
<td align="left" valign="middle">
<input style="width: 300px;" id="pmw-exclude-<?php echo "$number"; ?>" name="pmw-exclude-<?php echo "$number"; ?>" type="text" value="<?php echo $exclude; ?>" />
</td>
</tr>
<tr>
<td align="left" valign="middle" width="90" nowrap="nowrap">
Child of:
</td>
<td align="left" valign="middle">
<input style="width: 300px;" id="pmw-child_of-<?php echo "$number"; ?>" name="pmw-child_of-<?php echo "$number"; ?>" type="text" value="<?php echo $child_of; ?>" />
</td>
</tr>
<tr>
<td align="left" valign="middle" width="90" nowrap="nowrap">
Depth:
</td>
<td align="left" valign="middle">
<input style="width: 300px;" id="pmw-depth-<?php echo "$number"; ?>" name="pmw-depth-<?php echo "$number"; ?>" type="text" value="<?php echo $depth; ?>" />
<input type="hidden" id="pmw-submit-<?php echo "$number"; ?>" name="pmw-submit-<?php echo "$number"; ?>" value="1" />
</td>
</tr>
</table>
<br />

<?php
}

function widget_pmwold_setup() {
	$options = $newoptions = get_option('widget_pmwold');
	if ( isset($_POST['pmw-number-submit']) ) {
		$number = (int) $_POST['pmw-number'];
		if ( $number > 9 ) $number = 9;
		if ( $number < 1 ) $number = 1;
		$newoptions['number'] = $number;
	}
	if ( $options != $newoptions ) {
		$options = $newoptions;
		update_option('widget_pmwold', $options);
		widget_pmwold_register($options['number']);
	}
}

function widget_pmwold_page() {
	$options = $newoptions = get_option('widget_pmwold');
?>
	<div class="wrap">
		<form method="POST">
			<h2>Pages Manage Widget</h2>
			<p style="line-height: 30px;"><?php _e('How many Page widgets would you like?'); ?>
			<select id="pmw-number" name="pmw-number" value="<?php echo $options['number']; ?>">
<?php for ( $i = 1; $i < 10; ++$i ) echo "<option value='$i' ".($options['number']==$i ? "selected='selected'" : '').">$i</option>"; ?>
			</select>
			<span class="submit"><input type="submit" name="pmw-number-submit" id="pmw-number-submit" value="<?php _e('Save'); ?>" /></span></p>
		</form>
	</div>
<?php
}

function widget_pmwold_register() {
	$options = get_option('widget_pmwold');
	$number = $options['number'];
	if ( $number < 1 ) $number = 1;
	if ( $number > 9 ) $number = 9;
	for ($i = 1; $i <= 9; $i++) {
		$name = array('Page Manage Widget %s', null, $i);
		
		if ($wp_version == '2.2') {
			register_sidebar_widget($name, $i <= $number ? 'widget_pmwold' : /* unregister */ '','', $i);
		} else if ($wp_version == '2.3') {
			register_sidebar_widget($name, $i <= $number ? 'widget_pmwold' : /* unregister */ '', $i);
		} else {
			register_sidebar_widget($name, $i <= $number ? 'widget_pmwold' : /* unregister */ '','', $i);				
		}		
		
		register_widget_control($name, $i <= $number ? 'widget_pmwold_control' : /* unregister */ '', 460, 260, $i);
	}
	add_action('sidebar_admin_setup', 'widget_pmwold_setup');
	add_action('sidebar_admin_page', 'widget_pmwold_page');
}
// Delay plugin execution to ensure Dynamic Sidebar has a chance to load first
widget_pmwold_register();
}

// Tell Dynamic Sidebar about our new widget and its control
add_action('plugins_loaded', 'widget_pmwold_init');
}
?>