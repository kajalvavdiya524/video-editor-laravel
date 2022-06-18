@extends('backend.layouts.app')

@section('title', __('Theme Edit'))

@push("after-styles")
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <link href="{{ asset('css/fonts.css') }}" rel="stylesheet" type="text/css" >
@endpush

@section('content')

<x-backend.card>
    <x-slot name="header">
        <h4>Edit a theme</h4>
    </x-slot>
    <x-slot name="body">
      @if(Session::has('message'))
        <div class="alert alert-success" role="alert">{{ Session::get('message') }}</div>
      @endif
      @if ($errors->any())
        <div class="alert alert-danger">
          <ul>
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif
      <form method="POST" action="{{ route('admin.auth.video.themes.update', $theme->id) }}">
          <input type="hidden" name="custom_colors[color_type]" id="custom_colors_color_type" value="{{old('custom_colors[color_type]',$colorTypes)}}">
          <input type="hidden" name="custom_colors[color_name]" id="custom_colors_color_name" value="{{old('custom_colors[color_name]',$colorNames)}}">
          <input type="hidden" name="custom_colors[HEX_color]" id="custom_colors_HEX_color" value="{{old('custom_colors[HEX_color]',$colorHEX)}}">

        @csrf
        @method('PUT')
        <table class="table table-bordered datatable">
          <tbody>
            <tr>
              <th style="width: 20%;">
                Name
              </th>
              <td>
                <input class="form-control" name="name" type="text" value="{{ $theme->name }}" />
              </td>
            </tr>
            <tr>
              <th>
                  Font Names
              </th>
              <td>
                <select id="fonts-select" class="form-control" name="font_names[]" multiple="multiple" style="width: 100%;">
                  @foreach($fonts as $font)
                    <option
                      value="{{ $font }}"
                      @if (in_array($font, explode(',', $theme->font_names))) selected @endif
                    >
                      {{ $font }}
                    </option>
                  @endforeach
                </select>
              </td>
            </tr>
            <tr>
              <th>
                Default Font Name
              </th>
              <td>
                <select id="default-font-select" class="form-control" name="default_font_name" style="width: 100%;">
                  @foreach(explode(',', $theme->font_names) as $font)
                    <option style="font-family: {{ $font }}" value="{{ $font }}" @if ($theme->default_font_name == $font) selected @endif>{{ $font }}</option>
                  @endforeach
                </select>
              </td>
            </tr>
            <tr>
              <th>
                Default Font Size
              </th>
              <td>
                <input class="form-control" name="font_size" type="number" value="{{ $theme->font_size }}" />
              </td>
            </tr>
            <tr>
              <th>
                Stroke Colors
              </th>
              <td>
                  <div class="input-group">
                      <select id="stroke-colors-select" class="form-control" name="stroke_colors[]" multiple="multiple"  >
                          @foreach($color_stroke as $color=>$name)
                              <option
                                      value="{{ $color }}"
                                      @if (in_array($color, explode(',', $theme->stroke_colors))) selected @endif
                              >
                                  {{ $name }}
                              </option>
                          @endforeach
                      </select>
                      <div class="input-group-append">
                          <span class="btn custom_color" data-type="Stroke" style="width: 20%">{{__('Custom...')}}</span>
                      </div>
                      <div class="input-group form-check">
                        <input type="checkbox" class="form-check-input" id="stroke_color_selector" name="is_stroke_color_selector" @if($theme->is_stroke_color_selector == '1') checked @endif> 
                        <label for="stroke_color_selector">Color Selector</label>
                      </div>
                  </div>
              </td>
            </tr>
            <tr>
              <th>
                Default Stroke Width
              </th>
              <td>
                <input class="form-control" name="stroke_width" type="number" value="{{ $theme->stroke_width }}" />
              </td>
            </tr>
            <tr>
              <th>
                Font Colors
              </th>
              <td>
                  <div class="input-group">
                      <select id="font-colors-select" class="form-control" name="font_colors[]" multiple="multiple"  >
                          @foreach($color_fonts as $color=>$name)
                              <option
                                      value="{{ $color }}"
                                      @if (in_array($color, explode(',', $theme->font_colors))) selected @endif
                              >
                                  {{ $name }}
                              </option>
                          @endforeach
                      </select>
                      <div class="input-group-append">
                          <span class="btn custom_color" data-type="Font" style="width: 20%">{{__('Custom...')}}</span>
                      </div>
                      <div class="input-group form-check">
                          <input type="checkbox" class="form-check-input" id="font_color_selector" name="is_font_color_selector" @if($theme->is_font_color_selector == '1') checked @endif> 
                          <label for="font_color_selector">Color Selector</label>
                      </div>
                  </div>
              </td>
            </tr>
            <tr>
              <th>
                Default Font Color
              </th>
              <td>
                  <select id="default-font-color-select" class="form-control" name="default_font_color">
                      @foreach($color_fonts  as $color=>$name)
                          @if(in_array($color,explode(',', $theme->font_colors)))
                              <option value="{{ $color }}"
                                      @if ($color== $theme->default_font_color) selected @endif
                              >{{ $name }}</option>
                          @endif
                      @endforeach
                  </select>
              </td>
            </tr>
          </tbody>
        </table>
        <button class="btn btn-primary" type="submit">Update</button>
        <a class="btn btn-primary" href="{{ route('admin.auth.video.themes.index') }}">Return</a>
      </form>

          @section('modals')

          <!-- custom color picker confirmation modal -->
              <div class="modal fade" id="color-picker-modal" role="dialog">
                  <div class="modal-dialog">
                      <div class="modal-content">
                          <div class="modal-header">
                              <h5 class="modal-title">{{__('Custom Color')}}</h5>
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                  <span aria-hidden="true">&times;</span>
                              </button>
                          </div>
                          <div class="modal-body">
                              <form>
                                  <input type="hidden" name="type" id="color_type">
                                  <input type="hidden" name="theme_id" value="" id="theme_id">
                                  <div class="row">
                                      <div class="col">
                                          <div class="form-group">
                                              <label for="color_name">{{__('Name')}}</label>
                                              <input type="text" class="form-control" id="color_name" aria-describedby="color_name"
                                                     placeholder="{{__('Name')}}">
                                              <small id="emailHelp" class="form-text text-muted">{{__("What is the name of you'r color (optional)")}}</small>
                                          </div>
                                      </div>
                                  </div>

                                  <div class="row">
                                      <div class="col">
                                          <input type="hidden" class="form-control" id="HEX_color">
                                          <input type="hidden" class="form-control" id="RGB_Color">
                                          <div id="color-picker" class=" pb-4"></div>
                                      </div>
                                  </div>
                              </form>
                              <div class="d-flex justify-content-end">
                                  <button type="button" class="btn btn-primary mr-2" data-dismiss="modal" id="save-custom-color-btn">{{__('Save')}}</button>
                                  <button type="button" class="btn btn-secondary" data-dismiss="modal">{{__('Cancel')}}</button>
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
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script type="text/javascript" src="{{ asset('js/video-theme-edit.js') }}"></script>
@endpush