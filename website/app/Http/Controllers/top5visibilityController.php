<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

use DB;

class Top5visibilityController extends Controller
{

    /**
     * top5visibilityController constructor
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the data at /humidity
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function home() {
        $data = $this->calculateData();
        return view('top5visibility')->with('data', $data);
    }

    /**
     * Show the data at /top5/live/data
     *
     * @return JSON
     */
    public function getData() {
        return response()->json($this->calculateData());
    }


    /*
     * Check if OS = windows
     *
     * @return true if Windows is the OS
     */
    private static function checkOsIsWindows(){
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            //windows
            return true;
        }

        //probably linux
        return false;
    }

    /**
     * Filter the data needed for this controller
     *
     * @return array
     */
    private function calculateData() {
        $stations = DB::table('balkan_stations')->get()->toArray();
        DB::table('average_visibility')->where('date', date('Y-m-d'))->delete();

        foreach($stations as $id => $station) {
            try {
                $file = Storage::disk('weatherdata')->get(date('Y-m-d') . '/' . $station->balkan_station . '.csv');
            }
            catch(FileNotFoundException $e) {
                continue;
            }

            // Split the .csv by newline.
            if(self::checkOsIsWindows()){
                //os = windows
                $seperated = explode("\r\n", $file);
            }else{
                //os = linux
                $seperated = explode("\n", $file);
            }

            // Get the first value, split by comma and create an array with it. This array contains the measurement types
            $labels = explode(',', array_shift($seperated));

            // Dynamically fill an array with measurements. [ 'column_name1' => [], 'column_name2' => [] ]
            for($y = 0; $y < count($labels); $y++) {
                $fullData[strtolower($labels[$y])] = [];
            }

            $totalVisibility = 0;
            $index = 0;
            // Loop through all rows (except for the first one)
            for($i = 0; $i < count($seperated); $i++) {

                //If there is an empty row, skip it
                if(empty(trim($seperated[$i]))) {
                    continue;
                }

                // Split the row on commas
                $data = explode(',', $seperated[$i]);

                $totalVisibility += $data[4];
                $index++;
            }
            if($index) {
                DB::table('average_visibility')->insert([
                    'station_id' => $station->id,
                    'average_visibility' => $totalVisibility / $index,
                    'date' => date('Y-m-d')
                ]);
            }
        }

        $visibility = DB::table('average_visibility')
                            ->where('date', date('Y-m-d'))
                            ->orderBy('average_visibility', 'desc')
                            ->limit(5)
                            ->get();

        dd($visibility);

        return $visibility;
    }

}