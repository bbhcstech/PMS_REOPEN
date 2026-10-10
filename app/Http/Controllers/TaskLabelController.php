<?php

namespace App\Http\Controllers;
use App\Models\TaskLabel;

use Illuminate\Http\Request;

class TaskLabelController extends Controller
{
   public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:191',
            'color' => 'required|string|max:20',
            'project_id' => 'nullable|integer|exists:projects,id',
            'description' => 'nullable|string|max:255',
        ]);

        $label = TaskLabel::create([
            'label_name' => $request->name,
            'color' => $request->color,
            'project_id' => $request->project_id,
            'description' => $request->description,
        ]);
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Label added successfully.', 'label' => [
                'id' => $label->id, 'label_name' => $label->label_name, 'color' => $label->color,
                'description' => $label->description, 'project_name' => $label->project?->name,
                'delete_url' => route('labels.destroy', $label->id),
            ]], 201);
        }

        return redirect()->back()->with('success', 'Label added successfully.');
    }

    public function destroy($id)
    {
        TaskLabel::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Label deleted.');
    }
}
