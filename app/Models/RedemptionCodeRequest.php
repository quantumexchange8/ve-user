<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RedemptionCodeRequest extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $dates = ['approved_at'];

    public function items()
    {
        return $this->hasMany(RedemptionCodeRequestItem::class);
    }
}
