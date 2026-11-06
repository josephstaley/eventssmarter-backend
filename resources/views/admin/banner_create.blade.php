@extends('layouts.app')

@section('meta')
    <title>Create a banner | {{ env('APP_NAME') }}</title>
@endsection


@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">
                          BANNER INFORMATION
                    </div>

                    <div class="panel-body">

                        {{ html()->form('POST',route('Admin.banner_store'))->acceptsFiles()->open() }}

                        @include('admin.banner_form')

                        <br>

                        {{ html()->button('Create banner','submit')->class('btn btn-primary') }}

                        {{ html()->form()->close() }}

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script type="module">
        $(document).ready(function(){
            $('#link_type').change(function(){
               var type = $(this).find('option:selected').attr('id');
               if (type === 'org') {
                   $(".event").hide();
                   $(".organization").show();
                   $(".event").attr("selected",false);
                   $(".organization").first().attr("selected",true);

               } else {
                   $(".event").show();
                   $(".organization").attr("selected",false);
                   $(".event").first().attr("selected",true);

                   $(".organization").hide();
               }

            });
        });
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        window.banner_click = (id) => {

            $.ajax({
                type:'POST',
                url:'/banner_click',
                data:{id:id},
                success:function(data){

                }
            });
        }

    </script>
@endsection
