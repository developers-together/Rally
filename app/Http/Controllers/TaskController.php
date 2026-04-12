<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Inertia\Inertia;


class TaskController extends Controller
{
    // Display all team tasks
    public function index(TaskList $list)
    {
        Gate::authorize('viewAny', [Task::class, $list]);

        $tasks = $list->tasks()->get();
        return Inertia::render('tasks/task',['data' => $tasks]);
    }


    public function show(Task $task)
    {

        Gate::authorize('view',$task);

        // return $task->toJson();
        // if($user->teams->findOrFail($team) && $team->tasks->findOrFail($task->id))
        return Inertia::render('tasklists/task',['data' => $task]);
    }

     // Store a new task in the database
     public function store(Request $request, TaskList $list)
    {
        $team = $list->team;
         Gate::authorize('create',$list);

         $validated = $request->validate([
             'title' => 'required|string|max:255',
             'description' => 'nullable|string',
             // 'starred'=> 'nullable|boolean',
             // 'end' => 'nullable|date',
             // 'start' => 'nullable|date',
             'deadline' => 'nullable|date',
             'completed' => 'nullable|boolean',
             // 'category' => 'nullable|string|max:255',
             'priority' => 'sometimes|in:high,medium,low',
             // 'parent_task' => 'exists:tasks',

         ]);


         // Create the task with the team_id from route
         $task = Task::create([
             'title' => $validated['title'],
             'description' => $validated['description'] ?? null,
             // 'starred' => $validated['starred'] ?? false,
             // 'end' => $validated['end'] ?? null,
             // 'start' => $validated['start'] ?? null,
             'deadline' => $validated['deadline'] ?? null,
             // 'category' => $validated['category'] ?? null,
             'completed' => $validated['completed'] ?? false,
             'priority' => $validated['priority'] ?? 'medium',
             'task_list_id' => $list->id
         ]);

         return redirect('/dashboard');
     }


    // Update an existing task in the database
    public function update(Request $request, Task $task)
    {
        Gate::authorize('update', $task);

        $validated = $request->validate([
             'title' => 'required|string|max:255',
             'description' => 'nullable|string',
             'deadline' => 'nullable|date',
             'completed' => 'nullable|boolean',
             'priority' => 'sometimes|in:high,medium,low',
         ]);

        $task->update([
             'title' => $validated['title'],
             'description' => $validated['description'] ?? null,
             'deadline' => $validated['deadline'] ?? null,
             'completed' => $validated['completed'] ?? false,
             'priority' => $validated['priority'] ?? 'medium',
             'task_list_id' => $task->task_list_id,
        ]);

        return back()->with(['success' => 'task changed successfully']);
    }

    // Delete a task from the database
    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);

        $task->delete();

        return back()->with(['success' => 'task deleted successfully']);
    }
}
