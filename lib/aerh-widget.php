<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Omar
 * Date: 13/12/13
 * Time: 8:26 PM
 * To change this template use File | Settings | File Templates.
 */

class Aragon_eRH_Widget extends WP_Widget {

    function Aragon_eRH_Widget() {
        $widget_ops = array('classname' => 'Aragon_eRH_Widget', 'description' => 'Displays the list of positions' );
        $this->WP_Widget('Aragon_eRH_Widget', 'Position List', $widget_ops);
    }

    function form($instance) {
        $instance = wp_parse_args( (array) $instance, array( 'title' => '' , 'widget_height' => '', 'widget_scroll' => '') );
        $title = $instance['title'];
        $widget_height = $instance['widget_height'];
        $widget_scroll = $instance['widget_scroll'];
        ?>
    <p><label for="<?php echo $this->get_field_id('title'); ?>">Title: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label></p>
    <p><label for="<?php echo $this->get_field_id('widget_height'); ?>">Height in pixels (e.g., 100): <input class="widefat" id="<?php echo $this->get_field_id('widget_height'); ?>" name="<?php echo $this->get_field_name('widget_height'); ?>" type="text" value="<?php echo esc_attr($widget_height); ?>" /></label></p>
    <p><label for="<?php echo $this->get_field_id('widget_scroll'); ?>">Scroll widget?: <input id="<?php echo $this->get_field_id('widget_scroll'); ?>" name="<?php echo $this->get_field_name('widget_scroll'); ?>" type="checkbox" value="true" <?php echo $widget_scroll ? 'checked' : '' ?> /></label></p>
    <?php
    }

    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance['title'] = $new_instance['title'];
        if (is_numeric($new_instance['widget_height'])) {
            $instance['widget_height'] = $new_instance['widget_height'];
        } else {
            $instance['widget_height'] = '';
        }
        $instance['widget_scroll'] = $new_instance['widget_scroll'];
        return $instance;
    }

    function widget($args, $instance) {
        extract($args, EXTR_SKIP);

        echo $before_widget;
        $title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);

        $widget_height = $instance['widget_height'];
        $widget_scroll = $instance['widget_scroll'];

        if (!empty($title))
            echo $before_title . $title . $after_title;;

        // WIDGET CODE GOES HERE
        $core = new Aragon_eRH();
        echo $core->create_list($widget_height, $widget_scroll);

        echo $after_widget;
    }

}