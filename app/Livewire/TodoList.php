<?php

namespace App\Livewire;

use Exception;
use App\Models\Todo;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Rule;
use Illuminate\Support\Facades\Log;

class TodoList extends Component
{
    use WithPagination;

    #[Rule('required|min:2|max:50')]
    public $name;

    public $search;

    public $editingTodoID;

    #[Rule('required|min:2|max:50')]
    public $editingTodoName;

    public function create()
    {
        $validated = $this->validateOnly('name');

        Todo::create($validated);

        $this->reset('name');

        $this->resetPage();

        session()->flash('success', 'Task Created');
    }

    public function delete($todoID)
    {
        try {
            Todo::findOrFail($todoID)->delete();
        } catch (Exception $e) {
            // Log::error($e->getMessage());
            session()->flash('error', 'Faild to delete Todo!');
            return;
        }

        session()->flash('success', 'Task Deleted');
    }

    public function toggle($todoID)
    {
        $todo = Todo::find($todoID);
        $todo->completed = !$todo->completed;
        $todo->save();
    }

    public function edit($todoID)
    {
        $this->editingTodoID = $todoID;
        $this->editingTodoName = Todo::find($todoID)->name;
    }

    public function update()
    {
        $this->validateOnly('editingTodoName');

        Todo::find($this->editingTodoID)->update([
            'name' => $this->editingTodoName,
        ]);

        session()->flash('success', 'Task Updated');
        $this->cancelEdit();
    }

    public function cancelEdit()
    {
        $this->reset('editingTodoID', 'editingTodoName');
    }

    public function render()
    {
        return view('livewire.todo-list', [
            'todos' => Todo::latest()->where('name', 'like', "%{$this->search}%")->paginate(5),
        ]);
    }
}
