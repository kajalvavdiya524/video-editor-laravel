<?php

use App\Domains\Auth\Http\Controllers\Backend\Video\VideoThemeController;
use App\Domains\Auth\Http\Controllers\Frontend\HomeController;
use App\Domains\Auth\Http\Controllers\Frontend\FileController;
use App\Domains\Auth\Http\Controllers\Frontend\BannerController;
use App\Domains\Auth\Http\Controllers\Frontend\GridLayoutController;
use App\Domains\Auth\Http\Controllers\Frontend\GridLayoutTemplateController;
use App\Domains\Auth\Http\Controllers\Frontend\HistoryController;
use App\Domains\Auth\Http\Controllers\Frontend\ProjectController;
use App\Domains\Auth\Http\Controllers\Frontend\UploadImagesController;
use App\Domains\Auth\Http\Controllers\Frontend\User\AccountController;
use App\Domains\Auth\Http\Controllers\Frontend\User\ProfileController;
use App\Domains\Auth\Http\Controllers\Frontend\VideoPreviewController;
use App\Domains\Auth\Http\Controllers\Backend\Video\VideoTemplateController;
use App\Domains\Auth\Http\Controllers\Backend\Video\VideoProjectController;
use App\Domains\Auth\Http\Controllers\Backend\Video\VideoSceneController;
use App\Domains\Auth\Http\Controllers\Backend\Video\MediaController;
use App\Domains\Auth\Http\Controllers\Frontend\CreateController;

use App\Domains\Auth\Models\Customer;
use App\Domains\Auth\Models\GridLayout;
use App\Domains\Auth\Models\GridLayoutTemplate;
use App\Domains\Auth\Models\Template;
use Illuminate\Support\Facades\Route;
use Tabuna\Breadcrumbs\Trail;



/*
 * Frontend Controllers
 * All route names are prefixed with 'frontend.'.
 */
Route::get('/', [HomeController::class, 'index'])->name('index');
Route::get('/share', [BannerController::class, 'share'])->name('share');

/*
 * These frontend controllers require the user to be logged in
 * All route names are prefixed with 'frontend.'
 * These routes can not be hit if the user has not confirmed their email
 */
Route::group([
    'as' => 'user.',
    'middleware' => ['auth', 'password.expires', config('boilerplate.access.middleware.verified')]
], function () {
    Route::get('account', [AccountController::class, 'index'])->name('account');
    Route::patch('profile/update', [ProfileController::class, 'update'])->name('profile.update');
});

Route::group([
    'prefix' => 'file',
    'as' => 'file.',
    'middleware' => ['auth', 'password.expires', config('boilerplate.access.middleware.verified')]
], function () {
    Route::get('/', [FileController::class, 'index'])->name('index');
    Route::get('list', [FileController::class, 'list'])->name('list');
    Route::get('data', [FileController::class, 'ajax_data'])->name('data');
    Route::get('file_list', [FileController::class, 'ajax_file_list'])->name('file_list');
    Route::get('background_list', [FileController::class, 'ajax_background_list'])->name('background_list');
    Route::post('reindex', [FileController::class, 'ajax_reindex'])->name('reindex');
    Route::post('export', [FileController::class, 'ajax_export'])->name('export');
    Route::post('download', [FileController::class, 'ajax_download'])->name('download');
    Route::post('view', [FileController::class, 'ajax_view'])->name('view');
    Route::post('create_ads', [BannerController::class, 'index'])->name('create_ads');
    Route::post('generate_thumbnail', [FileController::class, 'ajax_generate_thumbnail'])->name('generate_thumbnail');
    Route::post('re_generate_thumbnail', [FileController::class, 'ajax_re_generate_thumbnail'])->name('re_generate_thumbnail');
    Route::post('save_cropped_image', [FileController::class, 'save_cropped_image'])->name('save_cropped_image');
    Route::post('restore_original_image', [FileController::class, 'restore_original_image'])->name('restore_original_image');

    Route::group([
        'prefix' => 'uploadimg',
        'as' => 'uploadimg.',
    ], function () {
        Route::get('/', [UploadImagesController::class, 'index'])->name('index');

        Route::post('image_from_web', [UploadImagesController::class, 'upload_image_from_web'])->name('image_from_web');

        Route::post('upload_images', [UploadImagesController::class, 'upload_images'])->name('upload_images');

        Route::post('update_image', [UploadImagesController::class, 'update_image'])->name('update_image');

        Route::group(['prefix' => '{upload_image}'], function () {
            Route::delete('/', [UploadImagesController::class, 'destroy'])->name('destroy');
            Route::get('download_image', [UploadImagesController::class, 'download_image'])->name('download_image');
        });

        Route::get('deleted', [UploadImagesController::class, 'index'])
        ->name('deleted')
        ->breadcrumbs(function (Trail $trail) {
            $trail->parent('frontend.file.uploadimg.index')
                ->push(__('Deleted Uploaded Image'), route('frontend.file.uploadimg.deleted'));
        });
    });
});

Route::group([
    'prefix' => 'banner',
    'as' => 'banner.',
    'middleware' => ['auth', 'password.expires', config('boilerplate.access.middleware.verified')]
], function () {
    Route::get('/', [BannerController::class, 'index'])
        ->name('index')
        ->breadcrumbs(function (Trail $trail) {
            $trail->push(__('Customer'));
        });
    Route::post('generate', [BannerController::class, 'generate'])->name('generate');
    Route::post('preview', [BannerController::class, 'preview'])->name('preview');
    Route::post('view', [BannerController::class, 'view'])->name('view');
    Route::post('download', [BannerController::class, 'download'])->name('download');
    Route::post('download_xlsx_output/{customer_id}', [BannerController::class, 'download_xlsx_output'])->name('download_xlsx_output');
    Route::post('download_sheet_output/{customer_id}', [BannerController::class, 'download_sheet_output'])->name('download_sheet_output');
    Route::post('download_layout_assets', [BannerController::class, 'download_layout_assets'])->name('download_layout_assets');
    Route::post('download_layout_logos', [BannerController::class, 'download_layout_logos'])->name('download_layout_logos');
    Route::post('download_layout_proof', [BannerController::class, 'download_layout_proof'])->name('download_layout_proof');
    Route::post('download_layout_web', [BannerController::class, 'download_layout_web'])->name('download_layout_web');
    Route::post('publish', [BannerController::class, 'publish'])->name('publish');
    Route::post('can_share', [BannerController::class, 'can_share'])->name('can_share');
    Route::post('update_product_selections', [BannerController::class, 'update_product_selections'])->name('update_product_selections');
    Route::post('background', [BannerController::class, 'get_background_images'])->name('background');
    Route::get('background-stock', [BannerController::class, 'get_background_stock_images'])->name('background_stock');
    Route::post('kroger_template_settings', [BannerController::class, 'get_kroger_template_settings'])->name('kroger_template_settings');
    Route::post('upload_cropped_bk_image', [BannerController::class, 'upload_cropped_bk_image'])->name('upload_cropped_bk_image');
    Route::post('delete_cropped_bk_image', [BannerController::class, 'delete_cropped_bk_image'])->name('delete_cropped_bk_image');
    Route::post('upload_cropped_product_image', [BannerController::class, 'upload_cropped_product_image'])->name('upload_cropped_product_image');
    Route::post('delete_bk_image', [BannerController::class, 'delete_bk_image'])->name('delete_bk_image');
    Route::post('upload_bk_image', [BannerController::class, 'upload_bk_image'])->name('upload_bk_image');
    Route::post('template_settings', [BannerController::class, 'get_template_settings'])->name('template_settings');
    Route::post('store_remote_image', [BannerController::class, 'store_remote_image'])->name('store_remote_image');
    Route::post('getbase64image', [BannerController::class, 'getBase64image'])->name('getbase64image');

    Route::post('isExistProject', [ProjectController::class, 'isExistProject'])->name('isExistProject');
    Route::post('isExistDraft', [HistoryController::class, 'isExistDraft'])->name('isExistDraft');

    Route::group([
        'prefix' => '{customer_id}/group',
        'as' => 'group.',
    ], function () {
        Route::get('/', [GridLayoutController::class, 'index'])
            ->name('index')
            ->breadcrumbs(function (Trail $trail, $customer_id) {
                $trail->parent('frontend.banner.customer', Customer::find($customer_id))
                    ->push(__('Layout Management'), route('frontend.banner.group.index', ['customer_id' => $customer_id]));
            });
        Route::get('create', [GridLayoutController::class, 'create'])
            ->name('create')
            ->breadcrumbs(function (Trail $trail, $customer_id) {
                $trail->parent('frontend.banner.group.index', $customer_id)->push(__('Create Layout'));
            });
        Route::post('store', [GridLayoutController::class, 'store'])->name('store');
        Route::post('assign', [GridLayoutController::class, 'assign'])->name('assign');

        Route::group(['prefix' => '{layout}'], function () {
            Route::get('show', [GridLayoutController::class, 'show'])
                ->name('show')
                ->breadcrumbs(function (Trail $trail, $customer_id, GridLayout $layout) {
                    $trail->parent('frontend.banner.group.index', $customer_id)->push(__('Viewing :layout', ['layout' => $layout->name]));
                });
            Route::post('change_aligns', [GridLayoutController::class, 'change_aligns'])->name('change_aligns');
            Route::put('save_changes', [GridLayoutController::class, 'save_changes'])->name('save_changes');
            Route::patch('update_options', [GridLayoutController::class, 'update_options'])->name('update_options');
            Route::get('preview', [GridLayoutController::class, 'preview']);
            Route::get('edit', [GridLayoutController::class, 'edit'])
                ->name('edit')
                ->breadcrumbs(function (Trail $trail, $customer_id, GridLayout $layout) {
                    $trail->parent('frontend.banner.group.index', $customer_id)->push(__('Editing :layout', ['layout' => $layout->name]), route('frontend.banner.group.edit', ['customer_id' => $customer_id, 'layout' => $layout]));
                });
            Route::get('copy', [GridLayoutController::class, 'copy'])->name('copy');
            Route::get('download_html', [GridLayoutController::class, 'download_html'])->name('download_html');
            Route::get('edit_html', [GridLayoutController::class, 'edit_html'])
                ->name('edit_html')
                ->breadcrumbs(function (Trail $trail, $customer_id, GridLayout $layout) {
                    $trail->parent('frontend.banner.group.index', $customer_id)->push(__('Editing :layout HTML', ['layout' => $layout->name]));
                });;
            Route::post('save_html', [GridLayoutController::class, 'save_html'])->name('save_html');
            Route::patch('update', [GridLayoutController::class, 'update'])->name('update');
            Route::delete('/', [GridLayoutController::class, 'destroy'])->name('destroy');

            Route::post('bulk_update', [GridLayoutController::class, 'bulk_update'])->name('bulk_update');

            Route::get('/template/{instance_id}/{template_id}', [GridLayoutTemplateController::class, 'index'])
                ->name('template')
                ->breadcrumbs(function (Trail $trail, $customer_id, $layout_id, $instance_id, $template_id) {
                    $template = Template::find($template_id);
                    $trail->parent('frontend.banner.group.edit', $customer_id, GridLayout::find($layout_id))->push(__('Editing :template', ['template' => $template->name]));
                });
            Route::post('/template/{instance_id}', [GridLayoutTemplateController::class, 'store'])->name('store_instance');
            Route::patch('/template/{instance_id}', [GridLayoutTemplateController::class, 'update'])->name('update_instance');
        });
    });
    Route::get('{customer}/{template_id?}', [BannerController::class, 'view_customer'])
        ->name('customer')
        ->breadcrumbs(function (Trail $trail, Customer $customer) {
            $trail->push($customer->name, route('frontend.banner.customer', ['customer' => $customer->value]));
        });

    // Route::get('generic/{template?}', [BannerController::class, 'view_generic'])->name('generic');
    // Route::get('amazon/{template_id?}', [BannerController::class, 'view_amazon'])->name('amazon');
    // Route::get('amazon_fresh/{template?}', [BannerController::class, 'view_amazon_fresh'])->name('amazon_fresh');
    // Route::get('kroger/{template?}', [BannerController::class, 'view_kroger'])->name('kroger');
    // Route::get('superama/{template?}', [BannerController::class, 'view_superama'])->name('superama');
    // Route::get('target/{template?}', [BannerController::class, 'view_target'])->name('target');
    // Route::get('walmart/{template?}', [BannerController::class, 'view_walmart'])->name('walmart');
    // Route::get('mrhi/{template?}', [BannerController::class, 'view_mrhi'])->name('mrhi');
    // Route::get('instagram/{template?}', [BannerController::class, 'view_instagram'])->name('instagram');
    // Route::post('amazon_fresh', [BannerController::class, 'view_amazon_fresh'])->name('amazon_fresh');
    // Route::get('pilot/{template?}', [BannerController::class, 'view_pilot'])->name('pilot');
    // Route::get('sam', [BannerController::class, 'view_sam'])->name('sam');

});

Route::group([
    'prefix' => 'history',
    'as' => 'history.'
    ], function () {

        Route::get('/share/outputs/{url}', [HistoryController::class, 'share'])->name('share_history');

        Route::group(['middleware' =>['auth', 'password.expires', config('boilerplate.access.middleware.verified')]], function(){
        Route::get('/', [HistoryController::class, 'index'])->name('index');
        Route::post('/columns', [HistoryController::class, 'columns'])->name('columns');
        Route::post('/delete', [HistoryController::class, 'destroy'])->name('destroy');
        Route::post('/download_all', [HistoryController::class, 'download_all'])->name('download_all');
        Route::group(['prefix' => '{history}'], function () {
            Route::get('show', [HistoryController::class, 'show'])->name('show');
            Route::get('publish', [HistoryController::class, 'publish'])->name('publish');
            Route::get('edit', [HistoryController::class, 'edit'])->name('edit');
            Route::delete('/', [HistoryController::class, 'destroy'])->name('destroy');
            Route::get('download', [HistoryController::class, 'download'])->name('download');
        });
    });

});

Route::group([
    'prefix' => 'projects',
    'as' => 'projects.'
], function () {

    Route::get('/share/outputs/{url}', [ProjectController::class, 'share'])->name('share_project');

    Route::group(['middleware' =>['auth', 'password.expires', config('boilerplate.access.middleware.verified')]], function(){
        Route::get('/', [ProjectController::class, 'index'])->name('index');
        Route::post('/columns', [ProjectController::class, 'columns'])->name('columns');
        Route::get('/countries', [ProjectController::class, 'countries'])->name('countries');
        Route::get('/languages', [ProjectController::class, 'languages'])->name('languages');
        Route::post('/download_all', [ProjectController::class, 'download_all'])->name('download_all');
        Route::get('master_projects', [ProjectController::class, 'master_projects'])->name('master_projects');
        Route::group(['prefix' => '{project}'], function () {
            Route::get('show', [ProjectController::class, 'show'])->name('show');
            Route::get('edit', [ProjectController::class, 'edit'])->name('edit');
            Route::delete('/', [ProjectController::class, 'destroy'])->name('destroy');
            Route::get('download', [ProjectController::class, 'download'])->name('download');
            Route::get('request_approve', [ProjectController::class, 'request_approve'])->name('request_approve');
            Route::post('approve', [ProjectController::class, 'approve'])->name('approve');
            Route::post('reject', [ProjectController::class, 'reject'])->name('reject');
            Route::get('subprojects', [ProjectController::class, 'subprojects'])->name('subprojects');
        });
    });
});

Route::group([
    'prefix' => 'video',
    'as' => 'video.',
    'middleware' => ['auth', 'password.expires', config('boilerplate.access.middleware.verified')]
], function () {
    Route::get('/', [VideoPreviewController::class, 'index'])->name('index');

});
Route::group([
    'prefix' => 'video'
], function() {
    Route::post('templates/update-order',       [VideoTemplateController::class, 'updateTemplatesOrder']);
    Route::get('templates/all',                 [VideoTemplateController::class, 'getTemplates']);
    Route::post('templates',                    [VideoTemplateController::class, 'store']);
    Route::put('templates/{id}',                [VideoTemplateController::class, 'update']);
    Route::get('templates/{id}',                [VideoTemplateController::class, 'show']);
    Route::delete('templates/{id}',             [VideoTemplateController::class, 'destroy']);
    Route::post('/export-template-to-server',   [VideoTemplateController::class, 'exportTemplateToServer']);
    Route::post('/add-or-remove-company',       [VideoTemplateController::class, 'addOrRemoveCompany']);

    Route::post('projects/update-order',        [VideoProjectController::class, 'updateProjectsOrder']);
    Route::get('projects/all',                  [VideoProjectController::class, 'getProjects']);
    Route::post('projects',                     [VideoProjectController::class, 'store']);
    Route::put('projects/{id}',                 [VideoProjectController::class, 'update']);
    Route::get('projects/{id}',                 [VideoProjectController::class, 'show']);
    Route::delete('projects/{id}',              [VideoProjectController::class, 'destroy']);

    Route::get('scene/get',                     [VideoSceneController::class, 'get']);
    Route::post('scene/save',                   [VideoSceneController::class, 'save']);
    Route::post('scene/edit',                   [VideoSceneController::class, 'edit']);
    Route::delete('scene/delete/{id}',          [VideoSceneController::class, 'delete']);

    Route::post('/import',                      [VideoTemplateController::class, 'importFile']);
    Route::post('/export-assets',               [VideoTemplateController::class, 'exportAssets']);
    Route::post('/upload_file',                 [VideoTemplateController::class, 'upload_file']);
    Route::post('/upload_thumb',                 [VideoTemplateController::class, 'upload_thumb']);
    Route::post('/create-video',                [VideoTemplateController::class, 'createVideo']);
    Route::get('/video-creation',               [VideoTemplateController::class, 'creationStarted']);
    Route::post('/video-creation',              [VideoTemplateController::class, 'creationPost'])->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
    Route::post('/crop-image',                  [VideoTemplateController::class, 'saveCroppedImage']);
    Route::post('/media/file/trim-video',       [MediaController::class, 'trimVideoNew']);
    Route::post('/custom-visible-columns',      [VideoTemplateController::class, 'updateCustomVisibleColumns']);
    Route::post('/export-template',             [VideoTemplateController::class, 'exportTemplate']);
    Route::get('/image-library',      [VideoTemplateController::class, 'getImages']);
});


Route::group([
    'prefix' => 'create',
    'as' => 'create.',
    'middleware' => ['auth', 'password.expires', config('boilerplate.access.middleware.verified')]
], function () {
    Route::get('/product', [CreateController::class, 'product'])->name('product');
    Route::get('/NFT', [CreateController::class, 'nft'])->name('nft');


});
