<?php
/**
 * Plugin Name: Custom Code
 * Plugin URI: http://www.click2check.net/wordpress-plugins/custom-code
 * Description: Add Custom script and CSS code to header and footer
 * Version: 1.1
 * Author: Bhagirath
 * Author URI: http://click2check.net
 * License: GPL2
 */



add_action( 'admin_init', 'register_costom_code_setting' );


function costom_code_link($links) { 
 // $settings_link = '<a href="options-general.php?page=costom-code.php">Settings</a>'; 
  //array_unshift($links, $settings_link); 
  $donate_link = '<a href="http://www.click2check.net">Donate</a>'; 
  array_unshift($links, $donate_link); 
  return $links; 
}
 
$plugin = plugin_basename(__FILE__); 
add_filter("plugin_action_links_$plugin", 'costom_code_link' );

function register_costom_code_setting()
{

}

add_action( 'init', 'custom_post_type_custom_code' );

function custom_post_type_custom_code() {
	$labels = array(
		'name'               => _x( 'Custom Codes', 'post type general name' ),
		'singular_name'      => _x( 'Custom Code', 'post type singular name' ),
		'add_new'            => _x( 'Add Custom Code', 'code' ),
		'add_new_item'       => __( 'Add New Custom Code' ),
		'edit_item'          => __( 'Edit Custom Code' ),
		'new_item'           => __( 'New Custom Code' ),
		'all_items'          => __( 'All Custom Codes' ),
		'view_item'          => __( 'View Custom Codes' ),
		'search_items'       => __( 'Search Custom Codes' ),
		'not_found'          => __( 'No Custom Codes Found' ),
		'not_found_in_trash' => __( 'No Custom Codes in the Trash' ), 
		'parent_item_colon'  => '',
		'menu_name'          => 'Custom Codes'
	);
	$args = array(
		'labels'        => $labels,
		'description'   => 'Holds our Custom Codes and Custom Code specific data',
		'public'        => true,
		'menu_position' => 5,
		'supports'      => array( 'title'),
        'rewrite' => array('slug' => ''),
	

	);

	add_action( 'add_meta_boxes', 'add_custom_code_metaboxes' );
	
	register_post_type( 'custom_code', $args );	
}
	
	function add_custom_code_metaboxes() {
		add_meta_box('custom_code_post', 'Custom Code', 'custom_code_post', 'custom_code', 'normal', 'default');
		
	}
	
	function custom_code_post() {
		global $post;
		// Noncename needed to verify where the data originated
		echo '<input type="hidden" name="custom_code_noncename" id="custom_code_noncename" value="' .
		wp_create_nonce( plugin_basename(__FILE__) ) . '" />';
		// Get the location data if its already been entered
		$custom_code = get_post_meta($post->ID, '_custom_code', true);
		
		$code_position = get_post_meta($post->ID, '_code_position', true);
		
		// Echo out the field
		echo '<br/><strong>Code Position</strong>';
		echo '<select name="_code_position">';
		echo '<option value="header"';  if($code_position == "header") {echo 'selected="selected"';} ; echo '>Header</option>';
		echo '<option value="footer"';  if($code_position == "footer") {echo 'selected="selected"';} ; echo '>Footer</option>';
		echo '<option value="before_content"';  if($code_position == "before_content") {echo 'selected="selected"';} ; echo '>Before Content</option>';
		echo '<option value="after_content"';  if($code_position == "after_content") {echo 'selected="selected"';} ; echo '>After Content</option>';
		
		echo '</select>';
		echo '<br/><br/><strong>Code: </strong>(Enter HTML,JavaScript and CSS code here.)<textarea name="_custom_code" class="widefat" rows="10">'. $custom_code  .'</textarea>';

		
	}
	
	
	
	function custome_code_save_meta($post_id, $post) {
		// verify this came from the our screen and with proper authorization,
		// because save_post can be triggered at other times
		if ( !wp_verify_nonce( $_POST['custom_code_noncename'], plugin_basename(__FILE__) )) {
			return $post->ID;
		}
		// Is the user allowed to edit the post or page?
		if ( !current_user_can( 'edit_post', $post->ID ))
			return $post->ID;
		// OK, we're authenticated: we need to find and save the data
		// We'll put it into an array to make it easier to loop though.
		$costom_code_meta['_custom_code'] = $_POST['_custom_code'];
		
		$costom_code_meta['_code_position'] = $_POST['_code_position'];
		// Add values of $costom_code_meta as custom fields
		foreach ($costom_code_meta as $key => $value) { // Cycle through the $events_meta array!
			if( $post->post_type == 'revision' ) return; // Don't store custom data twice
				$value = implode(',', (array)$value); // If $value is an array, make it a CSV (unlikely)
			if(get_post_meta($post->ID, $key, FALSE)) { // If the custom field already has a value
				update_post_meta($post->ID, $key, $value);
			} else { // If the custom field doesn't have a value
				add_post_meta($post->ID, $key, $value);
			}
			if(!$value) delete_post_meta($post->ID, $key); // Delete if blank
		}
	}
	add_action('save_post', 'custome_code_save_meta', 1, 2); // save the custom fields
	
	add_action('wp_head', 'add_custom_code_head');
	
	function add_custom_code_head(){
		add_custom_code('header');
	}
	
	add_action('wp_footer', 'add_custom_code_footer');
	
	add_filter( 'the_content', 'custom_code_content_filter', 20 );
	/**
	 * Add a icon to the beginning of every post page.
	 *
	 * @uses is_single()
	 */
	function custom_code_content_filter( $content ) {

		if ( is_single() )
		{
			$contenttemp = $content;
			$content= '';
			$content .= '<!-- Custom Code Start-->';
			$content .= PHP_EOL;
			$the_query = new WP_Query( array( 'numberposts' => -1,'post_type' => 'custom_code' , 'meta_key' => '_code_position', 'meta_value' => 'before_content' ) );
			// The Loop

			while ( $the_query->have_posts() ) : $the_query->the_post();
				$custom_code = get_post_meta($the_query->post->ID, '_custom_code', true);
				$content .= PHP_EOL;
				$content .= $custom_code;
				$content .= PHP_EOL;
				endwhile;
			$content .= '<!-- Custom Code Start-->';
			$content .= PHP_EOL;
			// Reset Post Data
			wp_reset_postdata();
			wp_reset_query(); 
			
				// Add image to the beginning of each page
				$content .= $contenttemp;
		

			$content .= PHP_EOL;
			$the_query = new WP_Query( array( 'numberposts' => -1,'post_type' => 'custom_code' , 'meta_key' => '_code_position', 'meta_value' => 'after_content' ) );
			// The Loop

			while ( $the_query->have_posts() ) : $the_query->the_post();
				$custom_code = get_post_meta($the_query->post->ID, '_custom_code', true);
				$content .= PHP_EOL;
				$content .= $custom_code;
				$content .= PHP_EOL;
				endwhile;
			$content .= '<!-- Custom Code Start-->';
			$content .= PHP_EOL;
			// Reset Post Data
			wp_reset_postdata();
			wp_reset_query(); 
			if($content == "")
			{
				$content = $contenttemp;
			}
			
		
			// Returns the content.
			return $content;
		}
	}
	function add_custom_code_footer(){
		add_custom_code('footer');
	}
	
	function add_custom_code($position)
	{
		echo '<!-- Custom Code Start-->';
		echo PHP_EOL;
		$the_query = new WP_Query( array( 'numberposts' => -1,'post_type' => 'custom_code' , 'meta_key' => '_code_position', 'meta_value' => $position ) );
		// The Loop

		while ( $the_query->have_posts() ) : $the_query->the_post();
			$custom_code = get_post_meta($the_query->post->ID, '_custom_code', true);
			echo PHP_EOL;
			echo $custom_code;
			echo PHP_EOL;
			endwhile;
		echo '<!-- Custom Code Start-->';
		echo PHP_EOL;
		// Reset Post Data
		wp_reset_postdata();
		wp_reset_query(); 
	}

	function remove_quick_edit( $actions ) {
		global $post;
		if( $post->post_type == 'custom_code' ) {
			unset($actions['inline hide-if-no-js']);
			unset( $actions['view'] );
		}
		return $actions;
	}

	if (is_admin()) {
		add_filter('post_row_actions','remove_quick_edit',10,2);
	}
	
	add_action('admin_init', 'remove_permalink');

	function remove_permalink() {

		if(isset($_GET['post'])) {

			$post_type = get_post_type($_GET['post']);

			if($post_type == 'custom_code') {
				echo '<style>#edit-slug-box{display:none;} #message a{ display:none;}</style>';
			}
		}
	}
	
/*	add_filter('post_updated_messages', 'costom_code_updated_messages');
	function costom_code_updated_messages( $messages ) {

		$messages['costom_code'] = array(
			0 => '', // Unused. Messages start at index 1.
			1 => sprintf( __('Costom Code updated. <a href="%s">View Costom Code</a>'), esc_url( get_permalink($post_ID) ) ),
			2 => __('Custom field updated.'),
			3 => __('Custom field deleted.'),
			4 => __('Costom Code updated.'),
			
			5 => isset($_GET['revision']) ? sprintf( __('Costom Code restored to revision from %s'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6 => sprintf( __('Costom Code published. <a href="%s">View Costom Code</a>'), esc_url( get_permalink($post_ID) ) ),
			7 => __('Costom Code saved.'),
			8 => sprintf( __('Costom Code submitted. <a target="_blank" href="%s">Preview Costom Code</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
			9 => sprintf( __('Costom Code scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview Costom Code</a>'),
			  // translators: Publish box date format, see http://php.net/date
			  date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
			10 => sprintf( __('Costom Code draft updated. <a target="_blank" href="%s">Preview Costom Code</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
		);

		return $messages;
	}
*/