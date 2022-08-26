<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Weather;
use App\Http\Controllers\DB;
use Carbon\Carbon;

class WeatherController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $query = $request->query('query');

        //Check database for query
        $cacheWeather = Weather::where('query','LIKE','%'.$query.'%')->first();       
        
        if($cacheWeather && $query){ 

            $now = Carbon::now();
            $updated_at = $cacheWeather->updated_at;

            //Calculate antiquity from register
            $minutesDiff = $updated_at->diffInMinutes($now);

            //Check 1 hour register
            if($minutesDiff > 60){ 
                //Delete old register, save new and show data
                $this->destroy($cacheWeather->id);
                return $this->store($query);
            }else{ 
                //Show data from database
                return json_decode($cacheWeather->weather_info);
            }

        }else {
            //Save new register and show data
            return $this->store($query);
        }        

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store($query)
    {
        //Get data from weatherstack
        $response = Http::get($_ENV['WEATHER_API_URL'], [
            'access_key' => $_ENV['WEATHER_API_KEY'],
            'query' => $query,
        ]);

        $jsonData = $response->json();

        //Check error response
        if(!array_key_exists('error', $jsonData)){
            $weather = new Weather();
            $weather->query = $jsonData['request']['query'];
            $weather->weather_info = json_encode($jsonData);
            $weather->save();
        }

        return $jsonData;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $weather = Weather::find($id);
        $weather->delete();
    }
}
