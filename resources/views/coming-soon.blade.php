@extends('layouts.app')

@section('content')
<div style="display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 50vh; text-align: center;">
    <div style="font-size: 6rem; color: #6f42c1; margin-bottom: 2rem;">
        <i class="fas fa-magic"></i>
    </div>
    <h1 style="color: var(--text-primary); margin-bottom: 1rem;">{{ $title ?? 'Coming Soon' }}</h1>
    <p style="color: var(--text-secondary); font-size: 1.2rem; margin-bottom: 2rem;">
        We're working our magic to bring you something amazing!
    </p>
    <div style="background: linear-gradient(45deg, #6f42c1, #28a745); padding: 20px; border-radius: 10px; color: white;">
        <p style="margin: 0; font-style: italic;">
            "The universe is not only magical, it is also constantly conspiring to help us achieve our dreams."
        </p>
    </div>
</div>
@endsection