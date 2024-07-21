<?php
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

    $url = 'https://jsonplaceholder.typicode.com/todos';
    $response = wp_remote_get($url);

    if (is_wp_error($response)) {
        echo 'Error';
    } else {
        $items = json_decode($response['body'], true);
    }

    foreach ($items as $item) {
        $rows_affected = $wpdb->replace($table_name,
            array('user_id' => $item['userId'], 'title' => $item['title'], 'completed' => $item['completed']));
    }
}

if ($_POST['action'] == 'Update') {
    updateData();
}