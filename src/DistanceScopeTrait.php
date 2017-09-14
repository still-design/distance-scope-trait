<?php

namespace Stilldesign\DistanceScopeTrait;

use DB;
use Exception;

trait DistanceScopeTrait
{
    protected $distanceUnitKilometers = 111.045;
    protected $distanceUnitMiles = 69.0;

    /**
     * @param $query
     * @param $lat
     * @param $lng
     * @param int $radius
     * @param string $units
     * @throws Exception
     */
    public function scopeDistance($query, $lat, $lng, $radius = 10, $units = 'K')
    {
        $distanceUnit = $this->distanceUnit($units);

        if (!(is_numeric($lat) && $lat >= -90 && $lat <= 90)) {
            throw new Exception("Latitude must be between -90 and 90 degrees.");
        }

        if (!(is_numeric($lng) && $lng >= -180 && $lng <= 180)) {
            throw new Exception("Longitude must be between -180 and 180 degrees.");
        }

        $distanceSelect = '*,
        (%f * DEGREES(ACOS(COS(RADIANS(%f)) * COS(RADIANS(lat)) * COS(RADIANS(%f - lng)
        ) + SIN(RADIANS(%f)) * SIN(RADIANS(lat)))))
         AS distance';

        $haversine = sprintf($distanceSelect,
            $distanceUnit,
            $lat,
            $lng,
            $lat
        );

        $subselect = clone $query;
        $subselect
            ->selectRaw(DB::raw($haversine));

        $latDistance      = $radius / $distanceUnit;
        $latNorthBoundary = $lat - $latDistance;
        $latSouthBoundary = $lat + $latDistance;
        $subselect->whereRaw(sprintf("lat BETWEEN %f AND %f", $latNorthBoundary, $latSouthBoundary));

        $lngDistance     = $radius / ($distanceUnit * cos(deg2rad($lat)));
        $lngEastBoundary = $lng - $lngDistance;
        $lngWestBoundary = $lng + $lngDistance;
        $subselect->whereRaw(sprintf("lng BETWEEN %f AND %f", $lngEastBoundary, $lngWestBoundary));

        $query
            ->from(DB::raw('(' . $subselect->toSql() . ') as d'))
            ->where('distance', '<=', $radius);
    }

    /**
     * @param string $units
     * @return float
     * @throws Exception
     */
    private function distanceUnit($units = 'K')
    {
        if ($units == 'K') {
            return $this->distanceUnitKilometers;
        } elseif ($units == 'M') {
            return $this->distanceUnitMiles;
        } else {
            throw new Exception("Unknown distance unit measure '$units'.");
        }
    }


}