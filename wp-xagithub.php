<?php
/*
Plugin Name: WP Xagithub Activity
Plugin URI: http://wordpress.org/extend/plugins/wp-xagithub-activity/
Description: Show your public activity of github in a widget
Version: 1.1
Author: Xavi Martínez, xaviermartinezf@me.com
Author URI: http://www.xavimartinezf.com
License: GPL2
*/
class XagithubWidget extends WP_Widget {
  function xagithubWidget() {
    $widget_ops = array('classname' => 'xagithubWidget', 'description' => 'Display your github activity' );
    $this->WP_Widget('XagithubWidget', 'xagithub', $widget_ops);
  }
  function form($instance){
    $instance = wp_parse_args( (array) $instance, array( 'title' => '','username' =>'xaviermartinezf','entries'=>'5') );
    $title = $instance['title'];
    $twitter_usr = $instance['username'];
    $nentries = $instance['entries'];
?>
  <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:') ?><input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo attribute_escape($title); ?>" /></label></p>
  
   <p><label for="<?php echo $this->get_field_id('username'); ?>"><?php _e('Username:') ?> <input class="widefat" id="<?php echo $this->get_field_id('username'); ?>" name="<?php echo $this->get_field_name('username'); ?>" type="text" value="<?php echo attribute_escape($twitter_usr); ?>" /></label></p>
   
   <p><label for="<?php echo $this->get_field_id('entries'); ?>"><?php _e('Entries:') ?> <input style="width:20pt;" class="widefat" id="<?php echo $this->get_field_id('entries'); ?>" name="<?php echo $this->get_field_name('entries'); ?>" type="text" value="<?php echo attribute_escape($nentries); ?>" /></label></p>
      
<?php
  }
  function update($new_instance, $old_instance) {
    	$instance = $old_instance;
    	$instance['title'] = $new_instance['title'];
    	$instance['username'] = $new_instance['username'];
    	$instance['entries'] = $new_instance['entries'];
    return $instance;
  }
  function widget($args, $instance) {
    extract($args, EXTR_SKIP);
 
    echo $before_widget;
    $title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);
 
    if (!empty($title))
      echo $before_title . $title . $after_title;
      
    $username =  $instance['username'];
    $username = strtolower($username);
    $entries = $instance['entries'];
   
    function getSslPage($url) {
    	$ch = curl_init();
    	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    	curl_setopt($ch, CURLOPT_HEADER, false);
    	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    	curl_setopt($ch, CURLOPT_URL, $url);
    	curl_setopt($ch, CURLOPT_REFERER, $url);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    	$result = curl_exec($ch);
    	curl_close($ch);
    return $result;
}

    $xml = getSslPage( "https://github.com/$username.atom");
		if ( !empty( $xml ) ) :
			$atom = simplexml_load_string( $xml );
			if ( !count( $atom ) ) :
				return false;
			endif;
			
			$i=1;
			
			foreach( $atom->entry as $entry ) :
				$email_g = $entry->author->email;
				$email = str_replace('""', "", $email_g);
				$avatar = get_avatar($email,64);
			endforeach;
			$html.='<div class="title-xagithub">'.$avatar.'<h4>'.$username.'<br><small>Github Activity</small></h4></div>';
			$html.='<div style="padding:10pt;"><ul class="xagithub">';
				foreach( $atom->entry as $entry ) :
				
					 /*
						$entry->link['href'],
						$entry->title,
						$entry->updated,
						$entry->author->name,
						$entry->author->uri,
						$entry->author->email,
					*/
					
					
					$updated= strtotime($entry->updated);
					$date = date('j M',$updated);
					$time = date('h:i a',$updated);
					$i++;
					$replace = array(
						$username => '',
						' '.'/'=>'&nbsp;'
						);
					$without_name = str_replace(array_keys($replace), array_values($replace), $entry->title);
					$html.='<li><span>'.$date.' at '.$time.'</span><br><em>&#8627;</em><a target="_blank" href="'.$entry->link['href'].'">'.$without_name.'</a></li>';
				if ( $i > $entries )  break ;			
				endforeach;
		$html.='</ul></div>';
			echo $html;
		endif;
    echo $after_widget;
  }
}
add_action( 'widgets_init', create_function('', 'return register_widget("XagithubWidget");') );
add_action( 'wp_enqueue_scripts', 'xagithub_styles' );
function xagithub_styles() {
    	wp_register_style( 'xagithub_css', plugins_url('css/wp-xagithub.css', __FILE__) );
    	wp_enqueue_style( 'xagithub_css' );
}
?>