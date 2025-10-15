<?php

namespace App\Http\Controllers;

use App\Models\Feed;
use App\Models\UpworkCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FeedController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $feeds = auth()->user()->feeds()->latest()->get();

        return Inertia::render('Feeds/Index', [
            'feeds' => $feeds,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Feeds/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'search_query' => 'array',
        ]);

        $feed = Feed::create([
            'user_id' => auth()->id(),
            'name' => $validated['name'],
            'search_query' => $validated['search_query'] ?? [],
            'is_active' => true,
        ]);

        return redirect()->route('feeds.index')->with('success', 'Feed created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $feed = Feed::where('id', $id)
                   ->where('user_id', auth()->id())
                   ->firstOrFail()
                   ->only(['id', 'name', 'search_query']);
        return Inertia::render('Feeds/Edit', [
            'feed' => $feed,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'search_query' => 'array',
        ]);

        $feed = Feed::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $feed->update([
            'name' => $validated['name'],
            'search_query' => $validated['search_query'] ?? [],
        ]);

        return redirect()->route('feeds.index')->with('success', 'Feed updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $feed = Feed::where('id', $id)
                   ->where('user_id', auth()->id())
                   ->firstOrFail();

        $feed->delete();

        return redirect()->route('feeds.index')->with('success', 'Feed deleted successfully!');
    }

    /**
     * Toggle feed active status.
     */
    public function toggle(string $id)
    {
        $feed = Feed::where('id', $id)
                   ->where('user_id', auth()->id())
                   ->firstOrFail();

        $feed->update(['is_active' => !$feed->is_active]);

        $status = $feed->is_active ? 'resumed' : 'paused';
        return back()->with('success', "Feed {$status} successfully!");
    }
}
