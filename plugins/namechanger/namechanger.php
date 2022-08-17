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
	if(empty($names)) {
		$names = explode(',', file_get_contents(NAMECHANGER_DIR . 'list_of_names.txt'));
	}
	print_r(explode(' ', $content));
	// 2------------------------------------------------
	$content_arr = explode(' ', $content);
	for($i = 0; $i < count($content_arr); $i++)
	{
		for($j = 0; $j < count($names); $j++)
		{
			if($content_arr[$i] === $names[$j])
			{
				$content_arr[$i] = 'xxx';
				array_splice($content_arr, $i-1, 1);
				array_splice($content_arr, $i, 1);
				//unset($content_arr[$i+1]);
			}
		}
	}
	print_r($content_arr);
	return $content;
}

add_filter('the_content', 'name_changer_filter');

/*
	for($i = 0; $i < count($names); $i++) {
		//$content = preg_replace('#' . $names[$i] . '#iu', 'ОСОБА_1', $content);
	}
*/