@extends('backend.layouts.app')

@section('title', __('Settings'))

@section('content')
    <x-forms.patch id="template-form" :action="route('admin.auth.settings.template.update')">
        <x-backend.card>
            <x-slot name="header">
                @lang('Theme')
            </x-slot>

            <x-slot name="body">
                <div class="form-row">
                    <div class="col-2">
                        <label class="font-weight-bold">Customer</label>
                        <div class="form-group">
                            <select class="form-control" name="customer">
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->value }}" {{ $customer->name == "Amazon Fresh" ? "selected": "" }}>{{$customer->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-1">
                        <label class="font-weight-bold">--pil-font</label>
                        <div class="form-group ml-4">
                            <input type="hidden" class="custom-control-input" value="off" id="pil_font_hidden" name="pil_font" />
                            <input type="checkbox" class="custom-control-input" value="on" id="pil_font" name="pil_font" {{ $settings["pil_font"] == "on" ? "checked" : "" }}/>
                            <label class="custom-control-label" for="pil_font"></label>
                        </div>
                    </div>
                    <div class="col-1">
                        <label class="font-weight-bold">Show 3H</label>
                        <div class="form-group ml-4">
                            <input type="hidden" class="custom-control-input" value="off" id="show_3h_hidden" name="Show_3H" />
                            <input type="checkbox" class="custom-control-input" value="on" id="show_3h" name="Show_3H" {{ $settings["Show_3H"] == "on" ? "checked" : "" }}/>
                            <label class="custom-control-label" for="show_3h"></label>
                        </div>
                    </div>
                    <div class="col-2">
                        <label class="font-weight-bold">Show Text Tracking</label>
                        <div class="form-group ml-4">
                            <input type="hidden" class="custom-control-input" value="off" id="show_text_tracking_hidden" name="Show_Text_Tracking" />
                            <input type="checkbox" class="custom-control-input" value="on" id="show_text_tracking" name="Show_Text_Tracking" {{ $settings["Show_Text_Tracking"] == "on" ? "checked" : "" }}/>
                            <label class="custom-control-label" for="show_text_tracking"></label>
                        </div>
                    </div>
                </div>
                <div>
                    <label class="font-weight-bold">Text positions</label>
                    <div class="form-row">
                        <div class="col-12">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <th>Template</th>
                                    <th>Row</th>
                                    <th>Left</th>
                                    <th>Top</th>
                                    <th>Font Size</th>
                                    <th>Font Family</th>
                                    <th>T&C Position</th>
                                </thead>
                                <tbody>
                                    @php
                                    $customer = "AmazonFresh";
                                    $map = Config::get("templates.AmazonFresh.dimensions_map");
                                    @endphp
                                    @foreach ($map as $template => $rows)
                                        @php
                                            $template_id = $customer."_".$template;
                                            $cta_pos_id = $template_id."_CTA_pos";
                                        @endphp
                                        @for ($i = 0; $i < count($rows); $i++)
                                            @php
                                                $id = $template_id."_".$rows[$i];
                                            @endphp
                                            <tr>
                                                @if ($i == 0)
                                                    <td rowspan="{{ count($rows) }}">{{ $template }}</td>
                                                @endif
                                                <td>{{ $rows[$i] }}</td>
                                                <td><input type="text" class="form-control" name="{{ $id."_Left" }}" value="{{ $settings[$id."_Left"] }}" /></td>
                                                <td><input type="text" class="form-control" name="{{ $id."_Top" }}" value="{{ $settings[$id."_Top"] }}" /></td>
                                                <td><input type="text" class="form-control" name="{{ $id."_FontSize" }}" value="{{ $settings[$id."_FontSize"] }}" /></td>
                                                <td><input type="text" class="form-control" name="{{ $id."_FontFamily" }}" value="{{ $settings[$id."_FontFamily"] }}" /></td>
                                                @if ($i == 0)
                                                    <td rowspan="{{ count($rows) }}">
                                                        <div class="row">
                                                            <span class="col-4">Left: </span>
                                                            <input type="text" class="form-control col-7" name="{{ $cta_pos_id }}_Left" value="{{ $settings[$cta_pos_id."_Left"] }}" />
                                                        </div>
                                                        <div class="row">
                                                            <span class="col-4">Top: </span>
                                                            <input type="text" class="form-control col-7" name="{{ $cta_pos_id }}_Top" value="{{ $settings[$cta_pos_id."_Top"] }}" />
                                                        </div>
                                                        <div class="row">
                                                            <span class="col-4">Right: </span>
                                                            <input type="text" class="form-control col-7" name="{{ $cta_pos_id }}_Right" value="{{ $settings[$cta_pos_id."_Right"] }}" />
                                                        </div>
                                                    </td>
                                                @endif
                                            </tr>
                                        @endfor
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div>
                    <label class="font-weight-bold">Drop shadow</label>
                    <div class="form-row">
                        <div class="col-sm-2 col-6">
                            <div class="form-group">
                                <label>Radius</label>
                                <input type="text" class="form-control" name="DropShadow_Radius" value="{{ $settings["DropShadow_Radius"] }}" />
                            </div>
                        </div>
                        <div class="col-sm-2 col-6">
                            <label>Color</label>
                            <div class="form-group">
                                <input type="text" name="DropShadow_Color" class="form-control color-hex" placeholder="Color Hex Code" value="{{ $settings["DropShadow_Color"] }}">
                            </div>
                        </div>
                        <div class="col-sm-2 col-6">
                            <div class="form-group">
                                <label>X</label>
                                <input type="text" class="form-control" name="DropShadow_X" value="{{ $settings["DropShadow_X"] }}"  />
                            </div>
                        </div>
                        <div class="col-sm-2 col-6">
                            <div class="form-group">
                                <label>Y</label>
                                <input type="text" class="form-control" name="DropShadow_Y" value="{{ $settings["DropShadow_Y"] }}"  />
                            </div>
                        </div>
                    </div>
                </div>
                <div>
                    <label class="font-weight-bold">Products area</label>
                    <div class="form-row" id="AmazonFresh_Products_Area">
                        <div class="col-sm-2 col-3">
                            <div class="form-group">
                                <label>Top</label>
                                <input type="text" class="form-control" id="AmazonFresh_Products_Top" name="AmazonFresh_Products_Top" value="{{ $settings["AmazonFresh_Products_Top"] }}" />
                            </div>
                        </div>
                        <div class="col-sm-2 col-3">
                            <div class="form-group">
                                <label>Left</label>
                                <input type="text" class="form-control" id="AmazonFresh_Products_Left" name="AmazonFresh_Products_Left" value="{{ $settings["AmazonFresh_Products_Left"] }}" />
                            </div>
                        </div>
                        <div class="col-sm-2 col-3">
                            <div class="form-group">
                                <label>Bottom</label>
                                <input type="text" class="form-control" id="AmazonFresh_Products_Bottom" name="AmazonFresh_Products_Bottom" value="{{ $settings["AmazonFresh_Products_Bottom"] }}" />
                            </div>
                        </div>
                        <div class="col-sm-2 col-3">
                            <div class="form-group">
                                <label>Right</label>
                                <input type="text" class="form-control" id="AmazonFresh_Products_Right" name="AmazonFresh_Products_Right" value="{{ $settings["AmazonFresh_Products_Right"] }}" />
                            </div>
                        </div>
                        <div class="col-sm-2 col-3">
                            <div class="form-group">
                                @php
                                    $w = $settings["AmazonFresh_Products_Right"] - $settings["AmazonFresh_Products_Left"];
                                    $h = $settings["AmazonFresh_Products_Bottom"] - $settings["AmazonFresh_Products_Top"];
                                @endphp
                                <br>
                                <br>
                                <span id="size">WxH: {{ $w }} x {{ $h }}</span> | 
                                <span id="ratio">W:H {{ round($w / $h, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </x-slot>

            <x-slot name="footer">
                <button class="btn btn-sm btn-success float-right ml-2" type="button" id="btn-reset">@lang('Reset to defaults')</button>
                <button class="btn btn-sm btn-primary float-right" id="btn-submit" type="button">@lang('Submit')</button>
            </x-slot>
        </x-backend.card>
    </x-forms.patch>

    <x-forms.post id="reset-form" :action="route('admin.auth.settings.template.reset')">
    </x-forms.post>
@endsection

@push("after-scripts")
	<script type="text/javascript" src="{{ asset("js/template.js") }}"></script>
@endpush