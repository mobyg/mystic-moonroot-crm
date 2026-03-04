@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="dashboard-card">
            <div class="card-header">
                <h4><i class="fas fa-cog"></i> Settings</h4>
            </div>
            <div class="card-content" style="height: auto; padding: 30px;">
                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('settings') }}">
                    @csrf

                    <div class="form-group row">
                        <label class="col-md-4 col-form-label">Theme Mode</label>
                        <div class="col-md-8">
                            <div class="custom-control custom-switch">
                                <input type="hidden" name="theme_mode" value="light">
                                <input type="checkbox" class="custom-control-input" id="theme-switch" 
                                       name="theme_mode" value="dark" {{ auth()->user()->theme_mode === 'dark' ? 'checked' : '' }}
                                       onchange="this.form.submit()">
                                <label class="custom-control-label" for="theme-switch">
                                    <span id="theme-label">{{ auth()->user()->theme_mode === 'dark' ? 'Dark Mode' : 'Light Mode' }}</span>
                                </label>
                            </div>
                            <small class="form-text text-muted">Toggle between light and dark themes</small>
                        </div>
                    </div>

                    <hr>

                    <div class="form-group row">
                        <label for="event_redirect" class="col-md-4 col-form-label">After Event Update</label>
                        <div class="col-md-8">
                            <select class="form-control" id="event_redirect" name="event_redirect">
                                <option value="return_previous" {{ auth()->user()->event_redirect === 'return_previous' ? 'selected' : '' }}>
                                    Return to previous view
                                </option>
                                <option value="visit_updated" {{ auth()->user()->event_redirect === 'visit_updated' ? 'selected' : '' }}>
                                    Visit updated time/date of event
                                </option>
                            </select>
                            <small class="form-text text-muted">Choose where to navigate after updating calendar events</small>
                        </div>
                    </div>

                    <div class="form-group row mb-0">
                        <div class="col-md-8 offset-md-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Settings
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('theme-switch').addEventListener('change', function() {
    const label = document.getElementById('theme-label');
    label.textContent = this.checked ? 'Dark Mode' : 'Light Mode';
});
</script>
@endsection