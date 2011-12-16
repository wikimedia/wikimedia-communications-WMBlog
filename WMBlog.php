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

/* TODO: Create a plugin options page in the admin interface
   with checkboxes for each option, so that any admin can 
   enable / disable a specific feature easily, without the 
   need to do it here */

/* Allow contributors to upload and insert files into their posts.
   This setting is written to the database. */

function WMBlog_allow_contributor_uploads() {
	$contributor = get_role('contributor');
	$contributor->add_cap('upload_files');
}

/* Do stuff when the plugin is activated. This includes calling the other
   plugin functions that actually do stuff. */

register_activation_hook( __FILE__, 'WMBlog_setup' );

function WMBlog_setup() {
	WMBlog_allow_contributor_uploads();
}

/* Do stuff when the plugin is deactivated. This includes removing some settings from
   the database that have been written by the plugin */

register_deactivation_hook( __FILE__, 'WMBlog_cleanup' );

function WMBlog_cleanup(){
	global $wp_roles;
	$wp_roles->remove_cap( 'contributor', 'upload_files' );
}

?>