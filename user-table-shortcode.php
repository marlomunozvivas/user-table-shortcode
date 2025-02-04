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
// Bind the CSS for the DataTable and make it responsive
function user_table_enqueue_styles() {
    wp_enqueue_style('dataTables-css', plugins_url('user-table-shortcode/assets/css/jquery.dataTables.min.css'), array(), filemtime(plugin_dir_path(__FILE__) . 'assets/css/jquery.dataTables.min.css'), 'all');
    wp_enqueue_style('user-table-css', plugins_url('user-table-shortcode/assets/css/responsive.dataTables.min.css'), array(), filemtime(plugin_dir_path(__FILE__) . 'assets/css/responsive.dataTables.min.css'), 'all');
}
add_action('wp_enqueue_scripts', 'user_table_enqueue_styles');

// Bind the necessary scripts to DataTable
function user_table_enqueue_scripts() {
    wp_enqueue_script('jquery');
    wp_enqueue_script('dataTables-js', plugins_url('user-table-shortcode/assets/js/jquery.dataTables.min.js'), array('jquery'), filemtime(plugin_dir_path(__FILE__) . 'assets/js/jquery.dataTables.min.js'), true);
    wp_enqueue_script('user-table-js', plugins_url('user-table-shortcode/assets/js/dataTables.responsive.min.js'), array('jquery', 'dataTables-js'), filemtime(plugin_dir_path(__FILE__) . 'assets/js/dataTables.responsive.min.js'), true);
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
                scrollX: true, // Enable horizontal scroll if the table exceeds screen width
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

            // Role filter functionality
            $('#role-filter').on('change', function() {
                var selectedRole = $(this).val();
                table.column(4).search(selectedRole).draw(); // Make sure the correct column is filtered
            });

            // Export to CSV functionality
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

        /* Ensure the table stays responsive on smaller screens */
        @media (max-width: 767px) {
            #user-table_wrapper {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            table#user-table {
                width: 100%;
                table-layout: fixed;  /* Key for fitting on smaller screens */
            }
            table#user-table th, 
            table#user-table td {
                font-size: 12px;  /* Reduces font size to fit on smaller screens */
                padding: 5px;     /* Adjusts padding for smaller devices */
            }
            #role-filter {
                width: 100%;
                margin-bottom: 10px;
            }
        }
        
        /* Adjust export button for smaller screens */
        #export-to-excel {
            margin-top: 20px;
            padding: 10px;
            font-size: 14px;
            width: 100%;
            max-width: 200px;
            margin: auto;
            display: block;
        }
    </style>';
}
add_action('wp_head', 'user_table_custom_css');
?>
