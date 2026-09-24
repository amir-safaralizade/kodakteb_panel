<?php

namespace App\Http\Controllers;

use App\Models\Insurance;
use App\Models\Visit;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\MyHelpers\MyJalaliDate;
use Carbon\CarbonPeriod;


class StatisicsController extends Controller
{
    public function daysStatisics(Request $request){
        $start = Carbon::today('asia/tehran')->subDays(30)->format('Y-m-d');
        $end = Carbon::now('asia/tehran')->format('Y-m-d');
        $startJ = Carbon::parse($start);
        $endJ = Carbon::parse($end);

        if($request->has('start') && !empty($request['start']) && isset($request['start'])){
            $startJ = (new MyJalaliDate())->jalaliToGeorgian($request['start']);
            $start = Carbon::parse($startJ)->format('Y-m-d');
        }
        if($request->has('end') && !empty($request['end']) && isset($request['end'])){
            $endJ = (new MyJalaliDate())->jalaliToGeorgian($request['end']);
            $end = Carbon::parse($endJ)->format('Y-m-d');
        }
        $period = CarbonPeriod::create($start, $end);
        $dates = [];
        $total_income = 0;
        $total_count = 0;
        $count_data = [];
        foreach ($period as $date) {
            $dates[] = $date->toDateString();
        }

        foreach($dates as $index=>$day){
            $start = Carbon::parse($day)->startOfDay();
            $end = Carbon::parse($day)->endOfDay();
            $sumvisits = Visit::where('created_at' , '>=' , $start)->where('created_at' , '<=' , $end)->get();
            $sum = 0;
            $count = 0;
            foreach($sumvisits as $item){
                if(!is_numeric($item->hazine)){
                    $item->hazine = 0;
                }
                $sum += $item->hazine;
                $total_count++;
                $count++;
            }
            $total_income += $sum;
            $data[] = floor($sum/10);
            $dates[$index] = (new MyJalaliDate())->georgianToJalali($day);
            $count_data[] = $count;
            
        }
        $labels = $dates;
       

        $insurances = Insurance::all();
        $insurance_list = [];
        $insurance_list_count = [];
        $insurance_mainColorList = [];
        $insurance_borderList = [];
        $start = Carbon::parse($startJ);
        $end = Carbon::parse($endJ);
       
        foreach($insurances as $item){
            $id = $item->id;
            $item_count = Visit::where('insurance_id' , $id)
            ->where('created_at' , '>=' , $start)
            ->where('created_at' , '<=' , $end)
            ->count();
            $insurance_list[] = $item->name;
            $insurance_list_count[] = $item_count;
            $insurance_mainColorList[] = $this->getRandomRGBA();
            $insurance_borderList[] = $this->getRandomRGBA(true);
        }
        $insurance_list_count;
        return view('Statistics.alldays' , compact('labels' , 'data' , 'total_count' , 'total_income' , 'count_data' , 'insurance_list' , 'insurance_list_count' , 'insurance_mainColorList' , 'insurance_borderList'));
    }


    function getRandomRGBA($a = false) {
        $r = rand(0, 255);
        $g = rand(0, 255);
        $b = rand(0, 255);
        if($a == false){
            $a = rand(20,50) / 100;
        }else{
            $a = 1;
        }
        
        return "rgba($r, $g, $b, $a)";
    }
    
}
