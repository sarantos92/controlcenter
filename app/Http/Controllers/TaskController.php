<?php

namespace App\Http\Controllers;

use App\Helpers\TaskStatus;
use App\Models\Task;
use App\Models\User;
use App\Rules\ValidTaskType;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

class TaskController extends Controller
{
    /**
     * Show the application task dashboard.
     */
    public function index(Authenticatable $user, string $activeFilter = null): View
    {
        $this->authorize('update', Task::class);

        if ($activeFilter == 'sent') {
            $tasks = Task::where('creator_user_id', $user->id)->get()->sortBy('created_at');
        } elseif ($activeFilter == 'archived') {
            $tasks = Task::where('assignee_user_id', $user->id)->whereIn('status', [TaskStatus::COMPLETED->value, TaskStatus::DECLINED->value])->get()->sortBy('created_at');
        } else {
            $tasks = Task::where('assignee_user_id', $user->id)->where('status', TaskStatus::PENDING->value)->with('creator', 'subject', 'assignee', 'subjectTraining')->get()->sortBy('created_at');
        }

        return view('tasks.index', compact('tasks', 'activeFilter'));
    }

    /**
     * Store a newly created task in storage.
     */
    public function store(Request $request, Authenticatable $user): RedirectResponse
    {

        $this->authorize('create', Task::class);

        $data = $request->validate([
            'type' => ['required', new ValidTaskType],
            'message' => 'sometimes|min:3|max:256',
            'subject_user_id' => 'required|exists:users,id',
            'subject_training_id' => 'required|exists:trainings,id',
            'assignee_user_id' => 'required|exists:users,id',
        ]);

        $data['creator_user_id'] = $user->id;
        $data['created_at'] = now();

        // Check if recipient is mentor or above
        $recipient = User::findOrFail($data['assignee_user_id']);

        // Policy check if recpient can recieve a task
        if ($recipient->can('receive', Task::class)) {
            // Create the model
            $task = Task::create($data);

            // Run the create method on the task type to trigger type specific actions on creation
            $task->type()->create($task);

            return redirect()->back()->with('success', 'Task created successfully.');
        }

        return redirect()->back()->withErrors('Recipient is not allowed to receive tasks.');

    }

    /**
     * Close the specified task with a given status.
     */
    protected function close(Task|int $task, TaskStatus $status): Task
    {
        $this->authorize('update', Task::class);
        $task = Task::findOrFail($task);
        $task->status = $status;
        $task->closed_at = now();
        $task->save();

        return $task;
    }

    /**
     * Complete the specified task.
     */
    public function complete(Request $request, Task|int $task): RedirectResponse
    {
        $completed = self::close($task, TaskStatus::COMPLETED);

        // Run the complete method on the task type to trigger type specific actions on completion
        $completed->type()->complete($completed);

        return redirect()->back()->with('success', sprintf('Completed task regarding %s from %s.', $completed->subject->name, $completed->creator->name));
    }

    /**
     * Decline the specified task.
     */
    public function decline(Request $request, Task|int $task): RedirectResponse
    {
        $declined = self::close($task, TaskStatus::DECLINED);

        // Run the decline method on the task type to trigger type specific actions on decline
        $declined->type()->decline($declined);

        return redirect()->back()->with('success', sprintf('Declined task regarding %s from %s.', $declined->subject->name, $declined->creator->name));
    }

    /**
     * Return the task type classes
     *
     * @return array
     */
    public static function getTypes()
    {
        // Specify the directory where your subclasses are located
        $subclassesDirectory = app_path('Tasks/Types');

        // Initialize an array to store the subclasses
        $subclasses = [];

        // Get all PHP files in the directory
        $files = File::files($subclassesDirectory);

        foreach ($files as $file) {
            // Get the class name from the file path
            $className = 'App\\Tasks\\Types\\' . pathinfo($file, PATHINFO_FILENAME);

            // Check if the class exists and is a subclass of Types
            if (class_exists($className) && is_subclass_of($className, 'App\\Tasks\\Types\\Types')) {
                $subclasses[] = new $className();
            }
        }

        return $subclasses;
    }

    /**
     * Check if a task type is valid
     *
     * @param  string  $type
     * @return bool
     */
    public static function isValidType($type)
    {
        $types = self::getTypes();

        foreach ($types as $taskType) {
            if ($taskType::class == $type) {
                return true;
            }
        }

        return false;
    }
}
