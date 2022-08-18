<?php
/*
Plugin name: Name Changer
Description: Приховування конфіденційних даних в вироку
Version: 1.0
Author: Oleksandr Hrytsenko
*/

if(!function_exists('add_filter'))
    die();

define('NAMECHANGER_DIR', plugin_dir_path(__FILE__));

function name_changer_filter($content) {
	static $names = [];
	$content_arr = explode(' ', $content);
	$people_arr = [];
	$counter = 1;

	if(empty($names)) {
		$names = explode(',', file_get_contents(NAMECHANGER_DIR . 'list_of_names.txt'));
	}
	for($i = 0; $i < count($content_arr); $i++)
	{
		for($j = 0; $j < count($names); $j++)
		{
			if($content_arr[$i] === $names[$j])
			{
				$full_name = $content_arr[$i-1] . ' ' . $content_arr[$i] . ' ' . $content_arr[$i+1];
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