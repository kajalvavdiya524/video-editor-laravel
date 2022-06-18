<style>
    .dropdown ul li a i {
        width: 14px;
        display: inline-block;
    }
</style>

<div class="dropdown">
    <button id="dropdownMenuButton-{{ $entityId }}"
            class="btn btn-secondary dropdown-toggle"
            type="button" data-toggle="dropdown"
            data-bs-auto-close="outside"
            aria-expanded="false"
    >
        <i class="cil-settings"></i>
    </button>
    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton-{{ $entityId }}">
        <li class="all-companies" data-entity-id="{{ $entityId }}" data-company-id="0"
            data-action="{{empty(array_diff($companies->pluck('id')->toArray(), $entityCompanies)) ? 'exclude' : 'select'}}"
            data-type="all"
        >
            <a class="dropdown-item pl-2" href="#">
                <i class="@if($entityAll)cil-check-alt @endif mr-2"></i>
                All Companies
            </a>
        </li>
        @foreach($companies as $company)
            <li data-entity-id="{{ $entityId }}" data-company-id="{{ $company->id }}" data-action="one">
                <a class="dropdown-item pl-2" href="#">
                    <i class="@if(in_array($company->id, $entityCompanies) || $entityAll)cil-check-alt @endif mr-2"></i>
                    {{ $company->name }}
                </a>
            </li>
        @endforeach
    </ul>
</div>