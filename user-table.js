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

    // Role filtering
    $('#role-filter').on('change', function() {
        var selectedRole = $(this).val();
        table.column(4).search(selectedRole).draw(); // Ensure the correct column is filtered
    });

    // Export to CSV
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
