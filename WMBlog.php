<?php
/*
Plugin Name: WMBlog
Plugin URI: http://blog.wikimedia.org
Description: A WordPress plugin bringing functionality specific to the Wikimedia blog
Version: 0.1
Author: Guillaume Paumier
Author URI: http://www.gpaumier.org
License: GPLv3

/*  Copyright 2011 Guillaume Paumier  (email : guillaume@gpaumier.org)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301  USA
*/


/* =========================================================
   TODO: Create a plugin options page in the admin interface
   with checkboxes for each option, so that any admin can 
   enable / disable a specific feature easily, without the 
   need to do it here */


/* =========================================================
   Allow contributors to upload and insert files into their posts.
   This setting is written to the database. */

function WMBlog_allow_contributor_uploads() {
	$contributor = get_role('contributor');
	$contributor->add_cap('upload_files');
}


/* =========================================================
   Replicate the default meta widget and extend it to include
   a link to the Wikimedia blog guidelines
   See https://codex.wordpress.org/Widgets_API for reference */

class WMBlog_meta_widget extends WP_Widget {

	function WMBlog_meta_widget() {
		// (constructor) Instantiate the parent object
		parent::WP_Widget( /* Base ID */'WMBlog_meta_widget', /* Name */'WMBlog_meta_widget', array( 'description' => 'The default meta widget plus Wikimedia-specific stuff' ) );
	}

	function form( $instance ) {
		// output the options form on admin
		// i.e. for now only the widget's title
		if ( $instance ) {
			$title = esc_attr( $instance[ 'title' ] );
		}
		else {
			$title = __( 'New title', 'text_domain' );
		}
		?>
		<p>
		<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
		</p>
		<?php 
	}

	function update( $new_instance, $old_instance ) {
		// process widget options to be saved
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		return $instance;
	}

	function get_eventlogging_url() {
		// construct url for logging current request using EventLogging API
		// url should be set as the 'src' attribute of an image
		// see <http://mediawiki.org/wiki/Extension:EventLogging>.
		if ( empty( $_SERVER['REQUEST_URI'] ) ) {
			return false;
		}

		$event = array(
			'event' => array( 'requestUrl' => $_SERVER['REQUEST_URI'] ),
			'revision' => 5308166,
			'schema' => 'WikimediaBlogVisit',
			'webHost' => $_SERVER['HTTP_HOST'],
			'wiki' => 'blog'
		);

		if ( !empty( $_SERVER['HTTP_REFERER'] ) ) {
			$event['event']['referrerUrl'] = $_SERVER['HTTP_REFERER'];
		}

		return urlencode( json_encode( $event ) );
	}

	function widget( $args, $instance ) {
		// output the content of the widget
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );
		echo $before_widget;
		if ( !empty( $title ) ) { echo $before_title . $title . $after_title; } ?>
			<ul>
			<?php wp_register(); ?>
			<li><?php wp_loginout(); ?></li>
			<li><a href="//meta.wikimedia.org/wiki/Wikimedia_Blog/Guidelines" title="General contribution guidelines for the Wikimedia blog">Blog guidelines</a></li>
			<li><a href="<?php bloginfo('rss2_url'); ?>" title="<?php echo esc_attr(__('Syndicate this site using RSS 2.0')); ?>"><?php _e('Entries <abbr title="Really Simple Syndication">RSS</abbr>'); ?></a></li>
			<li><a href="<?php bloginfo('comments_rss2_url'); ?>" title="<?php echo esc_attr(__('The latest comments to all posts in RSS')); ?>"><?php _e('Comments <abbr title="Really Simple Syndication">RSS</abbr>'); ?></a></li>
			<li><a href="http://wordpress.org/" title="<?php echo esc_attr(__('Powered by WordPress, state-of-the-art semantic personal publishing platform.')); ?>">WordPress.org</a></li>
			<?php wp_meta(); ?>
			</ul>
			<img style="display: none;" src="//bits.wikimedia.org/event.gif?<?php echo $this->get_eventlogging_url(); ?>;">
		<?php echo $after_widget;
	}

}

function WMBlog_register_widgets() {
	// register the plugin's available widgets
	register_widget( 'WMBlog_meta_widget' );
}

// plug in the widgets registration
add_action( 'widgets_init', 'WMBlog_register_widgets' );


/* =========================================================
   Do stuff when the plugin is activated. This includes calling the other
   plugin functions that actually do stuff. */

register_activation_hook( __FILE__, 'WMBlog_setup' );

function WMBlog_setup() {
	// Allow contributors to upload files
	WMBlog_allow_contributor_uploads();}


/* =========================================================
   Do stuff when the plugin is deactivated. This includes removing some settings from
   the database that have been written by the plugin */

register_deactivation_hook( __FILE__, 'WMBlog_cleanup' );

function WMBlog_cleanup(){
	global $wp_roles;
	$wp_roles->remove_cap( 'contributor', 'upload_files' );
}

?>