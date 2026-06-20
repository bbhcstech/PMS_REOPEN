<!-- Project Category Modal -->
<div class="modal fade" id="catModal" tabindex="-1" aria-labelledby="catModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="catModalLabel">Add Project Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="addCatForm">
                @csrf
                <div class="modal-body">
                    <div id="cat-error" class="alert alert-danger d-none"></div>
                    <div class="mb-3">
                        <label for="category_name" class="form-label">Category Name <span class="text-danger">*</span></label>
                        <input type="text" name="category_name" id="category_name" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Client Modal -->
<div class="modal fade" id="clientModal" tabindex="-1" aria-labelledby="clientModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="clientModalLabel">Add Client</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="addClientForm">
                @csrf
                <div class="modal-body">
                    <div id="client-error" class="alert alert-danger d-none"></div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="client_name" class="form-label">Client Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="client_name" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="client_email" class="form-label">Email</label>
                            <input type="email" name="email" id="client_email" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="company_name" class="form-label">Company Name</label>
                            <input type="text" name="company_name" id="company_name" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="client_login_allowed" class="form-label">Login Allowed <span class="text-danger">*</span></label>
                            <select name="login_allowed" id="client_login_allowed" class="form-control" required>
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="client_password" class="form-label">Password</label>
                            <input type="password" name="password" id="client_password" class="form-control" minlength="9">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Client</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Employee Modal -->
<div class="modal fade" id="employeeModal" tabindex="-1" aria-labelledby="employeeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="employeeModalLabel">Add Employee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ route('employees.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Employee ID <span class="text-danger">*</span></label>
                            <input type="text" name="employee_id" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Employee Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" name="password" class="form-control" minlength="9" required>
                                <button type="button" class="btn btn-outline-secondary toggle-password" title="Show or hide password">
                                    <i class="fa fa-eye"></i>
                                </button>
                                <button type="button" class="btn btn-outline-secondary generate-password" title="Generate password">
                                    <i class="fa fa-random"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Designation <span class="text-danger">*</span></label>
                            <select name="designation_id" class="form-control" required>
                                <option value="">Select</option>
                                @foreach($designations as $designation)
                                    <option value="{{ $designation->id }}">{{ $designation->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Parent Department <span class="text-danger">*</span></label>
                            <select name="parent_dpt_id" class="form-control" required>
                                <option value="">Select</option>
                                @foreach($prtdepartments as $department)
                                    <option value="{{ $department->id }}">{{ $department->dpt_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Department</label>
                            <select name="department_id" class="form-control">
                                <option value="">Select</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->dpt_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Country <span class="text-danger">*</span></label>
                            <select name="country" class="form-control" required>
                                <option value="">Select</option>
                                @foreach($countries as $country)
                                    <option value="{{ $country->name }}">{{ $country->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Mobile <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" name="mobile_country_code" class="form-control" value="+91" style="max-width: 90px;" required>
                                <input type="text" name="mobile" class="form-control" pattern="[1-9][0-9]{9}" required>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Date of Birth <span class="text-danger">*</span></label>
                            <input type="date" name="dob" class="form-control" max="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Joining Date <span class="text-danger">*</span></label>
                            <input type="date" name="joining_date" class="form-control" value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Profile Picture <span class="text-danger">*</span></label>
                            <input type="file" name="profile_picture" class="form-control" accept="image/jpeg,image/png,image/jpg" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Government ID Card <span class="text-danger">*</span></label>
                            <input type="file" name="government_id_card" class="form-control" accept="image/jpeg,image/png,image/jpg" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Business Address <span class="text-danger">*</span></label>
                            <textarea name="business_address" class="form-control" required>Kolkata</textarea>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-control" required>
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Login Allowed</label>
                            <select name="login_allowed" class="form-control">
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Email Notifications</label>
                            <select name="email_notifications" class="form-control">
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Employee</button>
                </div>
            </form>
        </div>
    </div>
</div>
