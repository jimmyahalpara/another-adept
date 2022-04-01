<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Organization;
use App\Models\PriceType;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    public function index(Request $request)
    {       
        $num_rows = $request->input('num_rows', 10);
        

        $search_text = $request -> input('search_text', '');

        
        $areas = $request -> input('areas', []);
        $categories_filter = $request -> input('categories_filter', []);
        $price_types_filter = $request -> input('price_types_filter', []);
        $organization_filter = $request -> input('organization_filter', []);
        $min_price = $request -> input('min_price', '');
        $max_price = $request -> input('max_price', '');
        
        DB::enableQueryLog();
        $services = Service::select('*');


        if ($areas != []){
            $services = $services -> orWhereHas('areas', function ($query) use ($areas){
                $query -> whereIn('areas.id', $areas);
            });
        }
        
        if ($categories_filter != []){
            $services = $services -> whereIn('service_category_id', $categories_filter);
        }

        if ($organization_filter != []){
            $services = $services -> whereIn('organization_id', $organization_filter);
        }

        if ($price_types_filter != []){
            $services = $services -> whereIn('price_type_id', $price_types_filter);
        }

        if ($min_price != ''){
            $services = $services -> where('price', '>=', $min_price);
        }   

        if ($max_price != ''){
            $services = $services -> where('price', '<=', $max_price);
        }



        if ($search_text != ''){
            // $search_organization_list = Organization::where('name', 'LIKE', '%'.$search_text.'%') -> get() -> toArray();
            $search_categories_list = ServiceCategory::select('id') -> where('name', 'LIKE', '%'.$search_text.'%') -> get() -> toArray();
            dd($search_categories_list);
            if ($search_categories_list != []){
                $services = $services -> orWhereIn('service_category_id', $search_categories_list);
            } 
    
            // if ($search_organization_list != []){
            //     $services = $services -> orWhereIn('organization_id', $search_organization_list);
            // }
    
            $services = $services -> orWhere('name', 'LIKE', '%'.$search_text.'%');
            $services = $services -> orWhere('description', 'LIKE', '%'.$search_text.'%');
        }



        
        
        
        $services = $services -> sortable('id')->simplePaginate($num_rows)->withQueryString();
        dd(DB::getQueryLog());

        $cities = City::orderBy('name') -> get();
        $categories = ServiceCategory::orderBy('name') -> get();
        $price_types = PriceType::orderBy('name') -> get();
        $user = Auth::user();
        $organizations = Organization::orderBy('name') -> get();
        return view('search.index', compact(
            'user',
            'services',
            'num_rows',
            'cities',
            'search_text',
            'areas',
            'categories_filter',
            'categories',
            'price_types_filter',
            'price_types',
            'min_price',
            'max_price',
            'organizations',
            'organization_filter'
        ));
    }
}
