<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Entry;
use App\Models\User;
use App\Services\EntryService;
use App\Services\ImageService;
use Session;
use App\Notifications\NewEntry;
use Notification;

class EntryController extends Controller
{
    private $feature = 'entry';

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

        return view('entry.index', ['entries' => $entries, 'feature' => $this->feature]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('entry.create', ['entry' => null, 'feature' => $this->feature]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, EntryService $entryService)
    {
        $entry = $entryService->create($request);

        Notification::send(
            User::all(), 
            new NewEntry([
                'entry_url' => url("/$this->feature/" . $entry->id), 
                'name'      => $entry->place,
            ])
        );

        Session::flash('success', 'Entry Created');

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

        return view('entry.show', ['entry' => $entry, 'feature' => $this->feature]);
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

        return view('entry.edit', ['entry' => $entry, 'feature' => $this->feature]);
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
        
        Session::flash('success', 'Entry Updated');

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

        Session::flash('success', 'Entry Deleted');

        return redirect('entry');
    }
}
