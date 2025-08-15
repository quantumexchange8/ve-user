<?php

namespace App\Http\Controllers;

use App\Models\SettingLicense;
use Illuminate\Http\Request;

class GeneralController extends Controller
{
    public function getSettingLicenses($returnAsArray = false)
    {
        $licensesRaw = SettingLicense::select('id', 'name')->distinct()->get();
    
        $licenses = [];
        foreach ($licensesRaw as $license) {
            $licenses[] = [
                'value' => $license->id,
                'name' => $license->name,
            ];
        }
    
        if ($returnAsArray) {
            return $licenses;
        }
    
        return response()->json([
            'licenses' => $licenses,
        ]);
    }
}
