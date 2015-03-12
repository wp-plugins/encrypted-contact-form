<?php 
/*
	Plugin Name: Encrypted Contact Form
	Plugin URI: https://i.cx/?icx.screen=formCreator
	Version: 1.0.3
 	Author: EveryBit Inc.
 	Author URI:  https://everybit.com
	Description: Secure contact form for WordPress. Uses end-to-end encryption to send information from your contact form. Not even your hosting provider can view the content.
	Text Domain: encrypted-contact-form
	License: MIT
 */


add_action( 'admin_menu', 'conformconf_menu');
   
   
function conformconf_admin_styles() {
	wp_enqueue_style( 'conformconf', plugins_url('css/style.css', __FILE__) );
}


function conformconf_admin_scripts() {
	wp_enqueue_script( 'conformconf', plugins_url('js/main.js', __FILE__) );		
}
  
   
function conformconf_menu(){
	$menu_id = add_options_page( 
		'Encrypted Contact Form', 
		'Encrypted Contact Form', 
		'manage_options', 
		'conformconf', 
		'conformconf_settings' 
	);
	add_action( 'admin_print_styles-'  . $menu_id, 'conformconf_admin_styles' );
	add_action( 'admin_print_scripts-' . $menu_id, 'conformconf_admin_scripts'  );
}


function conformconf_settings(){
	
	global $wpdb;
	$options = get_option( 'conformconf_options' );
	$user_error = false;
	$found = false;		
	$cfc_pages_parsed = array();
	$replacements = array(
		'NOP' => '',
		'NOF' => '',
		'NRE' => '',
		'EOP' => '',
		'EOF'  => '',
		'ERE'  => '',
		'POP'  => '',
		'POF'  => '',
		'PRE'  => '',
		'MOP'  => '',
		'MOF'  => '',
		'MRE'  => '',
		'PGNM' => '',
		'OPTS' => '',
		'DISPLAY' => '',
		'ERRD1'   => 'display:none',
		'ERRD2'   => 'display:none',
	);
	
	$page_name = isset( $_POST['cfc_page_name'] ) ? stripslashes( $_POST['cfc_page_name'] ) : "";
	$existing_page = isset( $_POST['existing_page'] ) ? (int) $_POST['existing_page'] : false;	
	
	if ( $page_name or $existing_page ){
		$response = file_get_contents( "https://i.cx/api/users/api.php?type=getUser&username=" . stripslashes( $_POST['recipient_name'] ) );
		if ( $response ){
			$response_json = json_decode( $response );
			if ( isset($response_json->FAIL) ){
				$user_error = true;
			} else if ( $response_json->username ){
				$user_error = false;
			} else {
				$user_error = true;
			}
		} else {
			$user_error = true;
		}
		
		if ( $user_error ){
			$replacements['PGNM'] = $page_name;
			$replacements['ERRD1'] = '';
			echo '<br/><div class="error" style="margin-left:1px;color:red"><p>Username not found. Please enter a valid I.CX username. If you do not yet have an I.CX account, <a href="https://i.cx" target="_new">sign up for one now<a/>.</p></div>';		
		} else {
			$iframe_link = $_POST['iframe_url'];
			if ( $page_name ) {
				$pid =  $wpdb->get_var( $wpdb->prepare( "SELECT count(*) from $wpdb->posts 
										   WHERE post_type = 'page'
										   AND post_title = %s",
										   $page_name
									  ) );
				if ( $pid ) {
					echo '<br/><div class="error" style="margin-left:1px;color:red"><p>A page with that name already exists!</p></div>';		
				} else {
					$pid = wp_insert_post( 
						array(
							'post_title'   => $page_name,
							'post_content' => '<iframe src="' . $iframe_link . '" width="600" height="500"></iframe>',
							'post_author'  => 1,
							'post_type'    => 'page',
							'post_status'  => 'publish',
						)
					);
					if ( $pid ){
						update_post_meta( $pid, 'cfc_page', 1);
						$permalink = get_permalink( $pid );
						echo '<br/><div class="updated" style="margin-left:1px"><p>Page Created: <a href="' . $permalink . '">' . $page_name . '</a></p></div>';
					} else {
						echo '<br/><div class="error" style="margin-left:1px;color:red"><p>Unexpected error occured while creating page</p></div>';		
					}		
				}
			} else if ($existing_page) {
				wp_update_post( 
					array(
						'ID'   => $existing_page,
						'post_content' => '<iframe src="' . $iframe_link . '" width="600" height="500"></iframe>',
					)
				);
				$updated_post = get_post( $existing_page );
				$permalink = get_permalink( $existing_page );
				echo '<br/><div class="updated" style="margin-left:1px"><p>Page Updated: <a href="' . $permalink . '">' . $updated_post->post_title . '</a></p></div>';
			}
		}
		
		$options['name']    = $_POST['name'];
		$options['email']   = $_POST['email'];
		$options['phone']   = $_POST['phone'];
		$options['message'] = $_POST['message'];
		$options['recipient_name'] = stripslashes( $_POST['recipient_name'] );
		$options['display_name']   = stripslashes( $_POST['display_name'] );
		$options['iframe_url']     = $_POST['iframe_url'];
		update_option( 'conformconf_options', $options );
		
	}
		
	if ( $options['name'] == 'optional' ){
		$replacements['NOP'] = 'checked';
	} else if ( $options['name'] == 'required' ){
		$replacements['NRE'] = 'checked';
	} else if ( $options['name'] == 'off' ){
		$replacements['NOF'] = 'checked';	
	} else {
		$replacements['NRE'] = 'checked';
	}
	
	if ( $options['email'] == 'optional' ){
		$replacements['EOP'] = 'checked';
	} else if ( $options['email'] == 'required' ){
		$replacements['ERE'] = 'checked';
	} else if ( $options['email'] == 'off' ){
		$replacements['EOF'] = 'checked';	
	} else {
		$replacements['EOP'] = 'checked';
	}	
	
	if ( $options['phone'] == 'optional' ){
		$replacements['POP'] = 'checked';
	} else if ( $options['phone'] == 'required' ){
		$replacements['PRE'] = 'checked';
	} else if ( $options['phone'] == 'off' ){
		$replacements['POF'] = 'checked';	
	} else {
		$replacements['POF'] = 'checked';
	}	
	
	if ( $options['message'] == 'optional' ){
		$replacements['MOP'] = 'checked';
	} else if ( $options['message'] == 'required' ){
		$replacements['MRE'] = 'checked';
	} else if ( $options['message'] == 'off' ){
		$replacements['MOF'] = 'checked';	
	} else {
		$replacements['MRE'] = 'checked';
	}		
	
	$replacements['iframe_url'] = $options['iframe_url'];
	
	if ( $options['display_name'] ){
		$replacements['DISN'] = $options['display_name'];	
	} else {
		$replacements['DISN'] = 'Example';	
	}
	
	if ( $options['recipient_name'] ){
		$replacements['RECN'] = $options['recipient_name'];	
	} else {
		$replacements['RECN'] = 'example';	
	}
	
	$cfc_pages = $wpdb->get_results(
						$wpdb->prepare(
							"SELECT p.ID as pid, p.post_title as title 
							FROM $wpdb->posts p 
							LEFT JOIN $wpdb->postmeta pm ON pm.post_id = p.ID
							WHERE pm.meta_key = %s"
							, 'cfc_page'
						)
					);

	
	foreach( $cfc_pages as $cfc_page){
		$cfc_pages_parsed[ $cfc_page->pid ] = $cfc_page->title;
		$found = true;
	}
	
	if ( ! $found ){
		$replacements['DISPLAY'] = 'display:none';
	} else {
		$opts = '';
		foreach( $cfc_pages_parsed as $pid => $title ){
			$opts .= '<option value="' . $pid . '">' . $title . '</option>' . "\r\n";
		}
		$replacements['OPTS'] = $opts;
	}

	conformconf_show_settings( $replacements );
	
}


function conformconf_show_settings( $replacements ){
	$file_content = file_get_contents( dirname( __FILE__ ) . "/tpl/admin_settings.html" );
	foreach( $replacements as $tag => $repl ){
		$file_content = str_replace( "%$tag%", $repl, $file_content );
	}
	echo $file_content;
}