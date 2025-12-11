@extends('layouts.app-master')

@push('css')
    <link rel="stylesheet" href="{{ asset('assets/css/custom-select-style.css') }}">
    <link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet" />
    <style>
        .bg-gradient-primary {
            background: linear-gradient(135deg, #0d5d31 0%, #327350 100%);
        }
        .step-card {
            transition: all 0.3s ease;
        }
        .step-item {
            border-left: 4px solid #667eea;
        }
        .step-number {
            font-size: 0.9rem;
            font-weight: 600;
        }
        .form-label {
            color: #495057;
            margin-bottom: 0.5rem;
        }
        .card-header h6 {
            font-weight: 600;
        }
        .dependency-steps-container {
            background-color: #f8f9fa;
            padding: 1rem;
            border-radius: 0.375rem;
            border: 1px solid #e9ecef;
        }
        .form-text {
            font-size: 0.875rem;
            color: #6c757d;
        }
        
        .section-header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 1rem;
            border-radius: 0.375rem;
            margin-bottom: 1rem;
        }
        
        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .section-code {
            font-family: 'Courier New', monospace;
            font-size: 0.875rem;
            opacity: 0.8;
        }
        
        .section-description {
            font-size: 0.9rem;
            opacity: 0.9;
            margin-top: 0.5rem;
        }
        
        .step-badge {
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .entry-point-badge {
            background-color: #ffc107;
            color: #000;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .info-card {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 0.375rem;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        
        .info-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.25rem;
        }
        
        .info-value {
            color: #6c757d;
            margin-bottom: 0.5rem;
        }
        
        .escalation-section {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 0.375rem;
            padding: 1rem;
            margin-top: 1rem;
        }
        
        .escalation-title {
            color: #856404;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
    </style>
@endpush

@section('content')
<div class="p-4 rounded">
    <div class="row g-3 mb-4">
        <div class="col-md-12">
            <div class="info-card">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <h5 class="mb-0" style="font-weight: 600;">Workflow Template Details</h5>
                    <a href="{{ route('workflow-assignments.tree', encrypt($assignment->id)) }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-diagram-3 me-1"></i> View Tree
                    </a>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="info-label">Title</div>
                        <div class="info-value">{{ $assignment->title }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-label">Status</div>
                        <div class="info-value">
                            @if($assignment->status == 1)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-label">Total Steps</div>
                        <div class="info-value">{{ $assignment->children->count() }} steps across {{ count($assignment->sections ?? []) }} sections</div>
                    </div>
                </div>
                @if($assignment->description)
                    <div class="row mt-2">
                        <div class="col-md-12">
                            <div class="info-label">Description</div>
                            <div class="info-value">{{ $assignment->description }}</div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if($assignment->sections && count($assignment->sections) > 0)
        @foreach($assignment->sections as $index => $section)
            @php
                $sectionSteps = $assignment->children->where('section_id', $section['id'])->sortBy('step_order');
            @endphp
            
            <div class="section-header">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="section-title">{{ $section['name'] }}</div>
                        <div class="section-code">{{ $section['code'] }}</div>
                        @if($section['description'])
                            <div class="section-description">{{ $section['description'] }}</div>
                        @endif
                    </div>
                    <div class="text-end">
                        <div class="step-badge">{{ $sectionSteps->count() }} steps</div>
                    </div>
                </div>
            </div>

            @if($sectionSteps->count() > 0)
                <div class="row g-3">
                    @foreach($sectionSteps as $stepIndex => $step)
                        <div class="col-md-12">
                            <div class="card border-0 shadow-sm step-item">
                                <div class="card-header bg-gradient-primary text-white d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <span class="step-number badge bg-white text-primary me-2">S{{ $step->step }}</span>
                                        <h6 class="mb-0">Step {{ $step->step }}</h6>
                                        @if($step->step_name)
                                            <span class="ms-2">- {{ $step->step_name }}</span>
                                        @endif
                                    </div>
                                    <div class="d-flex align-items-center">
                                        @if($step->is_entry_point)
                                            <span class="entry-point-badge me-2">Entry Point</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="info-card">
                                                <div class="info-label">Step Name</div>
                                                <div class="info-value">{{ $step->step_name ?? 'Not specified' }}</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info-card">
                                                <div class="info-label">Department</div>
                                                <div class="info-value">{{ $step->department->name ?? 'Not assigned' }}</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info-card">
                                                <div class="info-label">Checklist</div>
                                                <div class="info-value">{{ $step->checklist->name ?? 'Not assigned' }}</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info-card">
                                                <div class="info-label">Trigger</div>
                                                <div class="info-value">
                                                    @if($step->trigger == 1)
                                                        <span class="badge bg-warning">Manual</span>
                                                    @else
                                                        <span class="badge bg-info">Auto</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        
                                        @if($step->checklist_description)
                                            <div class="col-md-12">
                                                <div class="info-card">
                                                    <div class="info-label">Checklist Description</div>
                                                    <div class="info-value">{{ $step->checklist_description }}</div>
                                                </div>
                                            </div>
                                        @endif

                                        <div class="col-md-6">
                                            <div class="info-card">
                                                <div class="info-label">Dependency</div>
                                                <div class="info-value">
                                                    @if($step->dependency == 'ALL_COMPLETED')
                                                        All Previous Steps
                                                    @elseif($step->dependency == 'SELECTED_COMPLETED')
                                                        Selected Steps
                                                        @if($step->dependency_steps && count($step->dependency_steps) > 0)
                                                            <br><small class="text-muted">Steps: {{ implode(', ', $step->dependency_steps) }}</small>
                                                        @endif
                                                    @else
                                                        {{ $step->dependency }}
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        @if($step->turn_around_time)
                                            <div class="col-md-6">
                                                <div class="info-card">
                                                    <div class="info-label">Turnaround Time</div>
                                                    <div class="info-value">{{ $step->turn_around_time }}</div>
                                                </div>
                                            </div>
                                        @endif

                                        @if($step->user_id)
                                            <div class="col-md-6">
                                                <div class="info-card">
                                                    <div class="info-label">Maker</div>
                                                    <div class="info-value">
                                                        {{ $step->user->employee_id ?? '' }} - {{ $step->user->name ?? '' }} {{ $step->user->middle_name ?? '' }} {{ $step->user->last_name ?? '' }}
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        @if($step->checker_id)
                                            <div class="col-md-6">
                                                <div class="info-card">
                                                    <div class="info-label">Checker</div>
                                                    <div class="info-value">
                                                        {{ $step->checker->employee_id ?? '' }} - {{ $step->checker->name ?? '' }} {{ $step->checker->middle_name ?? '' }} {{ $step->checker->last_name ?? '' }}
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        @if($step->maker_turn_around_time_day || $step->maker_turn_around_time_hour)
                                            <div class="col-md-6">
                                                <div class="info-card">
                                                    <div class="info-label">Maker Turnaround Time</div>
                                                    <div class="info-value">
                                                        @if($step->maker_turn_around_time_day)
                                                            {{ $step->maker_turn_around_time_day }} days
                                                        @endif
                                                        @if($step->maker_turn_around_time_hour)
                                                            {{ $step->maker_turn_around_time_hour }} hours
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        @if($step->checker_turn_around_time_day || $step->checker_turn_around_time_hour)
                                            <div class="col-md-6">
                                                <div class="info-card">
                                                    <div class="info-label">Checker Turnaround Time</div>
                                                    <div class="info-value">
                                                        @if($step->checker_turn_around_time_day)
                                                            {{ $step->checker_turn_around_time_day }} days
                                                        @endif
                                                        @if($step->checker_turn_around_time_hour)
                                                            {{ $step->checker_turn_around_time_hour }} hours
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        @if($step->maker_escalation_user_id || $step->maker_escalation_after_day || $step->maker_escalation_after_hour)
                                            <div class="col-md-12">
                                                <div class="escalation-section">
                                                    <div class="escalation-title">Maker Escalation</div>
                                                    <div class="row">
                                                        @if($step->maker_escalation_user_id)
                                                            <div class="col-md-6">
                                                                <div class="info-label">Escalation User</div>
                                                                <div class="info-value">
                                                                    {{ $step->makerEscalationUser->employee_id ?? '' }} - {{ $step->makerEscalationUser->name ?? '' }} {{ $step->makerEscalationUser->middle_name ?? '' }} {{ $step->makerEscalationUser->last_name ?? '' }}
                                                                </div>
                                                            </div>
                                                        @endif
                                                        @if($step->maker_escalation_after_day || $step->maker_escalation_after_hour)
                                                            <div class="col-md-6">
                                                                <div class="info-label">Escalation After</div>
                                                                <div class="info-value">
                                                                    @if($step->maker_escalation_after_day)
                                                                        {{ $step->maker_escalation_after_day }} days
                                                                    @endif
                                                                    @if($step->maker_escalation_after_hour)
                                                                        {{ $step->maker_escalation_after_hour }} hours
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        @if($step->checker_escalation_user_id || $step->checker_escalation_after_day || $step->checker_escalation_after_hour)
                                            <div class="col-md-12">
                                                <div class="escalation-section">
                                                    <div class="escalation-title">Checker Escalation</div>
                                                    <div class="row">
                                                        @if($step->checker_escalation_user_id)
                                                            <div class="col-md-6">
                                                                <div class="info-label">Escalation User</div>
                                                                <div class="info-value">
                                                                    {{ $step->checkerEscalationUser->employee_id ?? '' }} - {{ $step->checkerEscalationUser->name ?? '' }} {{ $step->checkerEscalationUser->middle_name ?? '' }} {{ $step->checkerEscalationUser->last_name ?? '' }}
                                                                </div>
                                                            </div>
                                                        @endif
                                                        @if($step->checker_escalation_after_day || $step->checker_escalation_after_hour)
                                                            <div class="col-md-6">
                                                                <div class="info-label">Escalation After</div>
                                                                <div class="info-value">
                                                                    @if($step->checker_escalation_after_day)
                                                                        {{ $step->checker_escalation_after_day }} days
                                                                    @endif
                                                                    @if($step->checker_escalation_after_hour)
                                                                        {{ $step->checker_escalation_after_hour }} hours
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-list-ol" style="font-size: 2rem;"></i>
                    <p class="mt-2 mb-0">No steps defined for this section</p>
                </div>
            @endif
        @endforeach
    @else
        <div class="text-center py-5 text-muted">
            <i class="bi bi-list-ol" style="font-size: 3rem;"></i>
            <p class="mt-3">No sections defined for this workflow template</p>
        </div>
    @endif
</div>
@endsection

@push('js')
<script src="{{ asset('assets/js/select2.min.js') }}"></script>
<script>
$(document).ready(function () {

    $('.select2').select2({
        width: '100%',
        theme: 'classic',
        disabled: true
    });
});
</script>
@endpush