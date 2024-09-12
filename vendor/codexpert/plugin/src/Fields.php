<?php
namespace Codexpert\Plugin;

/**
 * if accessed directly, exit.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * @package Plugin
 * @subpackage Fields
 * @author Codexpert <hi@codexpert.io>
 */
abstract class Fields extends Base {

	public function hooks() {
		if( did_action( "cx-plugin_{$this->config['id']}_loaded" ) ) return;
		do_action( "cx-plugin_{$this->config['id']}_loaded" );

		$this->action( 'admin_head', 'callback_head', 99 );
	}

	public function enqueue_scripts() {
        wp_enqueue_media();

        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'wp-color-picker' );

        wp_register_script( 'select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/js/select2.min.js' );
        wp_register_style( 'select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/css/select2.min.css' );

        wp_register_script( 'chosen', 'https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js' );
        wp_register_style( 'chosen', 'https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.min.css' );

        if( $this->has_select2() ) {
        	wp_enqueue_style( 'select2' );
        	wp_enqueue_script( 'select2' );
        }

        if( $this->has_chosen() ) {
        	wp_enqueue_style( 'chosen' );
        	wp_enqueue_script( 'chosen' );
        }

        wp_enqueue_style( 'codexpert-product-fields', plugins_url( 'assets/css/fields.css', __FILE__ ), [], '' );
        wp_enqueue_script( 'codexpert-product-fields', plugins_url( 'assets/js/fields.js', __FILE__ ), [ 'jquery' ], '', true );
    }

	public function callback_head() {
		?>
		<script>
			jQuery(function($){<?php
				if( is_array( $this->sections ) && count( $this->sections ) > 0 ) {
					foreach ( $this->sections as $section_id => $section ) {
						if( isset( $section['fields'] ) && is_array( $section['fields'] ) && count( $section['fields'] ) > 0 ) {
							foreach ( $section['fields'] as $field ) {
								if( isset( $field['condition'] ) && is_array( $field['condition'] ) ) {
									$key = $field['condition']['key'];
									$value = isset( $field['condition']['value'] ) ? $field['condition']['value'] : 'on';
									$compare = isset( $field['condition']['compare'] ) ? $field['condition']['compare'] : '==';

									if( 'checked' != $compare ) {
										echo "$('#{$section['id']}-{$key}').change(function(e){if( $('#{$section['id']}-{$key}').val() {$compare} '{$value}' ) { $('#cx-row-{$section['id']}-{$field['id']}').slideDown();}else { $('#cx-row-{$section['id']}-{$field['id']}').slideUp();}}).change();";
									}
									else {
										echo "$('#{$section['id']}-{$key}').change(function(e){if( $('#{$section['id']}-{$key}').is(':checked') ) { $('#cx-row-{$section['id']}-{$field['id']}').slideDown();}else { $('#cx-row-{$section['id']}-{$field['id']}').slideUp();}}).change();";
									}
								}
							}
						}
					}
				}
				?>
			})
		</script>
		<?php
	}

	public function callback_fields( $post = null, $metabox = [] ) {

		$config = $this->config;

		$scope = $metabox == [] ? 'option' : 'post';
		
		echo '<div class="wrap">';

		if( $scope == 'option' ) :
		$icon = $this->generate_icon( $config['icon'] );
		echo "<h2 class='cx-heading'>{$icon} {$config['title']}</h2>";
		endif;

		do_action( 'cx-settings-heading', $config );

		if( ! isset( $this->sections ) || count( $this->sections ) <= 0 ) return;

		$tab_position = isset( $config['topnav'] ) && $config['topnav'] == true ? 'top' : 'left';
		echo "<div class='cx-wrapper cx-shadow cx-tab-{$tab_position} cx-sections-" . count( $this->sections ) . "'>";

		$sections = $this->sections;

		// nav tabs
		$display = count( $sections ) > 1 ? 'block' : 'none';
		echo '
		<div class="cx-navs-wrapper" style="display: ' . $display . '">
			<ul class="cx-nav-tabs">';
			foreach ( $sections as $section ) {
				$icon = $this->generate_icon( $section['icon'] );
				$color = isset( $section['color'] ) ? $section['color'] : '#1c2327';
				echo "<li id='cx-nav-tab-{$section['id']}' class='cx-nav-tab' data-color='{$color}'><a href='#{$section['id']}'>{$icon}<span id='cx-nav-label-{$section['id']}' class='cx-nav-label'> {$section['label']}</span></a></li>";
			}
			echo '</ul>
		</div><!--div class="cx-navs-wrapper"-->';

		// form areas
		echo '<div class="cx-sections-wrapper">';
		foreach ( $sections as $section ) {
			$icon = $this->generate_icon( $section['icon'] );
			$color = isset( $section['color'] ) ? $section['color'] : '#1c2327';
			$submit_button = isset( $section['submit_button'] ) ? $section['submit_button'] : __( 'Save Settings' );
			$reset_button = isset( $section['reset_button'] ) ? $section['reset_button'] : __( 'Reset Default' );
			$_nonce = wp_create_nonce();

			echo "<div id='{$section['id']}' class='cx-section' style='display:none'>";

			do_action( 'cx-settings-before-form', $section );

			$fields = isset( $section['fields'] ) ? $section['fields'] : [];
			$fields = apply_filters( 'cx-settings-fields', $fields, $section );
			$show_form = isset( $section['hide_form'] ) && $section['hide_form'] ? false : true;
			$show_form = apply_filters( 'cx-settigns-show-form', $show_form, $section );

			if( $scope == 'option' && $show_form ) {
				$page_load = isset( $section['page_load'] ) && $section['page_load'] ? 1 : 0;

				echo "<form id='cx-form-{$section['id']}' class='cx-form'>
						<div id='cx-message-{$section['id']}' class='cx-message'>
							<img src='" . plugins_url( 'assets/img/checked.png', __FILE__ ) . "' />
							<p></p>
						</div>
						<input type='hidden' name='action' value='cx-settings' />
						<input type='hidden' name='option_name' value='{$section['id']}' />
						<input type='hidden' name='page_load' value='{$page_load}' />
				";

				wp_nonce_field();
			}

			if( ! isset( $section['no_heading'] ) || $section['no_heading'] !== true ) {
				echo "<div class='cx-subheading'>";
				
				do_action( 'cx-settings-before-title', $section );
			
				echo "<div class='cx-section-subheading' style='color: {$color}'>{$icon}</span> <span class='cx-subheading-text'>{$section['label']}</div>";
			
				do_action( 'cx-settings-after-title', $section );

				if( $show_form && ( ! isset( $section['top_btn'] ) || false !== $section['top_btn'] ) ) {
					echo "<div id='cx-section-top_btn-{$section['id']}' class='cx-section-top_btn'>";
					if( $reset_button ) echo "<button type='button' class='button button-hero cx-reset-button' data-option_name='{$section['id']}' data-_nonce='{$_nonce}'>{$reset_button}</button>&nbsp;";
					if( $submit_button ) echo "<input type='submit' class='button button-hero button-primary cx-submit' value='{$submit_button}' />";
					echo '</div>';
				}

				echo "</div>";
			}
			
			if( isset( $section['desc'] ) && $section['desc'] != '' ) {
				echo "<p class='cx-desc'>{$section['desc']}</p>";
			}

			do_action( 'cx-settings-before-fields', $section );

			if( isset( $section['content'] ) && $section['content'] != '' ) {
				echo $section['content'];
			}
			elseif( isset( $section['template'] ) && $section['template'] != '' && file_exists( $section['template'] ) ) {
				include $section['template'];
			}
			elseif( isset( $section['fields'] ) && is_array( $section['fields'] ) ) {
				$this->populate_fields( $fields, $section, $scope );
			}

			do_action( 'cx-settings-after-fields', $section );

			if( $scope == 'option' && $show_form ) {
				$_is_sticky = isset( $section['sticky'] ) && ! $section['sticky'] ? ' cx-nonsticky-controls' : ' cx-sticky-controls';
				echo "<div class='cx-controls-wrapper{$_is_sticky}'>";

				if( $reset_button ) echo "<button type='button' class='button button-hero cx-reset-button' data-option_name='{$section['id']}' data-_nonce='{$_nonce}'>{$reset_button}</button>&nbsp;";
				if( $submit_button ) echo "<input type='submit' class='button button-hero button-primary cx-submit' value='{$submit_button}' />";

				echo '</div class="cx-controls-wrapper">
				</form>';
			}

			do_action( 'cx-settings-after-form', $section );

			echo "</div><!--div id='{$section['id']}'-->";
		}

		echo '</div><!--div class="cx-sections-wrapper"-->
			 <div class="cx-sidebar-wrapper">';

		do_action( 'cx-settings-sidebar', $config );

		echo '</div><!--div class="cx-sidebar-wrapper"-->
			</div><!--div class="cx-wrapper"-->
		</div><!--div class="wrap"-->
		<div id="cx-overlay" style="display: none;">
			<img src="' . plugins_url( 'assets/img/loading.gif', __FILE__ ) . '" />
		</div>';

		if( isset( $config['css'] ) && $config['css'] != '' ) {
			echo "<style>{$config['css']}</style>";
		}
	}

	/**
	 * Populates all fields under a section or tab
	 */
	public function populate_fields( $fields, $section, $scope ) {

		if( count( $fields ) > 0 ) :
		foreach ( $fields as $field ) {
		
			do_action( 'cx-settings-before-row', $field, $section );

			$_show_label = isset( $field['label'] ) && $field['type'] != 'tabs';

			if( isset( $field['type'] ) && $field['type'] == 'divider' ) {
				$style = isset( $field['style'] ) ? $field['style'] : '';
				echo "<div class='cx-row cx-divider' id='{$section['id']}-{$field['id']}' style='{$style}'><span>{$field['label']}</span></div>";
			}
			else {
				$field_display = isset( $field['condition'] ) && is_array( $field['condition'] ) ? 'none' : '';
				echo "
				<div id='cx-row-{$section['id']}-{$field['id']}' class='cx-row cx-row-{$section['id']} cx-row-{$field['type']}' style='display: {$field_display}'>";

				if( $_show_label ) {
					echo "<div class='cx-label-wrap'>";

					do_action( 'cx-settings-before-label', $field, $section );

					echo "<label for='{$section['id']}-{$field['id']}'>{$field['label']}</label>";

					do_action( 'cx-settings-after-label', $field, $section );

					echo "</div>";
				}

				$_label_class = $_show_label ? '' : 'cx-field-wrap-nolabel';
				
				echo "<div class='cx-field-wrap {$_label_class}'>";

					do_action( 'cx-settings-before-field', $field, $section );

					if( isset( $field['template'] ) && $field['template'] != '' ) echo $field['template'];

					if( isset( $field['type'] ) && $field['type'] != '' ) echo $this->populate( $field, $section, $scope );

					do_action( 'cx-settings-after-field', $field, $section );

					if( isset( $field['desc'] ) && $field['desc'] != '' ) {
						echo "<p class='cx-desc'>{$field['desc']}</p>";
					}

				do_action( 'cx-settings-after-description', $field, $section );

				echo "</div>
				</div>";
			}
			
			do_action( 'cx-settings-after-row', $field, $section );
		}
		endif; // if( count( $fields ) > 0 ) :
	}
	
	/**
	 * Populates a single input field
	 */
	public function populate( $field, $section, $scope = 'option' ) {

		$callback_fn = '';

		if( isset( $field['content'] ) && $field['content'] != '' ) {
			echo $field['content'];
		}
		elseif( isset( $field['template'] ) && $field['template'] != '' && file_exists( $field['template'] ) ) {
			include $field['template'];
		}
		elseif ( in_array( $field['type'], [ 'text', 'number', 'email', 'url', 'password', 'color', 'range', 'date', 'time' ] ) ) {
			$callback_fn = 'field_text';
		}
		else {
			$callback_fn = "field_{$field['type']}";
		}

		if( $callback_fn != '' && method_exists( $this, $callback_fn ) ) {
			return $this->$callback_fn( $field, $section, $scope );
		}

		return __( 'Invalid field type', 'cx-plugin' );
	}

	public function get_value( $field, $section, $default = '', $scope = 'option' ) {

		if( isset( $field['value'] ) ) return $field['value'];

		if( $scope == 'option' ) {
			$section_values = get_option( $section['id'] );
		}
		else {
			global $post;
			$section_values = get_post_meta( $post->ID, $section['id'], true );
		}

		if( isset( $section_values[ $field['id'] ] ) ) {
			return $section_values[ $field['id'] ];
		}
		
		return $default;
	}

	public function field_text( $field, $section, $scope ) {
		$default		= isset( $field['default'] ) ? $field['default'] : '';
		$value			= $this->esc_str( $this->get_value( $field, $section, $default, $scope ) );

		$type 			= $field['type'];
		$name 			= $scope == 'option' ? $field['id'] : "{$section['id']}[{$field['id']}]";
		$label 			= isset( $field['label'] ) ? $field['label'] : '';
		$id 			= "{$section['id']}-{$field['id']}";

		$class 			= "cx-field cx-field-{$field['type']}";
		$class 			.= isset( $field['class'] ) ? $field['class'] : '';

		$placeholder	= isset( $field['placeholder'] ) ? $field['placeholder'] : '';
		$required 		= isset( $field['required'] ) && $field['required'] ? " required" : "";
		$readonly 		= isset( $field['readonly'] ) && $field['readonly'] ? " readonly" : "";
		$disabled 		= isset( $field['disabled'] ) && $field['disabled'] ? " disabled" : "";
		$min 			= isset( $field['min'] ) && $field['min'] ? " min='{$field['min']}'" : "";
		$max 			= isset( $field['max'] ) && $field['max'] ? " max='{$field['max']}'" : "";
		$step 			= isset( $field['step'] ) && $field['step'] ? " step='{$field['step']}'" : "";

		if( $type == 'color' ) {
			$class .= ' cx-color-picker';
			$type = 'text';
		}

		$html = "<input type='{$type}' class='{$class}' id='{$id}' name='{$name}' value='{$value}' placeholder='{$placeholder}' {$min} {$max} {$step} {$required} {$readonly} {$disabled}/>";

		return $html;
	}

	public function field_textarea( $field, $section, $scope ) {
		$default		= isset( $field['default'] ) ? $field['default'] : '';
		$value			= $this->esc_str( $this->get_value( $field, $section, $default, $scope ) );

		$name 			= $scope == 'option' ? $field['id'] : "{$section['id']}[{$field['id']}]";
		$label 			= isset( $field['label'] ) ? $field['label'] : '';
		$id 			= "{$section['id']}-{$field['id']}";

		$class 			= "cx-field cx-field-{$field['type']}";
		$class 			.= isset( $field['class'] ) ? $field['class'] : '';

		$placeholder	= isset( $field['placeholder'] ) ? $field['placeholder'] : '';
		$required 		= isset( $field['required'] ) && $field['required'] ? " required" : "";
		$readonly 		= isset( $field['readonly'] ) && $field['readonly'] ? " readonly" : "";
		$disabled 		= isset( $field['disabled'] ) && $field['disabled'] ? " disabled" : "";
		$rows 			= isset( $field['rows'] ) ? $field['rows'] : 5;
		$cols 			= isset( $field['cols'] ) ? $field['cols'] : 3;

		$html  = "<textarea class='{$class}' id='{$id}' name='{$name}' cols='{$cols}' rows='{$rows}' placeholder='{$placeholder}' {$required} {$readonly} {$disabled}>{$value}</textarea>";

		return $html;
	}

	public function field_radio( $field, $section, $scope ) {
		$default		= isset( $field['default'] ) ? $field['default'] : '';
		$value			= $this->get_value( $field, $section, $default, $scope );

		$name 			= $scope == 'option' ? $field['id'] : "{$section['id']}[{$field['id']}]";
		$label 			= isset( $field['label'] ) ? $field['label'] : '';
		$id 			= "{$section['id']}-{$field['id']}";

		$class 			= "cx-field cx-field-{$field['type']}";
		$class 			.= isset( $field['class'] ) ? $field['class'] : '';

		$placeholder	= isset( $field['placeholder'] ) ? $field['placeholder'] : '';
		$required 		= isset( $field['required'] ) && $field['required'] ? " required" : "";
		$readonly 		= isset( $field['readonly'] ) && $field['readonly'] ? " readonly" : "";
		$disabled 		= isset( $field['disabled'] ) && $field['disabled'] ? " disabled" : "";
		$options 		= isset( $field['options'] ) ? $field['options'] : [];

		$html = '';
		foreach ( $options as $key => $title ) {
			$html .= "<input type='radio' name='{$name}' id='{$id}-{$key}' class='{$class}' value='{$key}' {$required} {$disabled} " . checked( $value, $key, false ) . "/>";
			$html .= "<label for='{$id}-{$key}'>{$title}</label><br />";
		}

		return $html;
	}

	public function field_checkbox( $field, $section, $scope ) {
		$default		= isset( $field['default'] ) ? $field['default'] : '';
		$value			= $this->get_value( $field, $section, $default, $scope );

		$name 			= $scope == 'option' ? $field['id'] : "{$section['id']}[{$field['id']}]";
		$label 			= isset( $field['label'] ) ? $field['label'] : '';
		$id 			= "{$section['id']}-{$field['id']}";

		$class 			= "cx-field cx-field-{$field['type']}";
		$class 			.= isset( $field['class'] ) ? $field['class'] : '';

		$placeholder	= isset( $field['placeholder'] ) ? $field['placeholder'] : '';
		$required 		= isset( $field['required'] ) && $field['required'] ? " required" : "";
		$disabled 		= isset( $field['disabled'] ) && $field['disabled'] ? " disabled" : "";
		$multiple 		= isset( $field['multiple'] ) && $field['multiple'];
		$options 		= isset( $field['options'] ) ? $field['options'] : [];

		$html  = '';
		if( $multiple ) {
			foreach ( $options as $key => $title ) {
				$html .= "
				<p>
					<input type='checkbox' name='{$name}[]' id='{$id}-{$key}' class='{$class}' value='{$key}' {$required} {$disabled} " . ( in_array( $key, (array)$value ) ? 'checked' : '' ) . "/>
					<label for='{$id}-{$key}'>{$title}</label>
				</p>";
			}
		}
		else {
			$html .= "<input type='checkbox' name='{$name}' id='{$id}' class='{$class}' value='on' {$required} {$disabled} " . checked( $value, 'on', false ) . "/>";
		}

		return $html;
	}

	public function field_switch( $field, $section, $scope ) {
		$default		= isset( $field['default'] ) ? $field['default'] : '';
		$value			= $this->get_value( $field, $section, $default, $scope );

		$name 			= $scope == 'option' ? $field['id'] : "{$section['id']}[{$field['id']}]";
		$label 			= isset( $field['label'] ) ? $field['label'] : '';
		$id 			= "{$section['id']}-{$field['id']}";

		$class 			= "cx-field cx-field-{$field['type']}";
		$class 			.= isset( $field['class'] ) ? $field['class'] : '';

		$placeholder	= isset( $field['placeholder'] ) ? $field['placeholder'] : '';
		$required 		= isset( $field['required'] ) && $field['required'] ? " required" : "";
		$disabled 		= isset( $field['disabled'] ) && $field['disabled'] ? " disabled" : "";
		$multiple 		= isset( $field['multiple'] ) && $field['multiple'];
		$options 		= isset( $field['options'] ) ? $field['options'] : [];

		$html  = '';
		if( $multiple ) {
			foreach ( $options as $key => $title ) {
				$html .= "
					<label class='cx-toggle'>
						<input type='checkbox' name='{$name}[]' id='{$id}-{$key}' class='cx-toggle-checkbox {$class}' value='{$key}' {$required} {$disabled} " . ( in_array( $key, (array)$value ) ? 'checked' : '' ) . "/>
						<div class='cx-toggle-switch'></div>
						<span class='cx-toggle-label'>{$title}</span>
					</label>
				";
			}
		}
		else {
			$html .= "
				<label class='cx-toggle'>
					<input type='checkbox' name='{$name}' id='{$id}' class='cx-toggle-checkbox {$class}' value='on' {$required} {$disabled} " . checked( $value, 'on', false ) . "/>
					<div class='cx-toggle-switch'></div>
				</label>
			";
		}

		return $html;
	}

	public function field_select( $field, $section, $scope ) {
		$default		= isset( $field['default'] ) ? $field['default'] : '';
		$value			= $this->get_value( $field, $section, $default, $scope );

		$name 			= $scope == 'option' ? $field['id'] : "{$section['id']}[{$field['id']}]";
		$label 			= isset( $field['label'] ) ? $field['label'] : '';
		$id 			= "{$section['id']}-{$field['id']}";

		$class 			= "cx-field cx-field-{$field['type']}";
		$class 			.= isset( $field['class'] ) ? $field['class'] : '';
		$class 			.= isset( $field['select2'] ) && $field['select2'] ? ' cx-select2' : '';
		$class 			.= isset( $field['chosen'] ) && $field['chosen'] ? ' cx-chosen' : '';

		$placeholder	= isset( $field['placeholder'] ) ? $field['placeholder'] : '';
		$required 		= isset( $field['required'] ) && $field['required'] ? " required" : "";
		$multiple 		= isset( $field['multiple'] ) && $field['multiple'] ? 'multiple' : false;
		$options 		= isset( $field['options'] ) ? $field['options'] : [];

		$disabled			= '';
		$disabled_options	= [];
		if( isset( $field['disabled'] ) && false !== $field['disabled'] ) {
			if( true === $field['disabled'] )	 {
				$disabled = 'disabled';
			}
			elseif( is_array( $field['disabled'] ) ) {
				$disabled_options = $field['disabled'];
			}
		}

		$html  = '';
		if( $multiple ) {
			$html .= "<select name='{$name}[]' id='{$id}' class='{$class}' multiple {$required} {$disabled} data-placeholder='{$placeholder}'>";
			foreach ( $options as $key => $title ) {
				$option_disabled = in_array( $key, $disabled_options ) ? 'disabled' : '';
				$html .= "<option {$option_disabled} value='{$key}' " . ( in_array( $key, (array)$value ) ? 'selected' : '' ) . ">{$title}</option>";
			}
			$html .= '</select>';
		}
		else {
			$html .= "<select name='{$name}' id='{$id}' class='{$class}' {$required} {$disabled} data-placeholder='{$placeholder}'>";
			foreach ( $options as $key => $title ) {
				$option_disabled = in_array( $key, $disabled_options ) ? 'disabled' : '';
				$html .= "<option {$option_disabled} value='{$key}' " . selected( $value, $key, false ) . ">{$title}</option>";
			}
			$html .= '</select>';
		}

		return $html;
	}

	public function field_file( $field, $section, $scope ) {
		$default		= isset( $field['default'] ) ? $field['default'] : '';
		$value			= $this->esc_str( $this->get_value( $field, $section, $default, $scope ) );

		$type 			= $field['type'];
		$name 			= $scope == 'option' ? $field['id'] : "{$section['id']}[{$field['id']}]";
		$label 			= isset( $field['label'] ) ? $field['label'] : '';
		$id 			= "{$section['id']}-{$field['id']}";

		$class 			= "cx-field cx-field-{$field['type']}";
		$class 			.= isset( $field['class'] ) ? $field['class'] : '';

		$placeholder	= isset( $field['placeholder'] ) ? $field['placeholder'] : '';
		$required 		= isset( $field['required'] ) && $field['required'] ? " required" : "";
		$readonly 		= isset( $field['readonly'] ) && $field['readonly'] ? " readonly" : "";
		$disabled 		= isset( $field['disabled'] ) && $field['disabled'] ? " disabled" : "";

		$upload_button	= isset( $field['upload_button'] ) ? $field['upload_button'] : __( 'Choose File' );
		$select_button	= isset( $field['select_button'] ) ? $field['select_button'] : __( 'Select' );

		$html  = '';
		$html .= "<input type='text' class='{$class} cx-file' id='{$id}' name='{$name}' value='{$value}' placeholder='{$placeholder}' {$readonly} {$required} {$disabled}/>";
		$html  .= "<input type='button' class='button cx-browse' data-title='{$label}' data-select-text='{$select_button}' value='{$upload_button}' {$required} {$disabled} />";

		return $html;
	}

	public function field_wysiwyg( $field, $section, $scope ) {
		$default		= isset( $field['default'] ) ? $field['default'] : '';
		$value			= stripslashes( $this->get_value( $field, $section, $default, $scope ) );

		$name 			= $scope == 'option' ? $field['id'] : "{$section['id']}[{$field['id']}]";
		$label 			= isset( $field['label'] ) ? $field['label'] : '';
		$id 			= "{$section['id']}-{$field['id']}";

		$class 			= "cx-field cx-field-{$field['type']}";
		$class 			.= isset( $field['class'] ) ? $field['class'] : '';

		$placeholder	= isset( $field['placeholder'] ) ? $field['placeholder'] : '';
		$readonly 		= isset( $field['readonly'] ) && $field['readonly'] ? " readonly" : "";
		$disabled 		= isset( $field['disabled'] ) && $field['disabled'] ? " disabled" : "";
		$teeny			= isset( $field['teeny'] ) && $field['teeny'];
		$text_mode		= isset( $field['text_mode'] ) && $field['text_mode'];
		$media_buttons  = isset( $field['media_buttons'] ) && $field['media_buttons'];
		$rows 			= isset( $field['rows'] ) ? $field['rows'] : 10;

		$html  = '';
		$settings = [
			'teeny'         => $teeny,
			'textarea_name' => $name,
			'textarea_rows' => $rows,
			'quicktags'		=> $text_mode,
			'media_buttons'	=> $media_buttons,
		];

		if ( isset( $field['options'] ) && is_array( $field['options'] ) ) {
			$settings = array_merge( $settings, $field['options'] );
		}

		ob_start();
		wp_editor( $value, $id, $settings );
		$html .= ob_get_contents();
		ob_end_clean();

		return $html;
	}

	public function field_divider( $field, $section, $scope ) {
		return $field['label'];
	}

	public function field_group( $field, $section, $scope ) {
		$items = $field['items'];
		$html = '';
		foreach ( $items as $item ) {
			$item['class'] = ' cx-field-group';
			$html .= $this->populate( $item, $section, $scope );
		}

		return $html;
	}

	public function field_tabs( $field, $section, $scope ) {
		$tabs = $field['items'];
		$html = $buttons = $content = '';
		if( ! isset( $section['color'] ) ) {
			$section['color'] = '#1c2327';
		}


		$count = 0;
		foreach ( $tabs as $id => $tab ) {
			$btn_active		= $count == 0 ? 'cx-tab-active' : '';
			$cnt_display	= $count == 0 ? '' : 'none';

			$buttons .= "<a class='cx-tab {$btn_active}' data-target='cx-tab-{$section['id']}-{$id}'>{$tab['label']}</a>";

			$content .= "<div class='cx-tab-content' id='cx-tab-{$section['id']}-{$id}' style='display: {$cnt_display}'>";
			
			ob_start();

			if( isset( $tab['content'] ) && $tab['content'] != '' ) {
				$content .= $tab['content'];
			}
			elseif( isset( $tab['template'] ) && $tab['template'] != '' && file_exists( $tab['template'] ) ) {
				include $tab['template'];
				$content .= ob_get_clean();
			}
			else {
				$this->populate_fields( $tab['fields'], $section, $scope );
				$content .= ob_get_clean();
			}
			
			ob_flush();

			$content .= "</div>";


			$count++;
		}
		$style = "<style>
			.cx-tabs {
				border-bottom: 1px solid {$section['color']};
				grid-template-columns: " . str_repeat( '1fr ', count( $tabs ) ) . ";
			}
			.cx-tab {
				border: 1px solid {$section['color']};
				border-right: 1px solid #fff;
				border-left: 0px solid #fff;
				color: #fff;
				background: {$section['color']};
			}
			.cx-tab:last-child {
				border-right: 1px solid {$section['color']};
			}
			.cx-tab:first-child {
				border-left: 1px solid {$section['color']};
			}
			.cx-tab-active,.cx-tab-active:hover {
				color: {$section['color']};
			}
		</style>";

		$html .= '<nav class="cx-tabs">' . $buttons . '</nav>';
		$html .= $content;
		$html .= $style;

		return $html;
	}

	public function field_repeater( $field, $section, $scope ) {
		$items = $field['items'];
		$html = '';

		$values = $this->get_value( $field, $section, [], $scope ) ? : [];
		
		$count = 0;

		for( $i = 0; $i < ( is_array( reset( $values ) ) ? count( reset( $values ) ) : 1 ); $i++ ) {
			$html .= '<div class="cx-repeatable">';
			foreach ( $items as $item ) {
				$item['class'] = ' cx-field-group';
				$item['default'] = isset( $item['default'] ) ? $item['default'] : '';
				$item['value'] = isset( $values[ $item['id'] ][ $count ] ) ? $values[ $item['id'] ][ $count ] : $item['default'];

				$item['id'] = "{$field['id']}[{$item['id']}][]";

				$html .= $this->populate( $item, $section, $scope );
			}

			$html .= '<button type="button" class="cx-repeater-remove">-</button>';
			$html .= '<button type="button" class="cx-repeater-add">+</button>';
			
			$html .= '</div>';

			$count++;
		}

		return $html;
	}

	public function generate_icon( $value ) {
		if( $value == '' ) return '';
		if( strpos( $value, '://' ) !== false ) {
			return "<img class='cx-icon-{$this->config['id']}' src='{$value}' />";
		}
		return "<span class='dashicons {$value}'></span>";
	}

	public function esc_str( $string ) {
		return stripslashes( esc_attr( $string ) );
	}

	public function deep_key_exists( $arr, $key ) {
		if ( array_key_exists( $key, $arr ) && $arr[ $key ] == true ) return true;
		foreach( $arr as $element ) {
			if( is_array( $element ) && $this->deep_key_exists( $element, $key ) ) {
				return true;
			}
		}
		return false;
	}

	public function has_select2() {
		return $this->deep_key_exists( $this->config, 'select2' );
	}

	public function has_chosen() {
		return $this->deep_key_exists( $this->config, 'chosen' );
	}
}