<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $location = Location::all();
        if (!$location || empty($location)) {
            return $this->errorResponse('Locations does not exist', 204);
        }
        return $this->showAll($location, 200);
    }


    public function show(Location $location)
    {
        return $this->showOne($location);
    }
}
