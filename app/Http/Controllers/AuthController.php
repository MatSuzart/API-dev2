<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Iluminate\Http\Facedes\Auth;
use Iluminate\Http\Facedes\validator;
use App\User;

class AuthController extends Controller
{

    public function __construct(){
        $this->middleware('auth:api', ['except'=>['create','login','unauthorized'] ]);
    }



    public function create(Request $request){
        $array = ['error'=>''];

        $validator = Validator::make($request->all(), [
            'name'=> 'required',
            'email'=> 'required|email',
            'password'=> 'required'
        ]);

        if(!$validator->fails()){
            $name = $request->input('name');
            $email = $request->input('email');
            $password = $request->input('password');

            $emailExists = User::where('email', $email)->count();

            if($emailExists ===0){
                $hash = password_hash($password, PASSWORD_DEFAULT);

                $newUser = new User();
                $newUser->name = $name;
                $newUser->email = $email;
                $newUser->password = $hash;

                $newUser->save();

                $token = auth()->attempt([
                    'email'=>$email,
                    'password'=>$password
                ]);

                if(!$token){
                    $array['error'] = 'OCORREU UM ERRRO';
                    return $array;
                }

                $info = auth()->user();
                $info['avatar'] = url('media/avatars/'.$info['avatar']);
                $array['data'] = $info;
                $array['token'] = $token;

            }else {
                $array['error'] = 'EMAIL JÁ CADASTRADO';
                return $array;
            }
        }else {
            $array['error'] = 'DADOS INCORRETOS';
            return $array;
        }

        return $array;
    }

    public function login(Request $request){
        $array = ['error'=>''];

        $email = $request->input('email');
        $password = $request->input('password');

        $token = auth()->attempt([
            'email'=>$email,
            'password'=>$password
        ]);

        if(!$token){
            $array['error']= 'USUARIO OU SENHA INCORRETOS';
            return $array;
        }


        $info = auth()->user();
        $info['avatar'] = url('media/avatars/'.$info['avatar']);
        $array['data'] = $info;
        $array['token'] = $token;

        return $array;
    }

    public function logout(){
        auth()->logout();
        return ['error'=>''];
    }

    public function refresh(){
        $array = ['error'=>''];

        $token = auth()->refresh();

        $info = auth()->user();
        $info['avatar'] = url('media/avatars/'.$info['avatar']);
        $array['data'] = $info;
        $array['token'] = $token;

        return $array;
    }

    public function unauthorized(){
        return response()->json([
            'error'=>'NÃO AUTORIZADO'
        ], 401);
    }
}
