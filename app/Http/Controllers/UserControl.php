<?php

namespace App\Http\Controllers;
use Hash;
use App\User;
use App\Policy;
use JWTAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use JWTFactory;
class UserControl extends Controller{

  public function greetings(Request $request){
    return response()->json(['message' => 'Hi there this is IGF API'], 200);
  }

  public function create(Request $request){
    $data= $request->json()->all();
    $user = new User;
    $user->fullname=$data['fullname'];
    $user->email=$data['email'];
    $user->password=bcrypt($data['password']);
    $user->group_id=$data['group_id'];
    $user->save();
    $token= JWTAuth::fromUser($user);
    return response()->json(['saved' => true, 'token'=> compact('token')], 201);
  }

  public function show($id){
    $user= User::find($id);
    if (is_null($user)) {
      return response()->json(['msj' => "User not found"], 404);
    }else{
      return $user;
    }

  }

  public function list(){
    $users=User::select('id','fullname', 'email')->get();
    return response()->json($users);
  }

  public function update($id, Request $request){
    $data= $request->json()->all();
    $user=User::find($id);
    if (is_null($user)) {
      return response()->json(['msj' => "User not found"], 404);
    }else{
      $user->fullname= $data['fullname'];
      $user->email=$data['email'];
      $user->save();
      return response()->json(['updated' => true], 200);
    }
  }

  public function delete($id){
    $user=User::find($id);
    if (is_null($user)) {
      return response()->json(['msj' => "User not found"], 404);
    }else{
      $user->delete();
      return response()->json(['msj' => "User deleted"], 202);
    }
  }

  public function setPolicies($id, Request $request){
  $data= $request->json()->all();
  $user=User::find($id);
  $policy= new Policy;
  $policy->name=$data['name'];
  $policy->code=$data['code'];
  $user->policies()->save($policy);
  return $user->policies;
  }

  public function login(Request $request){
    $data= $request->json()->all();
    $credentials = Input::only('email', 'password');
    $jsonPolicies=array();
    $user=User::where('email',$data['email'])->first();
    if (is_null($user)) {
      return response()->json(['msj' => "User not found"], 404);
    }else{
      foreach ($user->policies as $policie) {
        $jsonPolicies[]=($policie->code);
      }
      $payloadable=$userLogged=[
        'fullname'=> $user->fullname,
        'email'=> $user->email,
        'policies'=>$jsonPolicies
      ];
        if ( ! $token = JWTAuth::attempt($credentials)) {
          return response()->json(['msj'=>"invalid credentials"], 400);
        }

        $PermissionController='App\Http\Controllers\PermissionController';
        $json=app($PermissionController)->validateAccounttant($user);
        $value=$json->getData()->value;


        if ($value==true) {
          $msj=$json->getData()->msj;
        }else {
          $msj=null;
        }

        $payload = JWTFactory::userData($payloadable)->make();
        $userToken = JWTAuth::encode($payload);
        return response()->json(['token'=> $userToken->get(),'user' => $userLogged, 'msj'=> $msj], 201);

    }
  }
}
