<?php

namespace App\Http\Controllers\Web\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Traits\AdminApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class MaintenanceSettingsController extends Controller
{
    use AdminApiResponse;

    /*
    |--------------------------------------------------------------------------
    | View maintenance page
    |--------------------------------------------------------------------------
    */
    public function maintenance(): View
    {
        return view('web.admin.settings.maintenance',['s' => Setting::first()]);
    }

    /*
    |--------------------------------------------------------------------------
    | PATCH /admin/settings/maintenance
    |--------------------------------------------------------------------------
    */
    public function updateMaintenance(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'maintenance_mode'        => ['required', 'boolean'],
            'maintenance_message'     => ['nullable', 'string', 'max:500'],
            'maintenance_allowed_ips' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        // Validate each IP in the comma-separated list
        if ($request->maintenance_allowed_ips) {
            $ips = array_map('trim', explode(',', $request->maintenance_allowed_ips));
            foreach ($ips as $ip) {
                if ($ip && ! filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $this->error("Invalid IP address: {$ip}", [
                        'maintenance_allowed_ips' => ["\"$ip\" is not a valid IP address."],
                    ], 422);
                }
            }
        }

        Setting::first()->update([
            'maintenance_mode'        => (bool) $request->maintenance_mode,
            'maintenance_message'     => $request->maintenance_message,
            'maintenance_allowed_ips' => $request->maintenance_allowed_ips,
        ]);

        return $this->success('Maintenance settings updated successfully.');
    }
}
