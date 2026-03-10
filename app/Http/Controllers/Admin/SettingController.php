<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SettingRequest;
use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class SettingController extends Controller
{
    public function __construct(
        private readonly SettingService $settingService
    ) {
    }

    public function edit(): Response
    {
        $setting = $this->settingService->get();

        return Inertia::render('Admin/Settings/Edit', [
            'setting' => $setting,
        ]);
    }

    public function update(SettingRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('logo')) {
            $setting = $this->settingService->get();

            // Delete old logo if exists
            if ($setting->logo) {
                Storage::disk('public')->delete($setting->logo);
            }

            $data['logo'] = $request->file('logo')->store('logos', 'public');
        }

        $this->settingService->update($data);

        return redirect()->back()->with('success', 'Pengaturan berhasil diperbarui.');
    }
}
