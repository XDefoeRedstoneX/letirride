<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function index()
    {
        $grouped = Faq::orderBy('category')->orderBy('sort_order')->orderBy('id')
            ->get()
            ->groupBy('category');

        return view('admin.faqs', compact('grouped'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category'   => 'required|string|max:100',
            'question'   => 'required|string|max:500',
            'answer'     => 'required|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_active'  => 'nullable|boolean',
        ]);

        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_active']  = $request->boolean('is_active', true);

        Faq::create($data);

        return back()->with('success', 'FAQ added.');
    }

    public function update(Request $request, Faq $faq)
    {
        $data = $request->validate([
            'category'   => 'required|string|max:100',
            'question'   => 'required|string|max:500',
            'answer'     => 'required|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_active'  => 'nullable|boolean',
        ]);

        $data['sort_order'] = $data['sort_order'] ?? $faq->sort_order;
        $data['is_active']  = $request->boolean('is_active', false);

        $faq->update($data);

        return back()->with('success', 'FAQ updated.');
    }

    public function destroy(Faq $faq)
    {
        $faq->delete();

        return back()->with('success', 'FAQ deleted.');
    }
}
