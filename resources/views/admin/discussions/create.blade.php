@extends('admin.layout.app')

@section('content')
<main id="main" class="main">
    <div class="container-fluid">
    
     <br>
        {{-- Standardized Project Header & 13-Tab Navigation --}}
        @include('admin.projects.partials.header', [
            'project' => $project,
            'activeTab' => 'discussions'
        ])
  
        <h4>Create Discussion for {{ $project->name }}</h4>

        <form method="POST" action="{{ route('projects.discussions.store', $project->id) }}" enctype="multipart/form-data">
            @csrf

            <div class="mb-3">
            <label class="form-label">Category <span class="text-danger">*</span></label>
            <select name="discussion_category_id" class="form-control selectpicker" data-live-search="true" required>
                <option value="">Select</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}"
                        data-content="<span class='badge px-3 py-2' style='background-color: {{ $category->color }}'>{{ $category->name }}</span>">
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>





            <div class="mb-3">
                <label class="form-label">Title <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Reply <span class="text-danger">*</span></label>
                <textarea name="reply" class="form-control" rows="4" required></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Add File</label>
                <input type="file" name="file" class="form-control">
            </div>

            <button class="btn btn-success">Save</button>
            <a href="{{ route('projects.discussions.index', $project->id) }}" class="btn btn-secondary">Cancel</a>
        </form>
        
    
        

    </div>
</main>
@endsection

@push('js')


<script>
    document.getElementById('toggle-more').addEventListener('click', function(e) {
        e.preventDefault();
        const moreTabs = document.getElementById('more-tabs');
        if (moreTabs.classList.contains('d-none')) {
            moreTabs.classList.remove('d-none');
            this.innerHTML = 'Less ▴';
        } else {
            moreTabs.classList.add('d-none');
            this.innerHTML = 'More ▾';
        }
    });
</script>
@endpush
