@php
$types = [ 'Standard', 'Parent', 'Child', 'Language' ];
@endphp
<div class="form-row mt-3">
    <!-- <div class="project-type col-md-2 form-group">
        <label>Project Type</label>
        <select name="type" class="form-control">
            @for ($i = 0; $i < count($types); $i++)
            <option value="{{ $i }}" {{ isset($settings->type) && ($i == $settings->type) ? 'selected' : '' }}>{{ $types[$i] }}</option>
            @endfor
        </select>
    </div> -->
    <input type="hidden" name="type" value="3" />
    @php
    /* $name_class = 'col-md-10';
    if (isset($settings->type) && $settings->type == 2) {
        $name_class = 'col-md-8';
    } else if (isset($settings->type) && $settings->type == 3) {
        $name_class = 'col-md-6';
    } */
    $name_class = 'col-md-8';
    @endphp
    <div class="project-name {{ $name_class }} form-group">
        <label>Project Name</label>
        <input type="text" name="project_name" class="form-control" value="{{ empty($settings->project_name) ? "" : $settings->project_name }}">
    </div>
    <div class="country-select col-md-2 form-group mb-0">
        <label>Region</label>
        <select name="country_id" class="form-control">
        </select>
    </div>
    <div class="language-select col-md-2 form-group mb-0">
        <label>Language</label>
        <select name="language_id" class="form-control">
        </select>
    </div>
    <!-- <div class="parent-select col-md-2 form-group mb-0 {{ isset($settings->type) && $settings->type == 2 ? 'd-block' : 'd-none' }}">
        <label>Parent Project</label>
        <select name="parent_id" class="form-control">
            <option value="0">None</option>
        </select>
    </div> -->
    <!-- <div class="country-select col-md-2 form-group mb-0 {{ isset($settings->type) && $settings->type == 3 ? 'd-block' : 'd-none' }}">
        <label>Country</label>
        <select name="country_id" class="form-control">
        </select>
    </div>
    <div class="language-select col-md-2 form-group mb-0 {{ isset($settings->type) && $settings->type == 3 ? 'd-block' : 'd-none' }}">
        <label>Language</label>
        <select name="language_id" class="form-control">
        </select>
    </div> -->
</div>