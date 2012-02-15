<?php
/*
Plugin Name: Sell from Blog
Plugin URI: http://www.blogworkorange.net/sell-from-blog/
Description: Lets users sell ebooks, software etc. for premium SMS
Version: 0.90
Author: Paweł Pela
Author URI: http://www.paulpela.com
License: GPL2
Text Domain: sell-from-blog

	Copyright 2010  Paweł Pela  (email : paulpela@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/* TODO
- mobilepay.pl remote validation integration
- link to "add new codes" in the dashboard widget
- customize number of last transactions displayed in the dashboard
*/


function get_sellfromblog_form($email, $kod, $agree = "on") {
	global $wpdb;
	$wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "sellfromblog_codes WHERE active = 1");
	$number_of_codes = $wpdb->num_rows;
	if($number_of_codes > 0) {
		$sellfromblog_shortcode = '<style type="text/css">
		.sellfromblog td { padding: 16px 4px; border: none; }
		.sellfromblog tr { border: none; } 
		.sellfromblog input { width: 240px; padding: 4px;}
		.sellfromblog input[type="submit"] { width: 240px; padding: 4px; background: #DE7008; color: #fff; border: none; -webkit-border-radius: 4px; -moz-border-radius: 4px; border-radius: 4px; cursor: pointer;}
		.sellfromblog input[type="checkbox"] { padding: 4px; width: 20px;}
		.sellfromblog_error { width: 90%; margin: 6px auto; border: 1px solid #a00000; padding: 6px;}
		</style>';
		$sellfromblog_shortcode .= '<div id="sellfromblogdiv">';
		$sellfromblog_shortcode .= '<table class="sellfromblog">';
		$sellfromblog_shortcode .= '<tr><td>' . __("Your email", 'sell-from-blog') . ':</td><td><input type="text" id="sellfromblog_email" value="' . $email . '" /> *</td></tr>';
		$sellfromblog_shortcode .= '<tr><td>' . __("Code", 'sell-from-blog') . ':</td><td><input type="text" id="sellfromblog_kod" value="' . $kod . '" /> *</td></tr>';
		
		if($agree == "on" && get_option("sellfromblog_agree_ask") == "on") {
			$sellfromblog_shortcode .= '<tr><td colspan="2"><input type="checkbox" checked="checked" id="sellfromblog_agree" /> ' . __("I want to receive more info related to this website to my email address.", "sell-from-blog") . '</td></tr>';
		} else if (get_option("sellfromblog_agree_ask") == "on" && $agree != "on") {
			$sellfromblog_shortcode .= '<tr><td colspan="2"><input type="checkbox" id="sellfromblog_agree" /> ' . __("I want to receive more info related to this website to my email address.", "sell-from-blog") . '</td></tr>';
		}
		
		$sellfromblog_shortcode .= '<tr><td></td><td>* - ' . __("required", 'sell-from-blog') . '</td></tr>';
		$sellfromblog_shortcode .= '<tr><td></td><td><input type="submit" value="' . __("Send it to me", 'sell-from-blog') . '" onclick="sellfromblogForm(wpajax);" /></td></tr>';
		$sellfromblog_shortcode .= '</table>';
		$sellfromblog_shortcode .= '</div>';
	} else {
		$sellfromblog_shortcode .= '<p><strong style="color: #ff0000;">' . __("Unfortunately, the sales form has been temporarily disabled due to a shortage of codes in the database. The admin should replenish it shortly and the form will be available again.", "sell-from-blog"). '</strong></p>';
	}
	
	return $sellfromblog_shortcode;
}

function get_sellfromblog_adminmessage($email, $code, $agree) {
	global $wpdb;
	$wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "sellfromblog_codes WHERE active = 1");
	$number_of_codes = $wpdb->num_rows;
	
	$message = __("New sale has been registered on", "sell-from-blog") . " " . get_bloginfo("name") . "\n\n";
	$message .= __("Date:", "sell-from-blog") . " " . date('r') . "\n";
	$message .= __("Email:", "sell-from-blog") . " " . $email . "\n";
	$message .= __("Code:", "sell-from-blog") . " " . $code . "\n";
	if($agree == "on" && get_option("sellfromblog_agree_ask") == "on") {
		$message .= __("Marketing agreement:", "sell-from-blog") . " " . __("yes", "sell-from-blog") . "\n";
	} else if (get_option("sellfromblog_agree_ask") == "on") {
		$message .= __("Marketing agreement:", "sell-from-blog") . " " . __("no", "sell-from-blog") . "\n";
	}
	$host = $_SERVER['REMOTE_HOST'] ? $_SERVER['REMOTE_HOST'] : $_SERVER['REMOTE_ADDR'];
	$message .= __("IP:", "sell-from-blog") . " " . $_SERVER['REMOTE_ADDR'] . " (" . __("resolves as", "sell-from-blog") . ": " . $host . ")\n";
	$message .= __("Codes left:", "sell-from-blog") . " " . $number_of_codes . "\n";
	
	return $message;
}

add_action( 'wp_ajax_nopriv_sellfromblog', 'sellfromblog_form' );
add_action( 'wp_ajax_sellfromblog', 'sellfromblog_form' );
add_action( 'init', 'sellfromblog_init' );

add_shortcode('sell-from-blog', 'sellfromblog_shortcode');


function sellfromblog_form() {

	global $wpdb;
	
	$kod = $_GET['kod'];
	$email = $_GET['email'];
	$agree = $_GET['agree'];
	
	$confirmation_msg = get_option("sellfromblog_confirmation_msg");
	
	$result = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "sellfromblog_codes WHERE active = 1 AND Code = %s", $kod));
	
	if($wpdb->num_rows == 1 && $kod && $email) {
		$wpdb->update($wpdb->prefix . "sellfromblog_codes", array("active" => "0", "IP" => $_SERVER['REMOTE_ADDR'], "email" => mysql_real_escape_string($email), "transaction_date" => time(), "agree" => mysql_real_escape_string($agree)), array("id" => $result->id));
		
		$random_hash = md5(date('r', time())); 
		$admin_info = get_userdata(1);
		$headers = "From: " . $admin_info->user_email . "\nReply-To: " . $admin_info->user_email . "\n";
		$headers .= "MIME-Version: 1.0\n";
		$headers .= "Content-Type: multipart/mixed; boundary=\"mixed-boundary-sellfromblog\"\n";
		
		$filename = get_option("sellfromblog_basepath") . get_option("sellfromblog_file");
		$attachment_name = basename($filename);
		
		$attachment = chunk_split(base64_encode(file_get_contents($filename))); 
		
		echo $confirmation_msg;
		
		ob_start(); ?>
--mixed-boundary-sellfromblog
Content-Type: text/plain; charset=utf-8
Content-Disposition: inline
Content-Transfer-Encoding: quoted-printable

<?php echo get_option("sellfromblog_email_body"); ?>

--mixed-boundary-sellfromblog
Content-Type: <?php echo mime_content_type($filename); ?>; name="<?php echo $attachment_name; ?>"
Content-Disposition: attachment; filename="<?php echo $attachment_name; ?>"
Content-Transfer-Encoding: base64 

<?php echo $attachment; ?>

--mixed-boundary-sellfromblog--

		<?php
		
		$message = ob_get_clean();
		
		$subject = get_option("sellfromblog_email_subject");
		
		@mail($email, $subject, $message, $headers);
		
		if(get_option("sellfromblog_adminmessage") == "on") {
			$adminmessage = get_sellfromblog_adminmessage($email, $kod, $agree);
			$adminheaders = "From: Sell from Blog <" . $admin_info->user_email . ">\n";
			@mail($admin_info->user_email, "[Sell from Blog] " . __("New sale on", "sell-from-blog") . " " . get_bloginfo("name"), $adminmessage, $adminheaders);
		}
		
	} else if($wpdb->num_rows != 1 && $kod && $email){
		echo '<div class="sellfromblog_error">';
		echo "<p><strong>" . __("Error.", 'sell-from-blog') . "</strong> " . __("The entered code is incorrect.", 'sell-from-blog') . "</p>";
		echo "</div>";
		echo get_sellfromblog_form($email, $kod, $agree);
	} else {
		echo '<div class="sellfromblog_error">';
		echo "<p><strong>" . __("Error.", 'sell-from-blog') . "</strong> " . __("The form has been filled incorrectly.", 'sell-from-blog') . "</p>";
		echo "</div>";
		echo get_sellfromblog_form($email, $kod, $agree);
	}
	
	exit; // Bardzo ważne!
}

function sellfromblog_init() {
	// dołączamy bibliotekę Prototype
	//wp_register_script('prototype', "http://ajax.googleapis.com/ajax/libs/prototype/1.6.1.0/prototype.js");
	wp_enqueue_script('prototype');

	wp_enqueue_script('sellfromblog-script', plugin_dir_url( __FILE__ ) . 'sell-from-blog.js', array('prototype'));
	wp_localize_script('sellfromblog-script', 'wpajax', array('ajaxurl' => admin_url('admin-ajax.php'), 'plugindir' => plugin_dir_url( __FILE__ )));
	
	load_plugin_textdomain('sell-from-blog', false, 'sell-from-blog');
}


function sellfromblog_shortcode() {

	$sc = '<style type="text\css">.sellfromblog_wait { width: 32px; margin 50px auto; }</style>';
	$sc .= get_sellfromblog_form($email, $kod);
	return $sc;

}

add_action('admin_menu', 'sellfromblog_plugin_menu');

function sellfromblog_plugin_menu() {

  add_options_page('Sell from Blog', 'Sell from Blog', 'manage_options', 'sellfromblog-options', 'sellfromblog_plugin_options');

}

function get_sellfromblog_basepath($path) {
	$ds = DIRECTORY_SEPARATOR;
	
	$dir = dirname($path);
	
	$dir_arr = explode($ds, $dir, -3);
	
	$basepath = join($ds, $dir_arr);
	
	return $basepath;
}

function sellfromblog_plugin_options() {

	global $wpdb;
	
	update_option("sellfromblog_basepath", get_sellfromblog_basepath(__FILE__));
	
	if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have sufficient permissions to access this page.', 'sell-from-blog') );
	}

	$hidden_field_name = 'sellfromblog_submit_hidden';
    $data_field1_name = 'sellfromblog_codes';
    $data_field2_name = 'sellfromblog_file';
    $data_field3_name = 'sellfromblog_confirmation_msg';
    $data_field4_name = 'sellfromblog_email_body';
    $data_field5_name = 'sellfromblog_email_subject';
    $data_field6_name = 'sellfromblog_adminmessage';
    $data_field7_name = 'sellfromblog_agree_ask';
  

	if( isset($_POST[ $hidden_field_name ]) && $_POST[ $hidden_field_name ] == 'Y' ) {
		
		if($_POST[$data_field1_name]) {
			$new_codes = explode(",", $_POST[ $data_field1_name ]);
			
			foreach($new_codes as $new_code) {
				if($new_code) {
					$wpdb->insert($wpdb->prefix . "sellfromblog_codes", array("Code" => $new_code));
				}
			}
		}
		
		if($_POST[$data_field2_name]) {
			update_option($data_field2_name, $_POST[$data_field2_name]);
		}
		
		if($_POST[$data_field3_name]) {
			update_option($data_field3_name, $_POST[$data_field3_name]);
		}
		
		if($_POST[$data_field4_name]) {
			update_option($data_field4_name, $_POST[$data_field4_name]);
		}
		
		if($_POST[$data_field5_name]) {
			update_option($data_field5_name, $_POST[$data_field5_name]);
		}
		
		update_option($data_field6_name, $_POST[$data_field6_name]);
		update_option($data_field7_name, $_POST[$data_field7_name]);

?>
<div class="updated"><p><strong><?php _e("Changes have been saved.", 'sell-from-blog'); ?></strong></p></div>
<?php

    }

	$opt2_val = get_option($data_field2_name);
	$opt3_val = get_option($data_field3_name);
	$opt4_val = get_option($data_field4_name);
	$opt5_val = get_option($data_field5_name);
	$opt6_val = get_option($data_field6_name);
	$opt7_val = get_option($data_field7_name);
		
	$all_codes = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "sellfromblog_codes WHERE active = 1", ARRAY_A);
	$number_of_codes = $wpdb->num_rows;
	
	$length = 9;
	if($number_of_codes > 0) {
		foreach($all_codes as $code) {
			$codes_arr[] = $code['Code'];
			if(count($codes_arr) == $length) {
				$codes_arr2[] = join(",", $codes_arr);
				unset($codes_arr);
				$length = 12;
			}
		}
		$active_codes = join("<br/>", $codes_arr2);
	} else {
		$active_codes = "";
	}
?>

<div class="wrap">
<h2>Sell from Blog</h2>

<form name="form1" method="post" action="">
<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">

<p><?php echo sprintf(__("Currently there are %d active codes.", 'sell-from-blog'), $number_of_codes); ?></p>

<p><?php _e("Active (unused) codes", 'sell-from-blog'); ?>: <code><?php echo $active_codes; ?></code></p>

<h3><?php _e("Add new codes", 'sell-from-blog'); ?>:</h3>
<p><?php _e("You can add any number of new codes by separating them with commas like this:", 'sell-from-blog'); ?> <code><?php _e("code1,code2,code3,code4", 'sell-from-blog'); ?></code>. <?php _e("Remember not to put spaces before or after the commas.", 'sell-from-blog'); ?></p>
<p>
	<textarea name="<?php echo $data_field1_name; ?>" rows="6" cols="75"><?php echo $opt1_val; ?></textarea>
</p>

<h3><?php _e("File to be sent", 'sell-from-blog'); ?>:</h3>
<p><?php _e("Enter the path relative to the base directory of your WordPress installation.", 'sell-from-blog'); ?></p>
<p>
	<code><?php echo get_option("sellfromblog_basepath"); ?></code> <input style="width: 300px;" class="regular-text code" name="<?php echo $data_field2_name; ?>" value="<?php echo $opt2_val; ?>" />
</p>

<h3><?php _e("Confirmation message", 'sell-from-blog'); ?>:</h3>
<p><?php _e("This message will be displayed on the blog after the form has been correctly sent.", 'sell-from-blog'); ?></p>
<p>
	<textarea name="<?php echo $data_field3_name; ?>" rows="6" cols="75"><?php echo $opt3_val; ?></textarea>
</p>

<h3><?php _e("Email subject", 'sell-from-blog'); ?>:</h3>
<p><?php _e("Subject of the email that will be sent to the buyer.", 'sell-from-blog'); ?></p>
<p>
	<input style="width: 400px;" class="regular-text" name="<?php echo $data_field5_name; ?>" value="<?php echo $opt5_val; ?>" />
</p>

<h3><?php _e("Email body", 'sell-from-blog'); ?>:</h3>
<p><?php _e("Body of the message that will be sent to the buyer. <strong>Plain text only!</strong>", 'sell-from-blog'); ?></p>
<p>
	<textarea name="<?php echo $data_field4_name; ?>" rows="6" cols="75"><?php echo $opt4_val; ?></textarea>
</p>

<h3><?php _e("Notify Admin", 'sell-from-blog'); ?>:</h3>
<p><?php _e("Check if you want the admin to also receive a notification about each transaction.", 'sell-from-blog'); ?></p>
<p>
<?php 
	$opt6_val ? $checked = true : $checked = false;
?>
	<?php _e("Notify admin:", 'sell-from-blog'); ?> <input type="checkbox" name="<?php echo $data_field6_name; ?>" <?php if($checked) echo 'checked="checked"'; ?>></textarea>
</p>

<h3><?php _e("Ask for permission to send more info", 'sell-from-blog'); ?>:</h3>
<p><?php _e("Chcek if you want to ask users to let you send them info related to the purchase and the content of you blog.", 'sell-from-blog'); ?></p>
<p>
<?php 
	$opt7_val ? $checked = true : $checked = false;
?>
	<?php _e("Ask for permission:", 'sell-from-blog'); ?> <input type="checkbox" name="<?php echo $data_field7_name; ?>" <?php if($checked) echo 'checked="checked"'; ?>></textarea>
</p>

<p class="submit">
<input type="submit" name="Submit" class="button-primary" value="<?php _e("Save", 'sell-from-blog'); ?>" />
</p>

</form>
</div>

<?php

}

function sellfromblog_activation() {
	global $wpdb;
	
	$table_name = $wpdb->prefix . "sellfromblog_codes";
	$sql = "CREATE TABLE " . $table_name . " (
	  id mediumint(9) NOT NULL AUTO_INCREMENT,
	  Code varchar(32) DEFAULT '0' NOT NULL,
	  active tinyint(4) DEFAULT '1' NOT NULL,
	  IP varchar(128) NULL,
	  email varchar(128) NULL,
	  transaction_date int(64) NULL,
	  agree varchar(8) NULL,
	  UNIQUE KEY id (id)
	);";
	
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);


	//if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
	//	$wpdb->query($sql);
	//}
}

register_activation_hook(__FILE__, "sellfromblog_activation");
add_action("update_plugin_complete_actions", "sellfromblog_activation");

function sellfromblog_add_dashboard() {
	wp_add_dashboard_widget("sellfromblog", "Sell from Blog - " . __("Stats", "sell-from-blog"), "sellfromblog_dashboard", null);
}

add_action("wp_dashboard_setup", "sellfromblog_add_dashboard");

function sellfromblog_dashboard() {
	global $wpdb;
	
	$all_codes = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "sellfromblog_codes WHERE active = 1", ARRAY_A);
	$number_of_codes = $wpdb->num_rows;
	
	echo "<p>" . __("Number of active (unused codes)", "sell-from-blog") . ": $number_of_codes</p>";
	
	$last_transactions = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "sellfromblog_codes WHERE transaction_date IS NOT NULL ORDER BY transaction_date DESC LIMIT 0, 15", ARRAY_A);
	
	echo "<h4>" . __("Recent Sales", "sell-from-blog") . "</h4>";
	//var_dump($last_transactions);
	
	?>
	<style type="text/css">
		.sellfromblog_table { font-size: 9px; }
		.sellfromblog_table th { font-weight: bold; }
		.sellfromblog_table { border-collapse: collapse; }
		.sellfromblog_table td, .sellfromblog_table th { border: 1px solid #ddd; padding: 4px;}
	</style>
	<?php
	echo '<table class="sellfromblog_table">';
	echo "<tr>";
	echo "<th>". __("Date", "sell-from-blog") . "</td>";
	echo "<th>". __("Code", "sell-from-blog") . "</td>";
	echo "<th>". __("Email", "sell-from-blog") . "</td>";
	if(get_option("sellfromblog_agree_ask") == "on") {
		echo "<th>". __("Agree", "sell-from-blog") . "</td>";
	}
	echo "<th>". __("IP", "sell-from-blog") . "</td>";
	echo "</tr>";
	
	
	foreach($last_transactions as $transaction) {
		$agr = $transaction['agree'] ? __("yes", "sell-from-blog") : __("no", "sell-from-blog");
		
		echo "<tr>";
		echo "<td>". date_i18n('j M Y G:i:s', $transaction['transaction_date']) . "</td>";
		echo "<td>". $transaction['Code'] . "</td>";
		echo "<td>". preg_replace("/@/", "@<br />", $transaction['email']) . "</td>";
		if(get_option("sellfromblog_agree_ask") == "on") {
			echo "<td>" . $agr . "</td>";
		}
		echo "<td>". $transaction['IP'] . "</td>";
		echo "</tr>";
	}
	
	echo "</table>";
}

if(!function_exists('mime_content_type')) {

    function mime_content_type($filename) {

        $mime_types = array(

            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',

            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',

            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',

            // audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',

            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',

            // ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',

            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        );

        $ext = strtolower(array_pop(explode('.',$filename)));
        if (array_key_exists($ext, $mime_types)) {
            return $mime_types[$ext];
        }
        elseif (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME);
            $mimetype = finfo_file($finfo, $filename);
            finfo_close($finfo);
            return $mimetype;
        }
        else {
            return 'application/octet-stream';
        }
    }
}

?>