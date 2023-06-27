<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use Illuminate\Support\Facades\DB;
class PostController extends Controller
{
    public function index(){
        $posts = DB::select('select * from posts');
        return view('posts', [
            'posts' => $posts
        ]);
    }
    public function show($id){
        $post = DB::select('select * from posts where id = ?', $id);

        return view('view-post', [
            'post' => $post
        ]);
    }
}
