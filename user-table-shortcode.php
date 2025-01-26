<?php
/*
Plugin Name: User Table Shortcode
Plugin URI: https://github.com/marlomunozvivas/user-table-shortcode/
Description: Displays registered users in a responsive table with filtering, searching, and export to Excel functionality.
Version: 1.0
Author: Marlo Alexander Munoz Vivas
Author URI: https://marlomunoz.myportfolio.com
License: GPL2
*/

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Enqueue DataTables, custom styles, and scripts
function user_table_enqueue_scripts() {
    wp_enqueue_style('datatables-css', 'https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css');
    wp_enqueue_style('datatables-responsive-css', 'https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css');
    wp_enqueue_script('jquery');
    wp_enqueue_script('datatables-js', 'https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js', array('jquery'), null, true);
    wp_enqueue_script('datatables-responsive-js', 'https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js', array('jquery', 'datatables-js'), null, true);
    wp_enqueue_script('user-table-js', plugin_dir_url(__FILE__) . 'user-table.js', array('jquery', 'datatables-js', 'datatables-responsive-js'), null, true);
}
add_action('wp_enqueue_scripts', 'user_table_enqueue_scripts');

// Shortcode to display the user table
function user_table_shortcode() {
    ob_start();
    $users = get_users();
    $roles = array();

    foreach ($users as $user) {
        foreach ($user->roles as $role) {
            $roles[$role] = $role;
        }
    }
    ?>
    <label for="role-filter">Filter by Role:</label>
    <select id="role-filter">
        <option value="">All Roles</option>
        <?php foreach ($roles as $role): ?>
            <option value="<?php echo esc_html($role); ?>"><?php echo esc_html($role); ?></option>
        <?php endforeach; ?>
    </select>

    <table id="user-table" class="display responsive nowrap" style="width:100%">
        <thead>
            <tr>
                <th>Username</th>
                <th>Email</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Role</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo esc_html($user->user_login); ?></td>
                    <td><?php echo esc_html($user->user_email); ?></td>
                    <td><?php echo esc_html($user->first_name); ?></td>
                    <td><?php echo esc_html($user->last_name); ?></td>
                    <td><?php echo esc_html(implode(', ', $user->roles)); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <button id="export-to-excel">Export to Excel</button>
    <?php
    return ob_get_clean();
}
add_shortcode('user_table', 'user_table_shortcode');

// Add JavaScript for DataTable and Export functionality
function user_table_js_script() {
    ?>
    <script>
        jQuery(document).ready(function($) {
            var table = $('#user-table').DataTable({
                responsive: true,
                autoWidth: false,
                columnDefs: [
                    { targets: '_all', className: 'dt-center' }
                ],
                language: {
                    search: "Search Users:",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "Next",
                        previous: "Previous"
                    }
                }
            });

            $('#role-filter').on('change', function() {
                var selectedRole = $(this).val();
                table.column(4).search(selectedRole).draw();
            });

            $('#export-to-excel').on('click', function() {
                var tableData = [];
                table.rows().every(function(rowIdx) {
                    tableData.push(this.data());
                });

                var csvContent = "data:text/csv;charset=utf-8,";
                csvContent += "Username,Email,First Name,Last Name,Role\n";

                tableData.forEach(function(rowArray) {
                    var row = rowArray.join(",");
                    csvContent += row + "\n";
                });

                var encodedUri = encodeURI(csvContent);
                var link = document.createElement("a");
                link.setAttribute("href", encodedUri);
                link.setAttribute("download", "user_data.csv");
                document.body.appendChild(link);

                link.click();
                document.body.removeChild(link);
            });
        });
    </script>
    <?php
}
add_action('wp_footer', 'user_table_js_script');

// Add custom CSS for responsive table design
function user_table_custom_css() {
    echo '<style>
        #user-table_wrapper {
            overflow-x: auto;
        }
        table#user-table {
            width: 100%;
            table-layout: auto;
            border-collapse: collapse;
        }
        table#user-table th, 
        table#user-table td {
            text-align: left;
            padding: 10px;
        }
    </style>';
}
add_action('wp_head', 'user_table_custom_css');
