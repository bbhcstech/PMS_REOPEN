                            {{-- VIEW FULL APPRAISAL CARD MODAL --}}
                            <div class="modal fade" id="viewAppraisalModal-{{ $appr->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header bg-purple text-white">
                                            <h5 class="modal-title text-white fw-bold"><i class="bx bx-award me-1"></i> Appraisal Summary: {{ $appr->employee?->name }}</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body p-4">
                                            <div class="d-flex justify-content-between align-items-center mb-4">
                                                <div>
                                                    <h4 class="fw-bold text-dark mb-0">{{ $appr->employee?->name }}</h4>
                                                    <small class="text-muted">{{ $appr->employee?->designation ?? 'Staff Member' }} | Period: <strong>{{ $appr->appraisal_period }}</strong></small>
                                                </div>
                                                <div class="text-end">
                                                    <span class="{{ $appr->grade_badge }}">Grade {{ $appr->overall_grade }}</span>
                                                    <small class="d-block mt-2 text-muted">{{ ucfirst($appr->status ?? 'approved') }} ? Recommended salary increase: {{ $appr->recommended_increment_percent === null ? 'Not assessed' : number_format($appr->recommended_increment_percent, 2).'%' }}</small>
                                                    <div class="fw-bold text-dark mt-1">Final Score: {{ number_format($appr->overall_score, 1) }} / 100</div>
                                                </div>
                                            </div>

                                            <div class="row g-3 mb-4">
                                                <div class="col-md-4">
                                                    <div class="p-3 bg-light rounded border border-primary">
                                                        <small class="text-primary d-block fw-bold uppercase">1. Work delivery ({{ $appr->policy_snapshot['work_weight'] ?? 40 }}%)</small>
                                                        <div class="fs-4 fw-extrabold text-dark mt-1">{{ number_format($appr->project_score, 1) }} / 100</div>
                                                        <small class="text-muted d-block mt-1">Projects: {{ $appr->projects_count }} | Tasks Done: {{ $appr->completed_tasks }}</small>
                                                        <small class="text-secondary d-block mt-2 font-italic">"{{ $appr->project_remarks }}"</small>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="p-3 bg-light rounded border border-success">
                                                        <small class="text-success d-block fw-bold uppercase">2. Attendance ({{ $appr->policy_snapshot['attendance_weight'] ?? 30 }}%)</small>
                                                        <div class="fs-4 fw-extrabold text-dark mt-1">{{ number_format($appr->attendance_score, 1) }} / 100</div>
                                                        <small class="text-muted d-block mt-1">Present: {{ $appr->present_days }} / {{ $appr->total_working_days }} Days ({{ number_format($appr->attendance_percentage, 1) }}%)</small>
                                                        <small class="text-secondary d-block mt-2 font-italic">"{{ $appr->attendance_remarks }}"</small>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="p-3 bg-light rounded border border-warning">
                                                        <small class="text-warning d-block fw-bold uppercase">3. Cultural behaviour ({{ $appr->policy_snapshot['behaviour_weight'] ?? 30 }}%)</small>
                                                        <div class="fs-4 fw-extrabold text-dark mt-1">{{ number_format($appr->behaviour_score, 1) }} / 100</div>
                                                        <small class="text-muted d-block mt-1">Teamwork: {{ $appr->teamwork_score }}/10 | Communication: {{ $appr->communication_score }}/10 | Conduct: {{ $appr->punctuality_score }}/10</small>
                                                        <small class="text-secondary d-block mt-2 font-italic">"{{ $appr->behaviour_remarks }}"</small>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="p-3 bg-purple-soft rounded border border-purple">
                                                <small class="text-purple fw-bold uppercase d-block">Official Evaluation Recommendation</small>
                                                <h5 class="fw-extrabold text-purple mb-0 mt-1">{{ $appr->recommendation }}</h5>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if($canEdit && in_array($appr->id, $editableAppraisalIds, true))
                            {{-- EDIT APPRAISAL MODAL --}}
                            <div class="modal fade" id="editAppraisalModal-{{ $appr->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                    <div class="modal-content">
                                        <form method="POST" action="{{ route('appraisal.update', $appr->id) }}">
                                            @method('PUT')
                                            @csrf
                                            <input type="hidden" name="employee_id" value="{{ $appr->employee_id }}">
                                            <input type="hidden" name="appraisal_period" value="{{ $appr->appraisal_period }}">
                                            <div class="modal-header bg-purple text-white">
                                                <h5 class="modal-title text-white fw-bold"><i class="bx bx-edit-alt me-1"></i> Edit Appraisal: {{ $appr->employee?->name }}</h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body p-4">
                                                <div class="row g-3">
                                                    <div class="col-md-4">
                                                        <label class="form-label fw-bold text-primary">Project Work Score (0-100)</label>
                                                        <input type="number" name="project_score" class="form-control" value="{{ $appr->project_score }}" step="0.1" min="0" max="100" required>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label fw-bold text-success">Attendance Score (0-100)</label>
                                                        <input type="number" name="attendance_score" class="form-control" value="{{ $appr->attendance_score }}" step="0.1" min="0" max="100" required>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label fw-bold text-warning">Teamwork Rating (1-10)</label>
                                                        <input type="number" name="teamwork_score" class="form-control" value="{{ $appr->teamwork_score ?: '' }}" step="0.1" min="1" max="10" required>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <label class="form-label fw-bold text-warning">Communication Rating (1-10)</label>
                                                        <input type="number" name="communication_score" class="form-control" value="{{ $appr->communication_score ?: '' }}" step="0.1" min="1" max="10" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-bold text-warning">Professional conduct (1-10)</label>
                                                        <input type="number" name="punctuality_score" class="form-control" value="{{ $appr->punctuality_score ?: '' }}" step="0.1" min="1" max="10" required>
                                                    </div>

                                                    <div class="col-12">
                                                        <label class="form-label fw-bold">Project Work Remarks</label>
                                                        <textarea name="project_remarks" class="form-control" rows="2">{{ $appr->project_remarks }}</textarea>
                                                    </div>

                                                    <div class="col-12">
                                                        <label class="form-label fw-bold">Attendance evidence</label>
                                                        <textarea name="attendance_remarks" class="form-control" rows="2">{{ $appr->attendance_remarks }}</textarea>
                                                        <small class="text-muted">Required if you adjust the score away from recorded attendance.</small>
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="form-label fw-bold">Cultural behaviour evidence</label>
                                                        <textarea name="behaviour_remarks" class="form-control" rows="2" required>{{ $appr->behaviour_remarks }}</textarea>
                                                    </div>

                                                    <div class="col-12">
                                                        <label class="form-label fw-bold">HR Recommendation</label>
                                                        <select name="recommendation" class="form-select">
                                                            <option value="Promote & Merit Increment" {{ $appr->recommendation == 'Promote & Merit Increment' ? 'selected' : '' }}>Promote & Merit Increment</option>
                                                            <option value="Salary Increment & Role Growth" {{ $appr->recommendation == 'Salary Increment & Role Growth' ? 'selected' : '' }}>Salary Increment & Role Growth</option>
                                                            <option value="Standard Bonus & Continuation" {{ $appr->recommendation == 'Standard Bonus & Continuation' ? 'selected' : '' }}>Standard Bonus & Continuation</option>
                                                            <option value="Role Maintenance & Skill Mentoring" {{ $appr->recommendation == 'Role Maintenance & Skill Mentoring' ? 'selected' : '' }}>Role Maintenance & Skill Mentoring</option>
                                                            <option value="Performance Improvement Plan (PIP)" {{ $appr->recommendation == 'Performance Improvement Plan (PIP)' ? 'selected' : '' }}>Performance Improvement Plan (PIP)</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-purple-primary"><i class="bx bx-check-double me-1"></i> {{ \App\Services\WorkforceAccess::isAdmin(auth()->user()) ? 'Approve appraisal' : ($settings['admin_approval_required'] === '1' ? 'Submit for admin approval' : 'Finalize appraisal') }}</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endif
