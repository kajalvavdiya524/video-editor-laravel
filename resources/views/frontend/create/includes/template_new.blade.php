<div class="template-wrapper">
    <p class="d-flex">
        Template&nbsp;&nbsp;
        <a href="/banner/{{ $customer_id }}/group"><i class="c-icon cil-grid" aria-hidden="true" data-toggle="tooltip" title="Layout"></i></a>
    </p>
    @php
        $default_template = isset($settings->output_dimensions) ? $settings->output_dimensions : 0;
        $j = 0;
        for ($j = 0; $j < count($templates); $j++) {
            $t = $templates[$j];
            if (($t['system'] && $t['system_key'] == $default_template) || (!$t['system'] && $t['id'] == $default_template)) {
                break;
            }
        }
    @endphp
    @if(isset($template))
        <input type="hidden" name="output_dimensions" value="{{ $default_template }}" />
        <input type="hidden" name="template_name" value="{{ $template->name }}" />
        <div class="selected-template">
            <div class="slide-item">
                @if ($template->image_url == "")
                    <p class="selected">{{ $template->name }}</p>
                @else
                    <img class="selected" src="{{ asset($template->image_url) }}" title="{{ $template->name }}" loading="lazy" />
                @endif
            </div>
        </div>
        <div class="templates slide-popup">
            <div class="templates-carousel-hidden d-none">
                <div class="slide-item">
                    @if ($template->image_url == "")
                        <p class="selected" data-value="{{ $template->id }}">{{ $template->name }}</p>
                    @else
                        <img class="selected" src="{{ asset($template->image_url) }}" title="{{ $template->name }}" data-value="{{ $template->id }}" loading="lazy" />
                    @endif
                </div>
                @for ($i = $j + 1; $i < count($templates); $i++)
                    <div class="slide-item">
                        @if ($templates[$i]['image_url'] == "")
                            <p data-value="{{ $templates[$i]['id'] }}">{{ $templates[$i]['name'] }}</p>
                        @else
                            <img src="{{ asset($templates[$i]['image_url']) }}" title="{{ $templates[$i]['name'] }}" data-value="{{ $templates[$i]['id'] }}" loading="lazy" />
                        @endif
                    </div>
                @endfor
                @for ($i = 0; $i < $j; $i++)
                    <div class="slide-item">
                        @if ($templates[$i]['image_url'] == "")
                            <p data-value="{{ $templates[$i]['id'] }}">{{ $templates[$i]['name'] }}</p>
                        @else
                            <img src="{{ asset($templates[$i]['image_url']) }}" title="{{ $templates[$i]['name'] }}" data-value="{{ $templates[$i]['id'] }}" loading="lazy" />
                        @endif
                    </div>
                @endfor
            </div>
        </div>
    @else
        <div style="height: 100px;">No templates yet.</div>
    @endif
</div>