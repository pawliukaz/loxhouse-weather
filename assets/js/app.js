const $ = require('jquery');
require('bootstrap');
require('datatables.net');
import 'datatables.net-dt/css/jquery.datatables.css';

$(document).ready( function () {
    $('#weatherTable').DataTable();
} );