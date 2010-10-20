<?php
/*
Plugin Name: Sell from Blog
Plugin URI: http://www.blogworkorange.net/sell-from-blog/
Description: Lets users sell ebooks, software etc. for premium SMS
Version: 0.80
Author: Paweł Pela
Author URI: http://www.paulpela.com
License: GPL2

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

//	objaśnienie akcji:
//	wp_ajax_nopriv_ - dostępna tylko dla niezalogowanych użytkowników
//	wp_ajax_		- dostępna tylko dla zalogowanych
//	aby obsługiwać zalogowanych i niezalogowanych należy dodać obie akcje

function get_sellfromblog_form($email, $kod) {
	$audyt_shortcode = '<style type="text/css">
	.sellfromblog td { padding: 16px 4px; border: none; }
	.sellfromblog tr { border: none; } 
	.sellfromblog input { width: 240px; padding: 4px;}
	.sellfromblog input[type="submit"] { width: 240px; padding: 4px; background: #DE7008; color: #fff; border: none; -webkit-border-radius: 4px; -moz-border-radius: 4px; border-radius: 4px; cursor: pointer;}
	.sellfromblog_error { width: 90%; margin: 6px auto; border: 1px solid #a00000; padding: 6px;}
	</style>';
	$audyt_shortcode .= '<div id="sellfromblogdiv">';
	$audyt_shortcode .= '<table class="sellfromblog">';
	$audyt_shortcode .= '<tr><td>' . __("Your email") . ':</td><td><input type="text" id="sellfromblog_email" value="' . $email . '" /> *</td></tr>';
	$audyt_shortcode .= '<tr><td>' . __("Code") . ':</td><td><input type="text" id="sellfromblog_kod" value="' . $kod . '" /> *</td></tr>';
	$audyt_shortcode .= '<tr><td></td><td>* - ' . __("required") . '</td></tr>';
	$audyt_shortcode .= '<tr><td></td><td><input type="submit" value="' . __("Send") . '" onclick="sellfromblogForm(wpajax);" /></td></tr>';
	$audyt_shortcode .= '</table>';
	$audyt_shortcode .= '</div>';
	
	return $audyt_shortcode;
}

add_action( 'wp_ajax_nopriv_sellfromblog', 'sellfromblog_form' );
add_action( 'wp_ajax_sellfromblog', 'sellfromblog_form' );
add_action( 'init', 'sellfromblog_init' );

// dodajemy shordcode (należy go wpisać w treści wpisu w formie [ajax-shortcode]
add_shortcode('sell-from-blog', 'sellfromblog_shortcode');


// ta funkcja będzie wywoływana pry pomocy Ajax
function sellfromblog_form() {

	global $wpdb;
	
	$kod = $_GET['kod'];
	$email = $_GET['email'];
	
	$confirmation_msg = get_option("sellfromblog_confirmation_msg");
	
	$result = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "sellfromblog_codes WHERE active = 1 AND Code = %s", $kod));
	
	if($wpdb->num_rows == 1 && $kod && $email) {
		$wpdb->update($wpdb->prefix . "sellfromblog_codes", array("active" => "0"), array("id" => $result->id));
		
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
		
	} else if($wpdb->num_rows != 1 && $kod && $email){
		echo '<div class="sellfromblog_error">';
		echo "<p><strong>" . __("Error.") . "</strong> " . __("The entered code is incorrect.") . "</p>";
		echo "</div>";
		echo get_sellfromblog_form($email, $kod);
	} else {
		echo '<div class="sellfromblog_error">';
		echo "<p><strong>" . __("Error.") . "</strong> " . __("The form has been filled incorrectly.") . "</p>";
		echo "</div>";
		echo get_sellfromblog_form($email, $kod);
	}
	
	exit; // Bardzo ważne!
}

function sellfromblog_init() {
	// dołączamy bibliotekę Prototype
	//wp_register_script('prototype', "http://ajax.googleapis.com/ajax/libs/prototype/1.6.1.0/prototype.js");
	wp_enqueue_script('prototype');

	wp_enqueue_script('sellfromblog-script', plugin_dir_url( __FILE__ ) . 'sell-from-blog.js', array('prototype'));
	wp_localize_script('sellfromblog-script', 'wpajax', array('ajaxurl' => admin_url('admin-ajax.php'), 'plugindir' => plugin_dir_url( __FILE__ )));
}


// funkcja obsługująca shortcode
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
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}

	$hidden_field_name = 'sellfromblog_submit_hidden';
    $data_field1_name = 'sellfromblog_codes';
    $data_field2_name = 'sellfromblog_file';
    $data_field3_name = 'sellfromblog_confirmation_msg';
    $data_field4_name = 'sellfromblog_email_body';
    $data_field5_name = 'sellfromblog_email_subject';
  

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

?>
<div class="updated"><p><strong><?php _e("Changes have been saved."); ?></strong></p></div>
<?php

    }

	$opt2_val = get_option($data_field2_name);
	$opt3_val = get_option($data_field3_name);
	$opt4_val = get_option($data_field4_name);
	$opt5_val = get_option($data_field5_name);
		
	$all_codes = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "sellfromblog_codes WHERE active = 1", ARRAY_A);
	$number_of_codes = $wpdb->num_rows;
	
	if($number_of_codes > 0) {
		foreach($all_codes as $code) {
			$codes_arr[] = $code['Code'];
		}
		$active_codes = join(",", $codes_arr);
	} else {
		$active_codes = "";
	}
?>

<div class="wrap">
<h2>Sell from Blog</h2>

<form name="form1" method="post" action="">
<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">

<p><?php echo sprintf(__("Currently there are %d active codes."), $number_of_codes); ?></p>

<p><?php _e("Active (unused) codes"); ?>: <code><?php echo $active_codes; ?></code></p>

<h3><?php _e("Add new codes"); ?>:</h3>
<p><?php _e("You can add any number of new codes by separating them with commas like this:"); ?> <code><?php _e("code1,code2,code3,code4"); ?></code>. <?php _e("Remember not to put spaces before or after the commas."); ?></p>
<p>
	<textarea name="<?php echo $data_field1_name; ?>" rows="6" cols="75"><?php echo $opt1_val; ?></textarea>
</p>

<h3><?php _e("File to be sent"); ?>:</h3>
<p><?php _e("Enter the path relative to the base directory of your WordPress installation."); ?></p>
<p>
	<code><?php echo get_option("sellfromblog_basepath"); ?></code> <input style="width: 300px;" class="regular-text code" name="<?php echo $data_field2_name; ?>" value="<?php echo $opt2_val; ?>" />
</p>

<h3><?php _e("Confirmation message"); ?>:</h3>
<p><?php _e("This message will be displayed on the blog after the form has been correctly sent."); ?></p>
<p>
	<textarea name="<?php echo $data_field3_name; ?>" rows="6" cols="75"><?php echo $opt3_val; ?></textarea>
</p>

<h3><?php _e("Email subject"); ?>:</h3>
<p><?php _e("Subject of the email that will be sent to the buyer."); ?></p>
<p>
	<input style="width: 400px;" class="regular-text" name="<?php echo $data_field5_name; ?>" value="<?php echo $opt5_val; ?>" />
</p>

<h3><?php _e("Email body"); ?>:</h3>
<p><?php _e("Body of the message that will be sent to the buyer. <strong>Plain text only!</strong>"); ?></p>
<p>
	<textarea name="<?php echo $data_field4_name; ?>" rows="6" cols="75"><?php echo $opt4_val; ?></textarea>
</p>

<p class="submit">
<input type="submit" name="Submit" class="button-primary" value="<?php _e("Save"); ?>" />
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
	  UNIQUE KEY id (id)
	);";
	
	if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
		$wpdb->query($sql);
	}
}

register_activation_hook(__FILE__, "sellfromblog_activation");

?>