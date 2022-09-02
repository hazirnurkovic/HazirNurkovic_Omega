<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class RestaurantLocationController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Restaurant $restaurant)
    {
        $location = $restaurant->location;

        return $this->showAll($location);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Restaurant $restaurant)
    {
        $request->validate([
            'address' => 'required|string|max:200',
            'longitude' => 'required|numeric',
            'latitude' => 'required|numeric',
        ]);
        $location = new Location();
        $location->address = $request->address;
        $location->longitude = $request->longitude;
        $location->latitude = $request->latitude;
        $location->restaurant_id = $restaurant->id;

        $location->save();

        return $this->showOne($location);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Restaurant $restaurant, Location $location)
    {
        $location = $restaurant->locations()->where('id', $location->id)->first();

        return $this->showOne($location);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Restaurant $restaurant, Location $location)
    {
        $request->validate([
            'address' =>    'string|max:200',
            'longitude' =>  'numeric',
            'latitude' =>   'numeric',
        ]);

        $this->checkRestaurant($restaurant, $location);

        if ($location->isClean()) {
            return $this->errorResponse('You need to specify at least one different value in order to update', 422);
        }

        if ($request->has('address')) {
            $location->address = $request->address;
        }

        if ($request->has('longitude')) {
            $location->longitude = $request->longitude;
        }


        if ($request->has('latitude')) {
            $location->latitude = $request->latitude;
        }


        $location->save();

        return $this->showOne($location);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Restaurant $restaurant, Location $location)
    {
        $this->checkRestaurant($restaurant, $location);

        $location->delete();

        return $this->showOne($location);
    }


    protected function checkRestaurant(Restaurant $restaurant, Location $location)
    {
        if ($restaurant->id != $location->restaurant_id) {
            throw new HttpException(422, 'The specified restaurant chain does not have their unit at the given location');
        }
    }
}
