<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Users_Model;
use App\ProductsModel;
use App\CityProducts;
use App\WishlistModel;
use App\CategoryModel;

class ProductsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
    public function get_products(Request $request){
        $city_id = $request->input('city_id');
        $user_id = $request->input('user_id');

        if(!empty($city_id)){
            $products = CityProducts::with('city')->has('city')->with('product')->has('product')->where('city_id',$city_id)->get();
            
            if(count($products) > 0){
                foreach($products as $product){
                    $wishlist = 0;
                    if($user_id != ''){
                        $wishlist = WishlistModel::has('product')->has('user')->where('product_id',$product->id)->where('user_id',$user_id)->first();
                        if($wishlist != null){
                            $wishlist = 1;
                        }else{
                            $wishlist = 0;
                        }
                    }

                    $arr = array(
                            "product_id"=>$product->product->id,
                            "product_name"=>$product->product->product_name,
                            "product_image"=>$product->product->product_image_url,
                            "product_link"=>$product->product->product_link,
                            "product_price"=>$product->product->product_price,
                            "product_category"=>$product->product->category->category_name,
                            "wishlist"=>$wishlist,
                            "qty"=>$product->product->product_stock_qty
                    );
                    $products_arr[] = $arr;
                }
    
                return response()->json(['status'=>true,'status_code'=>'200','message'=>'success','data'=>$products_arr,'error'=>[]], 200);
            }else{
                return response()->json(['status'=>false,'status_code'=>'401','message'=>'No Products Found','data'=>[],'error'=>['No Products Found']], 200);
            }
            
        }else{
            $products = ProductsModel::with('category')->has('category')->where('product_stock_qty','>','0')->get();
            $products_arr = array();

            if(count($products) > 0){
                foreach($products as $product){
                    $wishlist = 0;
                    if($user_id != ''){
                        $wishlist = WishlistModel::has('product')->has('user')->where('product_id',$product->id)->where('user_id',$user_id)->first();
                        if($wishlist != null){
                            $wishlist = 1;
                        }else{
                            $wishlist = 0;
                        }
                    }

                    $arr = array(
                            "product_id"=>$product->id,
                            "product_name"=>$product->product_name,
                            "product_image"=>$product->product_image_url,
                            "product_link"=>$product->product_link,
                            "product_price"=>$product->product_price,
                            "product_category"=>$product->category->category_name,
                            "wishlist"=>$wishlist,
                            "qty"=>$product->product->product_stock_qty
                    );
                    $products_arr[] = $arr;
                }
    
                return response()->json(['status'=>true,'status_code'=>'200','message'=>'success','data'=>$products_arr,'error'=>[]], 200);
            }else{
                return response()->json(['status'=>false,'status_code'=>'401','message'=>'No Products Found','data'=>[],'error'=>['No Products Found']], 200);
            }
        }
    }
    public function get_product_detail(Request $request){
        $product_id = $request->input('product_id');

        $product = ProductsModel::with('category')->has('category')->where('id',$product_id)->first();
        if(!empty($product)){
            if($product->product_stock_qty > 0){
                $city =  '';
                $city_name = '';
                if(count($product->locations) > 0){
                    foreach($product->locations as $location){
                        $city =  $location->city_id;
                        $city_name = $location->city->city;
                    }
                }
    
                $data = array(
                    "product_id"=>$product->id,
                    "product_name"=>$product->product_name,
                    "product_category_id"=>$product->product_category,
                    "product_category"=>$product->category->category_name,
                    "product_image"=>$product->product_image_url,
                    "product_unique_id"=>$product->product_unique_id,
                    "product_stock_qty"=>$product->product_stock_qty,
                    "product_weight"=>$product->product_weight,
                    "product_weight_unit"=>$product->product_weight_unit,
                    "product_size"=>$product->product_size,
                    "product_length"=>$product->product_length,
                    "product_width"=>$product->product_width,
                    "product_height"=>$product->product_height,
                    "product_price"=>$product->product_price,
                    "product_link"=>$product->product_link,
                    "product_city_id"=>$city,
                    "product_city_name"=>$city_name,
                );
    
                return response()->json(['status'=>true,'status_code'=>'200','message'=>'success','data'=>$data,'error'=>[]], 200);
            }else{
                return response()->json(['status'=>false,'status_code'=>'401','message'=>'Product is out of stock','data'=>(object)[],'error'=>['Product is out of stock']], 200);
            }
            
        }else{
            return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid Product Id','data'=>(object)[],'error'=>['Invalid Product Id']], 200);
        }

    }
    public function toggle_wishlist(Request $request){
        $user_id = $request->input('user_id');
        $product_id = $request->input('product_id');

        $user = Users_Model::where('id',$user_id)->first();
        if($user != null){
            $product = ProductsModel::with('category')->has('category')->where('id',$product_id)->where('product_stock_qty','>','0')->first();

            if($product != null){
                $wishlist = WishlistModel::has('product')->has('user')->where('user_id',$user_id)->where('product_id',$product_id)->first();
                if($wishlist != null){
                    WishlistModel::where('id',$wishlist->id)->delete();

                    return response()->json(['status'=>true,'status_code'=>'200','message'=>'Product Removed From Wishlist Successfully','error'=>[]], 200);
                }else{
                    WishlistModel::create(['product_id'=>$product_id,'user_id'=>$user_id]);

                    return response()->json(['status'=>true,'status_code'=>'200','message'=>'Product Added To Wishlist Successfully','error'=>[]], 200);
                }

            }else{
                return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid Product Id','error'=>['Invalid Product Id']], 200);
            }

        }else{
            return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid User Id','error'=>['Invalid User Id']], 200);
        }

    }
    public function get_user_wishlist(Request $request){
        $user_id = $request->input('user_id');
        $user = Users_Model::where('id',$user_id)->first();
        if($user != null){
            $wishlists = WishlistModel::has('product')->has('user')->where('user_id',$user_id)->get();
            if(count($wishlists) > 0){
                $products_arr = array();
                foreach($wishlists as $wishlist){
                    $arr = array(
                            "product_id"=>$wishlist->product->id,
                            "product_name"=>$wishlist->product->product_name,
                            "product_image"=>$wishlist->product->product_image_url,
                            "product_link"=>$wishlist->product->product_link,
                            "product_price"=>$wishlist->product->product_price,
                            "product_category"=>$wishlist->product->category->category_name,
                    );
                    $products_arr[] = $arr;
                }
                
                return response()->json(['status'=>true,'status_code'=>'200','message'=>'success','error'=>[],'data'=>$products_arr], 200);
            }else{
                return response()->json(['status'=>false,'status_code'=>'401','message'=>'No Wishlist Product Found','error'=>['No Wishlist Product Found'],'data'=>[]], 200);
            }
        }else{
            return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid User Id','error'=>['Invalid User Id'],'data'=>[]], 200);
        }

    }
    public function get_categories(Request $request){
        $categories = CategoryModel::get();

        if(count($categories) > 0){
            return response()->json(['status'=>true,'status_code'=>'200','message'=>'success','error'=>[],'data'=>$categories], 200);
        }else{
            return response()->json(['status'=>false,'status_code'=>'401','message'=>'No Category Found','error'=>['No Category Found'],'data'=>[]], 200);
        }


    }




}
