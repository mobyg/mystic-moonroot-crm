@extends('layouts.app')

@section('content')
<div class="notes-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2><i class="fas fa-sticky-note"></i> Notes</h2>
    <button class="btn btn-success" onclick="openNoteModal()">
        <i class="fas fa-plus"></i> Add Note
    </button>
</div>

<div class="row">
    <div class="col-md-3">
        <div class="dashboard-card" style="height: auto; margin-bottom: 20px;">
            <div class="card-header">Categories</div>
            <div class="card-content" style="padding: 15px; height: auto;">
                <div class="list-group list-group-flush">
                    <a href="javascript:void(0)" class="list-group-item list-group-item-action active" 
                       onclick="filterNotes('all')">
                        <i class="fas fa-list"></i> All Notes <span class="badge badge-primary">{{ $notes->count() }}</span>
                    </a>
                    @foreach($categories as $category)
                    <a href="javascript:void(0)" class="list-group-item list-group-item-action" 
                       onclick="filterNotes({{ $category->id }})">
                        <i class="fas fa-folder"></i> {{ $category->name }}
                        <span class="badge badge-secondary">{{ $category->notes->count() }}</span>
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-9">
        <div class="notes-grid" id="notes-container">
            @include('notes.grid')
        </div>
    </div>
</div>

<!-- Note Modal -->
<div class="modal fade" id="noteModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="noteModalTitle">Add Note</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="noteForm">
                <div class="modal-body">
                    <input type="hidden" id="note-id" name="note_id">
                    
                    <div class="form-group">
                        <label for="note-title">Title *</label>
                        <input type="text" class="form-control" id="note-title" name="title" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="note-category">Category *</label>
                        <select class="form-control" id="note-category" name="category_id" required onchange="toggleNewCategory()">
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                            <option value="new">+ Add New Category</option>
                        </select>
                    </div>
                    
                    <div class="form-group" id="new-category-group" style="display: none;">
                        <label for="new-category">New Category Name</label>
                        <input type="text" class="form-control" id="new-category" name="new_category">
                    </div>
                    
                    <div class="form-group">
                        <label for="note-content">Content *</label>
                        <textarea class="form-control" id="note-content" name="content" rows="8" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" id="delete-note-btn" onclick="deleteNote()" style="display: none; margin-right: auto;">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Save Note</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
.notes-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
}

.note-card {
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 20px;
    cursor: pointer;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    height: fit-content;
}

.note-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.note-title {
    font-weight: bold;
    font-size: 1.1rem;
    margin-bottom: 10px;
    color: var(--text-primary);
}

.note-category {
    display: inline-block;
    background: #6f42c1;
    color: white;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 0.8rem;
    margin-bottom: 10px;
}

.note-content {
    color: var(--text-secondary);
    line-height: 1.4;
    margin-bottom: 10px;
    max-height: 150px;
    overflow: hidden;
    text-overflow: ellipsis;
}

.note-date {
    font-size: 0.8rem;
    color: var(--text-secondary);
    border-top: 1px solid var(--border-color);
    padding-top: 10px;
}

.list-group-item.active {
    background-color: #6f42c1 !important;
    border-color: #6f42c1 !important;
}
</style>
@endsection

@section('scripts')
<script>
let currentNoteId = null;
let currentFilter = 'all';

function openNoteModal(note = null) {
    const modal = $('#noteModal');
    const form = document.getElementById('noteForm');
    
    form.reset();
    document.getElementById('new-category-group').style.display = 'none';
    
    if (note) {
        currentNoteId = note.id;
        document.getElementById('noteModalTitle').textContent = 'Edit Note';
        document.getElementById('note-id').value = note.id;
        document.getElementById('note-title').value = note.title;
        document.getElementById('note-content').value = note.content;
        document.getElementById('note-category').value = note.category_id;
        document.getElementById('delete-note-btn').style.display = 'inline-block';
    } else {
        currentNoteId = null;
        document.getElementById('noteModalTitle').textContent = 'Add Note';
        document.getElementById('delete-note-btn').style.display = 'none';
    }
    
    modal.modal('show');
}

function toggleNewCategory() {
    const select = document.getElementById('note-category');
    const newGroup = document.getElementById('new-category-group');
    
    if (select.value === 'new') {
        newGroup.style.display = 'block';
        document.getElementById('new-category').required = true;
    } else {
        newGroup.style.display = 'none';
        document.getElementById('new-category').required = false;
    }
}

function filterNotes(categoryId) {
    currentFilter = categoryId;
    
    // Update active state
    document.querySelectorAll('.list-group-item').forEach(item => {
        item.classList.remove('active');
    });
    event.target.classList.add('active');
    
    // Filter notes
    const notes = document.querySelectorAll('.note-card');
    notes.forEach(note => {
        if (categoryId === 'all' || note.dataset.categoryId == categoryId) {
            note.style.display = 'block';
        } else {
            note.style.display = 'none';
        }
    });
}

function deleteNote() {
    if (!currentNoteId) return;
    
    if (confirm('Are you sure you want to delete this note?')) {
        fetch(`/notes/${currentNoteId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                $('#noteModal').modal('hide');
                location.reload();
            } else {
                alert('Failed to delete note');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to delete note');
        });
    }
}

// Handle form submission
document.getElementById('noteForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    data._token = '{{ csrf_token() }}';
    
    const url = currentNoteId ? `/notes/${currentNoteId}` : '/notes';
    const method = currentNoteId ? 'PUT' : 'POST';
    
    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            $('#noteModal').modal('hide');
            location.reload();
        } else {
            alert('Failed to save note');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to save note');
    });
});

// Make notes clickable
function editNote(noteElement) {
    const noteData = {
        id: noteElement.dataset.noteId,
        title: noteElement.dataset.noteTitle,
        content: noteElement.dataset.noteContent,
        category_id: noteElement.dataset.categoryId
    };
    
    openNoteModal(noteData);
}
</script>
@endsection