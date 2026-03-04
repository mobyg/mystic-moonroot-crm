<?php
// app/Http/Controllers/NoteController.php - Updated version

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Note;
use App\Models\NoteCategory;

class NotesController extends Controller
{
    public function index()
    {
        $notes = Note::with('category')->where('user_id', auth()->id())->latest()->get();
        $categories = NoteCategory::withCount(['notes' => function($query) {
            $query->where('user_id', auth()->id());
        }])->get();
        
        return view('notes.index', compact('notes', 'categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category_id' => 'required',
            'new_category' => 'nullable|string|max:255'
        ]);

        // Handle new category creation
        if ($request->category_id === 'new' && $request->new_category) {
            $category = NoteCategory::create(['name' => $request->new_category]);
            $categoryId = $category->id;
        } else {
            $categoryId = $request->category_id;
        }

        Note::create([
            'title' => $request->title,
            'content' => $request->content,
            'category_id' => $categoryId,
            'user_id' => auth()->id()
        ]);

        return response()->json(['success' => true]);
    }

    public function update(Request $request, Note $note)
    {
        $this->authorize('update', $note);

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category_id' => 'required'
        ]);

        $note->update($request->only(['title', 'content', 'category_id']));

        return response()->json(['success' => true]);
    }

    public function destroy(Note $note)
    {
        $this->authorize('delete', $note);
        
        $note->delete();

        return response()->json(['success' => true]);
    }
}