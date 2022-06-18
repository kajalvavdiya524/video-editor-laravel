<?php

namespace App\Http\Livewire;

use App\Domains\Auth\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\TableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;

/**
 * Class UsersTable.
 */
class UsersTable extends TableComponent
{
    /**
     * @var string
     */
    public $sortField = 'name';

    /**
     * @var string
     */
    public $status;

    /**
     * @var string
     */
    public $company_id;

    /**
     * @param  string  $status
     */
    public function mount($status = 'active', $companyId = 0): void
    {
        $this->status = $status;
        $this->company_id = (int)$companyId;

    }

    /**
     * @return Builder
     */
    public function query(): Builder
    {
        $query = User::with('roles', 'twoFactorAuth', 'company', 'customer')
            ->withCount('twoFactorAuth');
        
        if ($this->company_id != 0) {
            $query->where('company_id', $this->company_id);
        }

        if ($this->status === 'deleted') {
            return $query->onlyTrashed();
        }

        if ($this->status === 'deactivated') {
            return $query->onlyDeactivated();
        }

        return $query->onlyActive();
    }

    /**
     * @return array
     */
    public function columns(): array
    {
        return [
            Column::make(__('Name'))
                ->searchable()
                ->sortable(),
            Column::make(__('E-mail'), 'email')
                ->searchable()
                ->sortable(),
            Column::make(__('Company'), 'company.name')
                ->searchable()
                ->sortable(function ($builder, $direction) {
                    return $builder->orderBy('companies.name', $direction);
                }),
            Column::make(__('Teams'), 'team_list')
                ->customAttribute()
                ->html()
                ->searchable(function ($builder, $term) {
                    return $builder->orWhereHas('teams', function ($query) use ($term) {
                        return $query->where('name', 'like', '%'.$term.'%');
                    });
                }),
            Column::make(__('Default Customer'), 'customer.name')
                ->searchable()
                ->sortable(function ($builder, $direction) {
                    return $builder->orderBy('customer.name', $direction);
                }),
            Column::make(__('Verified'))
                ->view('backend.auth.user.includes.verified', 'user')
                ->sortable(function ($builder, $direction) {
                    return $builder->orderBy('email_verified_at', $direction);
                }),
            Column::make(__('2FA'))
                ->view('backend.auth.user.includes.2fa', 'user')
                ->sortable(function ($builder, $direction) {
                    return $builder->orderBy('two_factor_auth_count', $direction);
                }),
            Column::make(__('Roles'), 'roles_label')
                ->customAttribute()
                ->html()
                ->searchable(function ($builder, $term) {
                    return $builder->orWhereHas('roles', function ($query) use ($term) {
                        return $query->where('name', 'like', '%'.$term.'%');
                    });
                }),
            Column::make(__('Actions'))
                ->view('backend.auth.user.includes.actions', 'user'),
        ];
    }
}
