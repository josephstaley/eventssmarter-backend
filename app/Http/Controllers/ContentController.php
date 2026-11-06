<?php

namespace App\Http\Controllers;

use App\Models\Content;
use Illuminate\Http\Request;

class ContentController extends Controller
{
    function __construct()
    {
        $this->middleware('admin');
    }

    public function update(Content $content, Request $request)
    {
        $content->body = $request->body;
        $content->save();
    }
}
