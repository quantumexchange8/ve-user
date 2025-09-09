<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Code extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'redemption_code_request_id',
        'redemption_code',
        'meta_login',
        'acc_name',
        'license_name',
        'product_name',
        'version',
        'serial_number',
        'expired_date',
        'status',
    ];

    protected $dates = ['expired_date'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function license()
    {
        return $this->belongsTo(SettingLicense::class, 'license_name', 'slug');
    }

    public function getVersionControlAttribute()
    {
        // Use the eager loaded collection instead of running a new query
        return $this->license ? $this->license->versionControls->firstWhere('version', $this->version) : null;
    }
}