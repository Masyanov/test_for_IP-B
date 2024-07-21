<?php

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH.'wp-admin/includes/class-wp-list-table.php');
    require_once(ABSPATH.'wp-admin/includes/upgrade.php');
    require_once(ABSPATH.'wp-admin/includes/screen.php');
    require_once(ABSPATH.'wp-admin/includes/class-wp-screen.php');
    require_once(ABSPATH.'wp-admin/includes/template.php');
}

class TEST_List_Table extends WP_List_Table
{

    private $table_data;

    function get_columns()
    {
        $columns = array(
            'id' => 'id',
            'user_id' => 'user_id',
            'title' => 'title',
            'completed' => 'completed'
        );

        return $columns;
    }

    private function get_table_data($search = '')
    {
        global $wpdb;

        $table = $wpdb->prefix.'my_list';

        if (!empty($search)) {
            return $wpdb->get_results(
                "SELECT * from {$table} WHERE user_id Like '%{$search}%' OR title Like '%{$search}%' OR completed Like '%{$search}%'",
                ARRAY_A
            );
        } else {
            return $wpdb->get_results(
                "SELECT * from {$table}",
                ARRAY_A
            );
        }
    }

    function prepare_items()
    {
        if (isset($_POST['s'])) {
            $this->table_data = $this->get_table_data($_POST['s']);
        } else {
            $this->table_data = $this->get_table_data();
        }

        $columns = $this->get_columns();
        $hidden = (is_array(get_user_meta(get_current_user_id(),
            'managetoplevel_page_supporthost_list_tablecolumnshidden', true))) ? get_user_meta(get_current_user_id(),
            'managetoplevel_page_supporthost_list_tablecolumnshidden', true) : array();
        $sortable = $this->get_sortable_columns();
        $primary = 'id';
        $this->_column_headers = array($columns, $hidden, $sortable, $primary);

        usort($this->table_data, array(&$this, 'usort_reorder'));

        /* pagination */
        $per_page = 12;
        $current_page = $this->get_pagenum();
        $total_items = count($this->table_data);
        $this->table_data = array_slice($this->table_data, (($current_page - 1) * $per_page), $per_page);

        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page' => $per_page
        ));

        $this->items = $this->table_data;

    }

    function usort_reorder($a, $b)
    {
        $orderby = (!empty($_GET['orderby'])) ? $_GET['orderby'] : 'user_id';
        $order = (!empty($_GET['order'])) ? $_GET['order'] : 'asc';
        $result = strcmp($a[$orderby], $b[$orderby]);
        return ($order === 'asc') ? $result : -$result;
    }

    protected function get_sortable_columns()
    {
        $columns = array(
            'id' => array('id', true),
            'user_id' => array('user_id', true),
            'title' => array('title', true),
            'completed' => array('completed', true)
        );

        return $columns;
    }

    function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'id':
            case 'user_id':
            case 'title':
            case 'completed':
            default:
                return $item[$column_name];
        }
    }


}
