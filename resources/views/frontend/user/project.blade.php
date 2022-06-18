@extends('frontend.layouts.app')

@section('title', __('Projects'))

@section('meta')
<meta name="siteUrl" content="{{ siteUrl() }}">
<meta name="userName" content="{{ auth()->user()->name }}">
<meta name="userId" content="{{ auth()->user()->id }}">
@endsection

@php
$columns = Config::get('columns.project');
$columns_url = 'projects/columns';
@endphp

@push("after-styles")
<script>
function get_state(){
  if (localStorage.getItem("grid_view")) {
    return localStorage.getItem("grid_view");
  }else{
    return "list";
  }
}
function set_state(aState){
  localStorage.setItem("grid_view",aState);
}

function get_type(){
  if (localStorage.getItem("project_type")) {
    return localStorage.getItem("project_type");
  }else{
    return "image";
  }
}
function set_type(type){
  localStorage.setItem("project_type",type);
}

function draw_grid (){
  
  $("div.card-body").first().find("#grid_elements").remove();

  $("div.card-body").append("<div id='grid_elements'></div>");
  var table = $(".table-striped").first();
  
  // foreach element currently shown on the list
  $(table).find('tr').each(function() {

    var lasttd=  $(this).children('td').last();
    if (lasttd.length > 0 ){
      var id = $(lasttd).find("#project-id").val();
      var name = $(lasttd).find("#project-name").val();
      var type = $(lasttd).find("#project-type").val();
      if (name){
      var the_div = `
          <div class="col-sm-6 mb-3" id="grid_${id}">
            <h5 class="card-title">${name}</h5>
          </div> `;

      $("div.card-body").first().find("#grid_elements").append(the_div);
      if (type != 1) {
      axios({
        method: "get",
        url: "/projects/" + id + "/show",
        headers: {
          "Content-Type": "multipart/form-data",
        },
      })
        .then(function (response) {
          var data = response.data;
          var files = data.jpg_files.split(" ");
          
          var images = [];
          for (var file of files) {
            var image = $(
              `<img class="card-img-bottom border" src="/share?file=outputs/jpg/${file}">`  
                );
                images.push(image);
              }
          $("#grid_"+id).append(images);
        })
        .catch(function (response) {
          // showError([response]);
        });
      }
    }
    }

  });

  $(table).hide();
}

function show_results(){
  var state = get_state();
  if (state == 'grid'){
    $("#togle_list").html("<i class='cil-list'></i>");
    $(".table-striped").hide();
    $("#gearbox_button").hide();
    draw_grid();
    
  }else{
    //show list
    $("#togle_list").html("<i class='cil-grid'></i>");
    $("div.card-body").first().find("#grid_elements").remove();
    $(".table-striped").show();
    $("#gearbox_button").show();
  }

}

function toggle_viewmode(toggle = true){

  var state = get_state();

  if (state == 'list'){
    // show grid
    set_state("grid");
   
  }else{
    //show list
    set_state("list");
  }
  
  show_results();

}
</script>
@endpush

@section('content')
    <div class="row justify-content-center" id="project-section">
        <div class="col-md-12">
            <x-frontend.card>
                <x-slot name="header">
                    @lang('Projects Browser')
                </x-slot>

                <x-slot name="headerActions">
                    <button type="button" class="btn btn-link" id="download_all" style="display: none">
                        Download Selected
                    </button>

                    <ul class="nav nav-pills justify-content-center" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="active" id="images-tab" data-toggle="pill" href="#images" role="tab" aria-controls="images" aria-selected="true">Images</a>
                        </li>
                        <span> | </span>
                        <li class="nav-item" role="presentation">
                            <a class="" id="videos-tab" data-toggle="pill" href="#videos" role="tab" aria-controls="videos" aria-selected="false">Videos</a>
                        </li>
                    </ul>
                  
                    <button type="button" class="btn btn-sm" title="Select columns" id="gearbox_button" data-toggle="modal" data-target="#columnsModal">
                        <i class="cil-cog"></i>
                    </button>
                    
                    <button type="button" class="btn btn-sm" title="Toggle grid view" id="togle_list" data-state="list" >
                        <i class="cil-grid"></i>
                    </button>
                </x-slot>

                <x-slot name="body">
                    <div class="tab-content">
                        <div class="tab-pane fade active show" id="images" role="tabpanel" aria-labelledby="images-tab">
                            <livewire:project-table />
                        </div>
                        <div class="tab-pane fade" id="videos" role="tabpanel" aria-labelledby="videos-tab">
                            <livewire:video-project-table />
                        </div>
                    </div>
                </x-slot>
           
            </x-frontend.card>
        </div><!--col-md-12-->
    </div><!--row-->



@endsection

@section('modals')
    <!-- Share Modal -->
    @include('frontend.includes.modals.share_history')

    <!-- Column Options Modal -->
    @include('frontend.includes.modals.project_columns')
@endsection

@push("after-scripts")
    <script type="text/javascript" src="{{ asset("js/project.js") }}"></script>
    <script type="text/javascript" src="{{ asset("js/columns.js") }}"></script>
    <script type="text/javascript" src="{{ asset("js/col_resizable.js") }}"></script>
    <script>
    document.addEventListener("livewire:load", function(event) {
        set_type('image');
        window.livewire.hook('beforeDomUpdate', () => {
            // Add your custom JavaScript here.
            $(".table-striped").hide();
        });
 
        window.livewire.hook('afterDomUpdate', () => {
            // Add your custom JavaScript here.
            show_results();
        });
    });
    </script>
    <script>
          
        $(document).ready( function() {
            
          var project_type = get_type();
          if(project_type == 'video') {
            $('#videos').find('.table-striped').first().attr('isResizable', true);
            $('#videos').find('.table-striped').first().colResizable({
                liveDrag:true,
                postbackSafe:true,
                partialRefresh:true,
                // resizeMode: 'overflow'
            });	
          } else {
            $('.table-striped').first().attr('isResizable', true);
            $('.table-striped').first().colResizable({
                liveDrag:true,
                postbackSafe:true,
                partialRefresh:true,
                resizeMode: 'overflow'
            });	
          }

          $(document).on('click', '#videos-tab', function(){
              set_state("grid");
              toggle_viewmode();  
              set_type('video');
              $('#gearbox_button').addClass('d-none');
              $('#togle_list').addClass('d-none');
          });
          
          $(document).on('click', '#images-tab', function(){
              set_type('image');
              $('#gearbox_button').removeClass('d-none');
              $('#togle_list').removeClass('d-none');
          });
            

        } );

        function TableChangeListener(){

          var project_type = get_type();
          if(project_type == 'video') {
            var attr = $('#videos').find('.table-striped').first().attr('isResizable');
            if (typeof attr == 'undefined' || attr == false) {
                $('#videos').find('.table-striped').first().attr('isResizable', true);
                $('#videos').find('.table-striped').first().colResizable({
                  liveDrag:true,
                  postbackSafe:true,
                  partialRefresh:true,
                  // resizeMode: 'overflow'
               });	
            }
          } else {
            var attr = $('.table-striped').first().attr('isResizable');
            // For some browsers, `attr` is undefined; for others,
            // `attr` is false.  Check for both.
            if (typeof attr == 'undefined' || attr == false) {
                $('.table-striped').first().attr('isResizable', true);
                $('.table-striped').first().colResizable({
                  liveDrag:true,
                  postbackSafe:true,
                  partialRefresh:true,
                  resizeMode: 'overflow'
               });	
            }
          }
        }

        setInterval(TableChangeListener, 500);

    </script> 
    <style>
        .JCLRgrip .JColResizer {
            position: absolute;
            background-color: #ececec;
            filter: alpha(opacity=1);
            opacity: 0.5;
            width: 3px;
            height: 100%;
            cursor: e-resize;
            top: 0px;
        }

        .JColResizer > tbody > tr > td, 
        .JColResizer > tbody > tr > th {
          padding-left: 0.75em !important;
          padding-right: 0.75em !important;
        }

        #project-section .nav-item{
          padding: 0px 10px;
        }

        #project-section .nav-pills a{
          color: #313232;
        }

        #project-section .nav-pills a.active{
          color: #007bff;
        }

        .btn-project-edit{
          color: #fff;
          background-color: #321fdb;
          border-color: #321fdb;
        }
        .btn-project-edit:hover{
          color: #fff;
          background-color: #2a1ab9;
          border-color: #2819ae;
        }
    </style>


@endpush