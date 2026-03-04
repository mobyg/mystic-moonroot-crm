@forelse($notes as $note)
<div class="note-card" 
     data-note-id="{{ $note->id }}"
     data-note-title="{{ $note->title }}"
     data-note-content="{{ $note->content }}"
     data-category-id="{{ $note->category_id }}"
     onclick="editNote(this)">
    
    <div class="note-title">{{ $note->title }}</div>
    
    <div class="note-category">{{ $note->category->name }}</div>
    
    <div class="note-content">
        {{ Str::limit($note->content, 200) }}
    </div>
    
    <div class="note-date">
        <i class="fas fa-clock"></i> {{ $note->updated_at->diffForHumans() }}
    </div>
</div>
@empty
<div class="col-12 text-center py-5">
    <i class="fas fa-sticky-note fa-3x text-muted mb-3"></i>
    <h5 class="text-muted">No notes yet</h5>
    <p class="text-muted">Create your first note to get started!</p>
</div>
@endforelse