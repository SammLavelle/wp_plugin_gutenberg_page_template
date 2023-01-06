<?php
   /*
    Plugin Name: Gutenberg Stylesheet
    Description: Adds a page template with skeleton styling for Gutenberg
    Version: 1
    Author: Samantha Lavelle
    Requires at least: 5
   */


require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
/*register new page template*/
class PageTemplater {

	/**
	 * A reference to an instance of this class.
	 */
	private static $instance;

	/**
	 * The array of templates that this plugin tracks.
	 */
	protected $templates;

	/**
	 * Returns an instance of this class. 
	 */
	public static function get_instance() {

		if ( null == self::$instance ) {
			self::$instance = new PageTemplater();
		} 

		return self::$instance;

	} 

	/**
	 * Initializes the plugin by setting filters and administration functions.
	 */
	private function __construct() {

		$this->templates = array();


		// Add a filter to the attributes metabox to inject template into the cache.
		if ( version_compare( floatval( get_bloginfo( 'version' ) ), '4.7', '<' ) ) {

			// 4.6 and older
			add_filter(
				'page_attributes_dropdown_pages_args',
				array( $this, 'register_project_templates' )
			);

		} else {

			// Add a filter to the wp 4.7 version attributes metabox
			add_filter(
				'theme_page_templates', array( $this, 'add_new_template' )
			);

		}

		// Add a filter to the save post to inject out template into the page cache
		add_filter(
			'wp_insert_post_data', 
			array( $this, 'register_project_templates' ) 
		);


		// Add a filter to the template include to determine if the page has our 
		// template assigned and return it's path
		add_filter(
			'template_include', 
			array( $this, 'view_project_template') 
		);


		// Add your templates to this array.
		$this->templates = array(
			'page-full-width-gutenberg.php' => 'Full Width Gutenberg',
		);
			
	} 

	/**
	 * Adds our template to the page dropdown for v4.7+
	 *
	 */
	public function add_new_template( $posts_templates ) {
		$posts_templates = array_merge( $posts_templates, $this->templates );
		return $posts_templates;
	}

	/**
	 * Adds our template to the pages cache in order to trick WordPress
	 * into thinking the template file exists where it doens't really exist.
	 */
	public function register_project_templates( $atts ) {

		// Create the key used for the themes cache
		$cache_key = 'page_templates-' . md5( get_theme_root() . '/' . get_stylesheet() );

		// Retrieve the cache list. 
		// If it doesn't exist, or it's empty prepare an array
		$templates = wp_get_theme()->get_page_templates();
		if ( empty( $templates ) ) {
			$templates = array();
		} 

		// New cache, therefore remove the old one
		wp_cache_delete( $cache_key , 'themes');

		// Now add our template to the list of templates by merging our templates
		// with the existing templates array from the cache.
		$templates = array_merge( $templates, $this->templates );

		// Add the modified cache to allow WordPress to pick it up for listing
		// available templates
		wp_cache_add( $cache_key, $templates, 'themes', 1800 );

		return $atts;

	} 

	/**
	 * Checks if the template is assigned to the page
	 */
	public function view_project_template( $template ) {
		
		// Get global post
		global $post;

		// Return template if post is empty
		if ( ! $post ) {
			return $template;
		}

		// Return default template if we don't have a custom one defined
		if ( ! isset( $this->templates[get_post_meta( 
			$post->ID, '_wp_page_template', true 
		)] ) ) {
			return $template;
		} 

		$file = plugin_dir_path( __FILE__ ). get_post_meta( 
			$post->ID, '_wp_page_template', true
		);

		// Just to be safe, we check if the file exist first
		if ( file_exists( $file ) ) {
			return $file;
		} else {
			echo $file;
		}

		// Return template
		return $template;

	}

} 
add_action( 'plugins_loaded', array( 'PageTemplater', 'get_instance' ) );


/*load up stylesheet if page template is selected*/
function enqueue_styles() {
   $plugin_url = plugin_dir_url( __FILE__ );

	if(is_page_template('page-full-width-gutenberg.php')){
	
		wp_enqueue_style( 'full-width-gutenberg-css', $plugin_url . '/full-width-gutenberg.css' );
	
	};

};

add_action( 'wp_enqueue_scripts', 'enqueue_styles' );

/*made sure gutenberg theme support is included*/
add_theme_support('wp-block-styles');
add_theme_support('align-wide');
add_theme_support('responsive-embeds');

/*add width options to customiser*/
function customize_register( $wp_customize ) {
    $wp_customize->add_section( 
        'gutenberg_style_settings', array(
            'title' => __( 'Gutenberg Style Settings' ),
            'priority'   => 20,
        ) 
    );
    $wp_customize->add_setting(  
        'max_width', array(
            'default' => '',
            'type' => 'text',
            'capability' => 'edit_theme_options'
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Control(
            $wp_customize, // WP_Customize_Manager
            'max_width', array(
                'label'      => __( 'Max Content Width (please specify units e.g. px or rem)', 'textdomain' ),
                'priority'   => 10,
                'section'    => 'gutenberg_style_settings',
                'type'       => 'text',
            )
        )
    );
};
add_action( 'customize_register', 'customize_register' );

/*stuff to add: 
create a plugin settings page from which users can set:
- default widths
- gutenberg colours?
*/