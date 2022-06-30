<?php

namespace App\Http\Controllers;

use App\Http\Requests\Subscribe;
use App\Models\Subscribe as ModelsSubscribe;


class SubscribeController extends Controller
{
    public function subScribe(Subscribe $request){
        $subscribe = new ModelsSubscribe();
        $subscribe->email = $request->input('email');
        $subscribe->save();

        return response()->json([
            'message' => "Thank you for your subscription",
        ]);
    }
}
