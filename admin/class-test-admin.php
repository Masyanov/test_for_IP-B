<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @since      1.0.0
 *
 * @package    Test
 * @subpackage Test/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Test
 * @subpackage Test/admin
 * @author     Масьянов Алексей
 */

if (!class_exists('TEST_List_Table')) {
    require_once TEST_PLUGIN_DIR.'/admin/includes/class-test-list-table.php';
}

/**
 * Создаем страницу настроек плагина
 */
add_action('admin_menu', 'add_plugin_page');

function add_plugin_page()
{

    add_menu_page(
        'Настройки плагина',
        'ТЕСТ',
        'manage_options',
        'test_slug',
        'test_options_page_output',
        'dashicons-shortcode',
        30
    );
}

// Создаем новую таблицу для данных wp_list.
function updateData()
{
    global $wpdb;

    $table_name = $wpdb->prefix."my_list";

    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        $sql = "CREATE TABLE ".$table_name." (
	  id mediumint(9) NOT NULL AUTO_INCREMENT,
	  user_id mediumint(9) NOT NULL,
	  title tinytext NULL,
	  completed boolean NULL,
	  UNIQUE KEY id (id)
	);";
        dbDelta($sql);
    }

    // Принимаем данные по API и завписываем их в БД.
    $url = 'https://jsonplaceholder.typicode.com/todos';
    $response = wp_remote_get($url);

    if (is_wp_error($response)) {
        echo 'Error';
    } else {
        $items = json_decode($response['body'], true);
    }
    if (!empty($items)) {
        foreach ($items as $item) {
            $wpdb->replace($table_name,
                array('user_id' => $item['userId'], 'title' => $item['title'], 'completed' => $item['completed']));
        }
        echo '<div style="padding-left: 180px; padding-top: 20px; color: darkgreen"><div>Данные обновлены</div></div>';
    } else {
        echo '<div style="padding-left: 180px; padding-top: 20px; color: red"><div>Данных от API нет</div></div>';
    }

}


// Очищаем таблицу и обнуляем инкрементный ID

if (isset($_POST['action']) == 'Update') {
    global $wpdb;
    $wpdb->query($wpdb->prepare("TRUNCATE TABLE `wp_my_list`"));
    updateData();
}

// Вывод таблицы с данными в админке
function list_init()
{
    $table = new TEST_List_Table();
    echo '<div class="wrap"><h2>Список задач</h2>';


    echo '<form method="post">';
    $table->prepare_items();
    $table->search_box('Поиск', 'list');
    $table->display();
    echo '</div></form>';


    echo '</div>';
}

function test_options_page_output()
{
    ?>
    <div class="wrap">
        <h2>Обновить данные</h2>
        <form action="admin.php?page=test_slug" method="POST">
            <input type="submit" name="action" value="Обновить"/>
        </form>
    </div>
    <?php
    list_init();
}

// создаем шорткод для вывода последних 5-ти незавершённых задач
add_shortcode('test_view_data', 'test_func');

function test_func($atts)
{
    global $wpdb;
    $table = $wpdb->prefix.'my_list';
    $count = $atts['count'];

    $results = $wpdb->get_results("SELECT * FROM {$table} WHERE completed='0' ORDER BY id DESC LIMIT $count");

    ob_start();

    if (!empty($results)) : ?>
        <ul class="list-unstyled my-custom-posts" style="display: flex; flex-wrap: wrap; gap: 2%">
            <?php foreach ($results as $post): ?>
                <li style="width: 25%; display:flex; flex-direction: column; margin-bottom: 10px; border: 1px solid #666; padding: 15px">
                    <div><strong>ID записи: </strong><?= $post->id ?></div>
                    <div><strong>ID пользователя: </strong><?= $post->user_id ?></div>
                    <div><strong>Заголовок: </strong><?= $post->title ?></div>
                    <div><strong style="color: red"><?php if ($post->completed == false) {
                                echo 'Незавершенная';
                            } ?> </strong></div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <div class="alert alert-warning">Нет записей.</div>
    <?php endif; ?>
    <?php
    return ob_get_clean();
}


class Test_Admin
{

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $test The ID of this plugin.
     */
    private $test;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @param  string  $test  The name of this plugin.
     * @param  string  $version  The version of this plugin.
     * @since    1.0.0
     */
    public function __construct($test, $version)
    {

        $this->test = $test;
        $this->version = $version;

    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Test_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Test_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_style($this->test, plugin_dir_url(__FILE__).'css/test-admin.css', array(), $this->version, 'all');

    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Test_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Test_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_script($this->test, plugin_dir_url(__FILE__).'js/test-admin.js', array('jquery'), $this->version,
            false);

    }

}
