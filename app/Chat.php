<?php

namespace App;

use App\Events\AudioSend;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    protected $fillable = ['receiver','sender','audio'];

    public function receiver(){
        return $this->hasOne(\App\User::class,'receiver');
    }
    public function sender(){
        return $this->hasOne(\App\User::class,'sender');
    }

    public function getAllByMeAnd($id){
        return
            $this
                ->orWhere(function ($query) use($id){
                         return $query->where('sender',\Auth::user()->id)
                    ->where('receiver',$id);
                })
                ->orWhere(function ($query) use($id){
                        return $query->where('sender',$id)
                    ->where('receiver',\Auth::user()->id);
                })
                ->latest()
                ->get();
    }

    public function saveAudio($audio, $receiver)
    {
        $name = uniqid(date('h-i-s')) . '.ogg';
        $save_path = public_path() . DIRECTORY_SEPARATOR . 'audios';
        $audio->move($save_path, $name);

        $chat = $this->create([
            'audio'=>$name,
            'sender'=>\Auth::user()->id,
            'receiver'=>$receiver
            ]);
        broadcast(new AudioSend($chat));

        event(new AudioSend('hello world'));
        return $chat;
    }
}
