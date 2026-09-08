<!-- How It Works / Timesheet Lifecycle Modal -->
<div class="modal fade" id="howItWorksModal" tabindex="-1" aria-labelledby="howItWorksLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
      
      <style>
        #howItWorksModal .modal-header {
          display: flex !important;
          align-items: center !important;
          justify-content: space-between !important;
          padding: 1.25rem 1.5rem !important;
        }
        #howItWorksModal .btn-close {
          position: static !important;
          margin: 0 !important;
          width: 1.15rem !important;
          height: 1.15rem !important;
          padding: 0 !important;
          background-color: transparent !important;
          border: none !important;
          border-radius: 0 !important;
          box-shadow: none !important;
          outline: none !important;
          opacity: 0.85 !important;
          filter: grayscale(100%) brightness(0) !important;
          transition: opacity 0.15s ease !important;
          transform: none !important;
        }
        #howItWorksModal .btn-close:hover {
          background: transparent !important;
          background-color: transparent !important;
          opacity: 1 !important;
          filter: grayscale(100%) brightness(0) !important;
          transform: none !important;
          box-shadow: none !important;
        }
        #howItWorksModal .btn-close:focus {
          background: transparent !important;
          background-color: transparent !important;
          box-shadow: none !important;
          outline: none !important;
          transform: none !important;
        }
      </style>

      <div class="modal-header bg-light border-bottom">
        <div class="d-flex align-items-center gap-3">
          <div class="p-2 rounded-3 bg-primary text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; flex-shrink: 0;">
            <i class="bi bi-clock-history fs-5"></i>
          </div>
          <div>
            <h5 class="modal-title fw-bold text-dark mb-0" id="howItWorksLabel">Timesheet Lifecycle & How It Works</h5>
            <small class="text-muted">A quick guide to tracking and managing time across all project tasks</small>
          </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4">
        <div class="row g-3">
          {{-- Step 1 --}}
          <div class="col-md-6">
            <div class="p-3 rounded-3 border bg-white h-100 shadow-sm">
              <div class="d-flex align-items-center gap-2 mb-2">
                <span class="badge bg-primary rounded-circle px-2 py-1">1</span>
                <h6 class="fw-bold text-dark mb-0">Task Creation & Assignment</h6>
              </div>
              <p class="small text-muted mb-0">
                Tasks are scheduled under projects with defined start dates, due dates, and estimated hours, assigned to specific employees.
              </p>
            </div>
          </div>

          {{-- Step 2 --}}
          <div class="col-md-6">
            <div class="p-3 rounded-3 border bg-white h-100 shadow-sm">
              <div class="d-flex align-items-center gap-2 mb-2">
                <span class="badge bg-success rounded-circle px-2 py-1">2</span>
                <h6 class="fw-bold text-dark mb-0">Live Timer & Time Logging</h6>
              </div>
              <p class="small text-muted mb-0">
                Employees start live timers from task details or use the <strong>Log Time</strong> button to record work durations and memos.
              </p>
            </div>
          </div>

          {{-- Step 3 --}}
          <div class="col-md-6">
            <div class="p-3 rounded-3 border bg-white h-100 shadow-sm">
              <div class="d-flex align-items-center gap-2 mb-2">
                <span class="badge bg-info text-dark rounded-circle px-2 py-1">3</span>
                <h6 class="fw-bold text-dark mb-0">Timesheet Aggregation</h6>
              </div>
              <p class="small text-muted mb-0">
                All tasks across all projects show total logged duration, recorded session counts, and progress status in real time.
              </p>
            </div>
          </div>

          {{-- Step 4 --}}
          <div class="col-md-6">
            <div class="p-3 rounded-3 border bg-white h-100 shadow-sm">
              <div class="d-flex align-items-center gap-2 mb-2">
                <span class="badge bg-warning text-dark rounded-circle px-2 py-1">4</span>
                <h6 class="fw-bold text-dark mb-0">Manager Review & Analytics</h6>
              </div>
              <p class="small text-muted mb-0">
                Admins and managers switch between <strong>Timesheet List</strong>, <strong>Calendar</strong>, and <strong>Employee Breakdown</strong> views to monitor team capacity.
              </p>
            </div>
          </div>
        </div>

        {{-- Quick Tips Box --}}
        <div class="mt-4 p-3 rounded-3 bg-light border">
          <h6 class="fw-bold text-dark small mb-2"><i class="bi bi-lightbulb text-warning me-1"></i> Quick Tips:</h6>
          <ul class="small text-muted mb-0 ps-3">
            <li>Use the <strong>Duration filter</strong> above to view logs for specific date ranges.</li>
            <li>Click on the <strong>Calendar icon</strong> to view time distributions in a monthly schedule.</li>
            <li>Click on the <strong>Employee icon</strong> to see hours grouped per team member.</li>
          </ul>
        </div>
      </div>

      <div class="modal-footer border-top bg-light px-4 py-2">
        <button type="button" class="btn btn-primary rounded-pill px-4" data-bs-dismiss="modal">Got It</button>
      </div>

    </div>
  </div>
</div>
