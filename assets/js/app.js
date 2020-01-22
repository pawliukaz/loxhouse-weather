const $ = require('jquery');
require('bootstrap');
require('datatables.net');
import 'datatables.net-dt/css/jquery.dataTables.css';

$(document).ready( function () {
    $('#weatherTable').DataTable({
        "ordering": false
    });
} );