<?php

namespace App\Services;
class DmVehicleService
{
    public function getAddData(Object $request): array
    {
        return [
            "type" => $request->type[array_search('default', $request->lang)],
            "status" => 1,
            "extra_charges" => $request->extra_charges,
            "starting_coverage_area" => $request->starting_coverage_area,
            "maximum_coverage_area" => $request->maximum_coverage_area,
            "minimum_weight" => $request->minimum_weight,
            "maximum_weight" => $request->maximum_weight,
        ];
    }

    public function getUpdateData(Object $request): array
    {
        return [
            "type" => $request->type[array_search('default', $request->lang)],
            "extra_charges" => $request->extra_charges,
            "starting_coverage_area" => $request->starting_coverage_area,
            "maximum_coverage_area" => $request->maximum_coverage_area,
            "minimum_weight" => $request->minimum_weight,
            "maximum_weight" => $request->maximum_weight,
        ];
    }

}
