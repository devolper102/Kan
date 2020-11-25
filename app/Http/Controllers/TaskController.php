<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Task;

class TaskController extends Controller
{
 	public function index()
    {
        $tasks = Auth::user()->statuses()->with('tasks')->get();
        return view('tasks.index', compact('tasks'));
    }

    public function store(Request $request)
	{
	    $this->validate($request, [
	        'title' => ['required', 'string', 'max:56'],
	        'description' => ['required', 'string'],
	        'status_id' => ['required', 'exists:statuses,id']
	    ]);

	    return $request->user()
	        ->tasks()
	        ->create($request->only('title', 'description', 'status_id'));
	}

	public function sync(Request $request)
    {
        $this->validate(request(), [
            'columns' => ['required', 'array']
        ]);

        foreach ($request->columns as $status) {
            foreach ($status['tasks'] as $i => $task) {
                $order = $i + 1;
                if ($task['status_id'] !== $status['id'] || $task['order'] !== $order) {
                    request()->user()->tasks()
                        ->find($task['id'])
                        ->update(['status_id' => $status['id'], 'order' => $order]);
                }
            }
        }

        return $request->user()->statuses()->with('tasks')->get();
    }

    public function update(Request $request)
    {
   
    	 $request->user()
	        ->tasks()
	        ->find($request['status_id'])
            ->update(['title' => $request['title'], 'description' => $request['description']]);
        $task = Task::where('id',$request['status_id'])->get();
        return $task;
    }

    public function export()
    {
    	\Spatie\DbDumper\Databases\MySql::create()
                      ->setDbName('kabana')
                      ->setUserName('root')
                      ->setPassword('')
                      ->excludeTables('migrations')
                      ->dumpToFile('dump.sql');
      
           return true;
    }

}
