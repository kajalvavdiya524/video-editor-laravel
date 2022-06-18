@extends('backend.layouts.app')

@section('title', __('Video Themes'))

@section('css')
    <style type="text/css">
        .font {
            background-color: #e4e4e4;
            border: 1px solid #aaa;
            border-radius: 4px;
            display: inline-block;
            margin-left: 5px;
            margin-top: 5px;
            padding: 0 5px;
            position: relative;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            vertical-align: bottom;
            white-space: nowrap;
        }
    </style>
@endsection

@section('content')
<x-backend.card>
    <x-slot name="header">
        <h4>Themes</h4>
    </x-slot>
    <x-slot name="body">
        <div>
            <a class="btn btn-primary btn-sm" href="{{ route('admin.auth.video.themes.create') }}">Add a new theme</a>
        </div>
        <br>      
        <table class="table table-striped table-bordered table-sm">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Font Names</th>
                    <th>Theme Position</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($themes as $theme)
                    <tr>
                        <td>
                            {{ $theme->id }}
                        </td>
                        <td>
                            {{ $theme->name }}
                        </td>
                        <td>
                            @foreach(explode(',', $theme->font_names) as $font_name)
                                <span class="font">{{ $font_name }}</span>
                            @endforeach
                        </td>
                        <td>
                            <input type="number" onkeyup="themenumberView({{$theme->id}},{{count($themes)}})" id="themenumb{{$theme->id}}" value="{{$theme->theme_number}}"/>
                            <span id="themenumb_error{{$theme->id}}" class="error"></span>
                        </td>
                        <td>
                            <a href="{{ route('admin.auth.video.themes.edit', $theme->id ) }}" class="btn btn-primary btn-sm">
                                <i class="cil-pencil"></i>
                            </a>
                            <button class="btn btn-danger btn-sm btn-delete" attr="{{ $theme->id }}">
                                <i class="cil-trash"></i>
                            </button>
                            @include('backend.auth.video.common.select-company', [
                                'entityId' => $theme->id,
                                'entityAll' => $theme->all_companies,
                                'entityCompanies' => $theme->companies,
                                'companies' => $companies
                            ])
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @section('modals')

        <!-- delete confirmation modal -->
        <div class="modal fade" id="delete-confirm-modal" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Heads up!</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>This will discard existing theme. Are you sure?</p>
                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn btn-primary mr-2" data-dismiss="modal" id="delete-btn">Yes</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @endsection
    </x-slot>
</x-backend.card>

@endsection

@push("after-scripts")
<script type="text/javascript" src="{{ asset('js/video-theme.js?v='.time()) }}"></script>
<script>
function themenumberView(a,total) {
  var s=$("#themenumb"+a).val();
    
    if(total >= s){
       $("#themenumb_error"+a).html('');

        axios({
            method: "post",
            url: `/admin/auth/video/themes/${a}`,
            data: { 
                'id': a, 
                'value': s 
            },
            }).then(function (res) {

                    if(res.data.message==='Success'){
                        $("#themenumb_error"+a).html(' ');    
                    }else
                    {
                        $("#themenumb_error"+a).html('Invalid theme position');    
                    }
            }); 

    } else{
        $("#themenumb_error"+a).html('Invalid theme position');
        return false;
   }
}
</script>
@endpush
<style>
    .error{
        color: red;
    }
</style>