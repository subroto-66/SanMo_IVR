<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IvrCallLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CallLogController extends Controller
{
    /**
     * Display a listing of call logs with search and filter capabilities.
     */
    public function index(Request $request): View
    {
        $query = IvrCallLog::query()->latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('call_sid', 'like', "%{$search}%")
                  ->orWhere('from_number', 'like', "%{$search}%")
                  ->orWhere('to_number', 'like', "%{$search}%")
                  ->orWhere('selected_option', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status === 'contact') {
                $query->where(function ($q) {
                    $q->where('selected_option', 'like', '%Contact Request%')
                      ->orWhere('metadata', 'like', '%contact_requested%');
                });
            } else {
                $query->where('status', $status);
            }
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->input('date'));
        }

        $callLogs = $query->paginate(20)->withQueryString();

        return view('pages.admin.logs.index', [
            'title' => 'Call History & Logs',
            'callLogs' => $callLogs,
            'search' => $request->input('search'),
            'status' => $request->input('status'),
            'date' => $request->input('date'),
        ]);
    }

    /**
     * Display call log details including audit metadata trail.
     */
    public function show(IvrCallLog $callLog): View
    {
        return view('pages.admin.logs.show', [
            'title' => 'Call Details: ' . $callLog->call_sid,
            'callLog' => $callLog,
        ]);
    }
}
