<?php

namespace App;
use \Log;
use \Session;

use Illuminate\Database\Eloquent\Model;


class Post extends Model
{
    //
    protected $fillable=['title','body','link','description','mainImage'];

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
    public function addComment($req)
    {
        Log::info('here2');
        Log::info($req);
        $comment='';
        if(request()->has('parent'))
        {
            Log::info('parent');
            Log::info($req["parent"]);
            $comment=$this->comments()->create([
                'body' => $req["body"],
                'publish' => 0,
                'post_id' => $this->id,
                'replyTo' =>$req["parent"]
            ]);
        }
        else
        {
            $comment=$this->comments()->create([
                'body' => $req["body"],
                'publish' => 0,
                'post_id' => $this->id
            ]);
        }
        Session::flash('message', 'Your Comment Will Publish After Inspection, Thanks.'); 
        Session::flash('alert-class', 'alert-success'); 
        define('API_KEY','661968560:AAG0Izgk-fabybDKqNegqYe8jC0mQMQ_eAE');
        function makeHTTPRequest($method,$datas=[])
        {
            $url = "https://api.telegram.org/bot".API_KEY."/".$method;
            $ch = curl_init();
            curl_setopt($ch,CURLOPT_URL,$url);
            curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
            curl_setopt($ch,CURLOPT_POSTFIELDS,http_build_query($datas));
            $res = curl_exec($ch);
            if(curl_error($ch))
            {
                var_dump(curl_error($ch));
            }else
            {
                return json_decode($res);
            }
        }  
        makeHTTPRequest('sendMessage',[
            'chat_id'=>110374168,
            'text'=>"📢✉️New Comment:\n\n<b>".$req["body"]."</b>",
            'parse_mode'=>'html',
            'reply_markup'=>json_encode([
                'inline_keyboard'=>[
                        [
                            ['text'=>"Approve",'callback_data'=>'1_'.$comment->id],
                            // ['text'=>"Delete",'callback_data'=>'0_'.$comment->id]
                        ]
                    ]
                ])
        ]);
    }
}
