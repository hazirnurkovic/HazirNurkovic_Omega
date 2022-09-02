<?php

namespace App\Http\Controllers;

use App\Models\Food;
use App\Models\Location;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\HttpException;

use function PHPSTORM_META\type;

class OrderContoller extends ApiController
{
    /**
     * List all.
     */
    public function myOrders()
    {
        $user_id = Auth::user()->id;
        $orders = Order::all()->where('user_id', $user_id);
        return $this->showAll($orders);
    }

    public function createOrder(Request $request, $restaurant_id, $food_id)
    {
        $request->validate([
            'quantity' => 'required|integer'
        ]);
        $user_id = Auth::user()->id;

        $this->checkFoodRestaurant($food_id, $restaurant_id);

        $quantity = $this->checkFoodQuantity($food_id);
        if ($quantity == 0 || $request->quantity > $quantity) {
            return $this->errorResponse('Not enough food on the stack!', 422);
        }

        $location_id =  $this->findNearestLocation($restaurant_id, $user_id)->id;

        $order = new Order;

        $order->restaurant_id = $restaurant_id;
        $order->location_id = $location_id;
        $order->user_id = $user_id;
        $order->quantity = $request->quantity;
        $order->food_id = $food_id;
        $order->save();

        $location = Location::where('id', $location_id)->first();
        $location->ordered_at = Carbon::now();
        $location->save();

        $food = Food::where('id', $food_id)->first();
        $food->quantity = $food->quantity - $request->quantity;
        $food->save();

        return $this->showOne($order);
    }



    protected function findNearestLocation($restaurant_id, $user_id)
    {

        //find all available locations
        $locations = Location::where('restaurant_id', $restaurant_id)
            ->where(function ($q) {
                $q->where('ordered_at', '<', Carbon::now()->subMinutes(15)->toDateTimeString())
                    ->orWhereNull('ordered_at');
            })->get();

        if (count($locations) == 0) {
            throw new HttpException(422, 'No available restaurants. Try again later.');
        }

        $user = User::where('id', $user_id)->first();

        $user_latitude = $user->latitude;
        $user_longitude = $user->longitude;
        $distances_array = array();
        //find the nearest location
        foreach ($locations as $location) {
            $distance_element = $location::selectRaw("id, ST_Distance_Sphere(
                Point(" . $user_longitude . ", " . $user_latitude . "), 
                Point(longitude, latitude)
            ) * ? as distance", [1000])
                ->where('id', $location->id)
                ->first();

            array_push($distances_array, $distance_element);
        }

        $collection = collect($distances_array);

        return $collection->sortBy('distance')->first();
    }

    protected function checkFoodRestaurant($food_id, $restaurant_id)
    {
        $food_restaurant_id = Food::where('id', $food_id)->first()->restaurant_id;
        if ($food_restaurant_id != $restaurant_id) {
            throw new HttpException(422, 'This food does not belong to this restaurant');
        }
    }

    protected function checkFoodQuantity($food_id)
    {
        return Food::where('id', $food_id)->first()->quantity;
    }
}
