<?php

namespace App\Http\Controllers;

use App\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $posts=Post::latest()->get();
        return view('posts.index',compact('posts'));
        dd($posts);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        if (Auth::check() && Auth::user()->role=='admin') 
        {
            return view('posts.create');
        }
        else{
            return redirect('/login');
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $this->validate(request(),[
            'title'=>'required',
            'link'=>'required',
            'body'=>'required',
        ]);

 
        $dom = new \domdocument();
        $dom->loadHtml(mb_convert_encoding($request->body, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        $images = $dom->getelementsbytagname('img');
 
        foreach($images as $k => $img){
            $data = $img->getattribute('src');
            $filename=$img->getattribute('data-filename');
 
            list($type, $data) = explode(';', $data);
            list(, $data)      = explode(',', $data);
 
            $data = base64_decode($data);
            $image_name= time().'_'.$k.'_'.$filename;
            $path = public_path() .'/images/uploaded/'. $image_name;
 
            file_put_contents($path, $data);
 
            $img->removeattribute('src');
            $img->setattribute('src','/images/uploaded/'.$image_name);
        }
 
        $detail = $dom->savehtml($dom->documentElement);
        $post= new Post;
        $post->title=request('title');
        $post->link=request('link');
        $post->body=$detail;
        $post->save();
        return redirect('/posts/'.$post->link);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function show($post)
    {
        $post=Post::where('link',$post)->first();
        return view('posts.show',compact('post'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function edit($post)
    {
        if (Auth::check() && Auth::user()->role=='admin') 
        {
            $post=Post::where('link',$post)->first();
            return view('posts.edit',compact('post'));
        }
        else{
            return redirect('/login');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,$post)
    {
        //
        $this->validate(request(),[
            'title'=>'required',
            'link'=>'required',
            'body'=>'required',
        ]);

        libxml_use_internal_errors(true);
        $dom = new \domdocument();
        $dom->loadHtml(mb_convert_encoding($request->body, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        $images = $dom->getelementsbytagname('img');
        foreach($images as $k => $img){
            $data = $img->getattribute('src');
            $filename=$img->getattribute('data-filename');
            if(strpos($data, ';') !== false)
            {
                list($type, $data) = explode(';', $data);
                list(, $data)      = explode(',', $data);
            }
            else
                continue;
            $data = base64_decode($data);
            $image_name= time().'_'.$k.'_'.$filename;
            $path = public_path() .'/images/uploaded/'. $image_name;
 
            file_put_contents($path, $data);
 
            $img->removeattribute('src');
            $img->setattribute('src','/images/uploaded/'.$image_name);
        }
        
        $detail = $dom->savehtml($dom->documentElement);
        $post=Post::where('link',$post)->first();
        $post->title=request('title');
        $post->link=request('link');
        $post->body=$detail;
        $post->save();
        return redirect('/posts/'.$post->link);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function destroy(Post $post)
    {
        //
    }
}