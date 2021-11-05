<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Region;
use Illuminate\Support\Facades\DB;
use Session;

class RegionController extends Controller
{
    private $feature = 'region';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $regions = Region::all();
        $data    = ['regions' => $regions, 'feature' => $this->feature];

        return view('region.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data = ['region' => null, 'feature' => $this->feature];

        return view('region.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $region       = new Region;
        $region->name = $request->input('name');
        $region->save();

        DB::table('region_tree')->insert(['region_id' => $region->id, 'parent_id' => 0]);

        Session::flash('success', 'Region Created');

        return redirect('region');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $region = Region::findOrFail($id);
        $data   = ['region' => $region, 'feature' => $this->feature];

        return view('region.show', $data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $region = Region::findOrFail($id);
        $data   = ['region' => $region, 'feature' => $this->feature];

        return view('region.edit', $data);
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
        $region = Region::findOrFail($id);

        $region->update([
            'name' => $request->input('name'),
        ]);

        Session::flash('success', 'Region Updated');

        return redirect('region');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $region = Region::findOrFail($id);

        $region->delete();

        Session::flash('success', 'Region Deleted');

        return redirect('region');
    }
}
