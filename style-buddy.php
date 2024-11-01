<?php
/*
Plugin Name: Style Buddy
Plugin URI: https://wpovernight.com/
Description: Add custom css and javascript to a page via the WordPress administration panel. Code is only loaded on the page to which it was added.
Version: 1.1.1
Author: Jeremiah Prummer
Author URI: https://wpovernight.com/
License: GPL2
*/
/*  Copyright 2014 Jeremiah Prummer  (email : support@wpovernight.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
*/
class StyleBuddy {

	public static $plugin_basename;
	
	function __construct() {

		$this->options = get_option('stylebuddy');

		add_action( 'wp_head', array(&$this, 'print_css_by_page'), 0);
		add_action( 'wp_footer', array(&$this, 'print_js_by_page'), 0);
		add_action( 'load-post.php', array( &$this, 'stylebuddy_post_meta_boxes_setup' ) );
		add_action( 'load-post-new.php', array( &$this, 'stylebuddy_post_meta_boxes_setup' ) );
		//add_action( 'admin_init', array( &$this, 'delete_all_meta' ), 0 );
		add_action( 'init', array( $this, 'includes' ) );

		self::$plugin_basename = plugin_basename(__FILE__);
	}

	function includes() {
		include_once( 'includes/stylebuddy-settings.php' );
		$this->settings = new StyleBuddy_Settings();
	}

	function print_css_by_page() {
		//Query Posts
		$post_types = get_post_types();
		foreach ($post_types as $post_type) {
			if (($post_type != 'page') || ($post_type != 'attachment')) {
				$post_args = array(
					'post_type' => $post_type,
					'meta_key' => 'stylebuddy_page_css'
				);
				$posts = get_posts( $post_args );
						
				foreach ($posts as $post) {
					
					if (is_single($post->ID)) {
						$style = '';
						$css = get_post_meta($post->ID, 'stylebuddy_page_css', true);
						if (!empty($css) && empty($this->options['switch_css'])) {
							$style .= '<style type="text/css">';
							$style .= $css;
							$style .= '</style>';
						}
						print $style;
					}
				}
			}
		}

		//Query Pages
		$page_args = array(
			'post_type' => 'page',
			'meta_key' => 'stylebuddy_page_css'
		);
		$pages = get_posts( $page_args );

		foreach ($pages as $page) {
			
			if (is_page($page->ID)) {
				$style = '';
				$css = get_post_meta($page->ID, 'stylebuddy_page_css', true);
				if (!empty($css) && empty($this->options['switch_css'])) {
					$style .= '<style type="text/css">';
					$style .= $css;
					$style .= '</style>';
				}
				print $style;
			}
		}
	}
	
	function print_js_by_page() {
		//Query Posts
		$post_types = get_post_types();
		foreach ($post_types as $post_type) {
			if (($post_type != 'page') || ($post_type != 'attachment')) {
				$post_args = array(
					'post_type' => $post_type,
					'meta_key' => 'stylebuddy_page_js'
				);
				$posts = get_posts( $post_args );
						
				foreach ($posts as $post) {
					
					if (is_single($post->ID)) {
						$style = '';
						$js = get_post_meta($post->ID, 'stylebuddy_page_js', true);
						if (!empty($js) && empty($this->options['switch_js'])) {
							$style .= '<script type="text/javascript">';
							$style .= $js;
							$style .= '</script>';
						}
						print $style;
					}
				}
			}
		}

		//Query Pages
		$page_args = array(
			'post_type' => 'page',
			'meta_key' => 'stylebuddy_page_js'
		);
		$pages = get_posts( $page_args );

		foreach ($pages as $page) {
			
			if (is_page($page->ID)) {
				$style = '';
				$js = get_post_meta($page->ID, 'stylebuddy_page_js', true);
				if (!empty($js) && empty($this->options['switch_js'])) {
					$style .= '<script type="text/javascript">';
					$style .= $js;
					$style .= '</script>';
				}
				print $style;
			}
		}
	}


	/**
	 * Run the updater scripts from the Sidekick
	 * @return void
	 */
	public function load_updater() {
		// Check if sidekick is loaded
		if (class_exists('WPO_Updater')) {
			$this->updater = new WPO_Updater( $this->item_name, $this->file, $this->license_slug, $this->version, $this->author );
		}
	}

	function delete_all_meta(){
		if( isset( $_POST[ 'stylebuddy_removecss' ] ) ) {
			$post_args = array(
				'post_type' => $post_type,
				'meta_key' => 'stylebuddy_page_css'
			);
			$posts = get_posts( $post_args );
			foreach($posts as $post){
				$value = get_post_meta($post->ID,'stylebuddy_page_css','true');
				echo $value;
				update_post_meta($post-ID,'stylebuddy_page_css_saver',$value);
				delete_post_meta($post->ID,'stylebuddy_page_css');
			}
		}
		if( isset( $_POST[ 'stylebuddy_restorecss' ] ) ) {
			$post_args = array(
				'post_type' => $post_type,
				'meta_key' => 'stylebuddy_page_css_saver'
			);
			$posts = get_posts( $post_args );
			foreach($posts as $post){
				$value = get_post_meta($post->ID,'stylebuddy_page_css_saver','true');
				update_post_meta($post-ID,'stylebuddy_page_css',$value);
				delete_post_meta($post->ID,'stylebuddy_page_css_saver');
			}
		}
		if( isset( $_POST[ 'stylebuddy_removejs' ] ) ) {
			$post_args = array(
				'post_type' => $post_type,
				'meta_key' => 'stylebuddy_page_js'
			);
			$posts = get_posts( $post_args );
			foreach($posts as $post){
				$value = get_post_meta($post->ID,'stylebuddy_page_js','true');
				update_post_meta($post-ID,'stylebuddy_page_js_saver',$value);
				delete_post_meta($post->ID,'stylebuddy_page_js');
			}
		}
		if( isset( $_POST[ 'stylebuddy_restorejs' ] ) ) {
			$post_args = array(
				'post_type' => $post_type,
				'meta_key' => 'stylebuddy_page_js_saver'
			);
			$posts = get_posts( $post_args );
			foreach($posts as $post){
				$value = get_post_meta($post->ID,'stylebuddy_page_js_saver','true');
				update_post_meta($post-ID,'stylebuddy_page_js',$value);
				delete_post_meta($post->ID,'stylebuddy_page_js_saver');
			}
		}
	}

	/* Meta box setup function. */
	public function stylebuddy_post_meta_boxes_setup() {

		/* Add meta boxes on the 'add_meta_boxes' hook. */
		add_action('add_meta_boxes',  array( &$this, 'stylebuddy_add_post_meta_boxes' ) );
		/* Save post meta on the 'save_post' hook. */
		add_action( 'save_post', array( &$this, 'stylebuddy_save_post_class_meta'), 10, 2 );
		add_action( 'save_post', array( &$this, 'stylebuddy_save_post_class_meta2'), 10, 2 );
	}

	public function stylebuddy_add_post_meta_boxes() {
		
		$screens = get_post_types();

		foreach ( $screens as $screen ) {
			$codeform = isset($this->options['codeform_display']) ? $this->options['codeform_display'] : '3';
			
			if( ($codeform == 1) || ($codeform == 3) ) {
				add_meta_box(
					'stylebuddy-css',			// Unique ID
					esc_html__( 'CSS To Display', 'example' ),		// Title
					array( &$this,'stylebuddy_post_class_meta_box_1'),		// Callback function
					$screen,					// Admin page (or post type)
					'normal',					// Context
					'default'					// Priority
				);
			}

			if( ($codeform == 2) || ($codeform == 3) ) {
				add_meta_box(
					'stylebuddy-js',			// Unique ID
					esc_html__( 'Javascript To Display', 'example' ),		// Title
					array( &$this,'stylebuddy_post_class_meta_box_2'),		// Callback function
					$screen,					// Admin page (or post type)
					'normal',					// Context
					'default'					// Priority
				);
			}
		}
	}

	/* Display the post meta box 1. */
	public function stylebuddy_post_class_meta_box_1( $object, $box ) { ?>

		<?php wp_nonce_field( basename( __FILE__ ), 'stylebuddy_post_class_nonce_1' ); ?>

		<p>
			<label for="stylebuddy-css"><?php _e( "CSS to display on this page or post (Click Update or Publish to save)", 'example' ); ?></label>
			<br /><br />
			<textarea rows="1" cols="40" name="stylebuddy-css" id="stylebuddy-css" style="margin: 0; height: 10em; width: 98%;" class="style-buddy-editor"><?php echo esc_attr( get_post_meta( $object->ID, 'stylebuddy_page_css', true ) ); ?></textarea>
		</p>
	<?php }

	/* Display the post meta box 2. */
	public function stylebuddy_post_class_meta_box_2( $object, $box ) { ?>

		<?php wp_nonce_field( basename( __FILE__ ), 'stylebuddy_post_class_nonce_2' ); ?>

		<p>
			<label for="stylebuddy-js"><?php _e( "Javascript to display on this page or post (Click Update or Publish to save)", 'example' ); ?></label>
			<br /><br />
			<textarea rows="1" cols="40" name="stylebuddy-js" id="stylebuddy-js" style="margin: 0; height: 10em; width: 98%;" class="style-buddy-editor"><?php echo esc_attr( get_post_meta( $object->ID, 'stylebuddy_page_js', true ) ); ?></textarea>
		</p>
	<?php }

	public function stylebuddy_save_post_class_meta( $post_id, $post ) {

		/* Verify the nonce before proceeding. */
		if ( !isset( $_POST['stylebuddy_post_class_nonce_1'] ) || !wp_verify_nonce( $_POST['stylebuddy_post_class_nonce_1'], basename( __FILE__ ) ) )
			return $post_id;

		/* Get the post type object. */
		$post_type = get_post_type_object( $post->post_type );

		/* Check if the current user has permission to edit the post. */
		if ( !current_user_can( $post_type->cap->edit_post, $post_id ) )
			return $post_id;

		/* Get the posted data and sanitize it for use as an HTML class. */
		//$new_meta_value = ( isset( $_POST['stylebuddy-css'] ) ? sanitize_html_class( $_POST['stylebuddy-css'] ) : '' );
		$new_meta_value = ( isset( $_POST['stylebuddy-css'] ) ? $_POST['stylebuddy-css'] : '' );

		/* Get the meta key. */
		$meta_key = 'stylebuddy_page_css';

		/* Get the meta value of the custom field key. */
		$meta_value = get_post_meta( $post_id, $meta_key, true );

		/* If a new meta value was added and there was no previous value, add it. */
		if ( $new_meta_value && '' == $meta_value ) {
			add_post_meta( $post_id, $meta_key, $new_meta_value, true );
		}
		/* If the new meta value does not match the old value, update it. */
		elseif ( $new_meta_value && $new_meta_value != $meta_value ) {
			update_post_meta( $post_id, $meta_key, $new_meta_value );
		}
		/* If there is no new meta value but an old value exists, delete it. */
		elseif ( '' == $new_meta_value && $meta_value ) {
			delete_post_meta( $post_id, $meta_key, $meta_value );
		}	
	}

	public function stylebuddy_save_post_class_meta2( $post_id, $post ) {

		/* Verify the nonce before proceeding. */
		if ( !isset( $_POST['stylebuddy_post_class_nonce_2'] ) || !wp_verify_nonce( $_POST['stylebuddy_post_class_nonce_2'], basename( __FILE__ ) ) )
			return $post_id;

		/* Get the post type object. */
		$post_type = get_post_type_object( $post->post_type );

		/* Check if the current user has permission to edit the post. */
		if ( !current_user_can( $post_type->cap->edit_post, $post_id ) )
			return $post_id;

		/* Get the posted data and sanitize it for use as an HTML class. */
		//$new_meta_value = ( isset( $_POST['stylebuddy-css'] ) ? sanitize_html_class( $_POST['stylebuddy-css'] ) : '' );
		$new_meta_value = ( isset( $_POST['stylebuddy-js'] ) ? $_POST['stylebuddy-js'] : '' );

		/* Get the meta key. */
		$meta_key = 'stylebuddy_page_js';

		/* Get the meta value of the custom field key. */
		$meta_value = get_post_meta( $post_id, $meta_key, true );

		/* If a new meta value was added and there was no previous value, add it. */
		if ( $new_meta_value && '' == $meta_value ) {
			add_post_meta( $post_id, $meta_key, $new_meta_value, true );
		}
		/* If the new meta value does not match the old value, update it. */
		elseif ( $new_meta_value && $new_meta_value != $meta_value ) {
			update_post_meta( $post_id, $meta_key, $new_meta_value );
		}
		/* If there is no new meta value but an old value exists, delete it. */
		elseif ( '' == $new_meta_value && $meta_value ) {
			delete_post_meta( $post_id, $meta_key, $meta_value );
		}	
	}
}
$StyleBuddy = new StyleBuddy();