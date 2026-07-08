const $ = require('jquery');
require('bootstrap');
require('datatables.net');
import 'datatables.net-dt/css/jquery.dataTables.css';
import '../css/app.css';

$(document).ready( function () {
    $('#weatherTable').DataTable({
        "ordering": false,
        // Pagination is handled server-side (see the pagination links below
        // the table), so DataTables' own paging/search/info UI is disabled
        // to avoid showing two conflicting pagination controls.
        "paging": false,
        "searching": false,
        "info": false,
        "lengthChange": false
    });
} );

