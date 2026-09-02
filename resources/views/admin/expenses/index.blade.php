@extends('admin.layout.app')

@section('content')
<div class="container">
    
    <br>
    {{-- Standardized Project Header & 13-Tab Navigation --}}
    @include('admin.projects.partials.header', [
        'project' => $project,
        'activeTab' => 'expenses'
    ])

       
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Expense List</h4>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
        + Add Expense
    </button>
   </div>



        <table id="expenseTable" class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Item</th>
                <th>Price</th>
                <th>Employee</th>
                <th>Project</th>
                <th>Purchase Date</th>
                <th>Bill</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($expenses as $expense)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $expense->item_name }}</td>
                <td>₹{{ number_format($expense->price, 2) }}</td>
                <td>{{ $expense->employee->name ?? '-' }}</td>
                <td>{{ $expense->project->name ?? '-' }}</td>
                <td>{{ \Carbon\Carbon::parse($expense->purchase_date)->format('d-m-Y') }}</td>
                <td>
                    @if($expense->bill)
                        <a href="{{ asset('/'.$expense->bill) }}" target="_blank">View</a>
                    @else
                        -
                    @endif
                </td>
                <td>
                <a href="{{ route('expenses.edit', [$expense->project_id, $expense->id]) }}" class="btn btn-sm btn-warning">Edit</a>
            
                <form action="{{ route('expenses.destroy', [$expense->project_id, $expense->id]) }}" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button onclick="return confirm('Delete this expense?')" class="btn btn-sm btn-danger">Delete</button>
                </form>
            </td>

            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Add Expense Modal -->
<div class="modal fade" id="addExpenseModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('expenses.store', ['project' => $projectId]) }}" method="POST" enctype="multipart/form-data" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Add Expense</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body row g-3">
                <div class="col-md-6">
                    <label>Item Name *</label>
                    <input type="text" name="item_name" class="form-control" required>
                </div>

                <div class="col-md-6">
                    <label>Currency</label>
                    <select name="currency" class="form-select">
                        <option value="">Select</option>
                        @if(isset($currency) && is_iterable($currency))
                            @foreach($currency as $cu)
                                <option value="{{ $cu->id ?? $cu }}">{{ $cu->currency_name ?? $cu }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>

                <div class="col-md-4">
                    <label>Exchange Rate *</label>
                    <input type="number" name="exchange_rate" step="0.01" class="form-control" required>
                </div>

                <div class="col-md-4">
                    <label>Price *</label>
                    <input type="number" name="price" step="0.01" class="form-control" required>
                </div>

                <div class="col-md-4">
                    <label>Purchase Date *</label>
                    <input type="date" name="purchase_date" class="form-control" required>
                </div>

                <div class="col-md-6">
                    <label>Employee</label>
                    <select name="employee_id" class="form-select">
                        <option value="">Select</option>
                        @if(isset($employees) && is_iterable($employees))
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>

                <div class="col-md-6">
                    <label>Project</label>
                    <input type="hidden" name="project_id" value="{{ $project->id }}">
                    <input type="text" class="form-control" value="{{ $project->name }}" readonly>
                </div>

                <div class="col-md-6">
                    <label>Expense Category</label>
                    <select name="category_id" class="form-select">
                        <option value="">Select</option>
                        @if(isset($categories) && is_iterable($categories))
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->category_name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>

                <div class="col-md-6">
                    <label>Purchased From</label>
                    <input type="text" name="purchased_from" class="form-control">
                </div>

                <div class="col-md-6">
                    <label>Bank Account</label>
                    <select name="bank_account_id" class="form-select">
                        <option value="">Select</option>
                        @if(isset($accounts) && is_iterable($accounts))
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}">{{ $acc->bank_name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>

                <div class="col-md-6">
                    <label>Bill (PDF/Image)</label>
                    <input type="file" name="bill" class="form-control">
                </div>

                <div class="col-12">
                    <label>Description</label>
                    <textarea name="description" class="form-control" rows="2"></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-primary">Save</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('js')
<script>
   
    $(document).ready(function () {
    $('#expenseTable').DataTable({
        dom: 'rftip',
        responsive: true,
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        language: {
                search: "_INPUT_",
                searchPlaceholder: "Search tickets..."
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
