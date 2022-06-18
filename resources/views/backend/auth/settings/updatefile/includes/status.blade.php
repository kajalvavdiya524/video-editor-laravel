<!-- @if($urlsfile->status)
    <span class='badge badge-success' aria-hidden="true" data-toggle="tooltip" title="{{$urlsfile->updated_at}}">@lang('Retrieved')</span>
@else
    <span class='badge badge-danger'>@lang('Not Retrieved')</span>
@endif -->
    <!-- <span style="display: block;">New: {{$urlsfile->new}}, Changed: {{$urlsfile->changed}}</span> -->
    <table>
        <tr>
            <th>Images</th>
            <th>Prod.</th>
            <th>NF.</th>
            <th>Ingr.</th>
        </tr>
        <tr>
            <th>New</th>
            <td class="new-rows">{{$urlsfile->new_prod}}</td>
            <td>{{$urlsfile->new_nf}}</td>
            <td>{{$urlsfile->new_ingr}}</td>
        </tr>
        <tr>
            <th>Changed</th>
            <td>{{$urlsfile->changed_prod}}</td>
            <td>{{$urlsfile->changed_nf}}</td>
            <td>{{$urlsfile->changed_ingr}}</td>
        </tr>
    </table>
