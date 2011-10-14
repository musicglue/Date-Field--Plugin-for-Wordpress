<?php

/**
 *
 * Date Field widgets 
 *
 */

add_action('widgets_init', create_function('', 'return register_widget("df_widget");'));
class df_widget extends WP_Widget {
	
	function df_widget() {
		$widget_ops = array('classname' => 'df_widget', 'description' => __( 'Show posts by date field. View future/past posts & view posts on calendar.') );
		$this->WP_Widget('df_widget', __('Date Field'), $widget_ops);
		
		if ( is_active_widget(false, false, $this->id_base, true) ) {
			add_action( 'wp_enqueue_scripts', 'df_scripts', 100 );
			
		}
	}
	
	/** @see WP_Widget::widget */
    function widget($args, $instance) {		
        extract( $args );
        //error_log( var_export( $args, true ));
        $title = apply_filters('widget_title', $instance['title']);
        echo $before_widget;
		if ( $title )
			echo $before_title . $title . $after_title;		
		$df_args = array(
			'meta_key' => $instance['field'],
			'meta_compare' => $instance['compare'],
			'post_type' => $instance['post_type'],
		);
		$this->output( $args, $df_args, (bool) $instance['calendar'] );
		
		echo $after_widget;
    }

	function update($new_instance, $old_instance) {
		// processes widget options to be saved
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['field'] = strip_tags($new_instance['field']);
		$instance['compare'] = strip_tags($new_instance['compare']);
		$instance['post_type'] = strip_tags($new_instance['post_type']);
		$instance['calendar'] = strip_tags($new_instance['calendar']);
        return $instance;
	}

	/** @see WP_Widget::form */
    function form($instance) {				
        $title		= ( isset( $instance['title'] ) ) 		? esc_attr($instance['title']) 		: null;
        $field		= ( isset( $instance['field'] ) ) 		? esc_attr( $instance['field'] ) 	: null;
        $compare	= ( isset( $instance['compare'] ) )		? esc_attr( $instance['compare'] ) 	: null;
        $post_type	= ( isset( $instance['post_type'] ) )	? esc_attr($instance['post_type']) 	: null; 
        $calendar	= ( isset( $instance['calendar'] ) ) 	? (bool) esc_attr( $instance['calendar'] ) : null; 
        
        ?>
            <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title'); ?>: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label></p>
            <p><label for="<?php echo $this->get_field_id('field'); ?>"><?php _e('Field'); ?>: <input class="widefat" id="<?php echo $this->get_field_id('field'); ?>" name="<?php echo $this->get_field_name('field'); ?>" type="text" value="<?php echo $field; ?>" /></label></p>
            <p><label for="<?php echo $this->get_field_id('post_type'); ?>"><?php _e('Post Type'); ?>: <input class="widefat" id="<?php echo $this->get_field_id('post_type'); ?>" name="<?php echo $this->get_field_name('post_type'); ?>" type="text" value="<?php echo $post_type; ?>" /></label></p>
            <p><label for="<?php echo $this->get_field_id('compare'); ?>"><?php _e('Compare'); ?>: 
			<select id="<?php echo $this->get_field_id('compare'); ?>" name="<?php echo $this->get_field_name('compare');?>">
				<option value="past" <?php if( $compare == 'past' ) { echo 'selected="selected"'; } ?> >Past</option>
				<option value="future" <?php if( $compare == 'future' ) { echo 'selected="selected"'; } ?> >Future</option>
			</select>
			</label></p>
			<p><label for="<?php echo $this->get_field_id('calendar'); ?>"><?php _e('Calendar'); ?>: <input type="checkbox" value="true" id=<?php echo $this->get_field_id('calendar'); ?> name=<?php echo $this->get_field_name('calendar'); ?> <?php if( $calendar ) echo 'checked=checked'; ?>/></p>
        <?php 
    }

	
	function output( $args, $df_args = false, $calendar = false ) {
		
		if($df_args['meta_compare'] == 'future' ) {
			$df_args['meta_compare'] = '>';
		} else {
			$df_args['meta_compare'] = '<';
		}
		
		$defaults = array(
		   'post_type'=>'post',
		   'meta_key' => '_df_1_start',
		   'meta_compare' => '>',
		   'meta_value' => time(),
		   'orderby' => 'post_meta',
		   'order' => 'ASC'
		);
	
		$df_args = array_merge( $defaults, $df_args );
		
		if( !$df_args['meta_key'] )  {
			echo '<p>You must set a meta key to search by</p>'; 
			return;
		}
		
		$date_query = new WP_Query($df_args);
		if ($date_query->have_posts()) : ?>
	
		<ul id="<?php echo $args['widget_id']; ?>" <?php if( $calendar ) echo 'class="df_calendar"'; ?> data-date_start="<?php echo date( time() ); ?>">
			
			<?php while ($date_query->have_posts()) : $date_query->the_post(); global $post; ?>

				<?php
					$event_date = get_post_meta( $post->ID, '_df_1_start', true);
					if( ! $event_date ) 
						continue; 
				?>
				
				<li data-date="<?php echo  date('Y-m-d', $event_date ); ?>" data-timestamp="<?php echo $event_date; ?>">
					<a href="<?php the_permalink(); ?>">
						<span class="df_date"><?php echo date('d/m/y', $event_date ); ?><span class="df_separator"><?php echo apply_filters( 'df_separator', '&mdash;' ); ?></span></span><?php the_title(); ?>
					</a>
				</li>
				
			<?php endwhile; ?>

		</ul>

		<?php else : ?>
		
			<p>No upcoming events</p>
		
		<?php endif;
		
		if( $calendar ) :
			$style = "<style>
				.ui-datepicker-header	{ padding: 0.75em 0; overflow: hidden; -khtml-user-select: none; -o-user-select: none; -moz-user-select: -moz-none; -webkit-user-select: none; }
				.ui-datepicker-prev 	{ float: left; 	width: 25%; cursor: pointer; -khtml-user-select: none; -o-user-select: none; -moz-user-select: -moz-none; -webkit-user-select: none;}
				.ui-datepicker-next 	{ float: right; width: 25%; cursor: pointer; text-align: right; user-select: none; -khtml-user-select: none; -o-user-select: none; -moz-user-select: -moz-none; -webkit-user-select: none; }
				.ui-datepicker-title 	{ float: left;	width: 50%; text-align: center; font-weight: bold; }
				.ui-state-disabled span	{ color: #CCC; cursor: default; }
				tr:nth-child(even) .ui-state-disabled span 	{ color: #DDD; }
				.date_event:not(.ui-state-disabled)			{ background: #dfffcd; text-decoration: underline; }
			</style>";
			echo $style;
		endif; // $calendar
		// RESET THE QUERY
		wp_reset_query();
	}


	
}

function df_scripts(){
		
	wp_register_script( 'jquery-ui', DF_PLUGIN_URL . '/assets/jquery-ui-1.8.9.custom.min.js', 'jquery', '1.8.13', true );
	wp_enqueue_script( 'jquery-ui' );

	wp_register_script( 'df_script', DF_PLUGIN_URL . '/assets/script.js', 'jquery', '0.0.1', true );
	wp_enqueue_script( 'df_script' );

}

?>