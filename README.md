This is an API for Food Delivery app project, built using Laravel 8.
In order to start this project, clone it.
Create the given mysql database that i uploaded as "food_delivery.sql".
Then write php artisan serve terminal in root folder of the project, and then you can test it using postman.

First, you need to register, where you will get bearer token. Use that token until you logout. 
Admin can create restaurant chain, all of the locations of the specific restaurant, food.

User can see restaurants, food, locations, rate and order food.

!! I expect longitude and latitude to be given from front-end, so I just put 2 fields ( longitude and latitude ), for testing, I found longitude and latitude of some places that i chose and just inserted them as fields, and then meassured distance between user and restaurants. (Link to get longitude and latitude: https://www.latlong.net/) !!
