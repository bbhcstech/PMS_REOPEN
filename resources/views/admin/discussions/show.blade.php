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
          <div class="card mb-4">
            <div class="card-body">
                <h4>{{ $discussion->title }}</h4>

                <p>
                    <strong>Category:</strong>
                    <span class="badge" style="background-color: {{ $discussion->category->color ?? '#6c757d' }}">
                        {{ $discussion->category->name ?? 'Uncategorized' }}
                    </span>
                </p>

                <p><strong>Posted by:</strong> {{ $discussion->user->name ?? 'Unknown' }}</p>
                <p><strong>Posted on:</strong> {{ $discussion->created_at->format('d M Y h:i A') }}</p>

                <hr>

                {{-- Attached Files --}}
                @php
                    $files = \App\Models\DiscussionFile::where('discussion_id', $discussion->id)->get();
                @endphp

                @if($files->count())
                    <div class="mb-3">
                        <strong>Attached Files:</strong>
                        <ul>
                            @foreach($files as $file)
                                <li>
                                    <a href="{{ asset($file->hashname ? 'admin/uploads/discussion-files/' . $file->hashname : '#') }}" target="_blank">
                                        {{ $file->filename }}
                                    </a>
                                    <span class="text-muted">({{ number_format($file->size / 1024, 2) }} KB)</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>

        {{-- Replies --}}
        <div class="card mb-4">
            <div class="card-header"><strong>Replies</strong></div>
            <div class="card-body">
                @php
                    $replies = \App\Models\DiscussionReply::where('discussion_id', $discussion->id)->latest()->get();
                @endphp

                @forelse($replies as $reply)
                    <div class="mb-3 border-bottom pb-2">
                        <p><strong>{{ $reply->user->name ?? 'Anonymous' }}</strong>
                            <small class="text-muted">on {{ $reply->created_at->format('d M Y h:i A') }}</small>
                        </p>
                        <p>{!! nl2br(e($reply->body)) !!}</p>
                    </div>
                @empty
                    <p class="text-muted">No replies yet.</p>
                @endforelse
            </div>
        </div>

        {{-- Add Reply Form --}}
        <div class="card">
            <div class="card-header"><strong>Post a Reply</strong></div>
            <div class="card-body">
                <form method="POST" action="{{ route('projects.discussions.replies.store', [$project->id, $discussion->id]) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Your Reply <span class="text-danger">*</span></label>
                        <textarea name="body" class="form-control" rows="4" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Attach File (optional)</label>
                        <input type="file" name="file" class="form-control">
                    </div>

                    <button type="submit" class="btn btn-primary">Submit Reply</button>
                </form>
            </div>
        </div>
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
