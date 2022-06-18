<?php

use App\Domains\Auth\Http\Controllers\Backend\Role\RoleController;
use App\Domains\Auth\Http\Controllers\Backend\User\DeactivatedUserController;
use App\Domains\Auth\Http\Controllers\Backend\User\DeletedUserController;
use App\Domains\Auth\Http\Controllers\Backend\User\UserController;
use App\Domains\Auth\Http\Controllers\Backend\User\UserPasswordController;
use App\Domains\Auth\Http\Controllers\Backend\User\UserSessionController;
use App\Domains\Auth\Http\Controllers\Backend\Company\DeactivatedCompanyController;
use App\Domains\Auth\Http\Controllers\Backend\Company\DeletedCompanyController;
use App\Domains\Auth\Http\Controllers\Backend\Company\CompanyController;
use App\Domains\Auth\Http\Controllers\Backend\Team\TeamController;
use App\Domains\Auth\Http\Controllers\Backend\Template\TemplateController;
use App\Domains\Auth\Http\Controllers\Backend\Customer\CustomerController;
use App\Domains\Auth\Http\Controllers\Backend\Settings\TemplateSettingsController;
use App\Domains\Auth\Http\Controllers\Backend\Settings\AdvancedSettingsController;
use App\Domains\Auth\Http\Controllers\Backend\Settings\UpdateFileSettingsController;
use App\Domains\Auth\Http\Controllers\Backend\Settings\LoadingHistoryController;
use App\Domains\Auth\Http\Controllers\Backend\Settings\ExceptionController;
use App\Domains\Auth\Http\Controllers\Backend\Settings\ProductSelectionController;
use App\Domains\Auth\Http\Controllers\Backend\Settings\ThemeController;
use App\Domains\Auth\Http\Controllers\Backend\Settings\ImagesController;
use App\Domains\Auth\Http\Controllers\Backend\Settings\ImageListController;
use App\Domains\Auth\Http\Controllers\Backend\Video\VideoProjectController;
use App\Domains\Auth\Http\Controllers\Backend\Video\VideoTemplateController;
use App\Domains\Auth\Http\Controllers\Backend\Video\VideoThemeController;
use App\Domains\Auth\Http\Controllers\Backend\Video\MediaController;
use App\Domains\Auth\Http\Controllers\Backend\Video\MediaTagController;
use App\Domains\Auth\Http\Controllers\Backend\Video\VideoShareController;
use App\Domains\Auth\Http\Controllers\Backend\Video\VideoLogController;
use App\Domains\Auth\Http\Controllers\Backend\Video\VideoCommentController;
use App\Domains\Auth\Http\Controllers\Backend\Positioning\PositioningController;
use App\Domains\Auth\Http\Controllers\Backend\ApiKeys\ApiKeysController;
use App\Domains\Auth\Http\Controllers\Backend\Job\JobController;
use App\Domains\Auth\Models\ApiKeys;
use App\Domains\Auth\Models\Role;
use App\Domains\Auth\Models\User;
use App\Domains\Auth\Models\Company;
use App\Domains\Auth\Models\Customer;
use App\Domains\Auth\Models\Team;
use App\Domains\Auth\Models\Theme;
use App\Domains\Auth\Models\Template;
use App\Domains\Auth\Models\Job;
use Tabuna\Breadcrumbs\Trail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// All route names are prefixed with 'admin.auth'.

Route::group([
    'prefix' => 'auth',
    'as' => 'auth.',
    'middleware' => 'role:' . config('boilerplate.access.role.admin') . '|' . config('boilerplate.access.role.company_admin')
], function () {
    Route::post('/video/themes/add-or-remove-company', [VideoThemeController::class, 'addOrRemoveCompany'])->name('addOrRemoveCompany');
});

Route::group([
    'prefix' => 'auth',
    'as' => 'auth.',
    'middleware' => config('boilerplate.access.middleware.confirm'),
], function () {
    Route::group([
        'prefix' => 'company',
        'as' => 'company.',
    ], function () {
        Route::group([
            'middleware' => 'role:' . config('boilerplate.access.role.admin'),
        ], function () {
            Route::get('deleted', [DeletedCompanyController::class, 'index'])
                ->name('deleted')
                ->breadcrumbs(function (Trail $trail) {
                    $trail->parent('admin.auth.company.index')
                        ->push(__('Deleted Companies'), route('admin.auth.company.deleted'));
                });

            Route::get('create', [CompanyController::class, 'create'])
                ->name('create')
                ->breadcrumbs(function (Trail $trail) {
                    $trail->parent('admin.auth.company.index')
                        ->push(__('Create Company'), route('admin.auth.company.create'));
                });

            Route::post('/', [CompanyController::class, 'store'])->name('store');

            Route::group(['prefix' => '{company}'], function () {
                Route::delete('/', [CompanyController::class, 'destroy'])->name('destroy');
            });

            Route::group(['prefix' => '{deletedCompany}'], function () {
                Route::patch('restore', [DeletedCompanyController::class, 'update'])->name('restore');
                Route::delete('permanently-delete', [DeletedCompanyController::class, 'destroy'])->name('permanently-delete');
            });

            Route::get('deactivated', [DeactivatedCompanyController::class, 'index'])
                ->name('deactivated')
                ->breadcrumbs(function (Trail $trail) {
                    $trail->parent('admin.auth.company.index')
                        ->push(__('Deactivated Companies'), route('admin.auth.company.deactivated'));
                });

            Route::get('/', [CompanyController::class, 'index'])
                ->name('index')
                ->breadcrumbs(function (Trail $trail) {
                    $trail->parent('admin.dashboard')
                        ->push(__('Company Management'), route('admin.auth.company.index'));
                });

            Route::group(['prefix' => '{company}'], function () {
                Route::get('/', [CompanyController::class, 'show'])
                    ->name('show')
                    ->breadcrumbs(function (Trail $trail, Company $company) {
                        $trail->parent('admin.auth.company.index')
                            ->push(__('Viewing :company', ['company' => $company->name]), route('admin.auth.company.show', $company));
                    });

                Route::patch('mark/{status}', [DeactivatedCompanyController::class, 'update'])
                    ->name('mark')
                    ->where(['status' => '[0,1]']);
            });
        });

        Route::group([
            'middleware' => 'role:' . config('boilerplate.access.role.admin') . '|' . config('boilerplate.access.role.company_admin'),
        ], function () {
            Route::group(['prefix' => '{company}'], function () {
                Route::get('edit', [CompanyController::class, 'edit'])
                    ->name('edit')
                    ->breadcrumbs(function (Trail $trail, Company $company) {
                        if (Auth::user()->isMasterAdmin()) {
                            $trail->parent('admin.auth.company.index')
                                ->push(__('Editing :company', ['company' => $company->name]), route('admin.auth.company.edit', $company));
                        } else {
                            $trail->parent('admin.dashboard')
                                ->push(__('Editing :company', ['company' => $company->name]), route('admin.auth.company.edit', $company));
                        }
                    });

                Route::patch('/', [CompanyController::class, 'update'])->name('update');
            });
        });
    });

    Route::group([
        'prefix' => 'team',
        'as' => 'team.',
    ], function () {
        Route::group([
            'middleware' => 'role:' . config('boilerplate.access.role.admin') . '|' . config('boilerplate.access.role.company_admin'),
        ], function () {
            Route::get('create', [TeamController::class, 'create'])
                ->name('create')
                ->breadcrumbs(function (Trail $trail) {
                    $trail->parent('admin.auth.team.index')
                        ->push(__('Create Team'), route('admin.auth.team.create'));
                });

            Route::post('/', [TeamController::class, 'store'])->name('store');

            Route::group(['prefix' => '{team}'], function () {
                Route::delete('/', [TeamController::class, 'destroy'])->name('destroy');
            });

            Route::get('/', [TeamController::class, 'index'])
                ->name('index')
                ->breadcrumbs(function (Trail $trail) {
                    $trail->parent('admin.dashboard')
                        ->push(__('Team Management'), route('admin.auth.team.index'));
                });

            Route::group(['prefix' => '{team}'], function () {
                Route::get('/', [TeamController::class, 'show'])
                    ->name('show')
                    ->breadcrumbs(function (Trail $trail, Team $team) {
                        $trail->parent('admin.auth.team.index')
                            ->push(__('Viewing :team', ['team' => $team->name]), route('admin.auth.team.show', $team));
                    });
            });
        });

        Route::group([
            'middleware' => 'role:' . config('boilerplate.access.role.admin') . '|' . config('boilerplate.access.role.company_admin'),
        ], function () {
            Route::group(['prefix' => '{team}'], function () {
                Route::get('edit', [TeamController::class, 'edit'])
                    ->name('edit')
                    ->breadcrumbs(function (Trail $trail, Team $team) {
                        if (Auth::user()->isMasterAdmin()) {
                            $trail->parent('admin.auth.team.index')
                                ->push(__('Editing :team', ['team' => $team->name]), route('admin.auth.team.edit', $team));
                        } else {
                            $trail->parent('admin.dashboard')
                                ->push(__('Editing :team', ['team' => $team->name]), route('admin.auth.team.edit', $team));
                        }
                    });

                Route::patch('/', [TeamController::class, 'update'])->name('update');
            });
        });
    });

    Route::group([
        'prefix' => 'apikeys',
        'as' => 'apikeys.',
    ], function () {
        Route::group([
            'middleware' => 'role:' . config('boilerplate.access.role.admin'),
        ], function () {
            
            Route::get('create', [ApiKeysController::class, 'create'])
                ->name('create')
                ->breadcrumbs(function (Trail $trail) {
                    $trail->parent('admin.auth.apikeys.index')
                        ->push(__('Create Api Key'), route('admin.auth.apikeys.create'));
                });

            Route::post('/', [ApiKeysController::class, 'store'])->name('store');

            Route::get('toggle/{apiKeys}', [ApiKeysController::class, 'toggle'])->name('toggle');
          
            Route::get('/', [ApiKeysController::class, 'index'])
                ->name('index')
                ->breadcrumbs(function (Trail $trail) {
                    $trail->parent('admin.dashboard')
                        ->push(__('API Keys Management'), route('admin.auth.apikeys.index'));
                });

            
            Route::get('/{apiKeys}', [ApiKeysController::class, 'show'])
                ->name('show')
                ->breadcrumbs(function (Trail $trail, ApiKeys $apiKeys) {
                    $trail->parent('admin.auth.apikeys.index')
                        ->push(__('Viewing: API Key', ['apiKeys' => $apiKeys->name]), route('admin.auth.apikeys.show', $apiKeys));
             });
                    
             Route::get('edit/{apiKeys}', [ApiKeysController::class, 'edit'])
             ->name('edit')
             ->breadcrumbs(function (Trail $trail, ApiKeys $apiKeys) {
                 if (Auth::user()->isMasterAdmin()) {
                     $trail->parent('admin.auth.team.index')
                         ->push(__('Editing: API Key', ['apikeys' => $apiKeys->id]), route('admin.auth.apikeys.edit', $apiKeys));
                 } else {
                     $trail->parent('admin.dashboard')
                         ->push(__('Editing: API Key', ['apikeys' => $apiKeys->id]), route('admin.auth.apikeys.edit', $apiKeys));
                 }
             });

            Route::patch('/{apiKeys}', [ApiKeysController::class, 'update'])->name('update');
            
            
            Route::delete('/{apiKeys}', [ApiKeysController::class, 'destroy'])->name('destroy');
            
           
        });
    });


    Route::group([
        'prefix' => 'job',
        'as' => 'job.',
    ], function () {
        Route::group([
            'middleware' => 'role:' . config('boilerplate.access.role.admin'),
        ], function () {
            
            Route::get('create', [JobController::class, 'create'])
                ->name('create')
                ->breadcrumbs(function (Trail $trail) {
                    $trail->parent('admin.auth.job.index')
                        ->push(__('Create Job'), route('admin.auth.job.create'));
                });

            Route::post('/', [JobController::class, 'store'])->name('store');

            Route::get('toggle/{job}', [JobController::class, 'toggle'])->name('toggle');

            Route::get('process_detail/{job_detail}',[JobController::class, 'process_detail'])->name('process_detail');
          
            Route::get('/', [JobController::class, 'index'])
                ->name('index')
                ->breadcrumbs(function (Trail $trail) {
                    $trail->parent('admin.dashboard')
                        ->push(__('Jobs Management'), route('admin.auth.job.index'));
                });

            
            Route::get('/{job}', [JobController::class, 'show'])
                ->name('show')
                ->breadcrumbs(function (Trail $trail, Job $job) {
                    $trail->parent('admin.auth.job.index')
                        ->push(__('Viewing: Job', ['job' => $job->id]), route('admin.auth.job.show', $job));
             });
                    
             Route::get('edit/{job}', [JobController::class, 'edit'])
             ->name('edit')
             ->breadcrumbs(function (Trail $trail, Job $job) {
                 if (Auth::user()->isMasterAdmin()) {
                     $trail->parent('admin.auth.team.index')
                         ->push(__('Editing: Job', ['job' => $job->id]), route('admin.auth.job.edit', $job));
                 } else {
                     $trail->parent('admin.dashboard')
                         ->push(__('Editing: Job', ['job' => $job->id]), route('admin.auth.job.edit', $job));
                 }
             });

            Route::patch('/{job}', [JobController::class, 'update'])->name('update');
            
            
            Route::delete('/{job}', [JobController::class, 'destroy'])->name('destroy');
            
           
        });
    });

    Route::group([
        'prefix' => 'user',
        'as' => 'user.',
    ], function () {
        Route::group([
            'middleware' => 'role:' . config('boilerplate.access.role.admin') . '|' . config('boilerplate.access.role.company_admin'),
        ], function () {
            Route::get('deleted', [DeletedUserController::class, 'index'])
                ->name('deleted')
                ->breadcrumbs(function (Trail $trail) {
                    $trail->parent('admin.auth.user.index')
                        ->push(__('Deleted Users'), route('admin.auth.user.deleted'));
                });

            Route::get('create', [UserController::class, 'create'])
                ->name('create')
                ->breadcrumbs(function (Trail $trail) {
                    $trail->parent('admin.auth.user.index')
                        ->push(__('Create User'), route('admin.auth.user.create'));
                });

            Route::post('/', [UserController::class, 'store'])->name('store');

            Route::group(['prefix' => '{user}'], function () {
                Route::get('edit', [UserController::class, 'edit'])
                    ->name('edit')
                    ->breadcrumbs(function (Trail $trail, User $user) {
                        $trail->parent('admin.auth.user.index')
                            ->push(__('Editing :user', ['user' => $user->name]), route('admin.auth.user.edit', $user));
                    });

                Route::patch('/', [UserController::class, 'update'])->name('update');
                Route::delete('/', [UserController::class, 'destroy'])->name('destroy');
            });

            Route::group(['prefix' => '{deletedUser}'], function () {
                Route::patch('restore', [DeletedUserController::class, 'update'])->name('restore');
                Route::delete('permanently-delete', [DeletedUserController::class, 'destroy'])->name('permanently-delete');
            });
        });

        Route::group([
            'middleware' => 'permission:access.user.list|access.user.deactivate|access.user.reactivate|access.user.clear-session|access.user.impersonate|access.user.change-password',
        ], function () {
            Route::get('deactivated', [DeactivatedUserController::class, 'index'])
                ->name('deactivated')
                ->middleware('permission:access.user.reactivate')
                ->breadcrumbs(function (Trail $trail) {
                    $trail->parent('admin.auth.user.index')
                        ->push(__('Deactivated Users'), route('admin.auth.user.deactivated'));
                });

            Route::get('/', [UserController::class, 'index'])
                ->name('index')
                ->middleware('permission:access.user.list|access.user.deactivate|access.user.clear-session|access.user.impersonate|access.user.change-password')
                ->breadcrumbs(function (Trail $trail) {
                    $trail->parent('admin.dashboard')
                        ->push(__('User Management'), route('admin.auth.user.index'));
                });

            Route::group(['prefix' => '{user}'], function () {
                Route::get('/', [UserController::class, 'show'])
                    ->name('show')
                    ->middleware('permission:access.user.list')
                    ->breadcrumbs(function (Trail $trail, User $user) {
                        $trail->parent('admin.auth.user.index')
                            ->push(__('Viewing :user', ['user' => $user->name]), route('admin.auth.user.show', $user));
                    });

                Route::patch('mark/{status}', [DeactivatedUserController::class, 'update'])
                    ->name('mark')
                    ->where(['status' => '[0,1]'])
                    ->middleware('permission:access.user.deactivate|access.user.reactivate');

                Route::post('clear-session', [UserSessionController::class, 'update'])
                    ->name('clear-session')
                    ->middleware('permission:access.user.clear-session');

                Route::get('password/change', [UserPasswordController::class, 'edit'])
                    ->name('change-password')
                    ->middleware('permission:access.user.change-password')
                    ->breadcrumbs(function (Trail $trail, User $user) {
                        $trail->parent('admin.auth.user.index')
                            ->push(__('Changing Password for :user', ['user' => $user->name]), route('admin.auth.user.change-password', $user));
                    });

                Route::patch('password/change', [UserPasswordController::class, 'update'])
                    ->name('change-password.update')
                    ->middleware('permission:access.user.change-password');
            });
        });
    });
    
    Route::group([
        'prefix' => 'template',
        'as' => 'template.',
    ], function () {
        Route::group([
            'middleware' => 'role:' . config('boilerplate.access.role.admin') . '|' . config('boilerplate.access.role.company_admin'),
        ], function () {
            Route::get('/field_types', [TemplateController::class, 'field_types'])->name('field_types');
            Route::get('/image_lists', [TemplateController::class, 'image_lists'])->name('image_lists');
            Route::post('/upload', [TemplateController::class, 'upload'])->name('upload');
            Route::post('/update_xlsx', [TemplateController::class, 'update_xlsx'])->name('update_xlsx');
            Route::post('/update_image', [TemplateController::class, 'update_image'])->name('update_image');
            Route::post('/delete_image', [TemplateController::class, 'delete_image'])->name('delete_image');
            Route::post('/update_positioning', [TemplateController::class, 'update_positioning'])->name('update_positioning');

            Route::group(['prefix' => '{customer_id?}'], function () {               
                Route::get('/', [TemplateController::class, 'index'])
                    ->name('index')
                    ->breadcrumbs(function (Trail $trail, $customer_id = 1) {
                        $customer = Customer::find($customer_id);
                        $trail->parent('admin.dashboard')
                            ->push(__($customer->name), route('admin.auth.template.index', $customer_id));
                    });

                Route::get('/create', [TemplateController::class, 'create'])
                    ->name('create')
                    ->breadcrumbs(function (Trail $trail) {
                        $trail->parent('admin.auth.template.index')
                            ->push(__('Create Template'), route('admin.auth.template.index'));
                    });

                Route::post('/store', [TemplateController::class, 'store'])->name('store');
            });

            Route::group(['prefix' => '{customer_id}/{template}'], function () {
                Route::get('edit', [TemplateController::class, 'edit'])
                    ->name('edit')
                    ->breadcrumbs(function (Trail $trail, $customer_id, Template $template) {
                        $trail->parent('admin.auth.template.index', $customer_id)
                            ->push(__('Editing :template', ['template' => $template->name]), route('admin.auth.template.edit', ['customer_id' => $customer_id, 'template' => $template]));
                    });
                Route::post('update', [TemplateController::class, 'update'])->name('update');
                Route::post('copy', [TemplateController::class, 'copy'])->name('copy');
                Route::post('toggle', [TemplateController::class, 'toggle'])->name('toggle');
                Route::post('moveup', [TemplateController::class, 'moveup'])->name('moveup');
                Route::post('movedown', [TemplateController::class, 'movedown'])->name('movedown');
                Route::delete('/', [TemplateController::class, 'destroy'])->name('destroy');
                Route::get('download', [TemplateController::class, 'download'])->name('download');
                Route::get('export', [TemplateController::class, 'export'])->name('export');
            });
        });
    });
    
    Route::group([
        'prefix' => 'positioning',
        'as' => 'positioning.',
    ], function () {
        Route::group([
            'middleware' => 'role:' . config('boilerplate.access.role.admin') . '|' . config('boilerplate.access.role.company_admin'),
        ], function () {
            Route::post('/upload', [PositioningController::class, 'upload'])->name('upload');
            Route::post('/export', [PositioningController::class, 'export'])->name('export');

            Route::get('/', [PositioningController::class, 'index'])
                ->name('index')
                ->breadcrumbs(function (Trail $trail) {
                    $trail->parent('admin.dashboard')
                        ->push(__('Positioning Management'), route('admin.auth.positioning.index'));
                });
        });
    });
    
    Route::group([
        'prefix' => 'customer',
        'as' => 'customer.',
    ], function () {
        Route::group([
            'middleware' => 'role:' . config('boilerplate.access.role.admin'),
        ], function () {
            Route::get('create', [CustomerController::class, 'create'])
                ->name('create')
                ->breadcrumbs(function (Trail $trail) {
                    $trail->parent('admin.auth.customer.index')
                        ->push(__('Create Customer'), route('admin.auth.customer.create'));
                });

            Route::post('/', [CustomerController::class, 'store'])->name('store');

            Route::group(['prefix' => '{customer}'], function () {
                Route::delete('/', [CustomerController::class, 'destroy'])->name('destroy');
            });

            Route::get('/', [CustomerController::class, 'index'])
                ->name('index')
                ->breadcrumbs(function (Trail $trail) {
                    $trail->parent('admin.dashboard')
                        ->push(__('Customer Management'), route('admin.auth.customer.index'));
                });

            Route::group(['prefix' => '{customer}'], function () {
                Route::get('/', [CustomerController::class, 'show'])
                    ->name('show')
                    ->breadcrumbs(function (Trail $trail, Customer $customer) {
                        $trail->parent('admin.auth.customer.index')
                            ->push(__('Viewing :customer', ['customer' => $customer->name]), route('admin.auth.customer.show', $customer));
                    });

                Route::get('/download_xlsx_template', [CustomerController::class, 'download_xlsx_template'])->name('download_xlsx_template');

                Route::get('edit', [CustomerController::class, 'edit'])
                    ->name('edit')
                    ->breadcrumbs(function (Trail $trail, Customer $customer) {
                        if (Auth::user()->isMasterAdmin()) {
                            $trail->parent('admin.auth.customer.index')
                                ->push(__('Editing :customer', ['customer' => $customer->name]), route('admin.auth.customer.edit', $customer));
                        } else {
                            $trail->parent('admin.dashboard')
                                ->push(__('Editing :customer', ['customer' => $customer->name]), route('admin.auth.customer.edit', $customer));
                        }
                    });

                Route::patch('/', [CustomerController::class, 'update'])->name('update');
                
                Route::post('toggle', [CustomerController::class, 'toggle'])->name('toggle');
            
            });
        });
    });

    Route::group([
        'prefix' => 'settings',
        'as' => 'settings.',
    ], function () {
        Route::group([
            'middleware' => 'role:' . config('boilerplate.access.role.admin') . '|' . config('boilerplate.access.role.company_admin'),
        ], function () {

            Route::group([
                'prefix' => 'theme',
                'as' => 'theme.',
                'middleware' => 'role:' . config('boilerplate.access.role.admin') . '|' . config('boilerplate.access.role.company_admin'),
            ], function () {
                Route::get('/{customer_id?}', [ThemeController::class, 'index'])->name('index')
                    ->breadcrumbs(function (Trail $trail, $customer_id) {
                        $trail->parent('admin.dashboard')
                            ->push(__('Theme Settings'), route('admin.auth.settings.theme.index', $customer_id));
                    });
                Route::group(['prefix' => '{customer_id}'], function () {
                    Route::get('create', [ThemeController::class, 'create'])
                        ->name('create')
                        ->breadcrumbs(function (Trail $trail, $customer_id) {
                            $trail->parent('admin.auth.settings.theme.index', $customer_id)
                                ->push(__('Create Theme'), route('admin.auth.settings.theme.create', $customer_id));
                        });
                });
                Route::group(['prefix' => '{customer_id}/{theme}'], function () {
                    Route::get('edit', [ThemeController::class, 'edit'])
                        ->name('edit')
                        ->breadcrumbs(function (Trail $trail, $customer_id, Theme $theme) {
                            $trail->parent('admin.auth.settings.theme.index', $customer_id)
                                ->push(__('Editing :theme', ['theme' => $theme->name]), route('admin.auth.settings.theme.edit', ['customer_id' => $customer_id, 'theme' => $theme]));
                        });
                });
                Route::group(['prefix' => '{customer_id}'], function () {
                    Route::post('store', [ThemeController::class, 'store'])->name('store');
                    Route::post('update', [ThemeController::class, 'update'])->name('update');
                });
                Route::group(['prefix' => '{customer_id}/{theme}'], function () {
                    Route::post('copy', [ThemeController::class, 'copy'])->name('copy');
                    Route::post('toggle', [ThemeController::class, 'toggle'])->name('toggle');
                    Route::post('moveup', [ThemeController::class, 'moveup'])->name('moveup');
                    Route::post('movedown', [ThemeController::class, 'movedown'])->name('movedown');
                    Route::delete('/', [ThemeController::class, 'destroy'])->name('destroy');
                });
            });

            Route::group([
                'prefix' => 'template',
                'as' => 'template.',
                'middleware' => 'role:' . config('boilerplate.access.role.admin') . '|' . config('boilerplate.access.role.company_admin'),
            ], function () {
                Route::get('/', [TemplateSettingsController::class, 'index'])
                    ->name('index')
                    ->breadcrumbs(function (Trail $trail) {
                        $trail->parent('admin.dashboard')
                            ->push(__('Theme Settings'), route('admin.auth.settings.template.index'));
                    });

                Route::patch('update', [TemplateSettingsController::class, 'update'])->name('update');

                Route::post('reset', [TemplateSettingsController::class, 'reset'])->name('reset');

                Route::get('/{customer}', [TemplateSettingsController::class, 'view'])->name('view');
            });
            
            Route::group([
                'prefix' => 'images',
                'as' => 'images.',
                'middleware' => 'role:' . config('boilerplate.access.role.admin') . '|' . config('boilerplate.access.role.company_admin'),
            ], function () {
                Route::get('/', [ImagesController::class, 'index'])
                    ->name('index')
                    ->breadcrumbs(function (Trail $trail) {
                        $trail->parent('admin.dashboard')
                            ->push(__('Images'), route('admin.auth.settings.images.index'));
                    });

                Route::get('upload', [ImagesController::class, 'upload'])->name('upload');
                Route::post('store', [ImagesController::class, 'store'])->name('store');
                
                Route::group(['prefix' => '{image}'], function () {
                    Route::get('/', [ImagesController::class, 'edit'])->name('edit');
                    Route::post('moveup', [ImagesController::class, 'moveup'])->name('moveup');
                    Route::post('movedown', [ImagesController::class, 'movedown'])->name('movedown');
                    Route::post('update', [ImagesController::class, 'update'])->name('update');
                    Route::delete('/', [ImagesController::class, 'destroy'])->name('destroy');
                });
            });

            Route::group([
                'prefix' => 'imagelist',
                'as' => 'imagelist.',
                'middleware' => 'role:' . config('boilerplate.access.role.admin') . '|' . config('boilerplate.access.role.company_admin'),
            ], function () {
                Route::get('/', [ImageListController::class, 'index'])
                    ->name('index')
                    ->breadcrumbs(function (Trail $trail) {
                        $trail->parent('admin.dashboard')
                            ->push(__('Images'), route('admin.auth.settings.images.index'));
                    });
                    
                Route::get('create', [ImageListController::class, 'create'])->name('create');
                Route::post('store', [ImageListController::class, 'store'])->name('store');
                
                Route::group(['prefix' => '{imagelist}'], function () {
                    Route::get('/', [ImageListController::class, 'edit'])->name('edit');
                    Route::post('update', [ImageListController::class, 'update'])->name('update');
                    Route::delete('/', [ImageListController::class, 'destroy'])->name('destroy');
                    Route::post('copy', [ImageListController::class, 'copy'])->name('copy');
                });
            });

            Route::group([
                'prefix' => 'advanced',
                'as' => 'advanced.',
            ], function () {
                Route::get('/', [AdvancedSettingsController::class, 'index'])
                    ->name('index')
                    ->breadcrumbs(function (Trail $trail) {
                        $trail->parent('admin.dashboard')
                            ->push(__('Data Loading'), route('admin.auth.settings.advanced.index'));
                    });

                Route::get('download_dimension', [AdvancedSettingsController::class, 'download_dimension'])->name('download_dimension');

                Route::get('export_dimension', [AdvancedSettingsController::class, 'export_dimension'])->name('export_dimension');

                Route::post('dimension', [AdvancedSettingsController::class, 'dimension'])->name('dimension');

                Route::get('download_parent_child', [AdvancedSettingsController::class, 'download_parent_child'])->name('download_parent_child');

                Route::get('export_parent_child', [AdvancedSettingsController::class, 'export_parent_child'])->name('export_parent_child');

                Route::post('parent_child', [AdvancedSettingsController::class, 'parent_child'])->name('parent_child');

                Route::post('psd2png', [AdvancedSettingsController::class, 'psd2png'])->name('psd2png');

                Route::post('notification_email', [AdvancedSettingsController::class, 'notification_email'])->name('notification_email');

                Route::get('deleted', [AdvancedSettingsController::class, 'index'])
                    ->name('deleted')
                    ->breadcrumbs(function (Trail $trail) {
                        $trail->parent('admin.auth.settings.advanced.index')
                            ->push(__('Deleted Uploaded File'), route('admin.auth.settings.advanced.deleted'));
                    });
            });

            Route::group([
                'prefix' => 'loading',
                'as' => 'loading.',
            ], function () {
                Route::group(['prefix' => '{loading_history}'], function () {
                    Route::delete('/', [LoadingHistoryController::class, 'destroy'])->name('destroy');
                    Route::get('download_file', [LoadingHistoryController::class, 'download_file'])->name('download_file');
                });
            });

            Route::group([
                'prefix' => 'updatefile',
                'as' => 'updatefile.',
            ], function () {
                Route::get('/', [UpdateFileSettingsController::class, 'index'])
                    ->name('index')
                    ->breadcrumbs(function (Trail $trail) {
                        $trail->parent('admin.dashboard')
                            ->push(__('Upload Files'), route('admin.auth.settings.updatefile.index'));
                    });

                Route::get('export_file_list', [UpdateFileSettingsController::class, 'export_file_list'])->name('export_file_list');

                Route::post('upload_file', [UpdateFileSettingsController::class, 'upload_file'])->name('upload_file');

                Route::post('get_files', [UpdateFileSettingsController::class, 'get_files'])->name('get_files');

                Route::post('update_schedule', [UpdateFileSettingsController::class, 'update_schedule'])->name('update_schedule');

                Route::post('save_data_import_settings', [UpdateFileSettingsController::class, 'save_data_import_settings'])->name('save_data_import_settings');

                Route::post('stop_upload_progress', [UpdateFileSettingsController::class, 'stop_upload_progress'])->name('stop_upload_progress');

                Route::get('ajax_uploading_progress', [UpdateFileSettingsController::class, 'ajax_uploading_progress'])->name('ajax_uploading_progress');

                Route::get('ajax_check_compressing', [UpdateFileSettingsController::class, 'ajax_check_compressing'])->name('ajax_check_compressing');

                Route::get('download_image_dimension', [UpdateFileSettingsController::class, 'download_image_dimension'])->name('download_image_dimension');

                Route::post('mapping', [UpdateFileSettingsController::class, 'mapping'])->name('mapping');

                Route::get('download_mapping', [UpdateFileSettingsController::class, 'download_mapping'])->name('download_mapping');

                Route::get('export_mapping', [UpdateFileSettingsController::class, 'export_mapping'])->name('export_mapping');

                Route::group(['prefix' => '{urls_file}'], function () {
                    Route::delete('/', [UpdateFileSettingsController::class, 'destroy'])->name('destroy');
                    Route::get('get_files', [UpdateFileSettingsController::class, 'get_files'])->name('get_files');
                });
                Route::post('download_files', [UpdateFileSettingsController::class, 'download_files'])->name('download_files');

                Route::post('download_list', [UpdateFileSettingsController::class, 'download_list'])->name('download_list');

                Route::post('run_schedule', [UpdateFileSettingsController::class, 'run_schedule'])->name('run_schedule');

                Route::get('deleted', [UpdateFileSettingsController::class, 'index'])
                    ->name('deleted')
                    ->breadcrumbs(function (Trail $trail) {
                        $trail->parent('admin.auth.settings.updatefile.index')
                            ->push(__('Deleted Uploaded File'), route('admin.auth.settings.updatefile.deleted'));
                    });
            });

            Route::group([
                'prefix' => 'exception',
                'as' => 'exception.',
            ], function () {
                Route::get('generate_exceptions_report', [ExceptionController::class, 'generate_exceptions_report'])->name('generate_exceptions_report');

                Route::group(['prefix' => '{exception}'], function () {
                    Route::delete('/', [ExceptionController::class, 'destroy'])->name('destroy');
                });

                Route::get('deleted', [ExceptionController::class, 'index'])
                    ->name('deleted')
                    ->breadcrumbs(function (Trail $trail) {
                        $trail->parent('admin.auth.settings.updatefile.index')
                            ->push(__('Deleted Exception'), route('admin.auth.settings.updatefile.deleted'));
                    });
            });

            Route::group([
                'prefix' => 'product_selection',
                'as' => 'product_selection.',
            ], function () {
                Route::get('generate_product_selections_report', [ProductSelectionController::class, 'generate_product_selections_report'])->name('generate_product_selections_report');

                Route::group(['prefix' => '{product_selection}'], function () {
                    Route::delete('/', [ProductSelectionController::class, 'destroy'])->name('destroy');
                });

                Route::get('deleted', [ProductSelectionController::class, 'index'])
                    ->name('deleted')
                    ->breadcrumbs(function (Trail $trail) {
                        $trail->parent('admin.auth.settings.updatefile.index')
                            ->push(__('Deleted Exception'), route('admin.auth.settings.updatefile.deleted'));
                    });
            });
        });
    });

    Route::group([
        'prefix' => 'video',
        'as' => 'video.',
    ], function () {
        Route::group([
            'middleware' => 'role:' . config('boilerplate.access.role.admin') . '|' . config('boilerplate.access.role.company_admin'),
        ], function () {

            Route::group([
                'prefix' => 'projects',
                'as' => 'projects.',
                'middleware' => 'role:' . config('boilerplate.access.role.admin') . '|' . config('boilerplate.access.role.company_admin'),
            ], function () {
                Route::post('update-order',         [VideoProjectController::class, 'updateProjectsOrder']);
                Route::get('all',                   [VideoProjectController::class, 'getProjects']);
                Route::get('/',                     [VideoProjectController::class, 'index'])->name('index');
            });
        });

        Route::group([
            'middleware' => 'role:' . config('boilerplate.access.role.admin') . '|' . config('boilerplate.access.role.company_admin'),
        ], function () {

            Route::group([
                'prefix' => 'templates',
                'as' => 'templates.',
                'middleware' => 'role:' . config('boilerplate.access.role.admin') . '|' . config('boilerplate.access.role.company_admin'),
            ], function () {
                Route::post('update-order',         [VideoTemplateController::class, 'updateTemplatesOrder']);
                Route::get('all',                   [VideoTemplateController::class, 'getTemplates']);
                Route::get('/',                     [VideoTemplateController::class, 'index'])->name('index');
            });
        });

        Route::group([
            'middleware' => 'role:' . config('boilerplate.access.role.admin') . '|' . config('boilerplate.access.role.company_admin'),
        ], function () {

            Route::group([
                'prefix' => 'themes',
                'as' => 'themes.',
                'middleware' => 'role:' . config('boilerplate.access.role.admin') . '|' . config('boilerplate.access.role.company_admin'),
            ], function () {
                Route::get('/',                     [VideoThemeController::class, 'index'])->name('index');
                Route::get('/create',               [VideoThemeController::class, 'create'])->name('create');
                Route::post('/',                    [VideoThemeController::class, 'store'])->name('store');
                Route::get('/{id}/edit',            [VideoThemeController::class, 'edit'])->name('edit');
                Route::put('/{id}',                 [VideoThemeController::class, 'update'])->name('update');
                Route::delete('/{id}',              [VideoThemeController::class, 'destroy'])->name('delete');
                Route::post('/{id}',  [VideoThemeController::class, 'themePosition'])->name('themePosition');
            });
        });

        Route::group([
                         'middleware' => 'role:' . config('boilerplate.access.role.admin') . '|' . config('boilerplate.access.role.company_admin'),
                     ], function () {
            Route::group([
                             'prefix' => 'drafts',
                             'as' => 'drafts.',
                             'middleware' => 'role:' . config('boilerplate.access.role.admin') . '|' . config('boilerplate.access.role.company_admin'),
                         ], function () {
                Route::post('update-order',         [VideoProjectController::class, 'updateProjectsOrder']);
                Route::get('all',                   [VideoProjectController::class, 'getProjects']);
                Route::get('/',                     [VideoProjectController::class, 'index'])->name('index');
            });
        });

        Route::group([
            'middleware' => 'role:' . config('boilerplate.access.role.admin') . '|' . config('boilerplate.access.role.company_admin'),
        ], function () {
            Route::group([
                'prefix' => 'media',
                'as' => 'media.',
                'middleware' => 'role:' . config('boilerplate.access.role.admin') . '|' . config('boilerplate.access.role.company_admin'),
            ], function () {
                Route::post('/',                [MediaController::class, 'search'])->name('folder.search');
                Route::get('/',                 [MediaController::class, 'index'])->name('folder.index');
                Route::get('/folder/store',     [MediaController::class, 'folderAdd'])->name('folder.add');
                Route::post('/folder/update',   [MediaController::class, 'folderUpdate'])->name('folder.update');
                Route::get('/folder',           [MediaController::class, 'folder'])->name('folder');
                Route::post('/folder/move',     [MediaController::class, 'folderMove'])->name('folder.move');
                Route::post('/folder/delete',   [MediaController::class, 'folderDelete'])->name('folder.delete');;

                Route::post('/file/store',      [MediaController::class, 'fileAdd'])->name('file.add');
                Route::get('/file',             [MediaController::class, 'file']);
                Route::post('/file/delete',     [MediaController::class, 'fileDelete'])->name('file.delete');
                Route::post('/file/update',     [MediaController::class, 'fileUpdate'])->name('file.update');
                Route::post('/file/move',       [MediaController::class, 'fileMove'])->name('file.move');
                Route::post('/file/cropp',      [MediaController::class, 'cropp']);
                Route::post('/file/trim',       [MediaController::class, 'trimVideo'])->name('video.trim');
                Route::get('/file/copy',        [MediaController::class, 'fileCopy'])->name('file.copy');

                Route::get('/video',            [MediaController::class, 'getVideoInfo']);

                // tags
                Route::get('/tags',             [MediaTagController::class, 'getMediaTags']);
                Route::put('/tags',             [MediaTagController::class, 'update']);
            });
        });

        Route::group([
            'middleware' => 'role:' . config('boilerplate.access.role.admin') . '|' . config('boilerplate.access.role.company_admin'),
        ], function () {
            Route::group([
                'prefix' => 'shares',
                'as' => 'shares.',
                'middleware' => 'role:' . config('boilerplate.access.role.admin') . '|' . config('boilerplate.access.role.company_admin'),
            ], function () {
                Route::get('/',                         [VideoShareController::class, 'index'])->name('index');
                Route::get('/{id}',                     [VideoShareController::class, 'show'])->name('show');
                Route::put('/{id}',                     [VideoShareController::class, 'update'])->name('update');
                Route::post('/send',                    [VideoShareController::class, 'send']);
                Route::get('video-review/{uuid}',       [VideoShareController::class, 'review'])->name('review');
            });
            
            Route::get('comments',                      [VideoCommentController::class, 'index']);
            Route::post('comments',                     [VideoCommentController::class, 'store'])->name('comments.store');
            Route::get('video-review/{uuid}',           [VideoShareController::class, 'review'])->name('review');
        });

        Route::group([
            'middleware' => 'role:' . config('boilerplate.access.role.admin') . '|' . config('boilerplate.access.role.company_admin'),
        ], function () {
            Route::group([
                'prefix' => 'logs',
                'as' => 'logs.',
                'middleware' => 'role:' . config('boilerplate.access.role.admin') . '|' . config('boilerplate.access.role.company_admin'),
            ], function () {
                Route::get('/',                         [VideoLogController::class, 'index'])->name('index');
            });
        });
    });
    /*
    Route::group([
        'prefix' => 'role',
        'as' => 'role.',
        'middleware' => 'role:'.config('boilerplate.access.role.admin'),
    ], function () {
        Route::get('/', [RoleController::class, 'index'])
            ->name('index')
            ->breadcrumbs(function (Trail $trail) {
                $trail->parent('admin.dashboard')
                    ->push(__('Role Management'), route('admin.auth.role.index'));
            });

        Route::get('create', [RoleController::class, 'create'])
            ->name('create')
            ->breadcrumbs(function (Trail $trail) {
                $trail->parent('admin.auth.role.index')
                    ->push(__('Create Role'), route('admin.auth.role.create'));
            });

        Route::post('/', [RoleController::class, 'store'])->name('store');

        Route::group(['prefix' => '{role}'], function () {
            Route::get('edit', [RoleController::class, 'edit'])
                ->name('edit')
                ->breadcrumbs(function (Trail $trail, Role $role) {
                    $trail->parent('admin.auth.role.index')
                        ->push(__('Editing :role', ['role' => $role->name]), route('admin.auth.role.edit', $role));
                });

            Route::patch('/', [RoleController::class, 'update'])->name('update');
            Route::delete('/', [RoleController::class, 'destroy'])->name('destroy');
        });
    });
*/
});
