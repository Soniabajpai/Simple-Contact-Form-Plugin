<?php 
/*
Plugin Name: Contact Plug
Plugin URI: https://akismet.com/
Description: Simple Contact Form
Version: 3.3
Author: Sonia Bajpai
License: GPLv2 or later
Text Domain: akismet
*/

register_activation_hook( __FILE__, 'jal_install' );
//register_activation_hook( __FILE__, 'jal_install_data' );
   
function myplugin_register_settings() {
add_option( 'myplugin_option_name', 'Email');
register_setting( 'myplugin_options_group', 'myplugin_option_name', 'myplugin_callback' );
}
add_action( 'admin_init', 'myplugin_register_settings' );

function myplugin_register_options_page() {
  add_options_page('Contact Plug Settings', 'Contact Plug', 'manage_options', 'myplugin', 'myplugin_options_page');
}
add_action('admin_menu', 'myplugin_register_options_page');

function myplugin_options_page()
{
?>
  <div>
  <?php screen_icon(); ?>
  <h2>Contact Plug Settings</h2>
  <form method="post" action="options.php">
  <?php settings_fields( 'myplugin_options_group' ); ?>
  <h3>Add your email id where users can contact you.</h3>
  <table>
  <tr valign="top">
  <th scope="row"><label for="myplugin_option_name">Email</label></th>
  <td><input type="email" id="myplugin_option_name" name="myplugin_option_name" value="<?php echo get_option('myplugin_option_name'); ?>" /></td>
  </tr>
  </table>
  <?php  submit_button(); ?>
  </form>
  </div>
<?php
} 

global $jal_db_version;
$jal_db_version = '1.0';

function jal_install() {
	global $wpdb;
	global $jal_db_version;

	$table_name = $wpdb->prefix . 'contact_plug';
	
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		user_name tinytext NOT NULL,
		user_email tinytext NOT NULL,
		user_phone varchar(55) DEFAULT '' NOT NULL,
		user_message varchar(55) DEFAULT '' NOT NULL,
		PRIMARY KEY  (id)
	) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );

	add_option( 'jal_db_version', $jal_db_version );
}

add_action('init', function() {
  add_shortcode('contact_plug', 'print_user_form');
});

function print_user_form() {
  echo '<form method="POST">';
  wp_nonce_field('user_info', 'user_info_nonce', true, true);
  ?>

 <table>
 	<tr>
 		<td>Name : </td>
 		<td><input type="text" name="username"></td>
 	</tr>
 	<tr>
 		<td>Email : </td>
 		<td><input type="email" name="useremail"></td>
 	</tr>
 	<tr>
 		<td>Phone : </td>
 		<td><input type="text" name="userphone"></td>
 	</tr>
 	<tr>
 		<td>Message : </td>
 		<td><textarea name="usermessage"></textarea></td>
 	</tr>
 	<tr><td></td><td><input type="submit" name="submit" value="Contact Us"></td></tr>
 </table>
    

<?php
  
  echo '</form>';
}

add_action('template_redirect', function() {
   if ( ( is_single() || is_page() ) &&
        isset($_POST[user_info_nonce]) &&
        wp_verify_nonce($_POST[user_info_nonce], 'user_info')
    ) {
      // you should do the validation before save data in db.
      // I will not write the validation function, is out of scope of this answer

        $data = array(
          'user_name' => $_POST['username'],
          'user_email' => $_POST['useremail'],
          'user_phone' => $_POST['userphone'],
          'user_message' => $_POST['usermessage']
        );
        global $wpdb;
        // if you have followed my suggestion to name your table using wordpress prefix
        $table_name = $wpdb->prefix . 'contact_plug';
        // next line will insert the data
        $wpdb->insert($table_name, $data, '%s'); 
        // if you want to retrieve the ID value for the just inserted row use
        $rowid = $wpdb->insert_id;


        $contact_email = get_option('myplugin_option_name');
        $to = $contact_email;
        $subject = 'Contact Form Details';
        $message = $_POST['username'] . " with email : " . $_POST['useremail'] . " and phone number : " . $_POST['userphone'] . "has sent you a message : " . $_POST['usermessage'] ;
        wp_mail($to, $subject, $message );
        // after we insert we have to redirect user
        // I sugest you to cretae another page and title it "Thank You"
        // if you do so:
        $redirect_page = get_page_by_title('Thank You') ? : get_queried_object();
        // previous line if page titled 'Thank You' is not found set the current page
        // as the redirection page. Next line get the url of redirect page:
        $redirect_url = get_permalink( $redirect_page );
        // now redirect
        wp_safe_redirect( $redirect_url );
        // and stop php
        exit();
      
   }
});




?>

