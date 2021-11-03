<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Entry;
use App\Services\EntryService;
use App\Services\ImageService;

class EntryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Entry::query();

        if ($request->exists('search'))
        {
            $query->where('place', 'LIKE', '%' . $request->input('search') . '%');
        }

        $entries = $query
                    ->orderBy('updated_at', 'DESC')
                    ->paginate(env('PAG'));

        return view('entry.index', ['entries' => $entries]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('entry.create', ['entry' => null]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, EntryService $entryService)
    {
        $entryService->create($request);

        \Session::flash('success', 'Entry Created');

        return redirect('entry');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $entry = Entry::findOrFail($id);

        return view('entry.show', ['entry' => $entry]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $entry = Entry::findOrFail($id);

        return view('entry.edit', ['entry' => $entry]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, EntryService $entryService, $id)
    {
        $entryService->update($request, $id);
        
        \Session::flash('success', 'Entry Updated');

        return redirect('entry');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(ImageService $imageService, $id)
    {
        $entry = Entry::findOrFail($id);

        $imageService->deleteImage($entry->img_url);

        $entry->delete();

        \Session::flash('success', 'Entry Deleted');

        return redirect('entry');
    }
}
