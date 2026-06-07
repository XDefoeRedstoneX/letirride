<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function index()
    {
        $news = News::orderByDesc('created_at')->paginate(20);

        return view('admin.news', compact('news'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'image'     => 'required|image|mimes:jpg,jpeg,png,gif,webp|max:4096',
            'is_active' => 'nullable|boolean',
        ]);

        $file     = $request->file('image');
        $filename = uniqid('news_') . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('news'), $filename);

        News::create([
            'name'      => $data['name'],
            'image'     => 'news/' . $filename,
            'is_active' => isset($data['is_active']),
        ]);

        return back()->with('success', 'News item added.');
    }

    public function update(Request $request, News $news)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'image'     => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:4096',
            'is_active' => 'nullable|boolean',
        ]);

        $fields = [
            'name'      => $data['name'],
            'is_active' => isset($data['is_active']),
        ];

        if ($request->hasFile('image')) {
            $file     = $request->file('image');
            $filename = uniqid('news_') . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('news'), $filename);
            $fields['image'] = 'news/' . $filename;
        }

        $news->update($fields);

        return back()->with('success', 'News item updated.');
    }

    public function toggleActive(News $news)
    {
        $news->update(['is_active' => ! $news->is_active]);

        return back()->with('success', $news->is_active ? 'News item set to Active.' : 'News item set to Hidden.');
    }

    public function destroy(News $news)
    {
        if (file_exists(public_path($news->image))) {
            @unlink(public_path($news->image));
        }
        
        $news->delete();

        return back()->with('success', 'News item deleted.');
    }
}
