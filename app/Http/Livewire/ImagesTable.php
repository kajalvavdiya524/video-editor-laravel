<?php

namespace App\Http\Livewire;

use App\Domains\Auth\Models\Images;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\TableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;

/**
 * Class ImagesTable.
 */
class ImagesTable extends TableComponent
{
    /**
     * @var string
     */
    public $sortField = 'id';

    public $list_id;

    /**
     * @param  string  $status
     */
    public function mount($listId = 0): void
    {
        $this->list_id = (int)$listId;
    }

    /**
     * @return Builder
     */
    public function query(): Builder
    {
        return Images::where('list_id', $this->list_id);
    }

    /**
     * @return array
     */
    public function columns(): array
    {
        return [
            Column::make(__('ID'))
                ->sortable(),
            Column::make(__('Name'))
                ->searchable()
                ->sortable(),
            Column::make(__('Filename'))
                ->searchable()
                ->sortable(),
            Column::make(__('Actions'))
                ->view('backend.auth.settings.images.includes.actions', 'image'),
        ];
    }
}
