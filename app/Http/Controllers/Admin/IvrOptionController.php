<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IvrOptionRequest;
use App\Models\IvrOption;
use App\Services\AudioStorageService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IvrOptionController extends Controller
{
    protected AudioStorageService $audioStorageService;

    public function __construct(AudioStorageService $audioStorageService)
    {
        $this->audioStorageService = $audioStorageService;
    }

    /**
     * Display a listing of IVR options.
     */
    public function index(): View
    {
        $options = IvrOption::ordered()->get();

        return view('pages.admin.options.index', [
            'title' => 'IVR Keypad Options',
            'options' => $options,
        ]);
    }

    /**
     * Show form for creating a new IVR option.
     */
    public function create(): View
    {
        return view('pages.admin.options.create', [
            'title' => 'Create IVR Option',
            'option' => new IvrOption(['is_active' => true, 'action_type' => 'audio', 'sort_order' => 0]),
        ]);
    }

    /**
     * Store a newly created IVR option.
     */
    public function store(IvrOptionRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active', true);
        $data['sms_enabled'] = $request->boolean('sms_enabled', false);

        if ($request->hasFile('audio_file')) {
            try {
                $upload = $this->audioStorageService->store($request->file('audio_file'), 'audio/options');
                $data['audio_path'] = $upload['path'];
                $data['audio_url'] = $upload['url'];
            } catch (Exception $e) {
                return back()->withInput()->with('error', 'Audio upload failed: ' . $e->getMessage());
            }
        }

        IvrOption::create($data);

        return redirect()->route('admin.ivr.options.index')
            ->with('success', 'IVR option created successfully.');
    }

    /**
     * Show the form for editing an IVR option.
     */
    public function edit(IvrOption $option): View
    {
        return view('pages.admin.options.edit', [
            'title' => 'Edit IVR Option ' . $option->digit,
            'option' => $option,
        ]);
    }

    /**
     * Update an IVR option.
     */
    public function update(IvrOptionRequest $request, IvrOption $option): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active', false);
        $data['sms_enabled'] = $request->boolean('sms_enabled', false);

        $oldPaths = array_filter([$option->audio_path, $option->audio_url]);

        // Check if user requested to remove existing audio without uploading new
        if ($request->boolean('remove_audio_file', false)) {
            if (!empty($oldPaths)) {
                $this->audioStorageService->delete($oldPaths);
            }
            $data['audio_path'] = null;
            $data['audio_url'] = null;
        } elseif ($request->hasFile('audio_file')) {
            try {
                $upload = $this->audioStorageService->replace(
                    $request->file('audio_file'),
                    $oldPaths,
                    'audio/options'
                );
                $data['audio_path'] = $upload['path'];
                $data['audio_url'] = $upload['url'];
            } catch (Exception $e) {
                return back()->withInput()->with('error', 'Audio replacement failed: ' . $e->getMessage());
            }
        }

        $option->update($data);

        return redirect()->route('admin.ivr.options.index')
            ->with('success', "IVR option {$option->digit} updated successfully.");
    }

    /**
     * Remove an IVR option and its audio file.
     */
    public function destroy(IvrOption $option): RedirectResponse
    {
        $oldPaths = array_filter([$option->audio_path, $option->audio_url]);
        if (!empty($oldPaths)) {
            $this->audioStorageService->delete($oldPaths);
        }

        $digit = $option->digit;
        $option->delete();

        return redirect()->route('admin.ivr.options.index')
            ->with('success', "IVR option {$digit} deleted successfully.");
    }

    /**
     * Toggle active status of an option.
     */
    public function toggle(IvrOption $option): RedirectResponse
    {
        $newStatus = !$option->is_active;

        // If activating, verify no other active option has the same digit
        if ($newStatus) {
            $conflict = IvrOption::where('digit', $option->digit)
                ->where('is_active', true)
                ->where('id', '!=', $option->id)
                ->exists();

            if ($conflict) {
                return back()->with('error', "Cannot activate option: Digit '{$option->digit}' is already used by another active option.");
            }
        }

        $option->update(['is_active' => $newStatus]);

        $statusText = $newStatus ? 'enabled' : 'disabled';
        return back()->with('success', "IVR option {$option->digit} {$statusText}.");
    }
}
