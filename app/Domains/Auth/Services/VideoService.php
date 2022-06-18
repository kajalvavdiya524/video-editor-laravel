<?php

namespace App\Domains\Auth\Services;

use App\Domains\Auth\Models\Company;
use App\Domains\Auth\Models\VideoEntityCompanies;
use Illuminate\Support\Collection;

class VideoService
{
    const ACTION_SELECT = 'select';
    const ACTION_EXCLUDE = 'exclude';

    public function getEntityCompanies(array $entityIds, $entityType): Collection
    {
        return VideoEntityCompanies::setEntityType($entityType)
            ->whereIn(VideoEntityCompanies::FIELD_ENTITY_ID, $entityIds)
            ->get();
    }

    public function insertEntityOfCompany(int $entityId, int $companyId, string $entityType)
    {
        VideoEntityCompanies::insert([
            VideoEntityCompanies::FIELD_ENTITY_ID => $entityId,
            VideoEntityCompanies::FIELD_COMPANY_ID => $companyId,
            VideoEntityCompanies::FIELD_ENTITY_TYPE => $entityType
        ]);
    }


    public function addOrRemoveCompany(array $data, string $entityType, int $isAllCompanies = 0)
    {
        $companies = Company::all();

        if (in_array($data['action'], [self::ACTION_EXCLUDE, self::ACTION_SELECT])) {
            $this->selectAllOrExclude($data['entityId'], $companies, $entityType, $data['action']);

            return [
                'action' => $data['action']
            ];
        }

        if ($isAllCompanies) {
            $this->selectAllOrExclude($data['entityId'], $companies, $entityType, self::ACTION_SELECT);
        }

        $query = VideoEntityCompanies::setEntityType($entityType)
            ->where(VideoEntityCompanies::FIELD_ENTITY_ID, $data['entityId'])
            ->where(VideoEntityCompanies::FIELD_COMPANY_ID, $data['companyId']);

        $isExists = $query->exists();

        if ($isExists) {
            $query->delete();
        } else {
            $this->insertEntityOfCompany($data['entityId'], $data['companyId'], $entityType);
        }

        $isSelectAll = empty(array_diff(
            $companies->pluck('id')->toArray(),
            VideoEntityCompanies::setEntityType($entityType)
                ->where(VideoEntityCompanies::FIELD_ENTITY_ID, $data['entityId'])
                ->get()
                ->pluck(VideoEntityCompanies::FIELD_COMPANY_ID)
                ->toArray()
        ));

        return [
            'isExists' => !$isExists,
            'action' => $data['action'],
            'isSelectAll' => $isSelectAll
        ];
    }

    private function selectAllOrExclude(int $entityId, Collection $companies, string $entityType, string $action)
    {
        VideoEntityCompanies::setEntityType($entityType)
            ->where(VideoEntityCompanies::FIELD_ENTITY_ID, $entityId)
            ->delete();
        if ($action === self::ACTION_SELECT) {
            $insertData = $companies->map(function ($company) use ($entityId, $entityType) {
                return [
                    VideoEntityCompanies::FIELD_ENTITY_ID => $entityId,
                    VideoEntityCompanies::FIELD_COMPANY_ID => $company->id,
                    VideoEntityCompanies::FIELD_ENTITY_TYPE => $entityType
                ];
            });
            VideoEntityCompanies::insert($insertData->toArray());
        }
    }
}