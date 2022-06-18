<?php

namespace App\Domains\Auth\Models;

use Illuminate\Database\Eloquent\Model;

class VideoEntityCompanies extends Model
{
    const TABLE_NAME = 'video_entity-companies';

    const FIELD_ENTITY_ID = 'entity_id';
    const FIELD_COMPANY_ID = 'company_id';
    const FIELD_ENTITY_TYPE = 'entity_type';

    const ENTITY_TEMPLATE_TYPE = 'template';
    const ENTITY_THEME_TYPE = 'theme';

    protected $table = self::TABLE_NAME;

    public function scopeSetEntityType($query, string $entityType)
    {
        return $query->where(self::FIELD_ENTITY_TYPE, $entityType);
    }
}