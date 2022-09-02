<?php

namespace App\Http\Controllers;

use App\Models\Food;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\HttpException;

class RestaurantFoodController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Restaurant $restaurant)
    {
        $food = $restaurant->food;

        return $this->showAll($food, 200);
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
            'name' => 'required|string|max:100',
            'price' => 'required|numeric',
            'quantity' => 'required|integer'
        ]);

        $food = new Food;
        $food->name = $request->name;
        $food->price = $request->price;
        $food->quantity = $request->quantity;
        $food->restaurant_id = $restaurant->id;

        if ($request->hasFile('image')) {
            $request->validate(['image' => 'mimes:png,jpg,bmp']);
            $food->image = $request->image->store('');
        }


        $food->save();

        return $this->showOne($food);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Restaurant $restaurant, Food $food)
    {
        $food = $restaurant->food()->where('id', $food->id)->first();

        return $this->showOne($food);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Restaurant $restaurant, Food $food)
    {
        $request->validate([
            'name' => 'string|max:100',
            'price' => 'numeric',
            'quantity' => 'integer'
        ]);

        $this->checkRestaurant($restaurant, $food);

        if ($request->has('name')) {
            $food->name = $request->name;
        }
        if ($request->has('price')) {
            $food->price = $request->price;
        }

        if ($request->has('quantity')) {
            $food->quantity = $request->quantity;
        }

        if ($request->hasFile('image')) {
            Storage::delete($food->image);
            $request->validate(['image' => 'mimes:png,jpg,bmp']);
            $food->image = $request->image->store('');
        }

        if ($food->isClean()) {
            return $this->errorResponse('You need to specify at least one different value in order to update', 422);
        }

        $food->save();

        return $this->showOne($food);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Restaurant $restaurant, Food $food)
    {
        $this->checkRestaurant($restaurant, $food);

        $delete = Storage::delete($food->image);
        if (!$delete) {
            return $this->errorResponse('Error occured, please try again', 500);
        }
        $food_del = $food->delete();
        if (!$food_del) {
            return $this->errorResponse('Error occured, please try again.', 500);
        }
        return $this->showOne($food);
    }


    protected function checkRestaurant(Restaurant $restaurant, Food $food)
    {
        if ($restaurant->id != $food->restaurant_id) {
            throw new HttpException(422, 'The specified restaurant does not offer this food');
        }
    }
}
