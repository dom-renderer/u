<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use App\Models\Setting;
use Closure;

class CheckMaintenanceMode
{
    public function handle(Request $request, Closure $next)
    {
        $maintenanceMode = Setting::first()->maintenance_mode ?? 0;

        if ($maintenanceMode == 1) {
            return response()->json([
                'status' => true,
                'message' => 'Application is in maintenance mode',
            ], 503);
        }

        return $next($request);
    }
}
