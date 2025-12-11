<?php

namespace App\Http\Controllers;

use App\Models\CorporateOffice;
use Illuminate\Http\Request;

class CorporateOfficeController extends Controller
{
    public function index() {
        $page_title = "Corporate Office";
        $stores = CorporateOffice::all();

        return view('corporate-office.index', compact('page_title', 'stores'));
    }

    public function store(Request $request) {

        $request->validate([
            'name' => 'required|unique:corporate_offices,name',
            'address1' => 'required',
            'address2' => 'required',
            'block' => 'required',
            'street' => 'required',
            'location_url' => 'required',
            'map_latitude' => 'required',
            'map_longitude' => 'required',
            'mobile_type' => 'required',
            'whatsapp_type' => 'required',
            'open_time' => 'required',
            'close_time' => 'required'
        ]);

        CorporateOffice::create([
            'name' => $request->name,
            'address1' => $request->address1,
            'address2' => $request->address2,
            'block' => $request->block,
            'street' => $request->street,
            'landmark' => $request->landmark,
            'mobile' => $request->mobile_type,
            'whatsapp' => $request->whatsapp_type,
            'location' => $request->location,
            'open_time' => $request->open_time,
            'close_time' => $request->close_time,
            'latitude' => $request->map_latitude,
            'longitude' => $request->map_longitude,
            'location_url' => $request->location_url,
            'map_latitude' => $request->map_latitude,
            'map_longitude' => $request->map_longitude
        ]);

        return redirect()->route('corporate-office.index')->with('success', 'Corporate Office created successfully');
    }

    public function edit(Request $request, $id) {
        $page_title = "Corporate Office";
        $store = CorporateOffice::find($id);

        return view('corporate-office.edit', compact('page_title', 'store'));
    }

    public function update(Request $request, $stores) {

        $request->validate([
            'name' => "required|unique:corporate_offices,name,{$stores}",
            'address1' => 'required',
            'address2' => 'required',
            'block' => 'required',
            'street' => 'required',
            'location_url' => 'required',
            'map_latitude' => 'required',
            'map_longitude' => 'required',
            'mobile_type' => 'required',
            'whatsapp_type' => 'required',
            'open_time' => 'required',
            'close_time' => 'required'
        ]);

        CorporateOffice::where('id', $stores)->update([
            'name' => $request->name,
            'address1' => $request->address1,
            'address2' => $request->address2,
            'block' => $request->block,
            'street' => $request->street,
            'landmark' => $request->landmark,
            'mobile' => $request->mobile_type,
            'whatsapp' => $request->whatsapp_type,
            'location' => $request->location,
            'open_time' => $request->open_time,
            'close_time' => $request->close_time,
            'latitude' => $request->map_latitude,
            'longitude' => $request->map_longitude,
            'location_url' => $request->location_url,
            'map_latitude' => $request->map_latitude,
            'map_longitude' => $request->map_longitude
        ]);

        return redirect()->route('corporate-office.index')->with('success', 'Office updated successfully');
    }

    public function destroy(CorporateOffice $stores, $id) {
        $stores = CorporateOffice::find($id);

        if (!is_null($stores->employees)) {
            return redirect()->route('corporate-office.index')->with('error', 'You can\'t delete office because it has employees.');
        }

        $stores->delete();
        return redirect()->route('corporate-office.index')->with('success', 'Office deleted successfully');        
    }

    public function select2List(Request $request) {
        $queryString = trim($request->searchQuery);
        $page = $request->input('page', 1);
        $limit = 10;
    
        $query = CorporateOffice::query();
    
        if (!empty($queryString)) {
            $query->where('name', 'LIKE', "%{$queryString}%");
        }
    
        $data = $query->paginate($limit, ['*'], 'page', $page);
    
        return response()->json([
            'items' => $data->map(function ($item) {
                return [
                    'id' => $item->id,
                    'text' => $item->name
                ];
            }),
            'pagination' => [
                'more' => $data->hasMorePages()
            ]
        ]);
    }
}