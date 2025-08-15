<?php

namespace App\Http\Controllers;

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
        $versionHistory = SettingLicense::whereHas('versionControls')
            ->with([
                'versionControls' => function ($query) {
                    $query->select('id', 'setting_license_id', 'version', 'remarks', 'download_url')
                        ->latest('id');
                }
            ])
            ->select('id', 'name', 'shortform')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $versionHistory,
        ]);
    }
}
