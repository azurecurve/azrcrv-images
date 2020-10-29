<?php
/**
 * ------------------------------------------------------------------------------
 * Plugin Name: Images
 * Description: Shortcode to display image in post; add width, alt, caption or error attributes.
 * Version: 1.2.0
 * Author: azurecurve
 * Author URI: https://development.azurecurve.co.uk/classicpress-plugins/
 * Plugin URI: https://development.azurecurve.co.uk/classicpress-plugins/images/
 * Text Domain: images
 * Domain Path: /languages
 * ------------------------------------------------------------------------------
 * This is free software released under the terms of the General Public License,
 * version 2, or later. It is distributed WITHOUT ANY WARRANTY; without even the
 * implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. Full
 * text of the license is available at https://www.gnu.org/licenses/gpl-2.0.html.
 * ------------------------------------------------------------------------------
 */

// Prevent direct access.
if (!defined('ABSPATH')){
	die();
}

// include plugin menu
require_once(dirname(__FILE__).'/pluginmenu/menu.php');
add_action('admin_init', 'azrcrv_create_plugin_menu_im');

// include update client
require_once(dirname(__FILE__).'/libraries/updateclient/UpdateClient.class.php');

/**
 * Setup registration activation hook, actions, filters and shortcodes.
 *
 * @since 1.0.0
 *
 */

// add actions
add_action('admin_menu', 'azrcrv_im_create_admin_menu');
add_action('plugins_loaded', 'azrcrv_im_load_languages');

// add filters
add_filter('plugin_action_links', 'azrcrv_im_add_plugin_action_link', 10, 2);
add_filter('the_posts', 'azrcrv_im_check_for_shortcode', 10, 2);
add_filter('codepotent_update_manager_image_path', 'azrcrv_im_custom_image_path');
add_filter('codepotent_update_manager_image_url', 'azrcrv_im_custom_image_url');

// add shortcodes
add_shortcode('image', 'azrcrv_im_display_image');
add_shortcode('post-image', 'azrcrv_im_display_image');

/**
 * Load language files.
 *
 * @since 1.0.0
 *
 */
function azrcrv_im_load_languages() {
    $plugin_rel_path = basename(dirname(__FILE__)).'/languages';
    load_plugin_textdomain('images', false, $plugin_rel_path);
}

/**
 * Check if shortcode on current page and then load css and jqeury.
 *
 * @since 1.0.0
 *
 */
function azrcrv_im_check_for_shortcode($posts){
    if (empty($posts)){
        return $posts;
	}
	
	
	// array of shortcodes to search for
	$shortcodes = array(
						'image','post-image'
						);
	
    // loop through posts
    $found = false;
    foreach ($posts as $post){
		// loop through shortcodes
		foreach ($shortcodes as $shortcode){
			// check the post content for the shortcode
			if (has_shortcode($post->post_content, $shortcode)){
				$found = true;
				// break loop as shortcode found in page content
				break 2;
			}
		}
	}
 
    if ($found){
		// as shortcode found call functions to load css and jquery
        azrcrv_im_load_css();
    }
    return $posts;
}

/**
 * Load CSS.
 *
 * @since 1.0.0
 *
 */
function azrcrv_im_load_css(){
	wp_enqueue_style('azrcrv-im', plugins_url('assets/css/style.css', __FILE__), '', '1.0.0');
}

/**
 * Custom plugin image path.
 *
 * @since 1.2.0
 *
 */
function azrcrv_im_custom_image_path($path){
    if (strpos($path, 'azrcrv-images') !== false){
        $path = plugin_dir_path(__FILE__).'assets/pluginimages';
    }
    return $path;
}

/**
 * Custom plugin image url.
 *
 * @since 1.2.0
 *
 */
function azrcrv_im_custom_image_url($url){
    if (strpos($url, 'azrcrv-images') !== false){
        $url = plugin_dir_url(__FILE__).'assets/pluginimages';
    }
    return $url;
}

/**
 * Add Images action link on plugins page.
 *
 * @since 1.0.0
 *
 */
function azrcrv_im_add_plugin_action_link($links, $file){
	static $this_plugin;

	if (!$this_plugin){
		$this_plugin = plugin_basename(__FILE__);
	}

	if ($file == $this_plugin){
		$settings_link = '<a href="'.admin_url('admin.php?page=azrcrv-im').'"><img src="'.plugins_url('/pluginmenu/images/Favicon-16x16.png', __FILE__).'" style="padding-top: 2px; margin-right: -5px; height: 16px; width: 16px;" alt="azurecurve" />'.esc_html__('Settings' ,'images').'</a>';
		array_unshift($links, $settings_link);
	}

	return $links;
}

/**
 * Add to menu.
 *
 * @since 1.0.0
 *
 */
function azrcrv_im_create_admin_menu(){
	//global $admin_page_hooks;
	
	add_submenu_page("azrcrv-plugin-menu"
						,esc_html__("Images Settings", "images")
						,esc_html__("Images", "images")
						,'manage_options'
						,'azrcrv-im'
						,'azrcrv_im_display_options');
}

/**
 * Display Settings page.
 *
 * @since 1.0.0
 *
 */
function azrcrv_im_display_options(){
	if (!current_user_can('manage_options')){
        wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'images'));
    }
	
	?>
	<div id="azrcrv-im-general" class="wrap">
		<fieldset>
			<h1><?php echo esc_html(get_admin_page_title()); ?></h1>
			<?php if(isset($_GET['settings-updated'])){ ?>
				<div class="notice notice-success is-dismissible">
					<p><strong><?php esc_html_e('Settings have been saved.', 'images') ?></strong></p>
				</div>
			<?php } ?>
			<form method="post" action="admin-post.php">
				
				<table class="form-table">
				
				<tr><th scope="row" colspan=2><?php esc_html_e('Image Shortcode', 'images'); ?></th></tr>
				<tr><td colspan=2>
					<p><?php esc_html_e("The [image] shortcode is used to display images in a post or page. The <em>image</em> and <em>alt</em> attributes must be included, but other atttributes are optional.", "images"); ?></p>
					<p><?php esc_html_e("Example usage of the plugin is:", "images"); ?></p>
					<div style='width: 90%; margin: auto;'><em><?php esc_html_e("[image width=550px image='http://www.azurecurve.co.uk/images/posts/2019/03/SmartConnectScheduledMapCannotRun/SmartConnectScheduledMapCannotRun_1.png' alt='Windows Event Viewer showing error' error='JOURNALSTANDARD : SmartConnect Scheduler : Could not run the scheduled map You do not have access to the connectors required for this map.']", "images"); ?></em></div>
				</td></tr>
				
				<tr><th scope="row"><label for="image"><?php esc_html_e('image', 'images'); ?></label></th><td>
					<p class="image"><?php esc_html_e('Set to the full path of the image to be displayed.', 'images'); ?></p>
				</td></tr>
				
				<tr><th scope="row"><label for="alt"><?php esc_html_e('alt', 'images'); ?></label></th><td>
					<p class="alt"><?php esc_html_e('Set the alt text to be displayed if the image can;t load; support accessibility by making the alt text descriptive.', 'images'); ?></p>
				</td></tr>
				
				<tr><th scope="row"><label for="width"><?php esc_html_e('width', 'images'); ?></label></th><td>
					<p class="width"><?php esc_html_e('Set the width at which the image should be displayed.', 'images'); ?></p>
				</td></tr>
				
				<tr><th scope="row"><label for="caption"><?php esc_html_e('caption', 'images'); ?></label></th><td>
					<p class="caption"><?php esc_html_e('Set caption text to be displayed below the image.', 'images'); ?></p>
				</td></tr>
				
				<tr><th scope="row"><label for="error"><?php esc_html_e('error', 'images'); ?></label></th><td>
					<p class="error"><?php esc_html_e('Set error text to be displayed below the image.', 'images'); ?></p>
				</td></tr>
				
				</table>
				
			</form>
		</fieldset>
	</div>
	<?php
}

/**
 * Output image shortcode.
 *
 * @since 1.0.0
 *
 */
function azrcrv_im_display_image($atts, $content = null){
	
	// extract attributes from shortcode
	$args = shortcode_atts(array(
		'alt' => '',
		'image' => '',
		'width' => '550px',
		'caption' => '',
		'error' => '',
	), $atts);
	$alt = $args['alt'];
	$image = $args['image'];
	$width = $args['width'];
	$caption = $args['caption'];
	$error = $args['error'];
	
	$output = '';
	if (strlen($image) > 0 AND strlen($alt) > 0){
		$outputwidth = "width: $width; ";
		
		$output = "<div class='azrcrv-im' style='".esc_html__($outputwidth)."'>";
		$output .= "<a class='azrcrv-im' href='".esc_url($image)."'>";
		$output .= "<img class='azrcrv-im' style='width: 100%; ' src='".esc_url($image)."' alt='".esc_html($alt)."' />";
		$output .= "</a>";
		
		if (strlen($caption) > 0){
			$output .= "<pre class='azrcrv-im-caption'><span class='azrcrv-im-caption'>$caption</span></pre>";
		}else{
			if (strlen($error) > 0){
				$output .= "<pre class='azrcrv-im-error'><span class='azrcrv-im-error'>$error</span></pre>";
			}
		}
		$output .= "</div>";
	}
	
	return $output;
	
}

?>