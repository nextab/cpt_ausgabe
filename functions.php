// This shortcode allows you to output all posts of a particular post type (default = sponsors). You can define the output by using some of the shortcode attributes, e.g. "output_format=> featured-images", etc. The output is sorted alphabetically by default
function cpt_output_shortcode($atts) {
	global $pagenow;
	$all_sponsors_output = '';
	if (($pagenow == 'post.php') && ((get_post_type() == 'post') || (get_post_type() == 'page'))) {
		// editing a page
		return '<div class="nxt-info"><h3>Banners will be displayed here</h3></div>';
	}
	$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
	$a = shortcode_atts(array(
		'category' => '',
		'compare_mq' => 'LIKE',
		'container_class' => '',
		'meta_key' => '',
		'meta_relation' => 'AND',
		'meta_value' => '',
		'order' => 'ASC',
		'orderby' => 'title',
		'output_format' => 'titles-links',
		'paged' => $paged,
		'pagination' => 'no',
		'post_status' => 'publish',
		'post_type' => 'sponsors',
		'ppp' => '200',
		'show_description' => 'no',
		'show_title' => 'no',
		'taxonomy' => '',
	), $atts);
	// Set parameters for WP Query by reading out the attributes that were entered for the shortcode
	
	$args = array(
		'order' => $a["order"],
		'orderby' => $a["orderby"],
		'paged' => $paged,
		'post_status' => $a["post_status"],
		'post_type' => $a["post_type"],
		'posts_per_page' => $a["ppp"],
	);
	if(is_single()) {
		$args['post__not_in'] = [get_the_ID()];
	}
	// Check if a category has been set and whether we are outputting a custom post type or a regular post
	if ($a['category'] != '' && $a['post_type'] != 'post') {
		$tax_query = array(
			array(
				'taxonomy' => ($a['taxonomy'] == '') ? 'sponsor-categories' : $a['taxonomy'],
				'field' => 'slug',
				'terms' => $a["category"],
			),
		);
		$args['tax_query'] = $tax_query;
	} elseif ($a['category'] != '' && $a['post_type'] == 'post') {
		$args['category_name'] = $a['category'];
	}
	// Check if we need to filter the output and only show posts that have a distinct meta value 
	if (!$a['meta_key'] == '' && !$a['meta_value'] == '') {
		if ($a['meta_key'] == 'wpcf-data-of-event' && $a['compare_mq'] == ">=") {
			$args['meta_query'] = array(
				'relation' => 'OR',
				array(
					'compare' => ">=",
					'key' => 'wpcf-data-of-event',
					'value' => $a['meta_value'],
				),
				array(
					'compare' => '=',
					'key' => 'wpcf-data-of-event',
					'value' => '',
				),
			);
		} elseif ($a['meta_key'] == 'wpcf-data-of-event' && $a['compare_mq'] == "<") {
			$args['meta_query'] = array(
				'relation' => 'AND',
				array(
					'compare' => "<",
					'key' => 'wpcf-data-of-event',
					'value' => $a['meta_value'],
				),
				array(
					'compare' => '!=',
					'key' => 'wpcf-data-of-event',
					'value' => '',
				),
			);
		} elseif($a['meta_value'] == 'diamond+emerald') {
			$args['meta_query'] = [
				'relation' => 'OR',
				[
					'compare' => '=',
					'key' => $a['meta_key'],
					'value' => '7377',
				],
				[
					'compare' => '=',
					'key' => $a['meta_key'],
					'value' => '7379',	
				],
			];
		} else {
			$args['meta_query'] = [
				[
					'compare' => $a['compare_mq'],
					'key' => $a['meta_key'],
					'value' => $a['meta_value'],
				],
			];
		}
	}
	$args = apply_filters('cpt_query_args', $args);
	// print_r($args);
	$all_sponsors_loop = new WP_Query($args);
	if ($all_sponsors_loop->have_posts()) :
		$post_count = 0;
		$all_sponsors_output = '<div class="gallery ' . $a["container_class"] . '">';
		while ($all_sponsors_loop->have_posts()) : $all_sponsors_loop->the_post();
			// $all_sponsors_output .= '<div class="gallery-item"><dl><dt><img class="alignnone size-full" src="' . get_the_post_thumbnail_url() . '" alt="' . get_the_title() . '"></dt></dl></div>';
			// $post_count++;
			$nxt_id = get_the_ID();
			$nxt_title = get_the_title($nxt_id);
			$nxt_permalink = get_the_permalink($nxt_id);
			switch ($a['output_format']) {
				case 'company-banners':
					if (get_post_meta($nxt_id, '_company_video', true) != '') {
						$banner_url = get_post_meta($nxt_id, '_company_video', true);
					} else {
						$banner_url = get_the_post_thumbnail_url($nxt_id, 'full');
					}
					$all_sponsors_output .= '<div class="single-cpt"><a href="' . $nxt_permalink . '" title="' . $nxt_title . '"><img src="' . $banner_url . '" alt="' . $nxt_title . '" /></a>';
					if ($a['show_description'] == 'yes') {
						$all_sponsors_output .= '<div class="single-desc">' . nxt_truncate(get_the_content(), 50) . '</div>';
					}
					$all_sponsors_output .= '</div>';
					break;
				case 'featured-images':
					if ($post_count++ % 2 == '0') {
						$all_sponsors_output .= '<div class="single-cpt"><div class="single-link"><a href="' . $nxt_permalink . '" title="' . $nxt_title . '"><img src="' . get_the_post_thumbnail_url($nxt_id, 'full') . '" alt="' . $nxt_title . '" /></a></div>';
					} else {
						$all_sponsors_output .= '<div class="single-link"><a href="' . $nxt_permalink . '" title="' . $nxt_title . '"><img src="' . get_the_post_thumbnail_url($nxt_id, 'full') . '" alt="' . $nxt_title . '" /></a></div></div>';
					}
					break;
				case 'event-list':
					$event_start = get_field('wpcf-date-start'); // get_post_meta($nxt_id, 'wpcf-date-start', true);
					$event_end = get_field('wpcf-date-end'); // get_post_meta($nxt_id, 'wpcf-date-end', true);
					$event_start_output = ($event_start == '') ? 'tba' : date("F d, Y", strtotime($event_start));
					$event_end_output = ($event_end == '') ? '' : ' - ' . date("F d, Y", strtotime($event_end));
					$all_sponsors_output .= '<div class="single-event"><p><a href="' . $nxt_permalink . '" title="' . $nxt_title . '">' . $nxt_title . '</a></p><p class="event-meta">' . $event_start_output . $event_end_output . '</p></div> <!-- .single-event -->';
					break;
				case 'news':
					// $nxt_news_date = nxt_pretty_date_return('wpcf-news-date', 'l, F d, Y');
					// if($nxt_news_date == '') { get_the_date( 'l, F j, Y' ); }
					if ($a['post_type'] == 'data-centre-event') {
						if (get_post_meta($nxt_id, 'wpcf-data-of-event', true) == '') {
							$nxt_news_date = '<div class="month-year-holder">tba</div>';
						} else {
							$nxt_news_date = '<div class="day">' . nxt_pretty_date_return('wpcf-data-of-event', 'd') . '</div><div class="month-year-holder"><div class="month">' . nxt_pretty_date_return('wpcf-data-of-event', 'M') . '</div><div class="year">' . nxt_pretty_date_return('wpcf-data-of-event', 'Y') . '</div></div>';
						}
					} else {
						if (get_post_meta($nxt_id, 'wpcf-news-date', true) == '') {
							$nxt_news_date = '<div class="month-year-holder">tba</div>';
						} else {
							$nxt_news_date = '<div class="day">' . nxt_pretty_date_return('wpcf-news-date', 'd') . '</div><div class="month-year-holder"><div class="month">' . nxt_pretty_date_return('wpcf-news-date', 'M') . '</div><div class="year">' . nxt_pretty_date_return('wpcf-news-date', 'Y') . '</div></div>';
						}
					}
					if($a["post_type"] == 'data-centre-event') {
						$nxt_news_excerpt = get_field('wpcf-event-excerpt');
					} else {
						$nxt_news_excerpt = (has_excerpt()) ? get_the_excerpt() : nxt_truncate(get_the_content(), 50, '...', true);
					}
					$all_sponsors_output .= '<article class="single-news"><div class="news-meta">' . $nxt_news_date . '</div><div class="news-content"><h4><a href="' . $nxt_permalink . '" title="' . $nxt_title . '">' . $nxt_title . '</a></h4><div class="nxt_news_excerpt">' . $nxt_news_excerpt . '</div><a href="' . $nxt_permalink . '" title="' . $nxt_title . '" class="button et_pb_button read_news_button">Read more</a></div></article> <!-- .single-news -->';
					break;
				case 'white_papers':
					$all_sponsors_output .= '<article class="single-whitepaper"><div class="header-container"><h4 class="white-paper-header"><a href="' . $nxt_permalink . '" title="' . $nxt_title . '">' . $nxt_title . '</a></h4></div> <!-- .header-container -->';
					// $all_sponsors_output .= '<div class="meta-container"><p class="white-paper-meta">Published on ' . get_the_date() . '</p></div> <!-- .meta-container -->';
					$nxt_news_excerpt = (has_excerpt()) ? get_the_excerpt() : nxt_truncate(get_the_content(), 50, '...', true);
					$all_sponsors_output .= '<div class="content-container"><div class="featured-image">' . get_the_post_thumbnail() . '</div> <!-- .featured-image --> <div class="post-content">' . $nxt_news_excerpt . '</div> <!-- .post-content --></div> <!-- .content-container -->';
					if($a['category'] == "white-papers") {
						$all_sponsors_output .= nxt_whitepaper_button_output($nxt_id, $nxt_title);
					} elseif($a['category'] == "blog") {
						$all_sponsors_output .= '<div class="et_pb_button_module_wrapper et_pb_module"><a class="et_pb_button et_pb_bg_layout_light" href="' . get_the_permalink($nxt_id) . '">Read more</a></div>';
					} else {
						$all_sponsors_output .= nxt_whitepaper_button_output($nxt_id, $nxt_title, 'case_study');
					}
					// $all_sponsors_output .= ($a['category'] == 'white-papers') ? nxt_whitepaper_button_output($nxt_id, $nxt_title,'whitepaper') : nxt_whitepaper_button_output($nxt_id, $nxt_title, 'case_study');
					$all_sponsors_output .= '</article> <!-- .single-whitepaper -->';
					break;
				default:
					$all_sponsors_output .= '<div class="single-cpt">Title: ' . $nxt_title . ' ID: ' . $nxt_id . ' <a href="/wp-admin/post.php?post=' . $nxt_id . '&action=edit" target="_blank">Edit Post</a></div> <!-- .single-cpt -->';
					break;
			}
		endwhile;
		if (substr($all_sponsors_output, 0, -12) != '</div></div>' && $a['output_format'] == 'featured-images') {
			$all_sponsors_output .= '</div>';
		}
		if ($a['pagination'] == 'yes') {
			if (function_exists('wp_pagenavi'))
				$all_sponsors_output .= wp_pagenavi(array(
					'query' => $all_sponsors_loop,
					'echo' => false,
				));
			else
				$all_sponsors_output .= get_template_part('includes/navigation', 'index');
			// $all_sponsors_output .= '<div class="pagination"><div class="previous_post_link">' . get_previous_post_link() . '</div><div class="next_post_link">' . get_next_post_link() . '</div></div>'; 
		}
		$all_sponsors_output .= '</div> <!-- .gallery -->';
	endif;
	wp_reset_postdata();

	return $all_sponsors_output;
}
