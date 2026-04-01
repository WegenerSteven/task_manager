<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskStatusRequest;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $query = Task::query();

        if ($request->filled('status') && in_array($request->status, ['pending', 'in_progress', 'done'], true)) {
            $query->where('status', $request->status);
        }

        //  sort: priority high->low, then due_date asc
        $priorityOrder = [
            'high' => 1,
            'medium' => 2,
            'low' => 3,
        ];

        $tasks = $query
            ->orderByRaw('FIELD(priority, "high", "medium", "low")')
            ->orderBy('due_date', 'asc')
            ->get();

        if ($tasks->isEmpty()) {
            return response()->json([
                'message' => 'No tasks found.',
                'data' => [],
            ]);
        }

        return response()->json($tasks);
    }

    public function store(StoreTaskRequest $request)
    {
        $data = $request->validated();

        // check title + due_date uniqueness
        $exists = Task::where('title', $data['title'])
            ->where('due_date', $data['due_date'])
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'title' => 'A task with this title and due date already exists.',
            ]);
        }

        $tasks = Task::create($data);

        return response()->json($tasks, 201);

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    public function updateStatus(UpdateTaskStatusRequest $request, Task $task)
    {
        $newStatus = $request->status;

        // Define allowed transitions
        $allowed = [
            'pending' => ['in_progress'],
            'in_progress' => ['done'],
            'done' => [],
        ];

        $current = $task->status;

        if (! in_array($newStatus, $allowed[$current] ?? [])) {
            return response()->json([
                'message' => 'Invalid status transition.',
            ], 422);
        }

        $task->status = $newStatus;
        $task->save();

        return response()->json($task);
    }

    public function destroy(Task $task)
    {
        if ($task->status !== 'done') {
            return response()->json([
                'message' => 'Only tasks with status "done" can be deleted.',
            ], 403);
        }

        $task->delete();

        return response()->json(null, 204);
    }

    // GET /api/tasks/report?date=YYYY-MM-DD

    public function report(Request $request)
    {
        $request->validate([
            'date' => 'required|date_format:Y-m-d',
        ]);

        $date = $request->date;

        $tasks = Task::whereDate('due_date', $date)->get();

        $priorities = ['high', 'medium', 'low'];
        $statuses = ['pending', 'in_progress', 'done'];

        $summary = [];

        foreach ($priorities as $priority) {
            $summary[$priority] = [];
            foreach ($statuses as $status) {
                $summary[$priority][$status] = $tasks->where('priority', $priority)
                    ->where('status', $status)
                    ->count();
            }
        }

        return response()->json([
            'date' => $date,
            'summary' => $summary,
        ]);
    }
}
