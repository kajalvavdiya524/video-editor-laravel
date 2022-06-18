@extends('backend.layouts.app')

@section('title', __('Media'))

@push("after-styles")
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <style>
    #trim-video-video {
      width: 100%;
    }
    .click-file {
      max-width: 200px;
    }
    .table td {
      vertical-align: middle;
    }
    .card .table td {
      max-width: 100px;
    }
    input::-webkit-outer-spin-button,
    input::-webkit-inner-spin-button {
      display: none;
    }
  </style>
@endpush

@section('content')

<x-backend.card>
  <x-slot name="header">
      <h4>Media</h4>
  </x-slot>
  <x-slot name="body">
    <div class="row">
      <div class="col-12 mt-2">
        <div style="float:left;">
          @if($parentFolder !== 'disable')
            <a class="btn btn-primary btn-sm" href="{{ route('admin.auth.video.media.folder.index', [ 'id' => $parentFolder ]) }}">
              <i class="cil-level-up"></i>
              back
            </a>
          @endif
          <a class="btn btn-primary btn-sm" href="{{ route('admin.auth.video.media.folder.add', [ 'thisFolder' => $thisFolder ]) }}">
            <i class="cil-plus"></i> 
            <i class="cil-folder"></i>
            New folder
          </a>
        </div>
        <div style="float:left;">
          <form method="POST" action="{{ route('admin.auth.video.media.file.add') }}" enctype="multipart/form-data" id="file-file-form">
            @csrf
            <input type="hidden" name="thisFolder" value="{{ $thisFolder }}" id="this-folder-id"/> 
            <label class="btn btn-primary btn-sm ml-1">
              <i class="cil-plus"></i>
              <i class="cil-file"></i>
              New file <input type="file" name="file" id="file-file-input" hidden>
            </label> 
          </form>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-8">
        @if($parentFolder !== 'disable')
          <form action="{{ route('admin.auth.video.media.folder.search', ['id' => $thisFolder]) }}" method="POST">
            @csrf
            <div class="form-group d-flex mt-2">
              <select id="tags-select-search" class="form-control" name="ts[]" multiple="multiple" style="width: 100%;">
                @foreach($tags as $tag)
                  <option
                    value="{{ $tag->id }}"
                    @if (in_array($tag->id, $ts)) selected @endif
                  >
                    {{ $tag->name }}
                  </option>
                @endforeach
              </select>
              <button class="btn btn-primary btn-sm ml-1" type="submit"><i class="cil-search"></i></button>
            </div>
          </form>
        @endif
          <table class="table table-striped table-sm table-bordered datatable">
              <tbody>
                @foreach($mediaFolders as $mediaFolder)
                  <tr>
                    <td>
                      <a href="{{ route('admin.auth.video.media.folder.index', [ 'id' => $mediaFolder->id ]) }}">
                        <i class="cil-folder"></i> 
                        {{ $mediaFolder->name }}
                      </a>
                    </td>
                    <td>
                      <button
                        class="btn btn-primary btn-sm file-change-folder-name"
                        atr="{{ $mediaFolder->id }}"
                      >
                        Rename
                      </button>
                    </td>
                    <td></td>
                    <td>
                      <button 
                        class="btn btn-primary btn-sm file-move-folder"
                        atr="{{ $mediaFolder->id }}"
                      >
                        Move
                      </button>
                    </td>
                    <td></td>
                    <td>
                      @if($mediaFolder->resource != 1)
                        <button 
                          class="btn btn-danger btn-sm file-delete-folder"
                          atr="{{ $mediaFolder->id }}"
                        >
                          Delete
                        </button>
                      @endif
                    </td>
                  </tr>
                @endforeach
                @foreach($medias as $media)
                  <tr>
                    @if($media->type == 'image' || $media->type == 'video')
                      <td>
                        {{-- <img src="{{ $media->getUrl('thumb') }}" alt="{{ $media->name }}" style="max-width: 50px; max-height: 50px;"> --}}
                        <img src="/img/icons/video-folder.svg" alt="{{ $media->name }}" style="max-width: 50px; max-height: 50px;">
                      </td>
                    @endif
                    <td class="click-file" atr="{{ $media->id }}">
                      <i class="cil-file"></i>
                      {{ strlen($media->name) > 30 ? substr($media->name, 0, 30) . "..." : $media->name }}
                    </td>
                    <td>
                      <a
                        href="{{ $media->getUrl() }}"
                        class="btn btn-primary btn-sm"
                        download
                      >
                        <i class="cil-data-transfer-down"></i>
                      </a>
                    </td>
                    <td>
                      <a
                        href="<?php echo $media->getUrl(); ?>"
                        class="btn btn-primary btn-sm"
                        target="_blank"
                      >
                        <i class="cil-external-link"></i>
                      </a>
                    </td>
                    <td>
                      <button
                        class="btn btn-primary btn-sm file-change-file-name"
                        atr="{{ $media->id }}"
                        atr-type="{{ $media->type }}"
                      >
                        Rename
                      </button>
                    </td>
                    <td>
                      <a 
                        href="{{ route('admin.auth.video.media.file.copy', ['id' => $media->id, 'thisFolder' => $thisFolder]) }}"
                        class="btn btn-primary btn-sm"
                      >   
                        <i class="cil-copy"></i>
                      </a>
                    </td>
                    <td>
                      <button 
                        class="btn btn-primary btn-sm file-move-file"
                        atr="{{ $media->id }}"
                      >
                        Move
                      </button>
                    </td>
                    <td>
                        @php
                          $mime = explode('/', $media->mime_type);
                          if($mime[0] === 'image') {
                        @endphp
                            <button 
                              class="btn btn-success btn-sm file-cropp-file"
                              atr="{{ $media->id }}"
                            >
                              Cropp
                            </button>
                        @php
                          } else if ($mime[0] == 'video') {
                        @endphp
                          <button 
                            class="btn btn-success btn-sm file-trim-file"
                            atr="{{ $media->id }}"
                          >
                            Trim
                          </button>
                        @php
                          }
                        @endphp
                    </td>
                    <td>
                      <button
                        class="btn btn-primary btn-sm tag-btn"
                        atr="{{ $media->id }}"
                      >
                        Tag
                      </button>
                    </td>
                    <td>
                      <button 
                        class="btn btn-danger btn-sm file-delete-file"
                        atr="{{ $media->id }}"
                      >
                        <i class="cil-trash"></i>
                      </button>
                    </td>
                  </tr>
                @endforeach
              </tbody>
          </table>
      </div>
      <div class="col-4">

        <div class="card border-primary" id="file-move-folder">
          <div class="card-header">
            <h4>Move folder</h4>
          </div>
          <div class="card-body">
            <form method="post" action="{{ route('admin.auth.video.media.folder.move') }}">
              @csrf
              <input type="hidden" name="thisFolder" value="{{ $thisFolder }}">
              <input type="hidden" name="id" value="" id="file-move-folder-id">
              <table class="table table-striped table-sm table-bordered">
                @if($parentFolder !== 'disable')
                  <tr>
                    <td>
                      <input type="radio" name="folder" value="moveUp">
                    </td>
                    <td>
                      Move up
                    </td>
                  </tr>
                @endif
                @foreach($mediaFolders as $mediaFolder)
                  <tr>
                    <td>
                      <input 
                        type="radio" 
                        name="folder" 
                        value="{{ $mediaFolder->id }}"
                        class="file-move-folder-radio"
                      >
                    </td>
                    <td>
                      {{ $mediaFolder->name }}
                    </td>
                  </tr>
                @endforeach
              </table>
              <button type="submit" class="btn btn-primary btn-sm mt-3">Save</button>
              <button type="button" class="btn btn-primary btn-sm mt-3" id="file-move-folder-cancel">Cancel</button>
            </form>
          </div>
        </div>

        <div class="card border-primary" id="file-move-file">
            <div class="card-header">
                <h4>Move file</h4>
            </div>
            <div class="card-body">
                <form method="post" action="{{ route('admin.auth.video.media.file.move') }}">
                    @csrf
                    <input type="hidden" name="thisFolder" value="{{ $thisFolder }}">
                    <input type="hidden" name="id" value="" id="file-move-file-id">
                    <table class="table table-striped table-bordered">
                        @if($parentFolder !== 'disable')
                            <tr>
                                <td>
                                    <input type="radio" name="folder" value="moveUp">
                                </td>
                                <td>
                                    Move up
                                </td>
                            </tr>
                        @endif
                        @foreach($mediaFolders as $mediaFolder)
                            <tr>
                                <td>
                                    <input type="radio" name="folder" value="{{ $mediaFolder->id }}">
                                </td>
                                <td>
                                    {{ $mediaFolder->name }}
                                </td>
                            </tr>
                        @endforeach
                    </table>
                    <button type="submit" class="btn btn-primary mt-3">Save</button>
                    <button type="button" class="btn btn-primary mt-3" id="file-move-file-cancel">Cancel</button>
                </form>
            </div>
        </div>

        <div class="card border-primary" id="file-rename-file-card">
            <div class="card-header">
                <h4>Rename file</h4>
            </div>
            <div class="card-body">
                <form method="post" action="{{ route('admin.auth.video.media.file.update') }}">
                    @csrf
                    <input type="hidden" name="thisFolder" value="{{ $thisFolder }}">
                    <input type="hidden" name="id" value="" id="file-rename-file-id">
                    <input 
                        type="text"
                        name="name"
                        id="file-rename-file-name"
                        class="form-control"
                    >
                    <button type="submit" class="btn btn-primary mt-3">Save</button>
                    <button type="button" class="btn btn-primary mt-3" id="file-rename-file-cancel">Cancel</button>
                </form>
            </div>
        </div>

        <div class="card border-primary" id="file-rename-folder-card">
            <div class="card-header">
                <h4>Rename folder</h4>
            </div>
            <div class="card-body">
                <form method="post" action="{{ route('admin.auth.video.media.folder.update') }}">
                    @csrf
                    <input type="hidden" name="thisFolder" value="{{ $thisFolder }}">
                    <input type="hidden" name="id" value="" id="file-rename-folder-id">
                    <input 
                        type="text" 
                        name="name" 
                        id="file-rename-folder-name"
                        class="form-control"
                    >
                    <button type="submit" class="btn btn-primary mt-3">Save</button>
                    <button type="button" class="btn btn-primary mt-3" id="file-rename-folder-cancel">Cancel</button>
                </form>
            </div>
        </div>

        <div class="card border-primary" id="file-info-card">
            <div class="card-header">
                <h4>File info</h4>
            </div>
            <div class="card-body">
              <table class="table table-striped table-sm table-bordered">
                <tr>
                  <td>
                    Name
                  </td>
                  <td id="file-div-name">
                      
                  </td>
                </tr>
                <tr>
                  <td>
                    Real Name
                  </td>
                  <td id="file-div-real-name">

                  </td>
                </tr>
                <tr>
                  <td>
                    URL
                  </td>
                  <td id="file-div-url">

                  </td>
                </tr>
                <tr>
                  <td>
                    mime type
                  </td>
                  <td id="file-div-mime-type">

                  </td>
                </tr>
                <tr>
                  <td>
                    Size
                  </td>
                  <td id="file-div-size">

                  </td>
                </tr>
                <tr>
                  <td>
                    Created at
                  </td>
                  <td id="file-div-created-at">

                  </td>
                </tr>
                <tr>
                  <td>
                    Updated at
                  </td>
                  <td id="file-div-updated-at">

                  </td>
                </tr>
              </table> 
            </div>
        </div>
      </div>
    </div>
    
    @section('modals')
    <div class="modal fade" id="remove-file-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Delete file</h4>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure?</p>
                </div>
                <div class="modal-footer">
                    <form action="{{ route('admin.auth.video.media.file.delete') }}" method="POST">
                        @csrf
                        <input type="hidden" name="thisFolder" value="{{ $thisFolder }}">
                        <input type="hidden" name="id" value="" id="file-delete-file-id">
                        <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                        <button class="btn btn-primary" type="submit">Delete</button>
                    </form>
                </div>
            </div>
            <!-- /.modal-content-->
        </div>
        <!-- /.modal-dialog-->
    </div>

    <div class="modal fade" id="remove-folder-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Delete folder</h4>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <p>If you delete a folder, all subfolders and files will also be deleted</p>
                </div>
                <div class="modal-footer">
                    <form action="{{ route('admin.auth.video.media.folder.delete') }}" method="POST">
                        @csrf
                        <input type="hidden" name="thisFolder" value="{{ $thisFolder }}">
                        <input type="hidden" name="id" value="" id="file-delete-folder-id">
                        <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                        <button class="btn btn-primary" type="submit">Delete</button>
                    </form>
                </div>
            </div>
            <!-- /.modal-content-->
        </div>
        <!-- /.modal-dialog-->
    </div>

    <div class="modal fade" id="cropp-img-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Cropp image</h4>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    
                    <img src="" id="cropp-img-img" />


                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary" type="button" id="cropp-img-save-button">Save</button>
                </div>
            </div>
            <!-- /.modal-content-->
        </div>
        <!-- /.modal-dialog-->
    </div>

    <div class="modal fade" id="trim-video-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h4 class="modal-title">Trim video</h4>
            <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
          </div>
          <div class="modal-body text-center">
            <video controls muted loop id="trim-video-video"></video>
            
            <div class="row" align="center">
              <div class="col">
                <div class="input-group">
                  <input id="trim-start" type="number" class="form-control" name="start" placeholder="Start">
                  <div class="input-group-append">
                    <div class="input-group-text">s</div>
                  </div>
                </div>
              </div>
              <div class="col">
                <div class="input-group">
                  <input id="trim-end" type="number" class="form-control" name="end" placeholder="End">
                  <div class="input-group-append">
                    <div class="input-group-text">s</div>
                  </div>
                </div>
              </div>
              <div class="col">
                  <div class="form-control form-check">
                    <input type="checkbox" class="form-check-input" id="trim-check">
                    <label class="form-check-label" for="trim-check">Trim</label>
                  </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn" type="button" data-dismiss="modal">Cancel</button>
            <button class="btn btn-secondary" type="button" id="trim-video-preview-button">Preview</button>
            <button class="btn btn-primary" type="button" id="trim-video-save-button">
              <!-- <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
              Saving... -->
              Save
            </button>
          </div>
        </div>
        <!-- /.modal-content-->
      </div>
      <!-- /.modal-dialog-->
    </div>

    <div class="modal fade" id="tag-modal" role="dialog">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h4 class="modal-title">Tag</h4>
            <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
          </div>
          <div class="modal-body">
            <div class="form-group flex-grow-1">
              <label for="tags-select">Select Tag</label>
              <select id="tags-select" class="form-control" multiple="multiple" style="width: 100%;">
                @foreach($tags as $tag)
                  <option
                    value="{{ $tag->id }}"
                  >
                    {{ $tag->name }}
                  </option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="modal-footer">
              <button class="btn btn-primary btn-sm" id="tag-save-btn">Save</button>
          </div>
        </div>
        <!-- /.modal-content-->
      </div>
      <!-- /.modal-dialog-->
    </div>
    @endsection
  </x-slot>
</x-backend.card>
@endsection

@push("after-scripts")
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script type="text/javascript" src="{{ asset('js/cropper.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/video-media.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/video-media-cropper.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/video-media-trim.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/video-media-tag.js') }}"></script>
@endpush