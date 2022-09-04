<?php
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
            $added_name = mb_convert_case(mb_strtolower(preg_replace('/[01[:ascii:]%]/', '', trim($_POST['p_name']))), MB_CASE_TITLE, "UTF-8");
            if(!$wpdb->insert($table_name, ['name' => $added_name])) 
                echo '<div class="notice notice-error is-dismissible"><p>Трапилася помилка, ім’я <strong>' . $added_name . '</strong> вже уснує!</p></div>';
            else
                echo '<div class="notice notice-success is-dismissible"><p>Ім’я <strong>' . $added_name . '</strong> успішно додано!</p></div>';
        }
        if(!empty($_POST['delete_name']) && $_POST['delete_name']) {
            $wpdb->delete($table_name, ['name' => $_POST['delete_name']]);
            echo '<div class="notice notice-error is-dismissible"><p>Ім’я <strong>' . $_POST['delete_name'] . '</strong> успішно видалено!</p></div>';
        }

        $results = $wpdb->get_results("SELECT * FROM `" . $table_name . "` ORDER BY `name`");
        $ppl_i = 1;
?>
        <div class = "wrapper">
            <h1><?=get_admin_page_title()?></h1>
            <form method = "POST" action = "">
                <input type = "text" name = "p_name" required>
                <input type = "submit" style = "cursor: pointer; color: green;" value = "Додати ім’я">
            </form>
            <form method = "POST" action = "">
                <table style = "border-spacing: 10px;">
                    <?php foreach($results as $result) { ?>
                        <tr>
                            <td><strong><?php echo $ppl_i++ . ". " . $result->name ?></b></td>
                            <td><button style = 'cursor: pointer; color: red;' type = 'submit' name = 'delete_name' value = '<?php echo $result->name; ?>'>Видалити</button></td>
                        </tr>
                    <?php } ?>
                </table>
            </form>
        </div>
<?php
    }
}

new NamesPage();
?>
