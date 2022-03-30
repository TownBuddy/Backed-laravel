<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\CityModel;
use App\CountryModel;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.dashboard');
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
    public function store(Request $request)
    {
        //
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
        //
    }


    //Api Functions
    public function get_cities(Request $request){
        $keyword = $request->input('key');

        if($keyword != ''){
            $cities = CityModel::where('status','1')->where('country','101')->where('city','like',$keyword.'%')->get();
            $city_arr = array();
            if(count($cities) > 0){
                foreach($cities as $k=>$city){
                    $arr = array(
                            "id"=> $city->id,
                            "city"=> $city->city,
                            "country"=> $city->country_name,
                            "state"=> $city->state,
                            "status"=> $city->status,
                            "geonameid"=> $city->geonameid,
                            "created_at"=> $city->created_at,
                            "updated_at"=> $city->updated_at
                        );
                    $city_arr[] = $arr;    
                }
                return response()->json(['status'=>true,'status_code'=>'200','message'=>'success','error'=>[],'data'=>$city_arr], 200);
            }else{
                return response()->json(['status'=>false,'status_code'=>'401','message'=>'No City Found','error'=>['No City Found'],'data'=>[]], 200);
            }
        }else{
            $cities = CityModel::where('status','1')->where('country','101')->get();
            $city_arr = array();
            foreach($cities as $k=>$city){
                    $arr = array(
                            "id"=> $city->id,
                            "city"=> $city->city,
                            "country"=> $city->country_name,
                            "state"=> $city->state,
                            "status"=> $city->status,
                            "geonameid"=> $city->geonameid,
                            "created_at"=> $city->created_at,
                            "updated_at"=> $city->updated_at
                        );
                    $city_arr[] = $arr;    
                }
            if(count($city_arr) > 0){
                return response()->json(['status'=>true,'status_code'=>'200','message'=>'success','error'=>[],'data'=>$city_arr], 200);
            }else{
                return response()->json(['status'=>false,'status_code'=>'401','message'=>'No City Found','error'=>['No City Found'],'data'=>[]], 200);
            }
        }
    }
    //Api Functions
    public function get_countries(Request $request){
        $keyword = $request->input('key');

        if($keyword != ''){
            $cities = CountryModel::select('country_name as country')->where('status','1')->where('country_name','like','%'.$keyword.'%')->get();
            if(count($cities) > 0){
                return response()->json(['status'=>true,'status_code'=>'200','message'=>'success','error'=>[],'data'=>$cities], 200);
            }else{
                return response()->json(['status'=>false,'status_code'=>'401','message'=>'No Country Found','error'=>['No Country Found'],'data'=>[]], 200);
            }
        }else{
            $cities = CountryModel::select('country_name as country')->where('status','1')->get();
            if(count($cities) > 0){
                return response()->json(['status'=>true,'status_code'=>'200','message'=>'success','error'=>[],'data'=>$cities], 200);
            }else{
                return response()->json(['status'=>false,'status_code'=>'401','message'=>'No Country Found','error'=>['No Country Found'],'data'=>[]], 200);
            }
        }
    }
    //Api Functions
    public function get_country_list(Request $request){
        $countries = CountryModel::where('status','1')->get();
        
        if(count($countries) > 0){
            return response()->json(['status'=>true,'status_code'=>'200','message'=>'success','error'=>[],'data'=>$countries], 200);
        }else{
            return response()->json(['status'=>false,'status_code'=>'401','message'=>'No Country Found','error'=>['No Country Found'],'data'=>[]], 200);
        }
    }
    public function stateCountryByCity(Request $request){
        $city_id = $request->input('city_id');
        $city = CityModel::where('status','1')->where('id',$city_id)->first();
        if(($city) != null){
            $data['state'] = $city['state'];
            $data['country'] = $city['country'];
            return response()->json(['status'=>true,'status_code'=>'200','message'=>'success','error'=>[],'data'=>$data], 200);
        }else{
            return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid City Id','error'=>['Invalid City Id'],'data'=>[]], 200);
        }


    }

}
