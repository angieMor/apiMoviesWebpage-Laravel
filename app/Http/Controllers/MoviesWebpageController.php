<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Movie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class MoviesWebpageController extends Controller
{
    /**
     * Validates that the request contain minimum requirements assigned
     * @param Request $request
     * @param array $rules
     * @param array $messages, comes null by default
     * @return nodata if the validation was succesfull
     */
    public function validateAllMinimumRequirements($request, $rules, $messages = null){
        $validator = Validator::make($request->all(), $rules);
        if($messages) {$validator = Validator::make($request->all(), $rules, $messages);}
        
        if ($validator->fails()) {
            // Returns a JSON with the custom errors and the HTTP code 400
            return response()->json(['errors' => $validator->errors()], 400);
        }
        # Otherwise, the flow continues
        return;
    }

    /** 
     * Reads movies from DB and orders them depending on the user's input via params
     * @param \Illuminate\Http\Request
     * @return EloquentCollection that contains the movies retrieved from the DB
     */
    public function readMovies(Request $request){
        # Validates if $request have query url params
        if($request->query()){
            # When order is sended so will have to come with orderBy
            $rules = [
                'orderBy' => 'required|string|orderby_type',
                'order' => 'required|string|order_type'
            ];

            $invalidRequirements = $this->validateAllMinimumRequirements($request, $rules);
            if ($invalidRequirements) {
                return $invalidRequirements;
            }

            # Check for a parameter enter that is different from the 3 desired ones
            $valid_keys = ['order', 'orderBy', 'type'];
            $query_keys = array_keys($request->query());

            foreach ($query_keys as $key) {
                if (!in_array($key, $valid_keys)) {
                    return response()->json(['error' => 'Parameter invalid ' . $key], 400);
                }
            }

            # asc/desc
            $order = $request->query()['order'];
            # title/rate
            $orderBy = $request->query()['orderBy'];
            
            # Checks if param type optional was provided
            if(isset($request->query()['type'])) {
                $rules = [
                    'type' => 'required|string|movie_type',
                ];

                $invalidRequirements = $this->validateAllMinimumRequirements($request, $rules);
                if ($invalidRequirements) {
                    return $invalidRequirements;
                }

                # movie/series
                $type = ucfirst($request->query()['type']);

                if($orderBy == 'rate') {
                    # Calculates the average per item and order the list
                    $movies = DB::table('movies')
                                ->select('id', 'title', 'year', 'type', 'image_url', DB::raw('sum_rates / total_rates AS avg_rating'))
                                ->where('type', '=', $type)
                                ->where('total_rates', '>', 0)
                                ->orderBy(DB::raw('sum_rates / total_rates'), $order)
                                ->get();
                } else {
                    # Order by title
                    $movies = DB::table('movies')
                              ->select('id', 'title', 'year', 'type', 'image_url')
                              ->where('type', '=', $type)
                              ->orderBy($orderBy, $order)
                              ->get();
                }

            } else { 
                # If no query param type was provided then, makes queries for orderBy rate/title only
                if($orderBy == 'rate'){
                    $movies = DB::table('movies')
                                ->select('id', 'title', 'year', 'type', 'image_url', DB::raw('sum_rates / total_rates AS avg_rating'))
                                ->where('total_rates', '>', 0)
                                ->orderBy(DB::raw('sum_rates / total_rates'), $order)
                                ->get();
                }else{
                    $movies = Movie::orderBy($orderBy, $order)->get(); 
                }
            }
            
            return $movies;       
        }
        # If no query params where provided, all movies will be displayed
        $movies = Movie::all();
        return $movies;
    }


    /**
     * Creates a movie with the required fields
     * @param \Illuminate\Http\Request
     * @return Http response code with a confirmation message
     */
    public function createMovie(Request $request){
        # Validates that all the labels are provided
        $rules = [
            'title' => 'required|min:3|string',
            'year' => 'required|numeric|min:4',
            'imageUrl' => 'required|url',
            'type' => 'required|string|movie_type',
        ];

        $messages = [
            'required' => 'The field :attribute is required',
            'min' => 'The field :attribute must have at least :min characters',
            'max' => 'The field :attribute must have maximum :max characters',
            'numeric' => 'The field :attribute must be a number',
            'string' => 'The field :attribute must be a string'
        ];

        $invalidRequirements = $this->validateAllMinimumRequirements($request, $rules, $messages);
        if ($invalidRequirements) {
            return $invalidRequirements;
        }
        
        # Create a Movie Object instance
        $movie = new Movie();
        
        # Filling out the movies object with the data provided via request
        $movie->title     = $request->title;
        $movie->year      = $request->year;
        $movie->image_url = $request->imageUrl;
        $movie->type      = ucfirst($request->type);
        $movie->total_rates= 0;
        $movie->sum_rates=0;

        # Save the movie object
        $movie->save();
        
        # Redirects user to the route given with a message
        return response()->json(['message' => 'Item created'], 201);
    }


    /**
     * Updates the entire item if found
     * @param \Illuminate\Http\Request
     * @return Http response code with a confirmation message
     */
    public function updateMovie(Request $request){
        # Validates that all the labels are provided
        $rules = [
            'title' => 'required|min:3|string',
            'year' => 'required|numeric|min:4',
            'imageUrl' => 'required|url',
            'type' => 'required|string|movie_type',
        ];

        $messages = [
            'required' => 'The field :attribute is required',
            'min' => 'The field :attribute must have at least :min characters',
            'max' => 'The field :attribute must have maximum :max characters',
            'numeric' => 'The field :attribute must be a number',
            'string' => 'The field :attribute must be a string'
        ];

        $invalidRequirements = $this->validateAllMinimumRequirements($request, $rules, $messages);
        if ($invalidRequirements) {
            return $invalidRequirements;
        }

        $title = $request->title;

        try {           
            $movieFound = Movie::where('title', $title)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'The title was not found, please check again'], 400);
        }
        
        $movieFound->year      = $request->year;
        $movieFound->image_url = $request->imageUrl;
        $movieFound->type      = ucfirst($request->type);
        $movieFound->save();

        return response()->json(['message' => 'Update for item '.$title.' was succesfull'], 200);
    }


    public function deleteMovie($title){
        
        try{
            $movieFound =  Movie::where('title', $title)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => $title.' was not found to be deleted, please check again'], 400);
        }

        $movieFound->delete();
        return response()->json(['message' => 'Item with title '.$title.' was deleted'], 200);
    }


    /**
     * Updates the rate of the Movie which should be given by a user in the movieslist
     * @param \Illuminate\Http\Request
     * @return Http response code with a confirmation message
     */
    public function rateMovie(Request $request){
        # Only allows query params
        if($request->query()){
            $rules = [
                "ratetitle" => "required|string",
                "score" => "required|numeric"
            ];

            $invalidRequirements = $this->validateAllMinimumRequirements($request, $rules, $messages);
            if ($invalidRequirements) {
                return $invalidRequirements;
            }

            $title = $request->query()["ratetitle"];
            # Find movie object stored by title
            $movie = Movie::where('title', $title)->firstOrFail();
            
            # Only filling up the fields required for rating an movie
            $movie["total_rates"] = $movie["total_rates"] + 1;
            $movie["sum_rates"] = $movie["sum_rates"] + $request->query()["score"];

            $movie->save();

            return response()->json(['message' => 'The item '.$title.' was scored'], 200);
        }
        # If no query params were provided, the message will show
        return response()->json(['message' => 'Please send ratetitle and score as params'], 400);
    }
}
