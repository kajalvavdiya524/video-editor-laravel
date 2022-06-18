<div class="customer-wrapper">
    @php
        $cimagerul = '';
        $cvalue = '';
        $cindex = 0;
        $title = '';
        for ($i = 0; $i < count($customers); $i++) {
            if ($customers[$i]->id == $customer) {
                $cvalue = $customers[$i]->value;
                $cimagerul = $customers[$i]->image_url;
                $title = $customers[$i]->name;
                $cindex = $i;
            }
        }
        $title = $title == "Generic" ? "Banner Ads" : $title;
    @endphp
    <p>Customer</p>
    <input type="hidden" name="customer" value="{{ empty($customer) ? 'amazon' : $cvalue }}" />
    <input type="hidden" name="customer_id" value="{{ $customers[$cindex]->id }}" />
    <div class="selected-customer">
        <div class="slide-item">
            <img class="selected" src="{{ asset($cimagerul) }}" title="{{$title}}" loading="lazy" />
        </div>
    </div>
    <div class="customers slide-popup">
        <div class="customers-carousel">
            <div class="slide-item">
                <img class="selected" src="{{ asset($cimagerul) }}" title="{{$title}}" data-value="{{ $customers[$cindex]->value }}" loading="lazy" />
            </div>
            @for ($i = $cindex + 1; $i < count($customers); $i++)
                @php
                    $title = $customers[$i]->name == "Generic" ? "Banner Ads" : $customers[$i]->name;
                @endphp
                <div class="slide-item">
                    <img src="{{ asset($customers[$i]->image_url) }}" title="{{ $title }}" data-value="{{$customers[$i]->value}}" loading="lazy" />
                </div>
            @endfor
            @for ($i = 0; $i < $cindex; $i++)
                @php
                    $title = $customers[$i]->name == "Generic" ? "Banner Ads" : $customers[$i]->name;
                @endphp
                <div class="slide-item">
                    <img src="{{ asset($customers[$i]->image_url) }}" title="{{ $title }}" data-value="{{$customers[$i]->value}}" loading="lazy" />
                </div>
            @endfor
        </div>
    </div>
</div>