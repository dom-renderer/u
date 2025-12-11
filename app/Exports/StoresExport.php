<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StoresExport implements FromCollection, WithHeadings, WithMapping
{
    protected $stores;

    public function __construct($stores)
    {
        $this->stores = $stores;
    }

    public function collection()
    {
        return $this->stores;
    }

    public function headings(): array
    {
        return [
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
    }

    public function map($store): array
    {
        return [
            'store_name' => "{$store->code} {$store->name}",
            'type' => $store->storetype ? $store->storetype->name : '',
            'city' => $store->thecity ? $store->thecity->city_name : '',
            'state' => $store->thecity ? $store->thecity->city_state : '',
            'dom_first_name' => $store->dom->name ?? '',
            'dom_middle_name' => $store->dom->middle_name ?? '',
            'dom_last_name' => $store->dom->last_name ?? '',
            'ops_mgr' => '',
            'ops_head' => '',
            'model' => $store->modeltype ? $store->modeltype->name : '',
            'dom_id' => ($store->dom->employee_id ?? '') . '_' . ($store->dom->name ?? ''),
            'dom_mobile' => $store->dom->phone_number ?? '',
            'address_1' => $store->address1,
            'address_2' => $store->address2,
            'block' => $store->block,
            'street' => $store->street,
            'landmark' => $store->landmark,
            'mobile' => $store->mobile,
            'whatsapp' => $store->whatsapp,
            'latitude' => $store->latitude,
            'longitude' => $store->longitude,
            'location_url' => $store->location_url,
            'opening' => $store->open_time,
            'closing' => $store->close_time,
            'ops_start_time' => $store->ops_start_time,
            'ops_end_time' => $store->ops_end_time,
            'email' => $store->email,
            'category' => $store->storecategory ? $store->storecategory->name : '',
        ];
    }
}
