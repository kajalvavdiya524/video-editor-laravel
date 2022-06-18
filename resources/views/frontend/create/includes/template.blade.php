<div class="template-wrapper">
    <p>Template</p>
    @php
        $output_dimensions = Config::get("templates.".$customer_name.".output_dimensions");
        if (isset($new_templates)) {
            $output_dimensions = array_merge($output_dimensions, $new_templates);
        }
        $default_template = isset($settings->output_dimensions) ? $settings->output_dimensions : 0;
        $count = count($output_dimensions);
    @endphp
    <input type="hidden" name="output_dimensions" value="{{ $default_template }}" />
    <input type="hidden" name="template_name" value="{{ $output_dimensions[$customer_name == 'Generic' ? ($default_template + 1) : $default_template] }}" />
    <div class="selected-template">
        <div class="slide-item">
            <img class="selected" src="{{ asset('img/templates/'.$customer_name.'/'.$default_template.'.png') }}" title="{{ $output_dimensions[$customer_name == 'Generic' ? ($default_template + 1) : $default_template] }}" loading="lazy" />
        </div>
    </div>
    <div class="templates slide-popup">
        <div class="templates-carousel-hidden d-none">
            <div class="slide-item">
                <img class="selected" src="{{ asset('img/templates/'.$customer_name.'/'.$default_template.'.png') }}" title="{{ $output_dimensions[$customer_name == 'Generic' ? ($default_template + 1) : $default_template] }}" data-value="{{ $default_template }}" loading="lazy" />
            </div>
            @for ($i = $default_template + 1; $i < (($customer_name == 'Generic') ? $count-1 : $count); $i++)
                <div class="slide-item">
                    <img src="{{ asset('img/templates/'.$customer_name.'/'.$i.'.png') }}" title="{{ $output_dimensions[$customer_name == 'Generic' ? ($i + 1) : $i] }}" data-value="{{ $i }}" loading="lazy" />
                </div>
            @endfor
            @for ($i = ($customer_name == 'Generic') ?  -1 : 0; $i < $default_template; $i++)
                <div class="slide-item">
                    <img src="{{ asset('img/templates/'.$customer_name.'/'.$i.'.png') }}" title="{{ $output_dimensions[$customer_name == 'Generic' ? ($i + 1) : $i] }}" data-value="{{ $i }}" loading="lazy" />
                </div>
            @endfor
        </div>
    </div>
</div>