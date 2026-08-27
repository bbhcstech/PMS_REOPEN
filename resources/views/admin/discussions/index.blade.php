@extends('admin.layout.app')

@section('content')
<main id="main" class="main">
    <div class="container">
        
         <br>
    {{-- Standardized Project Header & 13-Tab Navigation --}}
    @include('admin.projects.partials.header', [
        'project' => $project,
        'activeTab' => 'discussions'
    ])
        <h4>Discussions for {{ $project->name }}</h4>
        <a href="{{ route('projects.discussions.create', $project->id) }}" class="btn btn-primary mb-3">+ New Discussion</a>
        
        <button class="btn btn-success mb-3 ms-2" data-bs-toggle="modal" data-bs-target="#addCategoryModal">+ Discussion Category</button>

       <table id="discussionTable" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Replies</th>
                    <th>Last Reply</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($discussions as $discussion)
                    <tr>
                        <td>{{ $discussion->title }}</td>
                        <td>{{ $discussion->replies->count() }}</td>
                       <td> {{ $discussion->last_reply_at? \Carbon\Carbon::parse($discussion->last_reply_at)->format('d M Y h:i A') : 'N/A' }}
                          </td>

                        <td>
                            <a href="{{ route('projects.discussions.show', [$project->id, $discussion->id]) }}" class="btn btn-sm btn-info">View</a>
                            <form action="{{ route('projects.discussions.destroy', [$project->id, $discussion->id]) }}" method="POST" style="display:inline;">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this discussion?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        
               <!-- Add Discussion Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg"> <!-- Wider modal -->
    <div class="modal-content bg-white">
      <div class="modal-header">
        <h5 class="modal-title" id="addCategoryModalLabel">Discussion Category</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <!-- Category Table First -->
      <div class="px-4 pt-3">
        <table class="table table-bordered">
          <thead>
            <tr>
              <th>#</th>
              <th>Category Name</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($categories as $index => $category)
              <tr>
                <td>{{ $index + 1 }}</td>
                <td>
                  <span class="badge" style="background-color: {{ $category->color }}">
                    {{ $category->name }}
                  </span>
                </td>
                <td>
                  <form action="{{ route('discussion-categories.destroy', $category->id) }}" method="POST" onsubmit="return confirm('Delete this category?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm btn-danger">Delete</button>
                  </form>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <!-- Category Add Form -->
      <form method="POST" action="{{ route('discussion-categories.store') }}">
        @csrf
        <div class="modal-body border-top">
          <div class="mb-3">
            <label class="form-label">Category Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" placeholder="Enter a category name" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Color Code <span class="text-danger">*</span></label>
            <input type="color" name="color" class="form-control form-control-color" value="#16813D" required>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">Save</button>
        </div>
      </form>

    </div>
  </div>
</div>

        
    </div>
</main>
@endsection

@push('js')

<script>
    document.querySelector('select[name="discussion_category_id"]').addEventListener('change', function () {
        const selectedOption = this.options[this.selectedIndex];
        const name = selectedOption.text;
        const color = selectedOption.text.match(/#(?:[0-9a-fA-F]{3}){1,2}/); // extract hex

        if (color) {
            document.getElementById('color-preview').innerHTML = `
                <span class="badge" style="background-color:${color[0]}">${name}</span>
            `;
        } else {
            document.getElementById('color-preview').innerHTML = '';
        }
    });
</script>
<script>
   
    $(document).ready(function () {
    $('#discussionTable').DataTable({
        dom: 'rftip',
        responsive: true,
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        language: {
                search: "_INPUT_",
                searchPlaceholder: "Search discussions..."
        }
    });
  });
</script>

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