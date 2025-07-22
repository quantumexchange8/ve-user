<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RedemptionCodeRequestItem extends Model
{
    protected $guarded = [];

    public function request()
    {
        return $this->belongsTo(RedemptionCodeRequest::class, 'redemption_code_request_id');
    }

    public function product()
    {
        return $this->belongsTo(SettingLicense::class, 'setting_license_id');
    }
}
