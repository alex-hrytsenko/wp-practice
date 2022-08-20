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

// Below page adding names

class NamesPage {

    public $page_slug;

    function __construct() {
        $this->page_slug = 'names';

        add_action('admin_menu', [$this, 'NC_add_page']);
        register_activation_hook( __FILE__, [$this, 'NC_create_table_with_names'] );
        register_deactivation_hook( __FILE__, [$this, 'NC_drop_table_with_names'] );
    }

    function NC_drop_table_with_names()
    {
    	global $wpdb;

    	$table_name = $wpdb->prefix . "people_names";
    	$wpdb->query("DROP TABLE IF EXISTS `" . $table_name . "`");
    }

    function NC_create_table_with_names()
    {
    	global $wpdb;
   		$table_name = $wpdb->prefix . "people_names";

    	if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
	        $sql = "CREATE TABLE " . $table_name . " (
	        name VARCHAR(50) NOT NULL,
	        UNIQUE(name)
	        );";

	      	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	        dbDelta($sql);
	    }
    }

    function NC_add_page() {
        add_menu_page(
            'Список імен',
            'Імена',
            'manage_options',
            $this->page_slug,
            [$this, 'NC_display_page'],
            'dashicons-buddicons-buddypress-logo',
            59
        );
    }

    function NC_display_page()
    {
    	global $wpdb;
    	$table_name = $wpdb->prefix . "people_names";
    	$results = $wpdb->get_results("SELECT * FROM `" . $table_name . "`");
    	//print_r($results[0]->name);

    	$page_title = get_admin_page_title();

    	echo <<<_END
    		<div class = "wrapper">
    		<h1>$page_title</h1>
    			<form method = "GET" action = "">
    			<input type = "text" name = "p_name">
    			<input type = "submit" value = "Додати ім’я">
    			</form>
    		</div>
    _END;
    	// display names
    	echo '<ol>';
    	foreach($results as $result) {
    		echo '<li><b>' . $result->name . '</b></li>';
    	}
    	echo '</ol>';
	}
}

new NamesPage();