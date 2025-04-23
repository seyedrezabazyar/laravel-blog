<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Publisher;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class PublisherController extends Controller
{
    public function index()
    {
        $publishers = Publisher::withCount('posts')->paginate(10);
        return view('admin.publishers.index', compact('publishers'));
    }

    public function create()
    {
        return view('admin.publishers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|max:255',
            'description' => 'nullable',
            'logo' => 'nullable|image|max:2048',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('publishers', 'public');
            $validated['logo'] = $path;
        }

        Publisher::create($validated);

        return redirect()->route('admin.publishers.index')
            ->with('success', 'ناشر با موفقیت ایجاد شد.');
    }

    public function show(Publisher $publisher)
    {
        $publisher->load('posts');
        return view('admin.publishers.show', compact('publisher'));
    }

    public function edit(Publisher $publisher)
    {
        return view('admin.publishers.edit', compact('publisher'));
    }

    public function update(Request $request, Publisher $publisher)
    {
        $validated = $request->validate([
            'name' => 'required|max:255',
            'description' => 'nullable',
            'logo' => 'nullable|image|max:2048',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        if ($request->hasFile('logo')) {
            if ($publisher->logo) {
                Storage::disk('public')->delete($publisher->logo);
            }

            $path = $request->file('logo')->store('publishers', 'public');
            $validated['logo'] = $path;
        }

        $publisher->update($validated);

        return redirect()->route('admin.publishers.index')
            ->with('success', 'ناشر با موفقیت به‌روزرسانی شد.');
    }

    public function destroy(Publisher $publisher)
    {
        // بررسی اینکه آیا ناشر دارای کتاب است
        if ($publisher->posts()->count() > 0) {
            return redirect()->route('admin.publishers.index')
                ->with('error', 'این ناشر دارای کتاب است و نمی‌توان آن را حذف کرد.');
        }

        if ($publisher->logo) {
            Storage::disk('public')->delete($publisher->logo);
        }

        $publisher->delete();

        return redirect()->route('admin.publishers.index')
            ->with('success', 'ناشر با موفقیت حذف شد.');
    }
}
