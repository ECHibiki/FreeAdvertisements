<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
class RedirectController extends Controller
{
    public function RedirectSiteRequest(Request $request){
        $site_query = "s";
        $site = $request->input($site_query);
        $filename_query = "f";
        $pkey = 'public/image/' . $request->input($filename_query);
        if($site == NULL){
          return response("Non existing URL", 404);
        }
        $existing = DB::table('ads')->where('uri','=',$pkey)->select('clicks')->first();
        if($existing == NULL){
          return response("Non existing URI", 404);
        }

        DB::table('ads')->where('uri','=',$pkey)->update(['clicks' =>  $existing->clicks + 1]);
        return redirect($site);
    }
}
