<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

use DB;

class parseTemporaryData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:parseAverageVisibility';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Parse the data of the past hour';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        /*$weatherdata = [131050,131160,131310,131825,132080,132090,132230,132240,132280,132420,132570,132610,132690,132740,132790,132850,132890,132950,133330,133340,133480,133520,133530,133670,
33760,
133766,
133770,
133780,
133840,
133880,
133890,
133970,
134520,
134570,
134590,
134620,
134624,
134770,
134810,
134811,
134890,
134930,
135620,
135780,
135790,
135830,
135860,
136150,
141050,
142160,
142190,
142320,
142410,
142440,
143030,
143070,
143080,
143140,
143210,
143230,
143240,
143280,
143300,
144270,
144280,
144310,
144380,
144410,
144420,
144440,
144450,
144470,
144520,
144540,
144620,
144720,
144740,
145420,
146480,
146520,
146540,
153350,
154090,
154800,
154810,
154990,
155020,
155050,
155110,
155250,
155253,
155260,
155280,
155300,
155350,
155490,
155500,
155520,
155560,
155561,
155610,
155620,
156000,
156050,
156090,
156091,
156130,
156140,
156150,
156250,
156270,
156350,
156400,
156550,
156610,
157120,
157250,
157260,
161080,
161100,
166220,
166240,
166270,
166650,
166750,
166820,
166870,
166990,
167160,
167161,
167180,
167260,
167340,
170500,
170560,
170575,
170600,
691810
];*/

$weatherdata = [130670];
    for($foo = 0; $foo < count($weatherdata); $foo++) {
        $file = Storage::disk('weatherdata')->get($weatherdata[$foo] . '.csv');

        // Split the .csv by newline.
        $seperated = explode("\r\n", $file);

        // Get the first value, split by comma and create an array with it. This array contains the measurement types
        $labels = explode(',', array_shift($seperated));

        // Dynamically fill an array with measurements. [ 'column_name1' => [], 'column_name2' => [] ]
        for($y = 0; $y < count($labels); $y++) {
            $fullData[strtolower($labels[$y])] = [];
        }

        // Loop through all rows (except for the first one)
        for($i = 0; $i < count($seperated); $i++) {

            //If there is an empty row, skip it
            if(empty(trim($seperated[$i]))) {
                continue;
            }

            // Split the row on commas
            $data = explode(',', $seperated[$i]);

            // Loop through the splitted row
            for($x = 0; $x < count($data); $x++) {
                // The index of specific data is located in the same index as the label. is.
                // For example: the first value (index 0) is the temperature.
                // The first value of the $labels array is temperature, so this data has to be filled in the array he has as value.
                $fullData[strtolower($labels[$x])][] = $data[$x];
            }

        }

        // Current hour (12, or 22 for example)
        $currentHour = \Carbon\Carbon::now()->hour;
        $temporaryArray = [];

        // Loop over the time array
        foreach ($fullData["time"] as $key => $value) {

            // Explode the timestamp (h:m:s)
            $currentHourCSV = explode(':', $value);

            // If the current timestamp is the same as the timestamp in the csv file
            if($currentHourCSV[0] == $currentHour) {
                //$fulldata = ['topFive' => ['130670' => 156.6, '130671' = 123.4]]
                $temporaryArray[] = $fullData['visibility'][$key];
            }
        }

        $total = 0;
        for($i = 0; $i < count($temporaryArray); $i++) {
            $total += $temporaryArray[$i];
        }

        $fullData['averageVisibility'][$weatherdata[$foo]] = $total / count($temporaryArray);
    }

    dd($fullData);
}
}