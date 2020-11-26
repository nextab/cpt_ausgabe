# cpt_ausgabe
Shortcode function to output posts in WordPress (useful for custom post types)

Here is one possible use: Create a second function inside your child theme's functions.php, allow it to be called with a shortcode, set new default parameters and call the other function.


// Call the cpt_output shortcode and set the parameters to post company banners of all the premiere and premiere elite sponsors
function premiere_elite_shortcode($atts) {
	$a = shortcode_atts(array(
		'category' => '',
		'container_class' => 'standardbanner',
		'meta_key' => '_package_id',
		'meta_value' => '1316',
		'order' => 'RAND',
		'orderby' => 'title',
		'output_format' => 'company-banners',
		'pagination' => 'no',
		'post_status' => 'publish',
		'post_type' => 'job_listing',
		'ppp' => '200',
		'show_description' => 'no',
		'show_title' => 'no',
		'taxonomy' => 'job_listing_category',
	), $atts);
	return cpt_output_shortcode($a);
}
add_shortcode('premiere-elite', 'premiere_elite_shortcode');
