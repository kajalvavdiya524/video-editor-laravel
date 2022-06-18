@php
$unchecked_columns = array_diff(array_keys($columns), $active_columns);
@endphp

<div class="modal fade" id="columnsModal" tabindex="-1" role="dialog" aria-labelledby="columnsModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Columns</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" action="{{ $columns_url }}">
                @csrf
                <div class="modal-body">
                    <div class="columns-list">
                        @foreach($active_columns as $col)
                            @if (isset($columns[$col]))
                            <div class="form-group d-flex">
                                <div class="mr-2 column-handler">
                                    <i class="cil-menu"></i>
                                </div>
                                <div class="custom-control custom-checkbox">
                                    <input checked type="checkbox" id="{{ $col }}" name="{{ $col }}" class="custom-control-input">
                                    <label class="custom-control-label" for="{{ $col }}">{{ $columns[$col] }}</label>
                                </div>
                            </div>
                            @endif
                        @endforeach
                        @foreach($unchecked_columns as $col)
                        <div class="form-group d-flex">
                            @if (isset($columns[$col]))
                            <div class="mr-2 column-handler">
                                <i class="cil-menu"></i>
                            </div>
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" id="{{ $col }}" name="{{ $col }}" class="custom-control-input">
                                <label class="custom-control-label" for="{{ $col }}">{{ $columns[$col] }}</label>
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>