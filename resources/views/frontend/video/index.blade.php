@extends('frontend.layouts.app')

@section('title', __('Video Preview'))

@push('after-styles')
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.css">
@endpush

@section('content')

    <div>
        <video-table :user-id="{{ json_encode($user_id) }}" :column-types="{{ json_encode($column_types) }}"
            :default-column-visibility-name="{{ json_encode($selected_column_visibility_name) }}"
            :all-columns="{{ json_encode($all_columns) }}"
            :column-visibility-types="{{ json_encode($column_visibility_types) }}"
            :custom-columns="{{ json_encode($custom_columns) }}" :timeframe-column="{{ json_encode($timeframe_column) }}"
            :preview-sizes="{{ json_encode($preview_sizes) }}"
            :colors="{{ json_encode($colors) }}" :font_names="{{ json_encode($font_names) }}"
            :themes="{{ json_encode($themes) }}" :tags="{{ json_encode($tags) }}"
            :musics="{{ json_encode($musics) }}" :videos="{{ json_encode($videos) }}"
            :images="{{ json_encode($images) }}" :creation-data="{{ json_encode($creation_data) }}" :delete-icon="{{ json_encode(asset('img/icons/x-icon.png')) }}"/>
    </div>

@endsection
