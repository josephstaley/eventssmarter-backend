<div class="col-md-4 after-4 card p-0">
    <div class="panel panel-default pump" style="background:url({{ $organization->cover_image }});margin:0px;background-size:contain;background-repeat: no-repeat;
    background-position: center;width:100%;display:table;
    ">
        <div class="organization-card">
                <div class="follow">
                    {!! \App\Models\User::followHtml($organization) !!}
                </div>
                <div class="info">
                    <div class="bg"></div>
                    {!! \App\Models\User::followHtml($organization) !!}
                    <div class="category">
                        {{ $organization->category }}
                        <br>
                        <p style="font-size:23pt;line-height:30px">{{ $organization->name }}</p>
                    </div>
                    <div class="profile-links">
                        <a href="{{ route('organization.show',$organization) }}" class="btn btn-primary o">{{ trans('organization.see-more') }}</a>
                    </div>
                </div>
            <br>
            <br>
        </div>
    </div>
</div>
