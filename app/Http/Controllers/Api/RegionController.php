<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Region;
use Illuminate\Support\Facades\DB;

class RegionController extends Controller
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
            $regions = Region::all();

            $linearData = [];

            foreach ($regions as $region) 
            {
                $region->text      = $region->name;
                $region->value     = $region->name;
                $region->opened    = true;
                $region->icon      = "";
                $region->selected  = false;
                $region->disabled  = false;
                $region->loading   = false;
                $region->children  = [];
                $region->region_id = $region->id;
                $region->parent_id = $region->regionTree ? $region->regionTree->parent_id : 0;

                $linearData[] = (object) $region->getAttributes();
            }

            $response = $this->prepareTree($linearData);

            return response()->json($response, 200);
        }
        catch (Throwable $t)
        {
            return response()->json(["error" => $t->getMessage()] , 400);
        }
    }

    protected function prepareTree($data)
    {
        $parentId = array_column($data, 'parent_id');
        array_multisort($parentId, SORT_DESC, $data); // @todo This can sometimes have bugs. Parent IDs shouldn't always be sorted descendingly.

        foreach ($data as $key => $item)
        {
            if ($item->parent_id !== 0)
            {
                foreach ($data as $key1 => $item1) 
                {
                    if ($item->parent_id === $item1->region_id)
                    {
                        $data[$key1]->children[] = $item;
                    }
                }

                unset($data[$key]);
            }
        }

        $data = array_values($data);

        return $data;
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
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function rearrange(Request $request)
    {
        $inputs = $request->all();

        DB::table('region_tree')->truncate();
        DB::table('region_tree')->insert($inputs);

        $oldRegions = Region::pluck('id')->all();
        $newRegions = array_keys( array_column($inputs, null, 'region_id') );
        $toDelete   = array_diff($oldRegions, $newRegions);

        Region::whereIn('id', $toDelete)->delete();
    }
}
