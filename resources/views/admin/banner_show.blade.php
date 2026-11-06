@extends('layouts.app')

@section('content')
    <div class="container">

        <h4><a href="/admin">Admin</a> / "Banners"</h4>

        <br>

        <p>Showing {{ $banner->count() .' of '. $banner->total() }} <a href="/admin/create_banner" class="btn btn-primary o" style="float:right">Create</a></p>


        <table class="table table-hover">
            <thead>
            <tr>
                <th></th>
                <th></th>
                <th>Name</th>
                <th>Link_URL</th>
                <th>Link_Type</th>
                <th>Link TO</th>
                <th>click counts</th>
            </tr>
            </thead>
            <tbody>
            @foreach($banner as $row)
                <tr>
                    <td><a href="/admin/banner/view/{{ $row->id }}">View</a></td>
                    <td><a href="/admin/banner/edit/{{ $row->id }}">Edit</a></td>
                    <td>{{ $row->name }}</td>
                    <td>{{ $row->link_url }}</td>
                    <td>
                        @if($row->link_type == '1')
                            Organization
                        @elseif($row->link_type == '0')
                          Event
                        @endif
                    </td>
                    <td>
                        <?php
                            if($row->link_type)
                                $db=DB::table('organizations')->where('id',$row->link_id)->first();
                            else
                                $db=DB::table('events')->where('id',$row->link_id)->first();

                                echo $db ? $db->name : '';
                        ?>

                    </td>
                    <td>{{ $row->click_counts }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>

        {{ $banner->render() }}

        @if($banner->isEmpty())
            No Results
        @endempty

    </div>
@endsection
