<?php
/**
 * @package Categories Recent Post
 * @version 1.0
 */
/*
Plugin Name: Categories Recent Post
Plugin URI: http://saokim.com.vn
Description: A widget that can display posts from checked categories and thumbnail image if post have <strong>set featured image</strong>
Author: Nguyen Duc Manh
Version: 1.0
Author URI: http://saokim.com.vn
*/

// Register thumbnail sizes.
if ( function_exists('add_image_size') )
{
	$sizes = get_option('sk_cat_post_thumb_sizes');
	if ( $sizes )
	{
		foreach ( $sizes as $id=>$size )
			add_image_size( 'cat_post_thumb_size' . $id, $size[0], $size[1], true );
	}
}

class Category_Recent_posts extends WP_Widget {
	function Category_Recent_posts() {
		parent::WP_Widget(false, $name='Categories Recent Post',array( 'description' =>'Display recent posts from checked categories'));
	}
	
	function widget($args, $instance)
	{
		// outputs the content of the widget
		extract( $args );
		$sizes = get_option('sk_cat_post_thumb_sizes');
		
		if($instance['use_timthumb']){
			//++++ Create cache and CHMOD to 777
			$plugin_url	=	plugins_url();
			
		}

		echo $before_widget;
			echo $before_title . $instance["title"]. $after_title;
			//Noi dung widget
			$catArr	=	$instance["selected_cats"];
			$strCat	=	"";
			if(!empty($catArr))
			{
				foreach($catArr as $value){
					$strCat	.=	$value.",";		
				}
				$strCat	=	substr($strCat,0,strlen($strCat)-1);
			}
			
			$valid_sort_orders = array('date', 'title', 'comment_count', 'random');
			 if ( in_array($instance['sort_by'], $valid_sort_orders) ) {
				$sort_by = $instance['sort_by'];
				$sort_order = (bool) $instance['asc_sort_order'] ? 'ASC' : 'DESC';
			  } else {
				$sort_by = 'date';
				$sort_order = 'DESC';
			 }
			
			$cat_posts = new WP_Query(
				"showposts=" . $instance["num"] . 
				"&cat=" . $strCat .
				"&orderby=" . $sort_by .
				"&order=" . $sort_order
			  );
			// Post list
			echo '<ul class="'.$instance["class"].'">';
			
			while ( $cat_posts->have_posts() )
			{
				$cat_posts->the_post();
			?>
				<li class="cat-post-item">
					<?php
						if (
							function_exists('the_post_thumbnail') &&
							current_theme_supports("post-thumbnails") &&
							$instance["thumb"] &&
							has_post_thumbnail()
						) :
					?>
                     <?php if($instance['use_timthumb']):?> <!-- Check use timthumb -->
					  	<a href="<?php the_permalink(); ?>">
							<?php 
								$w	=	$instance['thumb_w']?$instance['thumb_w']:50;
								$h	=	$instance['thumb_h']?$instance['thumb_h']:50;
								$thumb_url = wp_get_attachment_url( get_post_thumbnail_id($post->ID) );
							?>
                            <img src="<?php echo plugins_url(); ?>/categories-recent-posts/timthumb.php?src=<?php echo $thumb_url; ?>&w=<?php echo $w; ?>&h=<?php echo $h; ?>"  />
                        </a>
                      <?php else:?>
                      	<a href="<?php the_permalink(); ?>"><?php the_post_thumbnail( 'cat_post_thumb_size'.$this->id, 'class=post-list-img' ); ?></a>
                      <?php endif; ?><!-- End check timthumb -->
					
					<?php endif; ?>
                    
					<a class="post-title" href="<?php the_permalink(); ?>" rel="bookmark" title="Permanent link to <?php the_title_attribute(); ?>"><?php the_title(); ?></a>
					
					<?php if($instance['show_comment_num']):?>
						<div class="comment-num"><?php comments_number( '(No Comments)', '(1 Comment)', '(% Comments)' ); ?></div>
					<?php endif;?>
                    
                    <?php if($instance['show_post_date']):?>
						<div class="post-date">Date posted: <?php the_date(); ?></div>
					<?php endif;?>
                    
					<?php
						if($instance['show_excerpt']){
							$new_excerpt_length = create_function('$length', "return " . $instance["excerpt_length"] . ";");
							if ( $instance["excerpt_length"] > 0 ){
								add_filter('excerpt_length', $new_excerpt_length);
							}	
							the_excerpt();
						}
					?>
				</li>
			<?php
			}
			wp_reset_query();
			echo "</ul>";
			
		echo $after_widget;
		remove_filter('excerpt_length', $new_excerpt_length);
	}
	
	function form($instance) {
		// outputs the options form on admin
		$selected_cats	=	$instance["selected_cats"];	
		?>
		<p>
			<label for="<?php echo $this->get_field_id("title"); ?>">
				<?php _e( 'Title' ); ?>:
				<input class="widefat" id="<?php echo $this->get_field_id("title"); ?>" name="<?php echo $this->get_field_name("title"); ?>" type="text" value="<?php echo esc_attr($instance["title"]); ?>" />
			</label>
		</p>
        <p>
			<label for="<?php echo $this->get_field_id("class"); ?>">
				<?php _e( 'Class' ); ?>:
				<input class="widefat" id="<?php echo $this->get_field_id("class"); ?>" name="<?php echo $this->get_field_name("class"); ?>" type="text" value="<?php echo esc_attr($instance["class"]); ?>" />
			</label>
		</p>
        <p>
			<label for="<?php echo $this->get_field_id("num"); ?>">
				<?php _e('Number of posts to show'); ?>:
				<input style="text-align: center;" id="<?php echo $this->get_field_id("num"); ?>" name="<?php echo $this->get_field_name("num"); ?>" type="text" value="<?php echo absint($instance["num"]); ?>" size='3' />
			</label>
   		 </p>

        <p>
           <label for="<?php echo $this->get_field_id("sort_by"); ?>">
            <?php _e('Sort by'); ?>:
            <select id="<?php echo $this->get_field_id("sort_by"); ?>" name="<?php echo $this->get_field_name("sort_by"); ?>">
              <option value="date"<?php selected( $instance["sort_by"], "date" ); ?>>Date</option>
              <option value="title"<?php selected( $instance["sort_by"], "title" ); ?>>Title</option>
              <option value="comment_count"<?php selected( $instance["sort_by"], "comment_count" ); ?>>Number of comments</option>
              <option value="random"<?php selected( $instance["sort_by"], "random" ); ?>>Random</option>
            </select>
                </label>
        </p>
		
        <p>
            <label for="<?php echo $this->get_field_id("asc_sort_order"); ?>">
          <input type="checkbox" class="checkbox" 
          id="<?php echo $this->get_field_id("asc_sort_order"); ?>" 
          name="<?php echo $this->get_field_name("asc_sort_order"); ?>"
          <?php checked( (bool) $instance["asc_sort_order"], true ); ?> />
                <?php _e( 'Reverse sort order (ascending)' ); ?>
            </label>
         </p>
         
         
         <?php if ( function_exists('the_post_thumbnail') && current_theme_supports("post-thumbnails") ) : ?>
		<p>
			<label for="<?php echo $this->get_field_id("thumb"); ?>">
				<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id("thumb"); ?>" name="<?php echo $this->get_field_name("thumb"); ?>"<?php checked( (bool) $instance["thumb"], true ); ?> />
				<?php _e( 'Show post thumbnail' ); ?>
			</label>
		</p>
        <p>
				<?php _e('Thumbnail dimensions'); ?>:<br />
				<label for="<?php echo $this->get_field_id("thumb_w"); ?>">
					W: <input class="widefat" style="width:40%;" type="text" id="<?php echo $this->get_field_id("thumb_w"); ?>" name="<?php echo $this->get_field_name("thumb_w"); ?>" value="<?php echo $instance["thumb_w"]; ?>" /></label>
				
				
				<label for="<?php echo $this->get_field_id("thumb_h"); ?>">
					H: <input class="widefat" style="width:40%;" type="text" id="<?php echo $this->get_field_id("thumb_h"); ?>" name="<?php echo $this->get_field_name("thumb_h"); ?>" value="<?php echo $instance["thumb_h"]; ?>" /></label>
				
		</p>
        <p>
            <label for="<?php echo $this->get_field_id("use_timthumb"); ?>" title="Please CHMOD /wp-content/plugins/categories-recent-posts/cache   to 0777">
                <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id("use_timthumb"); ?>" name="<?php echo $this->get_field_name("use_timthumb"); ?>"<?php checked( (bool) $instance["use_timthumb"], true ); ?> />
                <?php _e( 'Use timthumb to resize image' ); ?>
            </label>
        </p>
		<?php endif; ?>
        
        <p>
			<label for="<?php echo $this->get_field_id("show_excerpt"); ?>">
				<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id("show_excerpt"); ?>" name="<?php echo $this->get_field_name("show_excerpt"); ?>"<?php checked( (bool) $instance["show_excerpt"], true ); ?> />
				<?php _e( 'Show excerpt' ); ?>
			</label>
		</p>
        <p>
        	<label for="<?php echo $this->get_field_id("excerpt_length"); ?>">
					Excerpt length: <input class="widefat" style="width:20%;" type="text" id="<?php echo $this->get_field_id("excerpt_length"); ?>" name="<?php echo $this->get_field_name("excerpt_length"); ?>" value="<?php echo $instance["excerpt_length"]; ?>" /></label>
        </p>
        
        <p>
			<label for="<?php echo $this->get_field_id("show_comment_num"); ?>">
				<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id("show_comment_num"); ?>" name="<?php echo $this->get_field_name("show_comment_num"); ?>"<?php checked( (bool) $instance["show_comment_num"], true ); ?> />
				<?php _e( 'Show number of comment' ); ?>
			</label>
		</p>
        <p>
			<label for="<?php echo $this->get_field_id("show_post_date"); ?>">
				<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id("show_post_date"); ?>" name="<?php echo $this->get_field_name("show_post_date"); ?>"<?php checked( (bool) $instance["show_post_date"], true ); ?> />
				<?php _e( 'Show post date' ); ?>
			</label>
		</p>
         
        <?php
				echo '			<b>Recent post in categories</b><hr />'; 
				echo '			<ul id="categorychecklist" class="list:category categorychecklist form-no-clear" style="list-style-type: none; margin-left: 5px; padding-left: 0px; margin-bottom: 20px;">';

				saokim_wp_category_checklist(0, 0, $selected_cats, false);  
				echo '			</ul>'; 
	}

	function update($new_instance, $old_instance) { 
		// processes widget options to be saved
		$instance = $old_instance;
		/*$instance['title'] 		= strip_tags($new_instance['title']);*/
		$instance['post_category']	=	serialize($_POST['post_category']);
		$new_instance['selected_cats']	=	($instance['post_category'] != '') ? unserialize($instance['post_category']) : false;
		if ( function_exists('the_post_thumbnail') )
		{
			$sizes = get_option('sk_cat_post_thumb_sizes'); 
			if ( !$sizes ) $sizes = array();
			$sizes[$this->id] = array($new_instance['thumb_w'], $new_instance['thumb_h']);
			update_option('sk_cat_post_thumb_sizes', $sizes);
		}
        return $new_instance;
	}
}


class SAOKIM_Walker_Category_Checklist extends Walker
{
	var $tree_type = 'category';
	var $db_fields = array('parent'=>'parent', 'id'=>'term_id'); //TODO: decouple this
	var $number;

	function start_lvl (&$output, $depth, $args)
	{
		$indent = str_repeat("\t", $depth);
		$output .= "$indent<ul class='children'>\n";
	}

	function end_lvl (&$output, $depth, $args)
	{
		$indent = str_repeat("\t", $depth);
		$output .= "$indent</ul>\n";
	}

	function start_el (&$output, $category, $depth, $args)
	{
		extract($args);
		
		$class = in_array($category->term_id, $popular_cats) ? ' class="popular-category"' : '';
		$output .= "\n<li id='category-$category->term_id-$this->number'$class>" . '<label class="selectit"><input value="' . $category->term_id . '" type="checkbox" name="post_category[]" id="post_category_' . $category->term_id . '"' . (in_array($category->term_id, $selected_cats) ? ' checked="checked"' : "") . '/> ' . wp_specialchars(apply_filters('the_category', $category->name)) . '</label>';
	}

	function end_el (&$output, $category, $depth, $args)
	{
		$output .= "</li>\n";
	}
}

/**
 * Creates the categories checklist
 *
 * @param int $post_id
 * @param int $descendants_and_self
 * @param array $selected_cats
 * @param array $popular_cats
 * @param int $number
 */
function saokim_wp_category_checklist ($post_id = 0, $descendants_and_self = 0, $selected_cats = false, $popular_cats = false)
{
	$walker = new SAOKIM_Walker_Category_Checklist();
	
	$descendants_and_self = (int) $descendants_and_self;
	
	$args = array();
	if (is_array($selected_cats))
		$args['selected_cats'] = $selected_cats;
	elseif ($post_id)
		$args['selected_cats'] = wp_get_post_categories($post_id);
	else
		$args['selected_cats'] = array();
	
	if (is_array($popular_cats))
		$args['popular_cats'] = $popular_cats;
	else
		$args['popular_cats'] = get_terms('category', array('fields'=>'ids', 'orderby'=>'count', 'order'=>'DESC', 'number'=>10, 'hierarchical'=>false));
	
	if ($descendants_and_self) {
		$categories = get_categories("child_of=$descendants_and_self&hierarchical=0&hide_empty=0");
		$self = get_category($descendants_and_self);
		array_unshift($categories, $self);
	} else {
		$categories = get_categories('get=all');
	}
	
	// Post process $categories rather than adding an exclude to the get_terms() query to keep the query the same across all posts (for any query cache)
	$checked_categories = array();
	for ($i = 0; isset($categories[$i]); $i ++) {
		if (in_array($categories[$i]->term_id, $args['selected_cats'])) {
			$checked_categories[] = $categories[$i];
			unset($categories[$i]);
		}
	}
	
	// Put checked cats on top
	echo call_user_func_array(array(&$walker, 'walk'), array($checked_categories, 0, $args));
	// Then the rest of them
	echo call_user_func_array(array(&$walker, 'walk'), array($categories, 0, $args));
}

add_action( 'widgets_init', create_function('', 'return register_widget("Category_Recent_posts");') );