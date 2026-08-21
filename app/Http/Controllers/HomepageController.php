<?php

namespace App\Http\Controllers;

use App\Models\HomepageSetting;
use App\Support\ChessNotation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class HomepageController extends Controller
{
    public function edit(): View
    {
        return view('members.admin.homepage.edit', [
            'settings' => HomepageSetting::current(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'hero_fen' => ['required', 'string', 'max:255'],
            'hero_orientation' => ['required', Rule::in(['white', 'black'])],
            'hero_caption' => ['nullable', 'string', 'max:180'],
        ]);

        if ($error = ChessNotation::fenError($data['hero_fen'])) {
            throw ValidationException::withMessages(['hero_fen' => $error]);
        }

        $data['hero_fen'] = ChessNotation::normaliseFen($data['hero_fen']);
        $data['hero_caption'] = trim($data['hero_caption'] ?? '') ?: null;

        HomepageSetting::query()->updateOrCreate(['id' => 1], $data);

        return redirect()->route('members.homepage.edit')
            ->with('status', 'Homepage position saved.');
    }

    public function reset(): RedirectResponse
    {
        HomepageSetting::query()->updateOrCreate(['id' => 1], HomepageSetting::defaults());

        return redirect()->route('members.homepage.edit')
            ->with('status', 'Homepage position restored to the Italian Game.');
    }
}
