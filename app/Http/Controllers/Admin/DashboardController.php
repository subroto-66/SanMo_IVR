<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IvrCallLog;
use App\Models\IvrOption;
use App\Models\IvrSetting;
use Carbon\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the main admin dashboard with statistics and recent activity.
     */
    public function index(): View
    {
        $today = Carbon::today();

        $totalOptions = IvrOption::count();
        $activeOptions = IvrOption::active()->count();

        // Count configured audio files (options with audio + system settings with audio)
        $optionsWithAudio = IvrOption::whereNotNull('audio_path')->count();
        $systemAudioCount = IvrSetting::where('key', 'like', '%_audio_path')
            ->whereNotNull('value')
            ->where('value', '!=', '')
            ->count();
        $totalAudioFiles = $optionsWithAudio + $systemAudioCount;

        $totalCalls = IvrCallLog::count();
        $todayCalls = IvrCallLog::whereDate('created_at', $today)->count();

        // SMS Count from logs
        $smsSentCount = IvrCallLog::where('metadata', 'like', '%"sms_sent"%')->count();

        // FlowCore Personal Contact Requests Count
        $contactRequestsCount = IvrCallLog::where(function ($q) {
            $q->where('selected_option', 'like', '%Contact Request%')
              ->orWhere('metadata', 'like', '%contact_requested%');
        })->count();

        $recentCalls = IvrCallLog::latest()->take(10)->get();

        $activeOptionsList = IvrOption::active()->ordered()->get();

        return view('pages.admin.dashboard', [
            'title' => 'IVR System Dashboard',
            'totalOptions' => $totalOptions,
            'activeOptions' => $activeOptions,
            'totalAudioFiles' => $totalAudioFiles,
            'totalCalls' => $totalCalls,
            'todayCalls' => $todayCalls,
            'smsSentCount' => $smsSentCount,
            'contactRequestsCount' => $contactRequestsCount,
            'recentCalls' => $recentCalls,
            'activeOptionsList' => $activeOptionsList,
        ]);
    }
}
