<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Code;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\SettingLicense;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Models\RedemptionCodeRequest;
use App\Mail\RedemptionCodeRequestMail;
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
        
        return Inertia::render('Redemption/RedemptionCodeRequest/RedemptionCodeRequest', [
            'products' => $products,
        ]);
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
    
        // ✅ Send the plain text email
        Mail::to('supporteam@simmigoh.info')->send(new RedemptionCodeRequestMail( $record));
    
        return back()->with('toast', [
            'title' => trans('public.toast_redemption_code_request_success'),
            'type' => 'success',
        ]);
    }

    public function getRedemptionCodeRequest(Request $request)
    {
        if ($request->has('lazyEvent')) {
            $data = json_decode($request->only(['lazyEvent'])['lazyEvent'], true); //only() extract parameters in lazyEvent

            $query = RedemptionCodeRequest::query()
                ->with('items.product:id,name')
                ->where('user_id', Auth::id());

            // Handle search functionality
            $search = $data['filters']['global']['value'];
            if ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('meta_login', 'like', '%' . $search . '%');
                });
            }

            $startDate = $data['filters']['start_date']['value'] ?? null;
            $endDate = $data['filters']['end_date']['value'] ?? null;

            // Handle Date
            if ($startDate && $endDate) {
                $start_date = Carbon::parse($startDate)->startOfDay();
                $end_date = Carbon::parse($endDate)->endOfDay();
                
                $query->whereBetween('created_at', [$start_date, $end_date]);
            }

            // Handle status
            $status = $data['filters']['status']['value'] ?? null;
            if ($status) {
                $query->where(function ($query) use ($status) {
                    $query->where('status', $status);
                });
            }

            // Handle sorting
            if ($data['sortField'] && $data['sortOrder']) {
                $order = $data['sortOrder'] == 1 ? 'asc' : 'desc';
                $query->orderBy($data['sortField'], $order);
            } else {
                $query->orderByDesc('created_at');
            }

            // Handle pagination
            $rowsPerPage = $data['rows'] ?? 15; // Default to 15 if 'rows' not provided

            $result  = $query->paginate($rowsPerPage);
            $items = $result->items();

            foreach ($items as $item) {
                $products = [];

                foreach ($item->items as $subItem) {
                    if ($subItem->product) {
                        $products[] = [
                            'value'   => $subItem->product->id,
                            'label' => $subItem->product->name,
                        ];
                    }
                }

                $item->products = $products;
                unset($item->items); // remove nested relation to save memory
            }
        }
        
        return response()->json([
            'success' => true,
            'data' => $result ,
        ]);

    }

    public function getRedemptionCodes(Request $request)
    {
        $request_id = $request->input('request_id');
        $name = $request->input('name');
        $metaLogin = $request->input('meta_login');

        $codes = Code::query()
            ->where('user_id', Auth::id())
            ->where('redemption_code_request_id', $request_id)
            ->where('acc_name', $name)
            ->where('meta_login', $metaLogin)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $codes,
        ]);
    }

    public function redemptionCodeListing()
    {
        return Inertia::render('Redemption/RedemptionCodeListing/RedemptionCodeListing');
    }

    public function getRedemptionCodeListing(Request $request)
    {
        if ($request->has('lazyEvent')) {
            $data = json_decode($request->only(['lazyEvent'])['lazyEvent'], true);
    
            $query = Code::with(['license:slug,url'])
                ->where('user_id', Auth::id());
                    
            // Handle search
            $search = $data['filters']['global']['value'] ?? null;
            if ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('meta_login', 'like', '%' . $search . '%')
                          ->orWhere('acc_name', 'like', '%' . $search . '%')
                          ->orWhere('product_name', 'like', '%' . $search . '%')
                          ->orWhere('serial_number', 'like', '%' . $search . '%');
                });
            }

            $startDate = $data['filters']['start_date']['value'] ?? null;
            $endDate = $data['filters']['end_date']['value'] ?? null;

            // Handle Date
            if ($startDate && $endDate) {
                $start_date = Carbon::parse($startDate)->startOfDay();
                $end_date = Carbon::parse($endDate)->endOfDay();
                
                $query->whereBetween('created_at', [$start_date, $end_date]);
            }

            // Handle status
            $status = $data['filters']['status']['value'] ?? null;
            if ($status) {
                $query->where(function ($query) use ($status) {
                    $query->where('status', $status);
                });
            }
            
            // Handle sorting
            if (!empty($data['sortField']) && !empty($data['sortOrder'])) {
                $order = $data['sortOrder'] == 1 ? 'asc' : 'desc';
                $query->orderBy($data['sortField'], $order);
            } else {
                $query->orderByDesc('created_at');
            }
    
            // Handle pagination
            $rowsPerPage = $data['rows'] ?? 15;
            $result = $query->paginate($rowsPerPage);

            foreach ($result as $code) {
                $code->indicator = $code->license->url ?? null;

                unset($code->license);
            }            
        }
    
        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

}
