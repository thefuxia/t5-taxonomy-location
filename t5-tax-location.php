<?php # -*- coding: utf-8 -*-
/**
 * Plugin Name: T5 Taxonomy Location
 * Plugin URI:  https://github.com/toscho/t5-taxonomy-location
 * Text Domain: plugin_t5_tax_location
 * Domain Path: /languages
 * Description: Creates a new taxonomy for locations.
 * Version:     2012.09.11
 * Author:      Thomas Scholz <info@toscho.de>
 * Author URI:  http://toscho.de
 * License:     MIT
 * License URI: http://www.opensource.org/licenses/mit-license.php
 *
 * Copyright (c) 2012 Thomas Scholz
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */


register_activation_hook(
	__FILE__ ,
	array ( 'T5_Taxonomy_Location', 'flush_rewrite_rules' )
);
register_deactivation_hook(
	__FILE__ ,
	array ( 'T5_Taxonomy_Location', 'flush_rewrite_rules' )
);

add_action(
	'init',
	array ( 'T5_Taxonomy_Location', 'get_instance' )
);

/**
 * Register the taxonomy, load language files.
 */
class T5_Taxonomy_Location
{
	/**
	 * Plugin instance.
	 *
	 * @see get_instance()
	 * @type object
	 */
	protected static $instance = NULL;

	/**
	 * Internal name.
	 *
	 * @type string
	 */
	protected $taxonomy = 'location';

	/**
	 * Creates a new instance.
	 *
	 * @wp-hook init
	 * @since   2012.09.11
	 * @see     __construct()
	 * @return  void
	 */
	public static function get_instance()
	{
		NULL === self::$instance and self::$instance = new self;
		return self::$instance;
	}

	/**
	 * Set actions, filters and basic variables, load language.
	 *
	 * @wp-hook init
	 * @since   2012.09.11
	 * @return  void
	 */
	public function __construct()
	{
		// no translation in front-end
		is_admin() and $this->load_language();
		$this->register_taxonomy();
		add_action(
			'right_now_content_table_end',
			array ( $this, 'add_to_dashboard' )
		);
	}

	/**
	 * Register taxonomy.
	 *
	 * @wp-hook init
	 * @since   2012.09.11
	 * @return  void
	 */
	protected function register_taxonomy()
	{
		$this->set_labels();

		$args = array (
			'labels'            => $this->labels,
			'label'             => $this->labels['singular_name'],
			'public'            => TRUE,
			'show_in_nav_menus' => TRUE,
			'show_ui'           => TRUE,
			'show_tagcloud'     => TRUE,
			'rewrite'           => array (
				'slug'       => _x( 'location', 'slug', 'plugin_t5_tax_location' ),
				'with_front' => apply_filters( 't5_tax_location_slug_front', FALSE )
			),
			'query_var'         => 'location',
			'hierarchical'      => TRUE,
			// New in WordPress 3.5
			// see http://core.trac.wordpress.org/ticket/21240
			'show_admin_column' => TRUE
		);

		$tax_post_types = apply_filters(
			't5_tax_location_post_types',
			array( 'post', 'page', 'attachment' )
		);

		register_taxonomy( $this->taxonomy, $tax_post_types, $args );
	}

	/**
	 * Create taxonomy labels.
	 *
	 * @wp-hook init
	 * @since   2012.09.11
	 * @return  void
	 */
	protected function set_labels()
	{
		$labels = array (
			'name'                       =>
				__( 'Locations',                       'plugin_t5_tax_location' ),
			'singular_name'              =>
				__( 'Location',                        'plugin_t5_tax_location' ),
			'search_items'               =>
				__( 'Search locations',                'plugin_t5_tax_location' ),
			'popular_items'              =>
				__( 'Popular locations',               'plugin_t5_tax_location' ),
			'all_items'                  =>
				__( 'All locations',                   'plugin_t5_tax_location' ),
			'edit_item'                  =>
				__( 'Edit location',                   'plugin_t5_tax_location' ),
			'update_item'                =>
				__( 'Update location',                 'plugin_t5_tax_location' ),
			'add_new_item'               =>
				__( 'Add new location',                'plugin_t5_tax_location' ),
			'new_item_name'              =>
				__( 'New location',                    'plugin_t5_tax_location' ),
			'separate_items_with_commas' =>
				__( 'Separate locations with commas',  'plugin_t5_tax_location' ),
			'add_or_remove_items'        =>
				__( 'Add or remove locations',         'plugin_t5_tax_location' ),
			'choose_from_most_used'      =>
				__( 'Choose from most used locations', 'plugin_t5_tax_location' )
		);

		$this->labels = apply_filters( 't5_tax_location_labels', $labels );
	}

	/**
	 * Show name and number in the Right Now dashboard widget.
	 *
	 * @wp-hook right_now_content_table_end
	 * @return  void
	 */
	public function add_to_dashboard()
	{
		$num  = wp_count_terms( $this->taxonomy );
		// thousands separator etc.
		$num  = number_format_i18n( $num );
		// Singular or Plural.
		$text = _n(
			$this->labels['singular_name'],
			$this->labels['name'],
			$num,
			'plugin_t5_tax_location'
		);

		if ( current_user_can( "manage_$this->taxonomy" ) )
		{
			$url  = admin_url( "edit-tags.php?taxonomy=$this->taxonomy" );
			$num  = "<a href='$url'>$num</a>";
			$text = "<a href='$url'>$text</a>";
		}

		$this->print_dashboard_row( $this->labels['name'], $num, $text );
	}

	/**
	 * Prints a table row in the right now widget.
	 *
	 * Helper for add_to_dashboard()
	 *
	 * @wp-hook right_now_content_table_end
	 * @param   string $name Taxonomy name
	 * @param   int    $num Amount of taxonomy items
	 * @param   string $text Public name of the item
	 * @return  void
	 */
	protected function print_dashboard_row( $name, $num, $text )
	{
		echo "<td class='first b b-{$name}s'>$num</td>
		<td class='t {$name}s'>$text</td></tr><tr>";
	}

	/**
	 * Loads translation file.
	 *
	 * @wp-hook init
	 * @since   2012.09.11
	 * @return  void
	 */
	protected function load_language()
	{
		$loaded = load_plugin_textdomain(
			'plugin_t5_tax_location',
			FALSE,
			plugin_basename( __DIR__ ) . '/languages'
		);
	}

	/**
	 * Add the taxonomy slug to rewrite rules after the taxonomy is registered.
	 *
	 * @wp-hook activate
	 * @wp-hook deactivate
	 * @since   2012.09.11
	 * @return  void
	 */
	public static function flush_rewrite_rules()
	{
		// no need to register the taxonomy on deactivation
		if ( 'deactivate_' . plugin_basename( __FILE__) === current_filter() )
		{
			remove_action( 'init', array ( __CLASS__, 'get_instance' ) );
		}

		add_action( 'init', 'flush_rewrite_rules', 11 );
	}
}