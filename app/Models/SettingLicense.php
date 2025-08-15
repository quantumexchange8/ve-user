<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SettingLicense extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'url',
        'category',
        'type',
        'valid_year',
    ];

    public function versionControls()
    {
        return $this->hasMany(VersionControl::class, 'setting_license_id', 'id');
    }
}
