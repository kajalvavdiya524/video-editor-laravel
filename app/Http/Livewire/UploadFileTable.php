<?php

namespace App\Http\Livewire;

use App\Domains\Auth\Models\UrlsFile;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\TableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;

/**
 * Class UploadFileTable.
 */
class UploadFileTable extends TableComponent
{
    /**
     * @var string
     */
    public $sortField = 'created_at';

    /**
     * @var string
     */
    public $sortDirection = 'desc';
    
    /**
     * @return Builder
     */
    public function query(): Builder
    {
        $query = UrlsFile::with('company', 'user');
        if (auth()->user()->company_id != 0) {
            $query->whereIn('company_id', [0, auth()->user()->company_id]);
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
                    return $builder->where('created_at', 'like', '%'.$term.'%');
                })
                ->sortable(function ($builder, $direction) {
                    return $builder->orderBy('created_at', $direction);
                }),
            Column::make(__('Company'), 'company.name')
                ->searchable()
                ->sortable(function ($builder, $direction) {
                    return $builder->orderBy('companies.name', $direction);
                }),
            Column::make(__('Uploaded By'))
                ->view('backend.auth.settings.updatefile.includes.uploadedby', 'urlsfile')
                ->searchable()
                ->sortable(),
            Column::make(__('Filename'))
                ->view('backend.auth.settings.updatefile.includes.filename', 'urlsfile')
                ->searchable()
                ->sortable(),
            Column::make(__('Rows'))
                ->searchable()
                ->sortable(),
            Column::make(__('Status'))
                ->view('backend.auth.settings.updatefile.includes.status', 'urlsfile')
                ->sortable(function ($builder, $direction) {
                    return $builder->orderBy('status', $direction);
                }),
            Column::make(__('Actions'))
                ->view('backend.auth.settings.updatefile.includes.actions', 'urlsfile'),
        ];
    }
}
