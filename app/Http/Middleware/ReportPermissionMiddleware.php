<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\Permission\Middleware\PermissionMiddleware;

class ReportPermissionMiddleware
{
    public function __construct(private PermissionMiddleware $permissionMiddleware) {}

    public function handle(Request $request, Closure $next): mixed
    {
        $report = $request->route('id');

        return $this->permissionMiddleware->handle($request, $next, "reports.{$report}.view");
    }
}
