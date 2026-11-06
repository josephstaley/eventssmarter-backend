@extends('layouts.app')

@section('meta')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/jquery.dataTables.min.css">
<style>
.paginate_button{
    margin-right: 10px;
    cursor: pointer;
}
.btn-clipboard-ticketlink, .btn-edit{
    height: 33px;
    width: 39px;
    margin-left: 10px;
}
tbody tr td:nth-child(5){
    text-align: center;
}
</style>
@endsection

@section('content')
    <div class="container" style="margin-bottom: 80px">

        <h4><a href="/admin">Admin</a> / {{ str_plural($name) }}</h4>

        <br>

        <table class="table table-hover" id="eventlist">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Organization</th>
                    <th>Start Date</th>
                    <th>Created</th>
                    <th class="text-center">Export</th>
                </tr>
            </thead>
        </table>
    </div>
@endsection
@section('scripts')
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js" defer></script>
<script type="text/javascript" charset="utf8" src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js" defer></script>
<script type="module">
    var table = null
     $(document).ready(function(){
        table = $('#eventlist').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{route('Admin.getEvents')}}",
                type: 'GET',
            },
            columns: [
                {
                    data: 'event_name',
                    render: function (data, type, row, meta){
                        return '<a target="_blank" href="' + row.event_link + '">' + row.event_name + '</>'
                    }
                },
                {
                    data: 'org_name',
                    render: function (data, type, row, meta){
                        return '<a target="_blank" href="' + row.org_link + '">' + row.org_name + '</>'
                    }
                },
                { data: 'event_starts_at' },
                { data: 'event_created_at' },
                {
                    data: 'Export',
                    render: function (data, type, row, meta){
                        return '<a class="btn btn-primary btn-xs" style="margin-left:4px;" href="' + row.event_export_link + '" target="_blank"><i class="fa fa-download"></i></a>';
                    }
                }
            ],
            "columnDefs": [
                {
                    "targets": 4,
                    "orderable": false,
                },
                // {
                //     "targets": 1,
                //     "orderable": false,
                // }
            ],
            "language": {
                "sInfo": "Showing _START_ to _END_ of _TOTAL_ Events",
                "sInfoEmpty": "Showing 0 to 0 of 0 Events",
                "infoFiltered" : "",
                oPaginate: {
                    sNext: '<i class="fa fa-forward"></i>',
                    sPrevious: '<i class="fa fa-backward"></i>',
                    sFirst: '<i class="fa fa-step-backward"></i>',
                    sLast: '<i class="fa fa-step-forward"></i>'
                }
            }
        });
     });

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
</script>
@endsection
