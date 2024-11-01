<?php
/**
 * Register Settings
 *
 * @package     Style Buddy
 * @subpackage  Admin/Settings
 * @copyright   Copyright (c) 2013, Jeremiah Prummer
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.1.0
*/

// Exit if accessed directly
//if ( !defined( 'ABSPATH' ) ) exit;

class StyleBuddy_Settings {

	public $options_page_hook;

	public function __construct() {
		add_action( 'admin_init', array( &$this, 'init_settings' ) ); // Registers settings
		add_action('admin_menu', array(&$this, 'stylebuddy_add_page'));
		add_filter( 'plugin_action_links_'.StyleBuddy::$plugin_basename, array( &$this, 'stylebuddy_add_settings_link' ) );
	}

	/**
	 * User settings.
	 */
	public function init_settings() {
		$option = 'stylebuddy';
	
		// Create option in wp_options.
		if ( false == get_option( $option ) ) {
			add_option( $option );
		}
	
		// Section.
		add_settings_section(
			'plugin_settings',
			__( 'Style Buddy Settings', 'stylebuddy' ),
			array( &$this, 'section_options_callback' ),
			$option
		);

		add_settings_field(
			'codeform_display',
			__( 'Display Script Form For:', 'stylebuddy' ),
			array( &$this, 'radio_element_callback' ),
			$option,
			'plugin_settings',
			array(
				'menu'			=> $option,
				'id'			=> 'codeform_display',
				'options' 		=> array(
					'1'			=> __( 'CSS Only.' , 'stylebuddy' ),
					'2'			=> __( 'Javascript Only.' , 'stylebuddy' ),
					'3'			=> __( 'Both CSS and Javascript' , 'stylebuddy' ),
				),
			)
		);

		add_settings_field(
			'switch_ace',
			__( 'Turn On Syntax Highlighting' ),
			array( &$this, 'checkbox_element_callback' ),
			$option,
			'plugin_settings',
			array(
				'menu'			=> $option,
				'id'			=> 'switch_ace',
				'disabled' 		=> true,
			)
		);

		add_settings_field(
			'ace_theme',
			__( 'Pick an Editor Theme', 'stylebuddy' ),
			array( &$this, 'select_element_callback' ),
			$option,
			'plugin_settings',
			array(
				'menu'			=> $option,
				'id'			=> 'ace_theme',
				'options'		=> array(
						'clouds'		=> 'Clouds',
						'idle_fingers'	=> 'idle Fingers',
						'tomorrow'		=> 'tomorrow',
						'textmate'		=> 'TextMate',
						'monokai'		=> 'Monokai',
						'xcode'			=> 'XCode'
				),
				'disabled' => true,
			)
		);

		add_settings_field(
			'hide_gutter',
			__( "Hide Gutter (line numbers on the code editors)", 'stylebuddy' ),
			array( &$this, 'checkbox_element_callback' ),
			$option,
			'plugin_settings',
			array(
				'menu'			=> $option,
				'id'			=> 'hide_gutter',
				'disabled'		=> true,
			)
		);

		add_settings_field(
			'switch_css',
			__( "Turn Off CSS", 'stylebuddy' ),
			array( &$this, 'checkbox_element_callback' ),
			$option,
			'plugin_settings',
			array(
				'menu'			=> $option,
				'id'			=> 'switch_css',
			)
		);

		add_settings_field(
			'switch_javascript',
			__( 'Turn Off Javascript', 'stylebuddy' ),
			array( &$this, 'checkbox_element_callback' ),
			$option,
			'plugin_settings',
			array(
				'menu'			=> $option,
				'id'			=> 'switch_javascript',
			)
		);
		
		// Register settings.
		register_setting( $option, $option, array( &$this, 'stylebuddy_options_validate' ) );

		// Register defaults if settings empty (might not work in case there's only checkboxes and they're all disabled)
		$option_values = get_option($option);
		if ( empty( $option_values ) )
			$this->default_settings();

	}

	/**
	 * Default settings.
	 */
	public function default_settings() {

		$default = array(

			'switch_css'		=> '',
			'switch_javascript'	=> '',
			'codeform_display'	=> '3',
			'ace_switch'		=> '1',
			'ace_theme'			=> 'clouds',
			'hide_gutter'		=> ''
		);

		update_option( 'stylebuddy', $default );
	}

	/**
	 * Add menu page
	 */
	public function stylebuddy_add_page() {

		if (class_exists('WPOvernight_Core')) {
			$this->options_page_hook = add_submenu_page(
				'wpo-core-menu',
				__( 'Style Buddy', 'stylebuddy' ),
				__( 'Style Buddy', 'stylebuddy' ),
				'manage_options',
				'stylebuddy_options_page',
				array( $this, 'style_buddy_page' )
			);
		}
		else {
			$this->options_page_hook = add_submenu_page(
				'options-general.php',
				__( 'Style Buddy', 'stylebuddy' ),
				__( 'Style Buddy', 'stylebuddy' ),
				'manage_options',
				'stylebuddy_options_page',
				array( $this, 'style_buddy_page' )
			);
		}
	}

	/**
	 * Add settings link to plugins page
	 */
	public function stylebuddy_add_settings_link( $links ) {
		if (class_exists('WPOvernight_Core')) {
			$settings_link = '<a href="admin.php?page=stylebuddy_options_page">'. __( 'Settings', 'woocommerce' ) . '</a>';
		} else {
			$settings_link = '<a href="options-general.php?page=stylebuddy_options_page">'. __( 'Settings', 'woocommerce' ) . '</a>';	
		}
		array_push( $links, $settings_link );
		return $links;
	}

	public function style_buddy_page() {
		?>
		<div class="wrap">
			<div class="icon32" id="icon-options-general"><br /></div>
			<h2><?php _e('Style Buddy Settings', 'stylebuddy') ?></h2>
			<?php
			//use for debugging
			//$option = get_option( 'stylebuddy' );
			//print_r($option);
			?>
			<div style="width: 65%;float:left">
				<form method="post" action="options.php">
				<?php
				settings_fields('stylebuddy');
				do_settings_sections('stylebuddy');

				submit_button('Save Changes');
				?>
				<!--
				<input type="submit" class="button-secondary stylebuddy-button" name="stylebuddy_removecss" value="Remove CSS"/>
				<input type="submit" class="button-secondary stylebuddy-button" name="stylebuddy_removejs" value="Remove Javascript"/>
				<input type="submit" class="button-secondary stylebuddy-button" name="stylebuddy_restorecss" value="Restore CSS"/>
				<input type="submit" class="button-secondary stylebuddy-button" name="stylebuddy_restorejs" value="Restore Javascript"/>
				-->
				</form>
				
			</div>
			<div style="width: 35%;float:left;">
				<div style="width: auto;">
					<div style="margin-bottom:20px;border-radius:2px;-moz-border-radius:2px;border:1px solid #999;padding:10px;background-color:#fff;text-align:center;max-width:250px">
						<h2>Do You Love This Plugin?</h2>
						<p>Rate It on WordPress.org!</p>
						<a href="http://wordpress.org/support/view/plugin-reviews/style-buddy" class="button-primary">Click to Rate</a>
					</div>
					<div style="border-radius:2px;-moz-border-radius:2px;border:1px solid #999;padding:10px;background-color:#fff;text-align:center;max-width:250px">
						<h2>Go Pro!</h2>
						<p>Get syntax highlighting for both CSS and Javascript fields + priority support</p>
						<a href="https://wpovernight.com/downloads/style-buddy-pro?utm_source=wordpress&utm_medium=stylebuddyfree&utm_campaign=getitnow" class="button-primary" style="text-align:center;margin:0px auto !important">Get it for only $19</a>
					</div>
				</div>
			</div>
		</div>
		<script type="text/javascript">
			jQuery('.hidden-input').click(function() {
				jQuery(this).closest('.hidden-input').prev('.pro-feature').show('slow');
				jQuery(this).closest('.hidden-input').hide();
			});
			jQuery( document ).ready(function( $ ) {
			    $("input[id^=wcbulkorderform]:radio:lt(13)").attr('disabled',true);
			});
		</script>
		<?php
	}

	/**
	 * Validate/sanitize options input
	 */
	public function stylebuddy_options_validate( $input ) {
		// Create our array for storing the validated options.
		$output = array();

		// Loop through each of the incoming options.
		foreach ( $input as $key => $value ) {

			// Check to see if the current option has a value. If so, process it.
			if ( isset( $input[$key] ) ) {
				// Strip all HTML and PHP tags and properly handle quoted strings.
				if ( is_array( $input[$key] ) && $key == 'menu_slugs' ) {
					$new_subkey = 1; //renumber array
					foreach ( $input[$key] as $sub_key => $sub_value ) {
						if (!empty($sub_value)) {
							$output[$key][$new_subkey] = strip_tags( stripslashes( $input[$key][$sub_key] ) );
							$new_subkey += 1;
						}
					}
				} else {
					$output[$key] = strip_tags( stripslashes( $input[$key] ) );
				}
			}
		}

		// Return the array processing any additional functions filtered by this action.
		return apply_filters( 'stylebuddy_validate_input', $output, $input );
	}

	/**
	 * Section null callback.
	 *
	 * @return void.
	 */
	public function section_options_callback() {
	
	}

	/**
	 * Displays a multicheckbox a settings field
	 *
	 * @param array   $args settings field args
	 */
	public function radio_element_callback( $args ) {
		$menu = $args['menu'];
		$id = $args['id'];
	
		$options = get_option( $menu );
	
		if ( isset( $options[$id] ) ) {
			$current = $options[$id];
		} else {
			$current = isset( $args['default'] ) ? $args['default'] : '';
		}

		$html = '';
		foreach ( $args['options'] as $key => $label ) {
			$html .= sprintf( '<input type="radio" class="radio" id="%1$s[%2$s][%3$s]" name="%1$s[%2$s]" value="%3$s"%4$s />', $menu, $id, $key, checked( $current, $key, false ) );
			$html .= sprintf( '<label for="%1$s[%2$s][%3$s]"> %4$s</label><br>', $menu, $id, $key, $label);
		}
		
		// Displays option description.
		if ( isset( $args['description'] ) ) {
			$html .= sprintf( '<p class="description">%s</p>', $args['description'] );
		}

		echo $html;
	}

	/**
	 * Checkbox field callback.
	 *
	 * @param  array $args Field arguments.
	 *
	 * @return string	  Checkbox field.
	 */
	public function checkbox_element_callback( $args ) {
		$menu = $args['menu'];
		$id = $args['id'];
	
		$options = get_option( $menu );
	
		if ( isset( $options[$id] ) ) {
			$current = $options[$id];
		} else {
			$current = isset( $args['default'] ) ? $args['default'] : '';
		}
	
		$disabled = (isset( $args['disabled'] )) ? ' disabled' : '';
		$html = sprintf( '<input type="checkbox" id="%1$s" name="%2$s[%1$s]" value="1"%3$s %4$s/>', $id, $menu, checked( 1, $current, false ), $disabled );
	
		// Displays option description.
		if ( isset( $args['description'] ) ) {
			$html .= sprintf( '<p class="description">%s</p>', $args['description'] );
		}
	
		if (isset( $args['disabled'] )) {
			$html .= ' <span style="display:none;" class="pro-feature"><i>'. __('This feature only available with', 'stylebuddy') .' <a href="https://wpovernight.com/downloads/style-buddy-pro?utm_source=wordpress&utm_medium=stylebuddyfree&utm_campaign=stylebuddysyntax">Style Buddy Pro</a></i></span>';
			$html .= '<div style="position:absolute; left:0; right:0; top:0; bottom:0; background-color:white; -moz-opacity: 0; opacity:0;filter: alpha(opacity=0);" class="hidden-input"></div>';
			$html = '<div style="display:inline-block; position:relative;">'.$html.'</div>';
		}
			
		echo $html;
	}

	/**
	 * Displays a selectbox for a settings field
	 *
	 * @param array   $args settings field args
	 */
	public function select_element_callback( $args ) {
		$menu = $args['menu'];
		$id = $args['id'];
		
		$options = get_option( $menu );
		
		if ( isset( $options[$id] ) ) {
			$current = $options[$id];
		} else {
			$current = isset( $args['default'] ) ? $args['default'] : '';
		}

		$disabled = (isset( $args['disabled'] )) ? ' disabled' : '';
		
		$html = sprintf( '<select name="%1$s[%2$s]" id="%1$s[%2$s]"%3$s>', $menu, $id, $disabled );
		$html .= sprintf( '<option value="%s"%s>%s</option>', '0', selected( $current, '0', false ), '' );
		
		foreach ( $args['options'] as $key => $label ) {
			$html .= sprintf( '<option value="%s"%s>%s</option>', $key, selected( $current, $key, false ), $label );
		}
		$html .= sprintf( '</select>' );

		if ( isset( $args['description'] ) ) {
			$html .= sprintf( '<p class="description">%s</p>', $args['description'] );
		}
		
		if (isset( $args['disabled'] )) {
			$html .= ' <span style="display:none;" class="pro-feature"><i>'. __('This feature only available with', 'stylebuddy') .' <a href="https://wpovernight.com/downloads/style-buddy-pro?utm_source=wordpress&utm_medium=stylebuddyfree&utm_campaign=stylebuddytheme">Style Buddy Pro</a></i></span>';
			$html .= '<div style="position:absolute; left:0; right:0; top:0; bottom:0; background-color:white; -moz-opacity: 0; opacity:0;filter: alpha(opacity=0);" class="hidden-input"></div>';
			$html = '<div style="display:inline-block; position:relative;">'.$html.'</div>';
		}

		echo $html;
	}
}