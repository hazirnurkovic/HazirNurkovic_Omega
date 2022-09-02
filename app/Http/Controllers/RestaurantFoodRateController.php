<?php

namespace App\Http\Controllers;

use App\Models\Food;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class RestaurantFoodRateController extends ApiController
{

    public function getRating($restaurant_id, $food_id)
    {
        $this->checkFoodRestaurant($food_id, $restaurant_id);

        $food_rating = Food::where('id', $food_id)->first();
        $food_rating = $food_rating->rating;

        return $this->successResponse(['rating' => $food_rating], 200);
    }

    public function rateFood(Request $request, $restaurant_id, $food_id)
    {
        $request->validate([
            'rating' => 'required|numeric|min:1|max:5'
        ]);

        $this->checkFoodRestaurant($food_id, $restaurant_id);

        $food = Food::where('id', $food_id)->first();
        if (empty($food->rating)) {
            $food->rating = $request->rating;
        } else {
            $food->rating = ($food->rating + $request->rating) / 2;
        }

        $food->save();

        return $this->showOne($food);
    }

    protected function checkFoodRestaurant($food_id, $restaurant_id)
    {
        $food_restaurant_id = Food::where('id', $food_id)->first()->restaurant_id;
        if ($food_restaurant_id != $restaurant_id) {
            throw new HttpException(422, 'This food does not belong to this restaurant');
        }
    }
}
