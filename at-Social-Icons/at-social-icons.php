<?php
/**
 * Plugin Name:  At Social Icons
 * Plugin URI:   https://github.com/md-ataur/social-icons-plugin
 * Description:  Social Icons Widget
 * Version:      1.0
 * Author:       Themehum
 * Author URI:	 https://themeforest.net/user/themehum
 * License:      GPLv2 or later
 * License URI:  https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:  socialicons
 * Domain Path: /languages
 */



class ATSocialIcons extends WP_Widget
{
    public function __construct()
    {
    	/**
		 * Register widget with WordPress
		 */
        parent::__construct(
            'socialicons', // Base ID
            esc_html__('Social Icons', 'socialicons'), // Name
            array('description' => esc_html__('Widget for social icons', 'socialicons')) 
        );

        add_action( "wp_enqueue_scripts", array($this,"socialicons_assets" ));
        add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array($this,'socialicons_action_links' ));
    }

    function socialicons_action_links($links){
    	$newlink = sprintf("<a href='%s'>%s</a>","widgets.php",esc_html__( "Settings","socialicons" ));
    	$links[] = $newlink;
    	return $links; 
    }

	function socialicons_assets(){
		wp_enqueue_style( "font-awesome", "//cdnjs.cloudflare.com/ajax/libs/font-awesome/5.8.2/css/all.min.css" );
	}


    /**
	 * Front-end display.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	
    function widget($args, $instance)
    {

    	$social_icons = array(
            "facebook",
            "twitter",
            "linkedin",
            "pinterest",
            "instagram",
            "google-plus",
            "github",
            "youtube",
            "vimeo",
            "tumblr",
            "dribbble",
            "flickr",
            "behance",
            "meetup",
            "whatsapp",           
        );

    	
    	echo wp_kses_post($args['before_widget']);
    	if (!empty($instance['title'])) {
    		echo wp_kses_post($args['before_title']);
    		echo apply_filters( 'widget_title', $instance['title'] );
    		echo wp_kses_post($args['after_title']);
    	}		
        ?>

    	<ul class="<?php echo esc_attr($instance['classname']);?>">
    		<?php
    		foreach ($social_icons as $si) {
    			$url = trim($instance[$si]);
    			if (!empty($url)) {
    				echo "<li><a target='__blank' href='".esc_html( $url )."'><i class='fab fa-".esc_attr($si)."'></i></a></li>";
    			}
    		}
    		?>	
    	</ul>
    	<?php
      	echo wp_kses_post($args['after_widget']);
    }


    /**
     * Back-end widget form.
     *
     * @see WP_Widget::form()
     *
     * @param array $instance Previously saved values from database.
     */

    public function form($instance)
    {
        $title = !empty($instance['title']) ? $instance['title'] : '';
        $classname = isset($instance['classname']) ? $instance['classname'] : '';

        $social_icons = array(
            "facebook",
            "twitter",
            "linkedin",
            "pinterest",
            "instagram",
            "google-plus",
            "github",
            "youtube",
            "vimeo",
            "tumblr",
            "dribbble",
            "flickr",
            "behance",
            "meetup",
            "whatsapp",            
        );

        foreach ($social_icons as $sc) {
            if (!isset($instance[$sc])) {
                $instance[$sc] = '';
            }
        }

        ?>
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php echo esc_html__('Title', 'socialicons'); ?></label>
			<input class="widefat" type="text" name="<?php echo esc_attr($this->get_field_name('title')); ?>" id="<?php echo esc_attr($this->get_field_id('title')); ?>" value="<?php echo esc_attr($title); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('classname')); ?>"><?php echo esc_html__('CSS Class', 'socialicons'); ?></label>
			<input class="widefat" type="text" name="<?php echo esc_attr($this->get_field_name('classname')); ?>" id="<?php echo esc_attr($this->get_field_id('classname')); ?>" value="<?php echo esc_attr($classname); ?>" />
		</p>
		<?php
		foreach ($social_icons as $scion) {
            ?>
			<p>
				<label for="<?php echo esc_attr($this->get_field_id($scion)); ?>"><?php echo esc_html(ucfirst($scion) . " " . esc_html__("URL", "socialicons")); ?></label>
				<input class="widefat" type="text" name="<?php echo esc_attr($this->get_field_name($scion)); ?>" id="<?php echo esc_attr($this->get_field_id($scion)); ?>" value="<?php echo esc_attr($instance[$scion]); ?>" />
			</p>
			<?php
		}
    }


    /**
     * Sanitize widget form values as they are saved.
     *
     * @see WP_Widget::update()
     *
     * @param array $new_instance Values just sent to be saved.
     * @param array $old_instance Previously saved values from database.
     *
     * @return array Updated safe values to be saved.
     */

    public function update($new_instance, $old_instance)
    {
        $instance                = array();
        $instance['title']       = sanitize_text_field(strip_tags($new_instance['title']));
        $instance['classname']   = sanitize_text_field(strip_tags($new_instance['classname']));
        $instance['facebook']    = sanitize_text_field(strip_tags($new_instance['facebook']));
        $instance['twitter']     = sanitize_text_field(strip_tags($new_instance['twitter']));
        $instance['linkedin']    = sanitize_text_field(strip_tags($new_instance['linkedin']));
        $instance['pinterest']   = sanitize_text_field(strip_tags($new_instance['pinterest']));
        $instance['instagram']   = sanitize_text_field(strip_tags($new_instance['instagram']));
        $instance['google-plus'] = sanitize_text_field(strip_tags($new_instance['google-plus']));
        $instance['github']      = sanitize_text_field(strip_tags($new_instance['github']));
        $instance['youtube']     = sanitize_text_field(strip_tags($new_instance['youtube']));
        $instance['vimeo']       = sanitize_text_field(strip_tags($new_instance['vimeo']));
        $instance['tumblr']      = sanitize_text_field(strip_tags($new_instance['tumblr']));
        $instance['dribbble']    = sanitize_text_field(strip_tags($new_instance['dribbble']));
        $instance['flickr']      = sanitize_text_field(strip_tags($new_instance['flickr']));
        $instance['behance']     = sanitize_text_field(strip_tags($new_instance['behance']));
        $instance['meetup']      = sanitize_text_field(strip_tags($new_instance['meetup']));
        $instance['whatsapp']    = sanitize_text_field(strip_tags($new_instance['whatsapp']));     

        return $instance;
    }

}

function socialicons_widget_regi(){
    register_widget("ATSocialIcons");
}
add_action("widgets_init", "socialicons_widget_regi");