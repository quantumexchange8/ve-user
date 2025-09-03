<?php

namespace App\Http\Controllers;

use App\Models\Code;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\SettingLicense;
use App\Models\VersionControl;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class VersionController extends Controller
{
    public function index()
    {
        return Inertia::render('Version/VersionControl');
    }

    public function getVersionHistory(Request $request)
    {
        $userId = Auth::id();
    
        // Step 1: Get distinct licenses that the user redeemed
        $redeemedLicenseSlugs = Code::where('user_id', $userId)
            ->where('status', 'redeemed')
            ->distinct()
            ->pluck('license_name');
    
        // Step 2: Get those licenses along with all their versions
        $versionHistory = SettingLicense::whereIn('slug', $redeemedLicenseSlugs)
            ->with(['versionControls' => function ($query) {
                $query->select('id', 'setting_license_id', 'version', 'remarks', 'download_url')
                      ->latest('id');
            }])
            ->select('id', 'name', 'shortform')
            ->get();
    
        return response()->json([
            'success' => true,
            'data' => $versionHistory,
        ]);
    }
}
