@extends('admin.layout.app')
@section('title', 'Edit Award')

@section('content')
<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0 fw-bold">Edit Appreciation Template</h4>
        <a href="{{ route('awards.index') }}" class="btn btn-outline-primary btn-sm px-3 rounded-pill d-inline-flex align-items-center gap-2">
            <i class="bi bi-arrow-left"></i>
            <span>Back to Recognition</span>
        </a>
    </div>

    @if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
  @endif

  <form method="POST" action="{{ route('awards.appreciation-update', $award->id) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <!-- Title -->
    <div class="row mb-3">
      <div class="col-md-6">
        <label for="title">Title <sup class="text-danger">*</sup></label>
        <input type="text" 
               class="form-control" 
               id="title" 
               name="title" 
               value="{{ old('title', $award->title) }}" 
               placeholder="e.g. Employee of the Month" 
               required>
      </div>
      <div class="col-md-6">
        <label for="icon">Choose Icon <sup class="text-danger">*</sup></label>
        <select class="form-control" id="icon" name="icon" required>
          <option value="">-- Select --</option>
          <option value="fa-star" {{ old('icon', $award->icon) == 'fa-star' ? 'selected' : '' }}>⭐ Star</option>
          <option value="fa-trophy" {{ old('icon', $award->icon) == 'fa-trophy' ? 'selected' : '' }}>🏆 Trophy</option>
          <option value="fa-medal" {{ old('icon', $award->icon) == 'fa-medal' ? 'selected' : '' }}>🥇 Medal</option>
        </select>
      </div>
    </div>

    <!-- Color -->
    <div class="mb-3">
      <label for="color_code">Color Code <sup class="text-danger">*</sup></label>
      <input type="color" 
             class="form-control form-control-color" 
             id="color_code" 
             name="color_code" 
             value="{{ old('color_code', $award->color_code) }}" 
             required>
    </div>

    <!-- Summary -->
    <div class="mb-3">
      <label for="summary">Summary</label>
      <textarea class="form-control" id="summary" name="summary" rows="3">{{ old('summary', $award->summary) }}</textarea>
    </div>

    <button class="btn btn-primary">Update</button>
    <a href="{{ route('awards.index') }}" class="btn btn-secondary">Cancel</a>
  </form>

</div>
@endsection
