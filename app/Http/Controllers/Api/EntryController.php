<?php

namespace App\Http\Controllers\Api;

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
    public function index()
    {
        try 
        {
            $entries = Entry::orderBy('updated_at', 'DESC')->paginate(env('PAG'));

            return response()->json([
                'data' => $entries
            ], 200);
        }
        catch (\Exception $e)
        {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, EntryService $entryService)
    {
        try 
        {
            $entry = $entryService->create($request);

            return response()->json([
                'data' => $entry
            ], 200);
        }
        catch (\Exception $e)
        {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try 
        {
            $entry = Entry::findOrFail($id);

            return response()->json([
                'data' => $entry
            ], 200);
        }
        catch (\Exception $e)
        {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        try 
        {
            $entry = $entryService->update($request, $id);
            
            return response()->json([
                'data' => $entry
            ], 200);
        }
        catch (\Exception $e)
        {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(ImageService $imageService, $id)
    {
        try 
        {
            $entry = Entry::findOrFail($id);

            $imageService->deleteImage($entry->img_url);

            $entry->delete();

            return response()->json([
                'data' => $entry
            ], 200);
        }
        catch (\Exception $e)
        {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}
