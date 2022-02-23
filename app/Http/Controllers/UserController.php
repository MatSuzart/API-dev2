<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Iluminate\Http\Facedes\Auth;

use App\UserFavorite;
use App\Barber;
use App\UserAppointment;
use App\BarberServices;


class UserController extends Controller
{
    private $loggedUser;

    public function __construct(){
        $this->middleware('auth:api');
        $this->loggedUser = auth()->user();

    }


    public function read(){
        $array = ['error'=>''];

        $info = $this->$loggedUser;
        $info['avatar'] = url('media/avatars/'.$info['avatar']);

        $array['data'] = $info;

        return $array;
    }

    public function toggleFavotire(Request $request){
        $array = ['error'=> ''];
        $id_barber = $request->input('barber');

        $barber = Barber::find($id_barber);

        if($barber){
          $hasFav = UserFavorite::select()
          ->where('id_user', $this->loggedUser->id)
          ->where('id_barber', $id_barber)->frist();

            if($fav){
                $fav->delete();
                $array['have'] = false;
            }else {

                $newFav = new UserFavorite();
                $newFav->id_user = $this->loggedUser->id;
                $newFav->id_barber = $id_barber;
                $newFav->save();
                $array['have'] = true;
            }
        }else{
            $array['error'] = 'BARBEIRO INEXISTENTE';
        }

    }

    public function getFavorite(){
        $array = ['error'=>'', 'list'=>[]];

        $favs = UserFavorite::select()
        ->where('id_user', $this->loggedUser->id);

        if($favs){
            foreach($favs as $fav){

                $barber = Barber::find($fav['id_barber']);
                $barber['avatar'] = url('media/avatars/'.$barber['avatars']);
                $array['list'][] = $barber;
            }
        }

        return $array;
    }

    public function getAppointment(){
        $array= ['error'=>'','list'=>[] ];

        $appointments = UserAppointment::select()
        ->where('id_user', $this->loggedUser->id)
        ->orderBy('ap_datetime', 'DESC')->get();

        if($apps){
            foreach($apps as $app){

                $barber = Barber::find($app['id_barber']);
                $barber['avatar']=url('media/avatars/'.$barber['avatar']);

                $serivce = BarberService::find($app['id_service']);

                $array['list'][] =[
                    'id'=>$app['id'],
                    'datetime'=>$datetime['ap_datetime'],
                    'barber'=> $barber,
                    'service'=> $service
                ];
            }
        }

        return $array;
    }

}
