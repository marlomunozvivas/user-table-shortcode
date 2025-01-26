jQuery(document).ready(function($) {
    // Check if DataTable is already initialized
    if (!$.fn.DataTable.isDataTable('#user-table')) {
        $('#user-table').DataTable({
            responsive: true,  // Enable responsive layout
            autoWidth: false,  // Disable auto width calculation
            columnDefs: [
                { targets: '_all', className: 'dt-center' }  // Center-align all columns
            ],
            language: {
                search: "Search Users:",  // Custom search label
                lengthMenu: "Show _MENU_ entries",  // Custom length menu
                info: "Showing _START_ to _END_ of _TOTAL_ entries",  // Custom info text
                paginate: {
                    first: "First",  // Custom first page label
                    last: "Last",  // Custom last page label
                    next: "Next",  // Custom next page label
                    previous: "Previous"  // Custom previous page label
                }
            }
        });
    }

    // Role filter functionality
    $('#role-filter').on('change', function() {
        var selectedRole = $(this).val();
        var table = $('#user-table').DataTable();
        table.column(4).search(selectedRole).draw();  // Filter by role
    });

    // Export to CSV functionality
    $('#export-to-excel').on('click', function() {
        var table = $('#user-table').DataTable();
        var tableData = [];

        // Get all table rows data
        table.rows().every(function(rowIdx) {
            tableData.push(this.data());
        });

        // Prepare CSV content
        var csvContent = "data:text/csv;charset=utf-8,";
        csvContent += "Username,Email,First Name,Last Name,Role\n";  // Column headers

        // Add each row data to the CSV content
        tableData.forEach(function(rowArray) {
            var row = rowArray.join(",");
            csvContent += row + "\n";
        });

        // Create a link and trigger the download
        var encodedUri = encodeURI(csvContent);
        var link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", "user_data.csv");
        document.body.appendChild(link);

        link.click();  // Initiate the download
        document.body.removeChild(link);  // Remove the link after download
    });
});
