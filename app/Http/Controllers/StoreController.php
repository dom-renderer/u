<?php

namespace App\Http\Controllers;

use PhpOffice\PhpSpreadsheet\Shared\Date;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\LocationImport;
use Illuminate\Http\Request;
use App\Models\ModelType;
use App\Models\Designation;
use App\Models\Department;
use App\Models\StoreType;
use App\Helpers\Helper;
use App\Models\Store;
use App\Models\City;
use App\Models\UserStore;
use App\Models\StoreCategory;
use App\Models\User;

class StoreController extends Controller
{
    public function index() {
        $page_title = "Stores";
        $stores = Store::query()
        ->when(!empty(request('filter_location')), function ($builder) {
            $builder->where('code', request('filter_location'));
        })
        ->when(!empty(request('filter_name')) && request('filter_name') != 'all', function ($builder) {
            $builder->where('name', 'LIKE', '%' . request('filter_name') . '%');
        })
        ->when(!empty(request('filter_state')) && request('filter_state') != 'all', function ($builder) {
            $builder->whereHas('thecity', function ($innerBuilder) {
                $innerBuilder->where('city_state', request('filter_state'));
            });
        })
        ->when(!empty(request('filter_mt')) && request('filter_mt') != 'all', function ($builder) {
            $builder->whereHas('modeltype', function ($innerBuilder) {
                $innerBuilder->where('id', request('filter_mt'));
            });
        })
        ->when(!empty(request('filter_city')) && request('filter_city') != 'all', function ($builder) {
            $builder->whereHas('thecity', function ($innerBuilder) {
                $innerBuilder->where('city_id', request('filter_city'));
            });
        })
        ->when(!empty(request('filter_dom')) && request('filter_dom') != 'all', function ($builder) {
            $builder->where('dom_id', request('filter_dom'));
        })
        ->paginate(12)->withQueryString();
        $storeTypes = StoreType::all();
        $modelTypes = ModelType::all();
        $storeCategories = StoreCategory::all();

        $stateFilter = City::select('city_state')->where('city_state', request('filter_state'))->first();
        $cityFilter = City::select('city_name')->where('city_id', request('filter_city'))->first();
        $domFilter = User::whereHas('roles', function ($builder) {
            $builder->where('id', Helper::$roles['divisional-operations-manager']);
        })->select('employee_id', 'name', 'middle_name', 'last_name')->where('id', request('filter_dom'))->first();

        return view( 'stores.index', compact( 'page_title', 'stores', 'storeTypes', 'modelTypes', 'stateFilter', 'cityFilter', 'domFilter', 'storeCategories' ) );
    }

    public function store(Request $request) {

        $request->validate([
            'store_type' => 'required',
            'model_type' => 'required',
            'name' => 'required|unique:stores,name',
            'code' => 'required|unique:stores,code',
            // 'address1' => 'required',
            // 'address2' => 'required',
            // 'block' => 'required',
            // 'street' => 'required',
            // 'location_url' => 'required',
            // 'map_latitude' => 'required',
            // 'map_longitude' => 'required',
            // 'mobile_type' => 'required',
            // 'whatsapp_type' => 'required',
            'open_time' => 'required',
            'close_time' => 'required',
            'city' => 'required',
            'dom_id' => 'required'
        ]);

        Store::create([
            'store_type' => $request->store_type,
            'model_type' => $request->model_type,
            'store_category' => !empty($request->store_category) ? $request->store_category : null,
            'email' => $request->email,
            'name' => $request->name,
            'code' => $request->code,
            'address1' => is_null($request->address1) ? '' : $request->address1,
            'address2' => $request->address2,
            'block' => $request->block,
            'street' => $request->street,
            'landmark' => $request->landmark,
            'mobile' => $request->mobile_type,
            'whatsapp' => $request->whatsapp_type,
            'location' => $request->location,
            'open_time' => date("h:i A", strtotime($request->open_time)),
            'close_time' => date("h:i A", strtotime($request->close_time)),
            'ops_start_time' => date("h:i A", strtotime($request->ops_start_time)),
            'ops_end_time' => date("h:i A", strtotime($request->ops_end_time)),
            'latitude' => $request->map_latitude,
            'longitude' => $request->map_longitude,
            'location_url' => $request->location_url,
            'map_latitude' => $request->map_latitude,
            'map_longitude' => $request->map_longitude,
            'city' => $request->city,
            'dom_id' => $request->dom_id
        ]);

        return redirect()->route('stores.index')->with('success', 'Location created successfully');
    }

    public function edit(Request $request, $id) {
        $page_title = "Locations";
        $store = Store::find($id);
        $storeTypes = StoreType::all();
        $modelTypes = ModelType::all();
        $storeCategories = StoreCategory::all();

        return view( 'stores.edit', compact( 'page_title', 'store', 'storeTypes', 'modelTypes', 'storeCategories' ) );
    }

    public function update(Request $request, $stores) {

        $request->validate([
            'store_type' => "required",
            'model_type' => "required",
            'name' => "required|unique:stores,name,{$stores}",
            'code' => "required|unique:stores,code,{$stores}",
            // 'address1' => 'required',
            // 'address2' => 'required',
            // 'block' => 'required',
            // 'street' => 'required',
            // 'location_url' => 'required',
            // 'map_latitude' => 'required',
            // 'map_longitude' => 'required',
            // 'mobile_type' => 'required',
            // 'whatsapp_type' => 'required',
            'open_time' => 'required',
            'close_time' => 'required',
            'city' => 'required',
            'dom_id' => 'required'
        ]);

        Store::where('id', $stores)->update([
            'store_type' => $request->store_type,
            'model_type' => $request->model_type,
            'store_category' => !empty($request->store_category) ? $request->store_category : null,
            'email' => $request->email,
            'name' => $request->name,
            'code' => $request->code,
            'address1' => is_null($request->address1) ? '' : $request->address1,
            'address2' => $request->address2,
            'block' => $request->block,
            'street' => $request->street,
            'landmark' => $request->landmark,
            'mobile' => $request->mobile_type,
            'whatsapp' => $request->whatsapp_type,
            'location' => $request->location,
            'open_time' => date("h:i A", strtotime($request->open_time)),
            'close_time' => date("h:i A", strtotime($request->close_time)),
            'ops_start_time' => date("h:i A", strtotime($request->ops_start_time)),
            'ops_end_time' => date("h:i A", strtotime($request->ops_end_time)),
            'latitude' => $request->map_latitude,
            'longitude' => $request->map_longitude,
            'location_url' => $request->location_url,
            'map_latitude' => $request->map_latitude,
            'map_longitude' => $request->map_longitude,
            'city' => $request->city,
            'dom_id' => $request->dom_id
        ]);

        return redirect()->route('stores.index')->with('success', 'Location updated successfully');
    }

    public function destroy(Store $stores, $id) {
        $stores = Store::find($id);

        if (!is_null($stores->designations)) {
            return redirect()->route('stores.index')->with('error', 'You can\'t delete Location because it has employees.');
        }

        $stores->delete();
        return redirect()->route('stores.index')->with('success', 'Location deleted successfully');        
    }

    public function select2List(Request $request) {
        $queryString = trim($request->searchQuery);
        $page = $request->input('page', 1);
        $limit = 10;
        $getAll = $request->getall;
    
        $query = Store::query();
    
        if (!empty($queryString)) {
            $query->where('name', 'LIKE', "%{$queryString}%")
            ->orWhere('code', 'LIKE', "%{$queryString}%");
        }

        if ($request->strict_stores == 1 && auth()->check()) {
            $currentUserStore = UserStore::where('user_id', auth()->user()->id)->pluck('store_id')->toArray();
            if (!empty($currentUserStore) && !auth()->user()->isAdmin()) {
                $query->whereIn('id', $currentUserStore);
            }
        }

        if (!auth()->user()->isAdmin() && $request->strict_stores != 1) {
            $query->where('dom_id', auth()->user()->id);
        }

        if ($request->has('for_ticket') && $request->for_ticket == 1 && auth()->check()) {
            $getCurrentUserRoles = auth()->user()->roles[0]->id ?? 0;

            if ($getCurrentUserRoles == Helper::$roles['store-phone']) {
                $query->where('code', auth()->user()->employee_id);
            } else if ($getCurrentUserRoles == Helper::$roles['divisional-operations-manager']) {
                $query->where('dom_id', auth()->user()->id);
            }
        }

        $data = $query->paginate($limit, ['*'], 'page', $page);
        $response = $data->map(function ($item) {
            return [
                'id' => $item->id,
                'text' => "{$item->code} - $item->name"
            ];
        });
    
        if ($getAll && $page == 1 && auth()->user()->isAdmin()) {
            $response->push(['id' => 'all', 'text' => 'All']);
        }

        return response()->json([
            'items' => $response->reverse()->values(),
            'pagination' => [
                'more' => $data->hasMorePages()
            ]
        ]);
    }

    public function stateLists(Request $request) {
        $queryString = trim($request->searchQuery);
        $page = $request->input('page', 1);
        $limit = 10;
        $getAll = $request->getall;
    
        $query = City::query();
    
        if (!empty($queryString)) {
            $query->where('city_state', 'LIKE', "%{$queryString}%");
        }

        $query = $query->groupBy('city_state');
    
        $data = $query->paginate($limit, ['*'], 'page', $page);
        $response = $data->map(function ($item) {
            return [
                'id' => $item->city_state,
                'text' => $item->city_state
            ];
        });

        if ($getAll && $page == 1) {
            $response->push(['id' => 'all', 'text' => 'All']);
        }

        return response()->json([
            'items' => $response->reverse()->values(),
            'pagination' => [
                'more' => $data->hasMorePages()
            ]
        ]);
    }

    public function cityLists(Request $request) {
        $queryString = trim($request->searchQuery);
        $page = $request->input('page', 1);
        $state = $request->state;
        $limit = 10;
        $getAll = $request->getall;
    
        $query = City::query();
    
        if (!empty($queryString)) {
            $query->where('city_name', 'LIKE', "%{$queryString}%");
        }
    
        if (!empty($state)) {
            if ($state !== 'all') {
                $query->where('city_state', $state);
            }
        }

        $data = $query->paginate($limit, ['*'], 'page', $page);
        $response = $data->map(function ($item) {
            return [
                'id' => $item->city_id,
                'text' => $item->city_name
            ];
        });

        if ($getAll && $page == 1) {
            $response->push(['id' => 'all', 'text' => 'All']);
        }

        return response()->json([
            'items' => $response->reverse()->values(),
            'pagination' => [
                'more' => $data->hasMorePages()
            ]
        ]);
    }

    public function importStores(Request $request) {
        $file = $request->file('xlsx');
        $data = Excel::toArray(new LocationImport(),$file);
        $response = [];
        $successCount = $errorCount = 0;

        $expectedHeaders = [
            'STORE NAME',
            'TYPE',
            'CITY',
            'STATE',
            'DOM FIRST NAME',
            'DOM MIDDLE NAME',
            'DOM LAST NAME',
            'OPS MGR',
            'OPS HEAD',
            'MODEL',
            'DOM ID',
            'DOM MOBILE',
            'ADDRESS 1',
            'ADDRESS 2',
            'BLOCK',
            'STREET',
            'LANDMARK',
            'STORE MOBILE',
            'STORE WHATSAPP',
            'LATITUDE',
            'LONGITUDE',
            'LOCATION URL',
            'STORE OPENING TIME',
            'STORE CLOSING TIME',
            'OPERATION START TIME',
            'OPERATION END TIME',
            'STORE MAIL ID',
            'CATEGORY',
        ];

        \DB::beginTransaction();

        try {
            if (!empty($data) && isset($data[0])) {
                foreach ($data[0] as $key => $row) {
                    if ($key) {

                        $codeName = explode(' ', $row[0]);
                        $city = City::where(\DB::raw('LOWER(city_name)'), strtolower($row[2]))
                        ->where(\DB::raw('LOWER(city_state)'), strtolower($row[3]))
                        ->first();

                        if (isset($codeName[0]) && isset($codeName[1])) {

                            $toBeAdded = [];

                            if (!$city) {
                                $errorCount++;
                                $response[$key] = 'City or state is invalid at C' . ($key + 1);
                                continue;
                            }

                            if (StoreType::where(\DB::raw('LOWER(name)'), strtolower($row[1]))->doesntExist()) {
                                $errorCount++;
                                $response[$key] = 'Store type is invalid at B' . ($key + 1);
                                continue;
                            }

                            if (ModelType::where(\DB::raw('LOWER(name)'), strtolower($row[9]))->doesntExist()) {
                                $errorCount++;
                                $response[$key] = 'Store model type is invalid at J' . ($key + 1);
                                continue;
                            }

                            if (!empty($row[27]) && StoreCategory::where( \DB::raw('LOWER(name)'), strtolower( $row[27] ) )->doesntExist() ) {
                                $errorCount++;
                                $response[$key] = 'Store category is invalid at AB' . ($key + 1);
                                continue;
                            }

                            $theCurrentDom = null;

                            if (empty($row[10])) {
                                $errorCount++;
                                $response[$key] = 'DOM does not exists at K' . ($key + 1);
                                continue;
                            } else {
                                $explodedDomString = explode('_', str_replace(' ', '', $row[10]));
                                if (isset($explodedDomString[0])) {
                                    $currentDom = User::withTrashed()->where('employee_id', $explodedDomString[0])->first();

                                    if ($currentDom) {
                                        if (!empty($row[4])) {
                                            $currentDom->name = $row[4];
                                        }

                                        if (!empty($row[5])) {
                                            $currentDom->middle_name = $row[5];
                                        }

                                        if (!empty($row[6])) {
                                            $currentDom->last_name = $row[6];
                                        }                                        

                                        if (!empty($row[11])) {
                                            if (User::withTrashed()->where('employee_id', '!=', $explodedDomString[0])->where('phone_number', $row[11])->exists()) {
                                                $errorCount++;
                                                $response[$key] = 'Use different phone number at L' . ($key + 1);
                                                continue;
                                            } else {
                                                $currentDom->phone_number = $row[11];
                                                $currentDom->password = $row[11];
                                            }
                                        } else {
                                            $errorCount++;
                                            $response[$key] = 'Phone number is required at L' . ($key + 1);
                                            continue;
                                        }

                                        $theCurrentDom = $currentDom->id;
                                        $currentDom->save();
                                    } else {
                                        $currentDom = new User();
                                        $currentDom->employee_id = $explodedDomString[0];

                                        if (!empty($row[4])) {
                                            $currentDom->name = $row[4];
                                        } else {
                                            $errorCount++;
                                            $response[$key] = 'First name is required E' . ($key + 1);
                                            continue;
                                        }

                                        if (!empty($row[5])) {
                                            $currentDom->middle_name = $row[5];
                                        }

                                        if (!empty($row[6])) {
                                            $currentDom->last_name = $row[6];
                                        }

                                        if (!empty($row[11])) {
                                            if (User::withTrashed()->where('phone_number', $row[11])->exists()) {
                                                $errorCount++;
                                                $response[$key] = 'Use different phone number at L' . ($key + 1);
                                                continue;
                                            } else {
                                                $currentDom->phone_number = $row[11];
                                                $currentDom->password = $row[11];
                                            }
                                        } else {
                                            $errorCount++;
                                            $response[$key] = 'Phone number is required at L' . ($key + 1);
                                            continue;
                                        }

                                        $currentDom->save();
                                        $theCurrentDom = $currentDom->id;
                                        $currentDom->syncRoles([Helper::$roles['divisional-operations-manager']]);

                                        $optDepartment = Department::where('name', 'Operations')->first();

                                        if ($optDepartment) {
                                            Designation::create([
                                                'user_id' => $currentDom->id,
                                                'type_id' => $optDepartment->id,
                                                'type' => 3
                                            ]);
                                        }
                                        
                                    }

                                } else {
                                    $errorCount++;
                                    $response[$key] = 'DOM does not exists at K' . ($key + 1);
                                    continue;
                                }
                            }                            
                            

                            if (isset($row[12])) {
                                $toBeAdded['address1'] = $row[12];
                            }

                            if (isset($row[13])) {
                                $toBeAdded['address2'] = $row[13];
                            }

                            if (isset($row[14])) {
                                $toBeAdded['block'] = $row[14];
                            }

                            if (isset($row[15])) {
                                $toBeAdded['street'] = $row[15];
                            }

                            if (isset($row[16])) {
                                $toBeAdded['landmark'] = $row[16];
                            }

                            if (isset($row[17])) {
                                $toBeAdded['mobile'] = $row[17];
                            }

                            if (isset($row[18])) {
                                $toBeAdded['whatsapp'] = $row[18];
                            }

                            if (isset($row[19])) {
                                $toBeAdded['latitude'] = $row[19];
                            }

                            if (isset($row[20])) {
                                $toBeAdded['longitude'] = $row[20];
                            }

                            if (isset($row[21])) {
                                $toBeAdded['location_url'] = $row[21];
                            }
                            
                            if (isset($row[22]) && !empty(trim($row[22]))) {
                                $cellValue = trim($row[22]);

                                if (is_numeric($cellValue)) {
                                    $toBeAdded['open_time'] = Date::excelToDateTimeObject(floatval($cellValue))->format('h:i A');
                                } else {
                                    $time = strtotime($cellValue);
                                    if ($time !== false) {
                                        $toBeAdded['open_time'] = date('h:i A', $time);
                                    } else {
                                        $toBeAdded['open_time'] = '12:00 AM';
                                    }
                                }
                            } else {
                                $toBeAdded['open_time'] = '12:00 AM';
                            }

                            if (isset($row[23]) && !empty(trim($row[23]))) {
                                $cellValue = trim($row[23]);

                                if (is_numeric($cellValue)) {
                                    $toBeAdded['close_time'] = Date::excelToDateTimeObject(floatval($cellValue))->format('h:i A');
                                } else {
                                    $time = strtotime($cellValue);
                                    if ($time !== false) {
                                        $toBeAdded['close_time'] = date('h:i A', $time);
                                    } else {
                                        $toBeAdded['close_time'] = '11:59 PM';
                                    }
                                }
                            } else {
                                $toBeAdded['close_time'] = '11:59 PM';
                            }

                            if (isset($row[24]) && !empty(trim($row[24]))) {
                                $cellValue = trim($row[24]);

                                if (is_numeric($cellValue)) {
                                    $toBeAdded['ops_start_time'] = Date::excelToDateTimeObject(floatval($cellValue))->format('h:i A');
                                } else {
                                    $time = strtotime($cellValue);
                                    if ($time !== false) {
                                        $toBeAdded['ops_start_time'] = date('h:i A', $time);
                                    } else {
                                        $toBeAdded['ops_start_time'] = '12:00 AM';
                                    }
                                }
                            } else {
                                $toBeAdded['ops_start_time'] = '12:00 AM';
                            }
                            
                            if (isset($row[25]) && !empty(trim($row[25]))) {
                                $cellValue = trim($row[25]);

                                if (is_numeric($cellValue)) {
                                    $toBeAdded['ops_end_time'] = Date::excelToDateTimeObject(floatval($cellValue))->format('h:i A');
                                } else {
                                    $time = strtotime($cellValue);
                                    if ($time !== false) {
                                        $toBeAdded['ops_end_time'] = date('h:i A', $time);
                                    } else {
                                        $toBeAdded['ops_end_time'] = '11:59 PM';
                                    }
                                }
                            } else {
                                $toBeAdded['ops_end_time'] = '11:59 PM';
                            }

                            if (isset($row[26]) && !empty(trim($row[26]))) {
                                $toBeAdded['email'] = $row[26];
                            } else {
                                $toBeAdded['email'] = '';
                            }

                            $toBeAdded['code'] = $codeName[0];
                            $toBeAdded['name'] = implode(' ', array_splice($codeName, 1, count($codeName)));
                            $toBeAdded['store_type'] = StoreType::firstWhere(\DB::raw('LOWER(name)'), strtolower($row[1]))->id ?? null;
                            $toBeAdded['model_type'] = ModelType::firstWhere(\DB::raw('LOWER(name)'), strtolower($row[9]))->id ?? null;
                            $toBeAdded['store_category'] = StoreCategory::firstWhere( \DB::raw( 'LOWER(name)' ), strtolower( $row[27] ) )->id ?? null;
                            $toBeAdded['city'] = $city->city_id ?? null;
                            $toBeAdded['dom_id'] = $theCurrentDom;
                            
                            Store::updateOrCreate([
                                'code' => $toBeAdded['code'],
                            ], $toBeAdded);

                            $successCount++;
                            
                        } else {
                                $errorCount++;
                                $response[$key] = 'Valid store information does not exists at A' . ($key + 1);
                                continue;
                        }
                    } else {

                        if (count($row) !== count($expectedHeaders)) {
                            \App\Http\Controllers\ChecklistSchedulingController::recordImport([
                                'checklist_id' => null,
                                'type' => 2,
                                'file_name' => $file->getClientOriginalName(),
                                'success' => 0,
                                'error' => 0,
                                'status' => 2,
                                'response' => [
                                    'Uploaded file has an incorrect number of columns.'
                                ]
                            ], $file);

                            \DB::rollBack();
                            return response()->json(['status' => false, 'message' => 'Uploaded file has an incorrect number of columns.']);
                        }

                        if (!(
                               strtoupper($row[0])  == $expectedHeaders[0]
                            && strtoupper($row[1])  == $expectedHeaders[1]
                            && strtoupper($row[2])  == $expectedHeaders[2]
                            && strtoupper($row[3])  == $expectedHeaders[3]
                            && strtoupper($row[4])  == $expectedHeaders[4]
                            && strtoupper($row[5])  == $expectedHeaders[5]
                            && strtoupper($row[6])  == $expectedHeaders[6]
                            && strtoupper($row[7])  == $expectedHeaders[7]
                            && strtoupper($row[8])  == $expectedHeaders[8]
                            && strtoupper($row[9])  == $expectedHeaders[9]
                            && strtoupper($row[10]) == $expectedHeaders[10]
                            && strtoupper($row[11]) == $expectedHeaders[11]
                            && strtoupper($row[12]) == $expectedHeaders[12]
                            && strtoupper($row[13]) == $expectedHeaders[13]
                            && strtoupper($row[14]) == $expectedHeaders[14]
                            && strtoupper($row[15]) == $expectedHeaders[15]
                            && strtoupper($row[16]) == $expectedHeaders[16]
                            && strtoupper($row[17]) == $expectedHeaders[17]
                            && strtoupper($row[18]) == $expectedHeaders[18]
                            && strtoupper($row[19]) == $expectedHeaders[19]
                            && strtoupper($row[20]) == $expectedHeaders[20]
                            && strtoupper($row[21]) == $expectedHeaders[21]
                            && strtoupper($row[22]) == $expectedHeaders[22]
                            && strtoupper($row[23]) == $expectedHeaders[23]
                            && strtoupper($row[24]) == $expectedHeaders[24]
                            && strtoupper($row[25]) == $expectedHeaders[25]
                            && strtoupper($row[26]) == $expectedHeaders[26]
                            && strtoupper($row[27]) == $expectedHeaders[27]
                        )) {
                            
                            \App\Http\Controllers\ChecklistSchedulingController::recordImport([
                                'checklist_id' => null,
                                'type' => 2,
                                'file_name' => $file->getClientOriginalName(),
                                'success' => 0,
                                'error' => 0,
                                'status' => 2,
                                'response' => [
                                    'Uploaded file headers do not match the expected format.'
                                ]
                            ], $file);

                            \DB::rollBack();
                            return response()->json(['status' => false, 'message' => 'Files header are mismatching.']);
                        }
                    }
                }
            } else {
                \App\Http\Controllers\ChecklistSchedulingController::recordImport([
                    'checklist_id' => null,
                    'type' => 2,
                    'file_name' => $file->getClientOriginalName(),
                    'success' => 0,
                    'error' => 0,
                    'status' => 2,
                    'response' => [
                        'File is empty'
                    ]
                ], $file);

                \DB::rollBack();
                return response()->json(['status' => false, 'message' => 'File is empty.']);
            }

            \App\Http\Controllers\ChecklistSchedulingController::recordImport([
                'checklist_id' => null,
                'type' => 1,
                'file_name' => $file->getClientOriginalName(),
                'success' => $successCount,
                'error' => $errorCount,
                'status' => $successCount == 0 ? 2 : (
                    $errorCount > 0 ? 3 : 1
                ),
                'response' => $response,
                'leave_blank' => 0
            ], $file, true);

            \DB::commit();
            return response()->json(['status' => true, 'message' => 'Store list updated successfully.']);
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error occured on importing the stores ' . $e->getMessage() . ' on line ' . $e->getLine());
            return response()->json(['status' => false, 'message' => 'Something went wrong!']);
        }
    }

    public function exportStores()
    {
        $stores = Store::with( [ 'thecity', 'storetype', 'modeltype', 'dom', 'storecategory' ] )
        ->when(!empty(request('filter_location')), function ($builder) {
            $builder->where('code', request('filter_location'));
        })
        ->when(!empty(request('filter_state')) && request('filter_state') != 'all', function ($builder) {
            $builder->whereHas('thecity', function ($innerBuilder) {
                $innerBuilder->where('city_state', request('filter_state'));
            });
        })
        ->when(!empty(request('filter_city')) && request('filter_city') != 'all', function ($builder) {
            $builder->whereHas('thecity', function ($innerBuilder) {
                $innerBuilder->where('city_id', request('filter_city'));
            });
        })
        ->when(!empty(request('filter_dom')) && request('filter_dom') != 'all', function ($builder) {
            $builder->where('dom_id', request('filter_dom'));
        })
        ->get();

        return Excel::download(new \App\Exports\StoresExport($stores), 'stores.xlsx');
    }
}
