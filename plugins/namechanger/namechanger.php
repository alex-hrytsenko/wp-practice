<?php
/*
Plugin name: Name Changer
Description: Приховування конфіденційних даних в справі
Version: 1.0
Author: Oleksandr Hrytsenko
*/

if(!function_exists('add_filter'))
    die();

require_once('namechanger-options-page.php');

define('NAMECHANGER_DIR', plugin_dir_path(__FILE__));

function name_changer_filter($content) {
	$content_arr = explode(' ', $content);
	$people_arr = [];
	$counter = 1;
	global $wpdb;

    $results = $wpdb->get_results("SELECT * FROM `" . $wpdb->prefix . "people_names" . "`");

	for($i = 0; $i < count($content_arr); $i++)
	{
		for($j = 0; $j < count($results); $j++)
		{
			if($content_arr[$i] === $results[$j]->name)
			{
				$full_name = trim($content_arr[$i-1] . ' ' . $content_arr[$i] . ' ' . $content_arr[$i+1], "\x00..\x40");
				if(!array_key_exists($full_name, $people_arr))
				{
					$people_arr[$full_name] = $counter;
					//replace first name
					$content_arr[$i] = 'ОСОБА_' . $counter++;
				} else
				{
					//replace first name
					$content_arr[$i] = 'ОСОБА_' . $people_arr[$full_name];
				}
				//delete last and middle name
				array_splice($content_arr, $i-1, 1);
				array_splice($content_arr, $i, 1);
			}
		}
	}

	$content = implode(' ', $content_arr);
	return $content;
}

add_filter('the_content', 'name_changer_filter');