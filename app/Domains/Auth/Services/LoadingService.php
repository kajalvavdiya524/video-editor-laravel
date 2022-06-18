<?php

namespace App\Domains\Auth\Services;

use Illuminate\Support\Collection;
use App\Domains\Auth\Models\LoadingHistory;
use App\Domains\Auth\Models\Company;
use Illuminate\Support\Facades\Storage;
use App\Exceptions\GeneralException;
use App\Services\BaseService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Class LoadingService.
 */
class LoadingService extends BaseService
{
    /**
     * LoadingService constructor.
     *
     * @param  LoadingHistory  $loading_history
     */
    public function __construct(LoadingHistory $loading_history)
    {
        $this->model = $loading_history;
    }

    /**
     * @param  array  $data
     *
     * @return LoadingHistory
     * @throws GeneralException
     * @throws \Throwable
     */
    public function store(array $data = []): LoadingHistory
    {
        $user = auth()->user();
        $tmpfile = $data['tmp_name'];
        $filename = $data['filename'];
        
        try {
            Storage::disk('s3')->put('loaded_data/'.$filename, file_get_contents($tmpfile));
    
            if (file_exists($filename)) {
                unlink($filename);
            }
        } catch (Exception $e) {
            throw new GeneralException(__($e->getMessage()));
        }

        DB::beginTransaction();

        try {
            $loading_history = $this->model::create([
                'company_id' => $user->company_id,
                'user_id' => $user->id,
                'filename' => $data['filename'], 
                'url' => 'loaded_data/'.$filename, 
                'type' => $data['type']
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            throw new GeneralException(__('There was a problem creating this urls file. Please try again.'));
        }

        DB::commit();

        return $loading_history;
    }

    
    /**
     * @param  LoadingHistory  $loading_history
     *
     * @return LoadingHistory
     * @throws GeneralException
     */
    public function delete(LoadingHistory $loading_history): LoadingHistory
    {
        Storage::disk('s3')->delete($loading_history->url);

        if ($this->deleteById($loading_history->id)) {
            return $loading_history;
        }

        throw new GeneralException('There was a problem deleting this urls file. Please try again.');
    }
}
