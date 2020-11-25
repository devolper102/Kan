<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Task;
use App\Models\Status;
class StatusController extends Controller
{
     public function store(Request $request)
	{
		
	    $this->validate($request, [
	        'title' => ['required', 'string', 'max:56']
	    ]);
	    $slug = Str::slug($request['title'],'-');
	     $request->user()
	        ->statuses()
	        ->create(array_merge($request->only('title','order'),['slug' => $slug]));
	    return $request->user()->statuses()->with('tasks')->get();
	}

	public function delete($id)
	{
		$tasks = request()->user()
	        ->tasks()
	        ->where('status_id',$id)
	        ->delete();
	   $statuses = request()->user()
	        ->statuses()
	        ->where('id',$id)
	        ->delete();
		 return request()->user()->statuses()->with('tasks')->get();
	}
}
