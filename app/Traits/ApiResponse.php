<?php

namespace App\Traits;

trait ApiResponse
{
    

public static function success($message=null,$result=null,$code=200){

return response()->json([
    'status'=>'success',
    'message'=>strtolower($message),
    'data'=>$result
],$code);
}
public static function error($message=null,$result=null,$code=404){

return response()->json([
    'status'=>"fail",
    'message'=>strtolower($message),
    'data'=>$result
],$code);
}
}
