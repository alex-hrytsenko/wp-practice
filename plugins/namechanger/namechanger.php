<?php
/*
Plugin name: Name Changer
Description: Приховування конфіденційних даних в справі
Version: 1.0
Author: Oleksandr Hrytsenko
*/

if(!function_exists('add_filter'))
    die();

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

    	if(!empty($_POST['p_name']) && $_POST['p_name']) {
    		if(!$wpdb->insert($table_name, ['name' => $_POST['p_name']])) 
    			echo '<div class="notice notice-error is-dismissible"><p>Трапилася помилка, ім’я <strong>' . $_POST['p_name'] . '</strong> вже уснує!</p></div>';
    		else
    			echo '<div class="notice notice-success is-dismissible"><p>Ім’я <strong>' . $_POST['p_name'] . '</strong> успішно додано!</p></div>';
    	}
    	if(!empty($_POST['delete_name']) && $_POST['delete_name']) {
    		$wpdb->delete($table_name, ['name' => $_POST['delete_name']]);
    		echo '<div class="notice notice-error is-dismissible"><p>Ім’я <strong>' . $_POST['delete_name'] . '</strong> успішно видалено!</p></div>';
    	}
    	
    	$results = $wpdb->get_results("SELECT * FROM `" . $table_name . "` ORDER BY `name`");
    	$page_title = get_admin_page_title();
    	$post_url = esc_url( admin_url('admin-post.php') );
    	$ppl_i = 1;
?>
    	<div class = "wrapper">
    		<h1><?=$page_title?></h1>
    		<form method = "POST" action = "">
    			<input type = "text" name = "p_name" required>
    			<input type = "submit" style = "cursor: pointer; color: green;" value = "Додати ім’я">
    		</form>
	    	<form method = "POST">
	    		<table style = "border-spacing: 10px;">
	    			<?php
		    		foreach($results as $result) {
			    		echo "<tr>
				    			<td><b>" . $ppl_i++ . ". " . $result->name . "</b></td>
				    			<td><button style = 'cursor: pointer; color: red;' type = 'submit' name = 'delete_name' value = '" . $result->name . "'>Видалити</button></td>
			    			</tr>";
			    		}
	    			?>
	    		</table>
	    	</form>
    	</div>
<?php
	}
}

new NamesPage();
?>