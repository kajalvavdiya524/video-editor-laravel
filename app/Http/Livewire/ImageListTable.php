<?php

namespace App\Http\Livewire;

use App\Domains\Auth\Models\ImageList;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\TableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;

/**
 * Class ImageListTable.
 */
class ImageListTable extends TableComponent
{
    /**
     * @var string
     */
    public $sortField = 'id';

    /**
     * @return Builder
     */
    public function query(): Builder
    {
        return ImageList::join('users', 'image_lists.created_by', '=', 'users.id')
            ->select(['image_lists.*', 'users.name AS user_name']);
    }

    /**
     * @return array
     */
    public function columns(): array
    {
        return [
            Column::make(__('ID')),
            Column::make(__('Name'))
                ->searchable()
                ->sortable(),
            Column::make(__('Created By'), 'user_name')
                ->searchable()
                ->sortable(),
            Column::make(__('Actions'))
                ->view('backend.auth.settings.imagelist.includes.actions', 'imagelist'),
        ];
    }
}
