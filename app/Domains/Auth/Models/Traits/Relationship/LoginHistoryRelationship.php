<?php

namespace App\Domains\Auth\Models\Traits\Relationship;

use App\Domains\Auth\Models\User;

/**
 * Class LoginHistoryRelationship.
 */
trait LoginHistoryRelationship
{
    /**
     * @return mixed
     */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
