@extends('layouts.app')

@section('meta')
    <style>
        #editor {
            height: 50vh;
        }
    </style>
@endsection

@section('content')
    <div class="container">

        <h4><a href="/admin">Admin</a> / <a href="/admin/{{ str_slug($name) }}">{{ str_plural($name) }}</a> / Editing "{{ $model->name }}"</h4>

        <a href="/admin/{{ str_slug($name) }}/{{ $model->id }}/delete" onclick="return confirm('Are you really sure you want to delete all this data?')" class="btn text pull-right">Delete</a>

        <br>
        <br>

        <div id="editor" class="form-control">{{ json_encode($model, JSON_PRETTY_PRINT) }}</div>

        <br>

        <button id="saveBtn" class="btn btn-primary">Save</button>

    </div>
@endsection

@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.22.1/ace.min.js" type="text/javascript" charset="utf-8" defer></script>
    <script type="module">
        var editor = ace.edit("editor");
        editor.setTheme("ace/theme/chrome");
        editor.getSession().setMode("ace/mode/json");

        var val = editor.session.getValue()
        var o = JSON.parse(val)
        val = JSON.stringify(o, null, 4)
        editor.session.setValue(val)

        $('#saveBtn').on('click',function(){
            var data = editor.getValue();

            try {
                data = JSON.parse(data);
            } catch(e) {
                data = null;
                alert('Unable to save, please fix editor errors first.');
            }

            if(data){
                axios.post('/admin/{{ str_slug($name) }}/{{ $model->id }}',data)
                    .then(function(response){
                        alert('Changes were saved successfully.')
                    })
                    .catch(function(e){
                        alert('Unable to save: ' + e)
                    })
            }
        });

    </script>
@endsection
