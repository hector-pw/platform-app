<?php

namespace App\Http\Controllers;

use App\AuthServer;
use App\Skin;
use App\User;
use Symfony\Component\HttpFoundation\Request;

class GameAPIController extends Controller
{
    public function usuuid($id){
        if(isset($id)) {
            $response = array(
                'id' => AuthServer::where('uuid',$id)->value('uuid'),
                'name' => user::where('id',AuthServer::where('uuid',$id)->value('id'))->value('name')
            );
            return response()->json($response);
        }
    }

    public function skinprehash($id){
        $response = array(
            'timestamp' => time(),
            'profileId' => AuthServer::where('uuid',$id)->value('uuid'),
            'profileName' => user::where('id',AuthServer::where('uuid',$id)->value('id'))->value('name')
        );
        if(Skin::where('id',AuthServer::where('uuid',$id)->value('id'))->value('cape') == '0000000000000000000000000000000f'){
            $response['textures'] = array(
                'SKIN' => array(
                    'url' => (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]/skins/".Skin::where('id',AuthServer::where('uuid',$id)->value('id'))->value('skin')));
        }else{
            $response['textures'] = array(
                'SKIN' => array(
                    'url' => (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]/skins/".Skin::where('id',AuthServer::where('uuid',$id)->value('id'))->value('skin')),
                'CAPE' => array(
                    'url' => (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]/capes/".Skin::where('id',AuthServer::where('uuid',$id)->value('id'))->value('cape'))
            );
        }
        return json_encode($response);
    }

    public function profile($id){
        $response = array(
            'id' => AuthServer::where('uuid',$id)->value('uuid'),
            'name' => user::where('id',AuthServer::where('uuid',$id)->value('id'))->value('name')
        );
        $response['properties'] = array(
            array('name' => 'textures',
                'value' => base64_encode(GameAPIController::skinprehash(AuthServer::where('uuid',$id)->value('uuid'))))
        );
        return response()->json($response);
    }

    public function joinclient(){
    $server = file_get_contents('php://input');
    $json = json_decode($server, true);
    if(isset($json['accessToken']) && isset($json['selectedProfile']) && isset($json['serverId'])) {
        AuthServer::where('access_token',$json['accessToken'])->update(['server' => $json['serverId']]);
        abort(204);
    }
    }
    public function joinserver(Request $request){
        if(isset($request->username) && isset($request->serverId)) {
            if($_GET['serverId'] == AuthServer::where('server',$request->serverId)->value('server')){
                $response = array(
                    'id' => AuthServer::where('server',$request->serverId)->value('uuid'),
                    'name' => user::where('id',AuthServer::where('server',$request->serverId)->value('id'))->value('name')
                );
                $response['properties'] = array(
                    array(
                        'signature' => '',
                        'name' => 'textures',
                        'value' => base64_encode(GameAPIController::skinprehash(AuthServer::where('server',$request->serverId)->value('uuid'))))
                );
                return response()->json($response);
            }else{
                echo $request->serverId;
                abort(204);
            }
        }
    }
}
