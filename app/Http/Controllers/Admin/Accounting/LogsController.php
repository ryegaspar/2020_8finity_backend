<?php

namespace App\Http\Controllers\Admin\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaginatedLogCollection;
use App\Models\Log;
use Illuminate\Http\Request;

class LogsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index(Request $request)
    {
        $logs = Log::with('admin')
            ->tableFilter($request)
            ->paginate($request->per_page);

        return response()->json(new PaginatedLogCollection($logs));
    }
}
