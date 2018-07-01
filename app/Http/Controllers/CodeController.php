<?php

namespace App\Http\Controllers;
use App\Code;
use Illuminate\Http\Request;

class CodeController extends Controller
{
    //
    public function massCreate(Request $request) {
        $data= $request->json()->all();
        $codes=$data['codigos'];
        foreach ($codes as $code){
            $codigo=Code::where('cod_validacion', $code)->first();
            if(!is_null($codigo)){
                return response()->json(['msj' => "El codigo ya existe", "codigo"=> $codigo], 404);
                
            }else{
                $codi= new Code;
                $codi->cod_validacion= $code;
                $codi->save();
            }
        }
        return response()->json(['created' => true], 201);
    }

    public function validation(Request $request){
        $data= $request->json()->all();
        $codes=$data['codigo'];
        $codigo=Code::where('cod_validacion', $codes)->first();
        if(is_null($codigo)){
            return response()->json(['msj' => "El codigo no existe", "exists"=> false], 404);
        }else{
            return response()->json(['msj' => "El codigo  existe", "exists"=> true], 200);
        }
     }

    public function list(){
        $code=Code::select('id','cod_validacion')->get();
        return response()->json($code);
      } 
}
