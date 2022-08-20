<?php
if(!defined('WP_UNINSTALL_PLUGIN'))
	die;

global $wpdb;

$table_name = $wpdb->prefix . "people_names";
$wpdb->query("DROP TABLE IF EXISTS `" . $table_name . "`");