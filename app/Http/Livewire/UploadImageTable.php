<?php

namespace App\Http\Livewire;

use App\Domains\Auth\Models\Uploadimg;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\TableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;

/**
 * Class UploadImageTable.
 */
class UploadImageTable extends TableComponent
{
    /**
     * @var string
     */
    public $sortField = 'updated_at';

    /**
     * @var string
     */
    public $sortDirection = 'desc';

    /**
     * @return Builder
     */
    public function query(): Builder
    {
        $query = Uploadimg::with('company');
        if (auth()->user()->company_id != 0) {
            $query->where('company_id', auth()->user()->company_id);
            $query->OrWhere('company_id', '0');
        }
        return $query;
    }

    /**
     * @return array
     */
    public function columns(): array
    {
        return [
            Column::make(__('Date'), 'uploaded_at')
                ->customAttribute()
                ->html()
                ->searchable(function ($builder, $term) {
                    return $builder->where('updated_at', 'like', '%'.$term.'%');
                })
                ->sortable(function ($builder, $direction) {
                    return $builder->orderBy('updated_at', $direction);
                }),
            Column::make(__('Filename'))
                ->searchable()
                ->sortable(),
            Column::make(__('Company'), 'company.name')
                ->searchable()
                ->view('frontend.user.includes.draft.company_name', 'row')
                ->sortable(function ($builder, $direction) {
                    return $builder->orderBy('companies.name', $direction);
                }),
            Column::make(__('ASIN'), 'ASIN')
                ->searchable()
                ->sortable(),
            Column::make(__('UPC'), 'UPC')
                ->searchable()
                ->sortable(),
            Column::make(__('GTIN'), 'GTIN')
                ->searchable()
                ->sortable(),
            Column::make(__('Width'))
                ->searchable()
                ->sortable(),
            Column::make(__('Height'))
                ->searchable()
                ->sortable(),
            Column::make(__('Actions'))
                ->view('frontend.user.uploadimg.includes.actions', 'upload_image'),
        ];
    }
}
