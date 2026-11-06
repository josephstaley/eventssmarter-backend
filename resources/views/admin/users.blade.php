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
tbody tr td:last-child{
    text-align: center;
}
tbody tr td:nth-child(6){
    text-align: center;
}
.user-avatar{
    width: 35px;
    height: 35px;
}
.close{
    font-size: 35px !important;
}
#userInfo label{
    margin-right: 10px;
    margin-top: 7px;
    width: 90px;
}
</style>
@endsection

@section('content')
    <div class="container" style="margin-bottom: 80px">

        <h4><a href="/admin">Admin</a> / {{ str_plural($name) }}</h4>

        <br>

        <table class="table table-hover" id="userlist">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Photo</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th class="text-center">Created Date</th>
                    <th></th>
                </tr>
            </thead>
        </table>
    </div>
    <div class="modal fade" id="userInfo" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><i class="fa fa-user-o"></i> User Detail Information</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4 col-sm-4">
                            <img id="user-avatar" src="" style="width: 100%">
                        </div>
                        <div class="col-md-8 col-sm-8">
                            <div class="flex">
                                <label for="user-name">Name</label>
                                <input id="user-name" class="form-control">
                            </div>
                            <div class="flex">
                                <label for="user-email">Email</label>
                                <input id="user-email" class="form-control">
                            </div>
                            <div class="flex">
                                <label for="user-phone">Phone</label>
                                <input id="user-phone" class="form-control">
                            </div>
                            <div class="flex">
                                <label for="user-facebook">Facebook link</label>
                                <input id="user-facebook" class="form-control">
                            </div>
                            <div class="flex">
                                <label for="user-twitter">Twitter link</label>
                                <input id="user-twitter" class="form-control">
                            </div>
                            <div class="flex">
                                <label for="user-linkedin">Linkedin link</label>
                                <input id="user-linkedin" class="form-control">
                            </div>
                            <div class="flex">
                                <label for="user-website">Website link</label>
                                <input id="user-website" class="form-control">
                            </div>
                            <div class="flex">
                                <label for="user-instagram">Instagram link</label>
                                <input id="user-instagram" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" onclick="saveUser()" id="save-btn">Save</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js" defer></script>
<script type="text/javascript" charset="utf8" src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js" defer></script>

<script type="module">
    var currentEditUser = null
    var table = null
     $(document).ready(function(){
        table = $('#userlist').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{route('Admin.getUsers')}}",
                type: 'GET',
            },
            columns: [
                { data: 'id' },
                { data: 'avatar',
                    render: function (data, type, row, meta) {
                        return '<img src="' + row.avatar + '" class="user-avatar" />';
                    }
                },
                { data: 'name' },
                { data: 'email' },
                { data: 'phone_number' },
                { data: 'created_at' },
                {
                    data: 'Action',
                    render: function (data, type, row, meta) {
                        return '<button type="button" class="btn btn-default btn-clipboard-ticketlink" onClick="sendTicketLinkToken(' + row.id + ', this)"> <i class="fa fa-envelope-o"></i> </button>' +
                               '<button type="button" class="btn btn-default btn-edit" onClick="editUser(' + row.id + ', this)"> <i class="fa fa-pencil"></i> </button>' +
                               '<button type="button" class="btn btn-default btn-edit" onClick="signUser(' + row.id + ', this)"> <i class="fa fa-sign-in"></i> </button>' +
                               '<button type="button" class="btn btn-default btn-edit" onClick="deleteUser(' + row.id + ', this)"> <i class="fa fa-trash"></i> </button>';
                    }
                }
            ],
            "columnDefs": [
                {
                    "targets": 6,
                    "orderable": false,
                },
                {
                    "targets": 1,
                    "orderable": false,
                }
            ],
            "language": {
                "sInfo": "Showing _START_ to _END_ of _TOTAL_ users",
                "sInfoEmpty": "Showing 0 to 0 of 0 users",
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
    window.signUser = (_user_id, target) => {
        currentEditUser = _user_id
        target.disabled = true
        swal({title : "Please wait", text : "Sign in this User now...", buttons: false, closeOnClickOutside: false, icon: "info"});
        $.ajax({
            type : 'PATCH',
            url : '/admin/users/' + currentEditUser,
            success:function(response){
                window.location.href = '/home';
                target.disabled = false
            },
            error: function (error){
                target.disabled = false
                swal({text: error.statusText, icon:"warning"});
            }
        });
    }

    window.editUser = (_user_id, target) => {
        currentEditUser = _user_id
        target.disabled = true
        // swal({title : "Please wait", text : "Getting User information...", buttons: false, closeOnClickOutside: false, icon: "info"});
        $.ajax({
            type : 'GET',
            url : '/admin/users/' + currentEditUser,
            success:function(response){
                target.disabled = false
                if (response.success){
                    const userinfo = response.data
                    $('#user-name').val(userinfo.name)
                    $('#user-email').val(userinfo.email)
                    $('#user-phone').val(userinfo.phone_number)

                    $('#user-facebook').val(userinfo.facebook_link)
                    $('#user-twitter').val(userinfo.twitter_link)
                    $('#user-linkedin').val(userinfo.linkedin_link)
                    $('#user-website').val(userinfo.website_link)
                    $('#user-instagram').val(userinfo.instagram_link)
                    $('#userInfo').modal('show');

                    document.getElementById('user-avatar').src = userinfo.avatar
                }
                else {
                    swal({text: response.message, icon:"warning"});
                }
            },
            error: function (error){
                target.disabled = false
                swal({text: error.statusText, icon:"warning"});
            }
        });
    }

    window.deleteUser = (_user_id, target) => {
        target.disabled = true
        swal({
            title: 'User Delete',
            text: "Are you sure to delete this user? \n All user's related information will be deleted.",
            showCancelButton: true,
            confirmButtonText: 'OK',
            cancelButtonText: 'Cancel',
            allowOutsideClick: false,
            icon: "warning",
            buttons: true
        }).then((result) => {
            if (result) {
                swal({title : "Please wait", text : "Deleting User now...", buttons: false, closeOnClickOutside: false, icon: "info"});
                $.ajax({
                    type : 'DELETE',
                    url : '/admin/users/' + _user_id,
                    success:function(response){
                        target.disabled = false
                        if (response.success){
                            table.ajax.reload(null, false);
                            swal({text: "Deleted User Succsssfully!", icon:"success"});
                        }
                        else {
                            swal({text: response.message, icon:"warning"});
                        }
                    },
                    error: function (error){
                        target.disabled = false
                        swal({text: error.statusText, icon:"warning"});
                    }
                });
            }
        });
    }

    window.saveUser = () => {
        if (!currentEditUser){
            alert('Please choose user for save')
            return;
        }
        $('#userInfo').modal('hide');
        swal({title : "Please wait", text : "Saving user information...", buttons: false, closeOnClickOutside: false, icon: "info"});
        const requestData = {
            'name' : $('#user-name').val(),
            'email' : $('#user-email').val(),
            'phone_number' : $('#user-phone').val(),
            'facebook_link' : $('#user-facebook').val(),
            'twitter_link' : $('#user-twitter').val(),
            'linkedin_link' : $('#user-linkedin').val(),
            'website_link' : $('#user-website').val(),
            'instagram_link' : $('#user-instagram').val()
        }
        $.ajax({
            type : 'PUT',
            url : '/admin/users/' + currentEditUser,
            data : requestData,
            success:function(){
                swal.close()
                table.ajax.reload(null, false);
            }
        });
    }

    window.sendTicketLinkToken = (_user_id) => {
        swal({
            title: 'Ticket Link Send',
            text: 'Are you sure to send the ticket link to this user?',
            showCancelButton: true,
            confirmButtonText: 'OK',
            cancelButtonText: 'Cancel',
            allowOutsideClick: false,
            icon: "warning",
            buttons: true
        }).then((result) => {
            if (result) {
                swal({title : "Please wait", text : "Sending email to User now...", buttons: false, closeOnClickOutside: false, icon: "info"});
                $.ajax({
                    type : 'POST',
                    url : '{{route('Admin.sendTicketLinkToken')}}',
                    data : {
                        user_id : _user_id
                    },
                    success:function(response){
                        if (response){
                            swal({text: "Email sent successfully!", icon:"success"});
                        }
                        else {
                            swal({text: "User Not Found", icon:"warning"});
                        }
                    },
                    error: function (error){
                        swal({text: error.statusText, icon:"warning"});
                    }
                });
            }
        })
     }
</script>
@endsection
