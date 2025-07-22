<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\SettingLicense;
use Illuminate\Support\Facades\Auth;
use App\Models\RedemptionCodeRequest;
use Illuminate\Support\Facades\Validator;

class RedemptionController extends Controller
{
    public function index()
    {
        $rawProducts = SettingLicense::select('id', 'name')->get();
        $products = [];
        
        foreach ($rawProducts as $product) {
            $products[] = [
                'label' => $product->name,
                'value' => $product->id,
            ];
        }
        
        return Inertia::render('RedemptionCodeRequest', [
            'products' => $products,
        ]);
    }

    public function getLicenses()
    {
        $licenses = SettingLicense::select('id', 'name')->get();

        $formatted = [];

        foreach ($licenses as $license) {
            $formatted[] = [
                'label' => $license->name,
                'value' => $license->id,
            ];
        }

        return response()->json($formatted);
    }

    public function requestRedemptionCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'meta_login' => 'required|string',
            'product_ids' => 'required|array',
            'product_ids.*' => 'required|integer|exists:setting_licenses,id',
            ])->setAttributeNames([
            'name' => trans('public.name'),
            'meta_login' => trans('public.meta_login'),
            'product_ids' => trans('public.products'),
        ]);
        $validator->validate();

        $record = RedemptionCodeRequest::create([
            'user_id' => Auth::id(),
            'name' => $request->input('name'),
            'meta_login' => $request->input('meta_login'),
        ]);
    
        foreach ($request->input('product_ids') as $licenseId) {
            $record->items()->create([
                'setting_license_id' => $licenseId,
            ]);
        }
    
        return back()->with('toast', [
            'title' => trans('public.toast_redemption_code_request_success'),
            'type' => 'success',
        ]);
    }
}
