<?php

namespace App\Domains\Auth\Models;

use App\Domains\Auth\Models\Traits\Relationship\UrlsFileRelationship;
use App\Domains\Auth\Models\Traits\Attribute\UrlsFileAttribute;
use Illuminate\Database\Eloquent\Model;

class UrlsFile extends Model
{
    use UrlsFileAttribute;
    use UrlsFileRelationship;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'company_id',
        'user_id',
        'filename',
        'rows', 
        'status',
        'url', 
        'id_column', 
        'zip_file_url', 
        'new_prod',
        'new_nf',
        'new_ingr',
        'changed_prod',
        'changed_nf',
        'changed_ingr'
    ];

}
