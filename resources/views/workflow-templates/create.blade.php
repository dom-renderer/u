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

        .step-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15) !important;
        }

        .step-item {
            border-left: 4px solid #667eea;
        }

        .drag-handle {
            cursor: move;
        }

        .drag-handle:hover {
            background-color: rgba(255, 255, 255, 0.2) !important;
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

        .btn-outline-light:hover {
            background-color: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.3);
        }

        .select2-container--default .select2-selection--multiple {
            min-height: 38px;
            border: 1px solid #ced4da;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #667eea;
            border: 1px solid #667eea;
        }

        .section-item {
            border: 1px solid #e9ecef;
            border-radius: 0.375rem;
            margin-bottom: 0.75rem;
            cursor: pointer;
            transition: all 0.2s ease;
            background-color: #f8f9fa;
        }

        .section-item:hover {
            background-color: #e9ecef;
            border-color: #dee2e6;
        }

        .section-item.active {
            background-color: #28a745;
            border-color: #28a745;
            color: white;
        }

        .section-item.active .text-muted {
            color: rgba(255, 255, 255, 0.8) !important;
        }

        .section-drag-handle {
            cursor: move;
            padding: 0.5rem;
            color: #6c757d;
        }

        .section-drag-handle:hover {
            color: #495057;
        }

        .section-item.active .section-drag-handle {
            color: rgba(255, 255, 255, 0.8);
        }

        .section-code {
            font-family: 'Courier New', monospace;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .section-steps-count {
            font-size: 0.75rem;
            color: #6c757d;
        }

        .section-item.active .section-steps-count {
            color: rgba(255, 255, 255, 0.8);
        }

        .section-number {
            font-size: 0.75rem;
            font-weight: 600;
            color: #6c757d;
        }

        .section-item.active .section-number {
            color: rgba(255, 255, 255, 0.8);
        }

        .form-hint {
            font-size: 0.75rem;
            color: #6c757d;
            margin-top: 0.25rem;
        }

        .character-counter {
            font-size: 0.75rem;
            color: #6c757d;
        }

        .steps-placeholder {
            text-align: center;
            padding: 2rem;
            color: #6c757d;
        }

        .steps-placeholder i {
            font-size: 3rem;
            margin-bottom: 1rem;
            display: block;
        }
    </style>
@endpush

@section('content')
    <div class="p-4 rounded">
        <form action="{{ route('workflow-templates.store') }}" method="post" id="templateForm">
            @csrf

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="row g-3 mb-4">
                <div class="col-md-12">
                    <label class="form-label">Title <span class="text-danger"> * </span> </label>
                    <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
                </div>

                <div class="col-md-12">
                    <label class="form-label">Description <span class="text-danger"> * </span> </label>
                    <input type="text" name="description" class="form-control" value="{{ old('description') }}">
                </div>

                <div class="col-md-12">
                    <label class="form-label">Status <span class="text-danger"> * </span> </label>
                    <select name="status" id="status" class="form-control">
                        <option value="1"> Active </option>
                        <option value="0"> InActive </option>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Workflow Sections</h6>
                            <button type="button" class="btn btn-success btn-sm" id="addSection">
                                <i class="bi bi-plus-circle me-1"></i>Add Section
                            </button>
                        </div>
                        <div class="card-body p-3">
                            <p class="text-muted small mb-3">Drag to reorder sections. Click to edit details.</p>
                            <div id="sectionsContainer">
                                <!-- Sections will be dynamically added here -->
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">Section Details</h6>
                                <small class="text-muted">Configure the details for this workflow section</small>
                            </div>
                            <div>
                                <button type="button" class="btn btn-outline-secondary btn-sm me-2" id="cloneSection"
                                    disabled>
                                    <i class="bi bi-files me-1"></i>Clone
                                </button>
                                <button type="button" class="btn btn-danger btn-sm" id="deleteSection" disabled>
                                    <i class="bi bi-trash me-1"></i>Delete
                                </button>
                            </div>
                        </div>
                        <div class="card-body" id="sectionDetailsContainer">
                            <div class="text-center py-5">
                                <i class="bi bi-list-ol text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-3">Select a section to configure its details</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <button class="btn btn-success mt-4">Save</button>
        </form>
    </div>
@endsection



@push('js')
    <script src="{{ asset('assets/js/select2.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script>
        $(document).ready(function () {
            let sectionIndex = 0;
            let currentSectionId = null;
            let sections = {};
            let globalStepCounter = 0;

            function generateSectionCode(name) {
                if (!name) return '';
                return name.toUpperCase()
                    .replace(/[^A-Z0-9\s]/g, '')
                    .replace(/\s+/g, '_')
                    .substring(0, 20);
            }

            function getAllStepsExceptCurrent(currentStepId) {
                const allSteps = [];
                Object.values(sections).forEach(section => {
                    section.steps.forEach(step => {
                        if (step.id !== currentStepId) {
                            allSteps.push({
                                id: step.globalNumber,
                                text: `Step ${step.globalNumber}${step.step_name ? ' - ' + step.step_name : ''}`
                            });
                        }
                    });
                });
                return allSteps;
            }

            function generateDependencyStepsOptions(currentStepId, selectedSteps) {
                const allSteps = getAllStepsExceptCurrent(currentStepId);
                const selectedArray = Array.isArray(selectedSteps) ? selectedSteps.map(s => String(s)) : [];
                return allSteps.map(step =>
                    `<option value="${step.id}" ${selectedArray.includes(String(step.id)) ? 'selected' : ''}>${step.text}</option>`
                ).join('');
            }

            function addSection() {
                sectionIndex++;
                const sectionId = `section_${sectionIndex}`;
                const sectionNumber = sectionIndex;

                const sectionData = {
                    id: sectionId,
                    name: '',
                    code: '',
                    description: '',
                    steps: []
                };

                sections[sectionId] = sectionData;

                const sectionItem = $(`
                <div class="section-item" data-section-id="${sectionId}">
                    <div class="d-flex align-items-center p-3">
                        <div class="section-drag-handle me-2">
                            <i class="bi bi-grip-vertical"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-semibold section-name">New Section</div>
                            <div class="section-code text-muted">NEW_SECTION</div>
                            <div class="text-muted small section-description">No description provided</div>
                            <div class="d-flex justify-content-between align-items-center mt-1">
                                <span class="section-steps-count">0 steps</span>
                                <span class="section-number">Section ${sectionNumber}</span>
                            </div>
                        </div>
                    </div>
                </div>
            `);

                $('#sectionsContainer').append(sectionItem);
                sectionItem.click(function () {
                    selectSection(sectionId);
                });

                selectSection(sectionId);
                initializeSortable();
            }

            function selectSection(sectionId) {
                if (currentSectionId && sections[currentSectionId]) {
                    saveCurrentStepData(currentSectionId);
                }

                $('.section-item').removeClass('active');
                $(`.section-item[data-section-id="${sectionId}"]`).addClass('active');

                currentSectionId = sectionId;
                $('#cloneSection, #deleteSection').prop('disabled', false);

                const section = sections[sectionId];
                renderSectionDetails(section);
            }

            function renderStepsForSection(section) {
                if (section.steps.length === 0) {
                    return `
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-list-ol" style="font-size: 2rem;"></i>
                        <p class="mt-2 mb-0">No steps added yet. Click "Add Step" to get started.</p>
                    </div>
                `;
                }

                return section.steps.map(step => `
                <div class="step-card mb-3" data-step-id="${step.id}">
                    <div class="card border-0 shadow-sm step-item">
                        <div class="card-header bg-gradient-primary text-white d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <span class="step-number badge bg-white text-primary me-2">S${step.globalNumber}</span>
                                <h6 class="mb-0">Step ${step.globalNumber}</h6>
                                <h5 class="mb-0 nearest-step-heading"></h5>
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="form-check form-switch me-4">
                                    <label class="form-check-label" for="switchCheckChecked-${step.id}"> Entry Point </label>
                                    <input class="form-check-input" type="checkbox" role="switch" id="switchCheckChecked-${step.id}" name="sections[${section.id}][steps][${step.id}][is_entry_point]" ${step.is_entry_point ? 'checked' : ''}>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-light me-2 drag-handle">
                                    <i class="bi bi-grip-vertical"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-light btn-danger remove-step" data-step-id="${step.id}">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Step Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control step-name" name="sections[${section.id}][steps][${step.id}][step_name]" value="${step.step_name || ''}" required>
                                    <input type="hidden" class="step-input" name="sections[${section.id}][steps][${step.id}][step]" value="${step.globalNumber}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Department <span class="text-danger">*</span></label>
                                    <select class="form-select select2" data-whichselect="departments-list" id="step-${step.id}-department_id" name="sections[${section.id}][steps][${step.id}][department_id]" required>
                                        ${step.department_id ? `<option value="${step.department_id}" selected>${step.department_name || 'Selected Department'}</option>` : ''}
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Checklist <span class="text-danger">*</span></label>
                                    <select class="form-select select2" data-whichselect="checklists-list" id="step-${step.id}-checklist_id" name="sections[${section.id}][steps][${step.id}][checklist_id]" required>
                                        ${step.checklist_id ? `<option value="${step.checklist_id}" selected>${step.checklist_name || 'Selected Checklist'}</option>` : ''}
                                    </select>
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">Checklist Description</label>
                                    <textarea class="form-control" name="sections[${section.id}][steps][${step.id}][checklist_description]" rows="2" placeholder="Describe what needs to be done...">${step.checklist_description || ''}</textarea>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Trigger</label>
                                    <select class="form-select select2" name="sections[${section.id}][steps][${step.id}][trigger]" required>
                                        <option value="0" ${step.trigger == 0 ? 'selected' : ''}>Auto</option>
                                        <option value="1" ${step.trigger == 1 ? 'selected' : ''}>Manual</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Dependency</label>
                                    <select class="form-select dependency-select select2" name="sections[${section.id}][steps][${step.id}][dependency]" required>
                                        <option value="ALL_COMPLETED" ${step.dependency == 'ALL_COMPLETED' ? 'selected' : ''}>All Previous Steps</option>
                                        <option value="SELECTED_COMPLETED" ${step.dependency == 'SELECTED_COMPLETED' ? 'selected' : ''}>Selected Steps</option>
                                    </select>
                                </div>
                                <div class="col-12 dependency-steps-container ${step.dependency == 'SELECTED_COMPLETED' ? '' : 'd-none'}">
                                    <label class="form-label fw-semibold">Select Dependent Steps</label>
                                    <select class="form-select dep-steps select2" multiple name="sections[${section.id}][steps][${step.id}][dependency_steps][]" data-step-id="${step.id}">
                                        ${generateDependencyStepsOptions(step.id, step.dependency_steps || [])}
                                    </select>
                                    <div class="form-text">Choose which specific steps must be completed before this step can start.</div>
                                </div>

                                <div class="accordion" id="accordionExample-${step.id}">
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-maker-${step.id}" aria-expanded="true" aria-controls="collapse-maker-${step.id}">
                                                Maker
                                            </button>
                                        </h2>
                                        <div id="collapse-maker-${step.id}" class="accordion-collapse collapse" data-bs-parent="#accordionExample-${step.id}">
                                            <div class="accordion-body row">
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold">Maker <span class="text-danger">*</span></label>
                                                    <select class="form-select select2" data-whichselect="maker-list" id="step-${step.id}-user_id" name="sections[${section.id}][steps][${step.id}][user_id]" required>
                                                        ${step.user_id ? `<option value="${step.user_id}" selected>${step.user_name || 'Selected User'}</option>` : ''}
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold">Turnaround Time <span class="text-danger">*</span></label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">Days</span>
                                                        <input type="number" class="form-control" name="sections[${section.id}][steps][${step.id}][maker_turn_around_time_day]" placeholder="Enter days" value="${step.maker_turn_around_time_day || ''}">
                                                        <span class="input-group-text">Hours</span>
                                                        <input type="number" class="form-control" name="sections[${section.id}][steps][${step.id}][maker_turn_around_time_hour]" placeholder="Enter hours" value="${step.maker_turn_around_time_hour || ''}">
                                                    </div>
                                                </div>
                                                <hr class="mt-4 mb-4">
                                                <h5>Maker Escalation</h5>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold">Escalation User</label>
                                                    <select class="form-select select2" data-whichselect="escalation-maker-list" id="step-${step.id}-maker_escalation_user_id" name="sections[${section.id}][steps][${step.id}][maker_escalation_user_id]">
                                                        ${step.maker_escalation_user_id ? `<option value="${step.maker_escalation_user_id}" selected>${step.maker_escalation_user_name || 'Selected User'}</option>` : ''}
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold">Escalation After</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">Days</span>
                                                        <input type="number" class="form-control" name="sections[${section.id}][steps][${step.id}][maker_escalation_after_day]" placeholder="Enter days" value="${step.maker_escalation_after_day || ''}">
                                                        <span class="input-group-text">Hours</span>
                                                        <input type="number" class="form-control" name="sections[${section.id}][steps][${step.id}][maker_escalation_after_hour]" placeholder="Enter hours" value="${step.maker_escalation_after_hour || ''}">
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mt-2">
                                                    <label class="form-label fw-semibold">Email Notification Template</label>
                                                    <select class="form-select select2" data-whichselect="maker-email-list" id="step-${step.id}-maker_escalation_email_notification" name="sections[${section.id}][steps][${step.id}][maker_escalation_email_notification]" required>
                                                        ${step.maker_escalation_email_notification ? `<option value="${step.maker_escalation_email_notification}" selected>${step.maker_escalation_email_name || 'Selected Template'}</option>` : ''}
                                                    </select>
                                                </div>
                                                <div class="col-md-6 mt-2">
                                                    <label class="form-label fw-semibold">Push Notification Template</label>
                                                    <select class="form-select select2" data-whichselect="maker-push-list" id="step-${step.id}-maker_escalation_push_notification" name="sections[${section.id}][steps][${step.id}][maker_escalation_push_notification]" required>
                                                        ${step.maker_escalation_push_notification ? `<option value="${step.maker_escalation_push_notification}" selected>${step.maker_escalation_push_name || 'Selected Template'}</option>` : ''}
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-checker-${step.id}" aria-expanded="false" aria-controls="collapse-checker-${step.id}">
                                                Checker
                                            </button>
                                        </h2>
                                        <div id="collapse-checker-${step.id}" class="accordion-collapse collapse" data-bs-parent="#accordionExample-${step.id}">
                                            <div class="accordion-body row">
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold">Checker <span class="text-danger">*</span></label>
                                                    <select class="form-select select2" data-whichselect="checker-list" id="step-${step.id}-checker_id" name="sections[${section.id}][steps][${step.id}][checker_id]" required>
                                                        ${step.checker_id ? `<option value="${step.checker_id}" selected>${step.checker_name || 'Selected User'}</option>` : ''}
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold">Turnaround Time <span class="text-danger">*</span></label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">Days</span>
                                                        <input type="number" class="form-control" name="sections[${section.id}][steps][${step.id}][checker_turn_around_time_day]" placeholder="Enter days" value="${step.checker_turn_around_time_day || ''}">
                                                        <span class="input-group-text">Hours</span>
                                                        <input type="number" class="form-control" name="sections[${section.id}][steps][${step.id}][checker_turn_around_time_hour]" placeholder="Enter hours" value="${step.checker_turn_around_time_hour || ''}">
                                                    </div>
                                                </div>
                                                <hr class="mt-4 mb-4">
                                                <h5>Checker Escalation</h5>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold">Escalation User</label>
                                                    <select class="form-select select2" data-whichselect="escalation-checker-list" id="step-${step.id}-checker_escalation_user_id" name="sections[${section.id}][steps][${step.id}][checker_escalation_user_id]">
                                                        ${step.checker_escalation_user_id ? `<option value="${step.checker_escalation_user_id}" selected>${step.checker_escalation_user_name || 'Selected User'}</option>` : ''}
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold">Escalation After</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">Days</span>
                                                        <input type="number" class="form-control" name="sections[${section.id}][steps][${step.id}][checker_escalation_after_day]" placeholder="Enter days" value="${step.checker_escalation_after_day || ''}">
                                                        <span class="input-group-text">Hours</span>
                                                        <input type="number" class="form-control" name="sections[${section.id}][steps][${step.id}][checker_escalation_after_hour]" placeholder="Enter hours" value="${step.checker_escalation_after_hour || ''}">
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mt-2">
                                                    <label class="form-label fw-semibold">Email Notification Template</label>
                                                    <select class="form-select select2" data-whichselect="checker-email-list" id="step-${step.id}-checker_escalation_email_notification" name="sections[${section.id}][steps][${step.id}][checker_escalation_email_notification]" required>
                                                        ${step.checker_escalation_email_notification ? `<option value="${step.checker_escalation_email_notification}" selected>${step.checker_escalation_email_name || 'Selected Template'}</option>` : ''}
                                                    </select>
                                                </div>
                                                <div class="col-md-6 mt-2">
                                                    <label class="form-label fw-semibold">Push Notification Template</label>
                                                    <select class="form-select select2" data-whichselect="checker-push-list" id="step-${step.id}-checker_escalation_push_notification" name="sections[${section.id}][steps][${step.id}][checker_escalation_push_notification]" required>
                                                        ${step.checker_escalation_push_notification ? `<option value="${step.checker_escalation_push_notification}" selected>${step.checker_escalation_push_name || 'Selected Template'}</option>` : ''}
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');
            }

            function renderSectionDetails(section) {
                if (currentSectionId && sections[currentSectionId]) {
                    saveCurrentStepData(currentSectionId);
                }

                const sectionDetailsHtml = `
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Section Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="sections[${section.id}][name]" 
                               value="${section.name}" placeholder="e.g., Store Setup & Configuration" required>
                        <div class="form-hint">
                            <i class="bi bi-info-circle me-1"></i>
                            Use section-oriented names that clearly indicate the section's purpose
                        </div>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Section Code <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" class="form-control" name="sections[${section.id}][code]" 
                                   value="${section.code}" placeholder="e.g., STORE_SETUP" required>
                            <button class="btn btn-outline-secondary" type="button" id="regenerateCode">
                                <i class="bi bi-arrow-clockwise"></i>
                            </button>
                        </div>
                        <div class="form-hint">
                            Use uppercase letters and underscores (e.g., STORE_SETUP, INVENTORY_CHECK)
                        </div>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea class="form-control" name="sections[${section.id}][description]" 
                                  rows="3" placeholder="Describe the activities, objectives, and outcomes for this section..." 
                                  maxlength="200">${section.description}</textarea>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <div class="form-hint">
                                <i class="bi bi-pencil me-1"></i>
                                Include key activities and expected outcomes
                            </div>
                            <div class="character-counter">
                                <span id="charCount">${section.description.length}</span>/200 characters
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">Steps In This Section</h6>
                            <div>
                                <span class="badge bg-secondary me-2">${section.steps.length} steps</span>
                                <button type="button" class="btn btn-primary btn-sm" onclick="addStepToSection('${section.id}')">
                                    <i class="bi bi-plus-circle me-1"></i>Add Step
                                </button>
                            </div>
                        </div>
                        <div id="steps-container-${section.id}" class="steps-container">
                            ${renderStepsForSection(section)}
                        </div>
                    </div>
                </div>
            `;

                $('#sectionDetailsContainer').html(sectionDetailsHtml);

                $('#regenerateCode').click(function () {
                    const name = $(`input[name="sections[${section.id}][name]"]`).val();
                    const newCode = generateSectionCode(name);
                    $(`input[name="sections[${section.id}][code]"]`).val(newCode);
                    updateSectionDisplay(section.id);
                });

                $(`input[name="sections[${section.id}][name]"]`).on('input', function () {
                    const name = $(this).val();
                    const code = generateSectionCode(name);
                    $(`input[name="sections[${section.id}][code]"]`).val(code);
                    updateSectionDisplay(section.id);
                });

                $(`textarea[name="sections[${section.id}][description]"]`).on('input', function () {
                    const length = $(this).val().length;
                    $('#charCount').text(length);
                    updateSectionDisplay(section.id);
                });

                $('input, textarea').on('input change', function () {
                    updateSectionData(section.id);
                });

                initializeStepEventHandlers(section.id);
            }

            function addStepToSection(sectionId) {
                globalStepCounter++;
                const stepId = `step_${globalStepCounter}`;
                const stepData = {
                    id: stepId,
                    globalNumber: globalStepCounter,
                    step_name: '',
                    department_id: '',
                    checklist_id: '',
                    checklist_description: '',
                    trigger: 0,
                    dependency: 'ALL_COMPLETED',
                    dependency_steps: [],
                    is_entry_point: false,
                    user_id: '',
                    maker_turn_around_time_day: '',
                    maker_turn_around_time_hour: '',
                    maker_escalation_user_id: '',
                    maker_escalation_after_day: '',
                    maker_escalation_after_hour: '',
                    maker_escalation_email_notification: '',
                    maker_escalation_push_notification: '',
                    checker_id: '',
                    checker_turn_around_time_day: '',
                    checker_turn_around_time_hour: '',
                    checker_escalation_user_id: '',
                    checker_escalation_after_day: '',
                    checker_escalation_after_hour: '',
                    checker_escalation_email_notification: '',
                    checker_escalation_push_notification: ''
                };

                sections[sectionId].steps.push(stepData);
                updateSectionDisplay(sectionId);

                if (currentSectionId === sectionId) {
                    renderSectionDetails(sections[sectionId]);
                }
            }

            function removeStepFromSection(sectionId, stepId) {
                const section = sections[sectionId];
                const stepIndex = section.steps.findIndex(step => step.id === stepId);

                if (stepIndex > -1) {
                    section.steps.splice(stepIndex, 1);
                    updateSectionDisplay(sectionId);

                    if (currentSectionId === sectionId) {
                        renderSectionDetails(section);
                    }

                    refreshAllStepNumbers();
                }
            }

            function refreshAllStepNumbers() {
                globalStepCounter = 0;
                Object.values(sections).forEach(section => {
                    section.steps.forEach(step => {
                        globalStepCounter++;
                        step.globalNumber = globalStepCounter;
                    });
                });

                if (currentSectionId) {
                    renderSectionDetails(sections[currentSectionId]);
                }
            }

            function initializeStepEventHandlers(sectionId) {
                $(document).off('click', '.remove-step');
                $(document).on('click', '.remove-step', function () {
                    const stepId = $(this).data('step-id');
                    Swal.fire({
                        title: 'Are you sure you want to delete this step?',
                        text: "You won't be able to revert this!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            removeStepFromSection(sectionId, stepId);
                        }
                    });
                });

                $(document).off('keyup', '.step-name');
                $(document).on('keyup', '.step-name', function () {
                    const nearestHeading = $(this).closest('.step-card').find('.nearest-step-heading');
                    if (nearestHeading && $(this).val().trim()) {
                        nearestHeading.text(` - ${$(this).val().trim()}`);
                    }

                    saveCurrentStepData(sectionId);
                });

                $(document).off('change', '.dependency-select');
                $(document).on('change', '.dependency-select', function () {
                    const stepCard = $(this).closest('.step-card');
                    const isEntryPoint = stepCard.find('input[name*="[is_entry_point]"]').is(':checked');

                    if (isEntryPoint) {
                        return;
                    }

                    const container = stepCard.find('.dependency-steps-container');
                    if ($(this).val() === 'SELECTED_COMPLETED') {
                        container.removeClass('d-none');
                    } else {
                        container.addClass('d-none');
                        container.find('.dep-steps').val(null).trigger('change');
                    }

                    saveCurrentStepData(sectionId);
                });

                $(document).off('change', 'input[name*="[is_entry_point]"]');
                $(document).on('change', 'input[name*="[is_entry_point]"]', function () {
                    const stepCard = $(this).closest('.step-card');
                    const isEntryPoint = $(this).is(':checked');
                    const dependencySelect = stepCard.find('.dependency-select');
                    const dependencyContainer = stepCard.find('.dependency-steps-container');
                    const depSteps = stepCard.find('.dep-steps');

                    if (isEntryPoint) {
                        dependencySelect.val('ALL_COMPLETED').trigger('change.select2');
                        dependencySelect.prop('disabled', true);
                        depSteps.val(null).trigger('change');
                        dependencyContainer.addClass('d-none');
                    } else {
                        dependencySelect.prop('disabled', false);
                    }

                    saveCurrentStepData(sectionId);
                });

                $(document).off('input change', '.step-card input, .step-card select, .step-card textarea');
                $(document).on('input change', '.step-card input, .step-card select, .step-card textarea', function () {
                    saveCurrentStepData(sectionId);
                });

                setTimeout(() => {
                    initializeSelect2($('#sectionDetailsContainer'));
                }, 100);
            }

            function initializeSelect2(scope) {
                if (scope) {
                    if (scope.find('[data-whichselect="departments-list"]').length > 0) {
                        scope.find('[data-whichselect="departments-list"]').select2({
                            placeholder: 'Select Department',
                            width: '100%',
                            theme: 'classic',
                            ajax: {
                                url: "{{ route('departments-list') }}",
                                type: "POST",
                                dataType: 'json',
                                delay: 250,
                                data: function (params) {
                                    return {
                                        searchQuery: params.term,
                                        page: params.page || 1,
                                        _token: "{{ csrf_token() }}"
                                    };
                                },
                                processResults: function (data, params) {
                                    params.page = params.page || 1;
                                    return {
                                        results: $.map(data.items, function (item) {
                                            return {
                                                id: item.id,
                                                text: item.text
                                            };
                                        }),
                                        pagination: {
                                            more: data.pagination.more
                                        }
                                    };
                                },
                                cache: true
                            }
                        });
                    }

                    if (scope.find('[data-whichselect="checklists-list"]').length > 0) {
                        scope.find('[data-whichselect="checklists-list"]').select2({
                            placeholder: 'Select Checklist',
                            allowClear: true,
                            width: '100%',
                            theme: 'classic',
                            ajax: {
                                url: "{{ route('checklists-list') }}",
                                type: "POST",
                                dataType: 'json',
                                delay: 250,
                                data: function (params) {
                                    return {
                                        searchQuery: params.term,
                                        page: params.page || 1,
                                        _token: "{{ csrf_token() }}",
                                        type: 2
                                    };
                                },
                                processResults: function (data, params) {
                                    params.page = params.page || 1;
                                    return {
                                        results: $.map(data.items, function (item) {
                                            return {
                                                id: item.id,
                                                text: item.text
                                            };
                                        }),
                                        pagination: {
                                            more: data.pagination.more
                                        }
                                    };
                                },
                                cache: true
                            }
                        });
                    }

                    if (scope.find('[data-whichselect="maker-list"]').length > 0) {
                        scope.find('[data-whichselect="maker-list"]').select2({
                            placeholder: "Select a User",
                            allowClear: true,
                            width: "100%",
                            theme: 'classic',
                            ajax: {
                                url: "{{ route('users-list') }}",
                                type: "POST",
                                dataType: 'json',
                                delay: 250,
                                data: function (params) {
                                    return {
                                        searchQuery: params.term,
                                        page: params.page || 1,
                                        _token: "{{ csrf_token() }}",
                                        ignoreDesignation: 1
                                    }
                                },
                                processResults: function (data, params) {
                                    params.page = params.page || 1;
                                    return {
                                        results: $.map(data.items, function (item) {
                                            return {
                                                id: item.id,
                                                text: item.text
                                            };
                                        }),
                                        pagination: {
                                            more: data.pagination.more
                                        }
                                    };
                                },
                                cache: true
                            }
                        });
                    }

                    if (scope.find('[data-whichselect="checker-list"]').length > 0) {
                        scope.find('[data-whichselect="checker-list"]').select2({
                            placeholder: "Select a User",
                            allowClear: true,
                            width: "100%",
                            theme: 'classic',
                            ajax: {
                                url: "{{ route('users-list') }}",
                                type: "POST",
                                dataType: 'json',
                                delay: 250,
                                data: function (params) {
                                    return {
                                        searchQuery: params.term,
                                        page: params.page || 1,
                                        _token: "{{ csrf_token() }}",
                                        ignoreDesignation: 1
                                    }
                                },
                                processResults: function (data, params) {
                                    params.page = params.page || 1;
                                    return {
                                        results: $.map(data.items, function (item) {
                                            return {
                                                id: item.id,
                                                text: item.text
                                            };
                                        }),
                                        pagination: {
                                            more: data.pagination.more
                                        }
                                    };
                                },
                                cache: true
                            }
                        });
                    }

                    if (scope.find('[data-whichselect="escalation-maker-list"]').length > 0) {
                        scope.find('[data-whichselect="escalation-maker-list"]').select2({
                            placeholder: "Select a User",
                            allowClear: true,
                            width: "100%",
                            theme: 'classic',
                            ajax: {
                                url: "{{ route('users-list') }}",
                                type: "POST",
                                dataType: 'json',
                                delay: 250,
                                data: function (params) {
                                    return {
                                        searchQuery: params.term,
                                        page: params.page || 1,
                                        _token: "{{ csrf_token() }}",
                                        ignoreDesignation: 1
                                    }
                                },
                                processResults: function (data, params) {
                                    params.page = params.page || 1;
                                    return {
                                        results: $.map(data.items, function (item) {
                                            return {
                                                id: item.id,
                                                text: item.text
                                            };
                                        }),
                                        pagination: {
                                            more: data.pagination.more
                                        }
                                    };
                                },
                                cache: true
                            }
                        });
                    }

                    if (scope.find('[data-whichselect="escalation-checker-list"]').length > 0) {
                        scope.find('[data-whichselect="escalation-checker-list"]').select2({
                            placeholder: "Select a User",
                            allowClear: true,
                            width: "100%",
                            theme: 'classic',
                            ajax: {
                                url: "{{ route('users-list') }}",
                                type: "POST",
                                dataType: 'json',
                                delay: 250,
                                data: function (params) {
                                    return {
                                        searchQuery: params.term,
                                        page: params.page || 1,
                                        _token: "{{ csrf_token() }}",
                                        ignoreDesignation: 1
                                    }
                                },
                                processResults: function (data, params) {
                                    params.page = params.page || 1;
                                    return {
                                        results: $.map(data.items, function (item) {
                                            return {
                                                id: item.id,
                                                text: item.text
                                            };
                                        }),
                                        pagination: {
                                            more: data.pagination.more
                                        }
                                    };
                                },
                                cache: true
                            }
                        });
                    }

                    if (scope.find('[data-whichselect="maker-email-list"]').length > 0) {
                        scope.find('[data-whichselect="maker-email-list"]').select2({
                            placeholder: "Select Template",
                            allowClear: true,
                            width: "100%",
                            theme: 'classic',
                            ajax: {
                                url: "{{ route('notification-template-list') }}",
                                type: "POST",
                                dataType: 'json',
                                delay: 250,
                                data: function (params) {
                                    return {
                                        searchQuery: params.term,
                                        page: params.page || 1,
                                        _token: "{{ csrf_token() }}",
                                        type: 0
                                    }
                                },
                                processResults: function (data, params) {
                                    params.page = params.page || 1;
                                    return {
                                        results: $.map(data.items, function (item) {
                                            return {
                                                id: item.id,
                                                text: item.text
                                            };
                                        }),
                                        pagination: {
                                            more: data.pagination.more
                                        }
                                    };
                                },
                                cache: true
                            }
                        });
                    }

                    if (scope.find('[data-whichselect="maker-push-list"]').length > 0) {
                        scope.find('[data-whichselect="maker-push-list"]').select2({
                            placeholder: "Select Template",
                            allowClear: true,
                            width: "100%",
                            theme: 'classic',
                            ajax: {
                                url: "{{ route('notification-template-list') }}",
                                type: "POST",
                                dataType: 'json',
                                delay: 250,
                                data: function (params) {
                                    return {
                                        searchQuery: params.term,
                                        page: params.page || 1,
                                        _token: "{{ csrf_token() }}",
                                        type: 1
                                    }
                                },
                                processResults: function (data, params) {
                                    params.page = params.page || 1;
                                    return {
                                        results: $.map(data.items, function (item) {
                                            return {
                                                id: item.id,
                                                text: item.text
                                            };
                                        }),
                                        pagination: {
                                            more: data.pagination.more
                                        }
                                    };
                                },
                                cache: true
                            }
                        });
                    }

                    if (scope.find('[data-whichselect="checker-email-list"]').length > 0) {
                        scope.find('[data-whichselect="checker-email-list"]').select2({
                            placeholder: "Select Template",
                            allowClear: true,
                            width: "100%",
                            theme: 'classic',
                            ajax: {
                                url: "{{ route('notification-template-list') }}",
                                type: "POST",
                                dataType: 'json',
                                delay: 250,
                                data: function (params) {
                                    return {
                                        searchQuery: params.term,
                                        page: params.page || 1,
                                        _token: "{{ csrf_token() }}",
                                        type: 0
                                    }
                                },
                                processResults: function (data, params) {
                                    params.page = params.page || 1;
                                    return {
                                        results: $.map(data.items, function (item) {
                                            return {
                                                id: item.id,
                                                text: item.text
                                            };
                                        }),
                                        pagination: {
                                            more: data.pagination.more
                                        }
                                    };
                                },
                                cache: true
                            }
                        });
                    }

                    if (scope.find('[data-whichselect="checker-push-list"]').length > 0) {
                        scope.find('[data-whichselect="checker-push-list"]').select2({
                            placeholder: "Select Template",
                            allowClear: true,
                            width: "100%",
                            theme: 'classic',
                            ajax: {
                                url: "{{ route('notification-template-list') }}",
                                type: "POST",
                                dataType: 'json',
                                delay: 250,
                                data: function (params) {
                                    return {
                                        searchQuery: params.term,
                                        page: params.page || 1,
                                        _token: "{{ csrf_token() }}",
                                        type: 1
                                    }
                                },
                                processResults: function (data, params) {
                                    params.page = params.page || 1;
                                    return {
                                        results: $.map(data.items, function (item) {
                                            return {
                                                id: item.id,
                                                text: item.text
                                            };
                                        }),
                                        pagination: {
                                            more: data.pagination.more
                                        }
                                    };
                                },
                                cache: true
                            }
                        });
                    }

                    scope.find('select').each(function () {
                        if ($(this).is('[data-whichselect="departments-list"], [data-whichselect="checklists-list"], [data-whichselect="maker-list"], [data-whichselect="checker-list"], [data-whichselect="escalation-maker-list"], [data-whichselect="escalation-checker-list"], [data-whichselect="maker-email-list"], [data-whichselect="maker-push-list"], [data-whichselect="checker-email-list"], [data-whichselect="checker-push-list"]')) {
                            return;
                        }
                        $(this).select2({
                            width: '100%',
                            theme: 'classic',
                            placeholder: 'Select an option...'
                        });
                    });

                } else {
                    $(document).find('select.select2').select2({
                        width: '100%',
                        theme: 'classic',
                        placeholder: 'Select an option...',
                        allowClear: true
                    });
                }
            }

            function saveCurrentStepData(sectionId) {
                if (!sections[sectionId]) return;

                const section = sections[sectionId];
                const stepsContainer = $(`#steps-container-${sectionId}`);

                stepsContainer.find('.step-card').each(function () {
                    const stepCard = $(this);
                    const stepId = stepCard.data('step-id');
                    const step = section.steps.find(s => s.id === stepId);

                    if (step) {
                        step.step_name = stepCard.find('input[name*="[step_name]"]').val() || '';
                        step.department_id = stepCard.find('select[name*="[department_id]"]').val() || '';
                        step.checklist_id = stepCard.find('select[name*="[checklist_id]"]').val() || '';
                        step.checklist_description = stepCard.find('textarea[name*="[checklist_description]"]').val() || '';
                        step.trigger = parseInt(stepCard.find('select[name*="[trigger]"]').val()) || 0;
                        step.dependency = stepCard.find('select[name*="[dependency]"]').val() || 'ALL_COMPLETED';
                        step.is_entry_point = stepCard.find('input[name*="[is_entry_point]"]').is(':checked');
                        step.user_id = stepCard.find('select[name*="[user_id]"]').val() || '';

                        step.maker_turn_around_time_day = stepCard.find('input[name*="[maker_turn_around_time_day]"]').val() || '';
                        step.maker_turn_around_time_hour = stepCard.find('input[name*="[maker_turn_around_time_hour]"]').val() || '';
                        step.maker_escalation_user_id = stepCard.find('select[name*="[maker_escalation_user_id]"]').val() || '';
                        step.maker_escalation_after_day = stepCard.find('input[name*="[maker_escalation_after_day]"]').val() || '';
                        step.maker_escalation_after_hour = stepCard.find('input[name*="[maker_escalation_after_hour]"]').val() || '';
                        step.maker_escalation_email_notification = stepCard.find('select[name*="[maker_escalation_email_notification]"]').val() || '';
                        step.maker_escalation_push_notification = stepCard.find('select[name*="[maker_escalation_push_notification]"]').val() || '';

                        step.checker_id = stepCard.find('select[name*="[checker_id]"]').val() || '';
                        step.checker_turn_around_time_day = stepCard.find('input[name*="[checker_turn_around_time_day]"]').val() || '';
                        step.checker_turn_around_time_hour = stepCard.find('input[name*="[checker_turn_around_time_hour]"]').val() || '';
                        step.checker_escalation_user_id = stepCard.find('select[name*="[checker_escalation_user_id]"]').val() || '';
                        step.checker_escalation_after_day = stepCard.find('input[name*="[checker_escalation_after_day]"]').val() || '';
                        step.checker_escalation_after_hour = stepCard.find('input[name*="[checker_escalation_after_hour]"]').val() || '';
                        step.checker_escalation_email_notification = stepCard.find('select[name*="[checker_escalation_email_notification]"]').val() || '';
                        step.checker_escalation_push_notification = stepCard.find('select[name*="[checker_escalation_push_notification]"]').val() || '';

                        const dependencySteps = stepCard.find('select[name*="[dependency_steps]"]').val() || [];
                        step.dependency_steps = Array.isArray(dependencySteps) ? dependencySteps : [];

                        step.department_name = stepCard.find('select[name*="[department_id]"] option:selected').text() || '';
                        step.checklist_name = stepCard.find('select[name*="[checklist_id]"] option:selected').text() || '';
                        step.user_name = stepCard.find('select[name*="[user_id]"] option:selected').text() || '';
                        step.maker_escalation_user_name = stepCard.find('select[name*="[maker_escalation_user_id]"] option:selected').text() || '';
                        step.maker_escalation_email_name = stepCard.find('select[name*="[maker_escalation_email_notification]"] option:selected').text() || '';
                        step.maker_escalation_push_name = stepCard.find('select[name*="[maker_escalation_push_notification]"] option:selected').text() || '';
                        step.checker_name = stepCard.find('select[name*="[checker_id]"] option:selected').text() || '';
                        step.checker_escalation_user_name = stepCard.find('select[name*="[checker_escalation_user_id]"] option:selected').text() || '';
                        step.checker_escalation_email_name = stepCard.find('select[name*="[checker_escalation_email_notification]"] option:selected').text() || '';
                        step.checker_escalation_push_name = stepCard.find('select[name*="[checker_escalation_push_notification]"] option:selected').text() || '';
                    }
                });
            }

            function updateSectionData(sectionId) {
                if (sectionId && sections[sectionId]) {
                    const section = sections[sectionId];
                    section.name = $(`input[name="sections[${sectionId}][name]"]`).val();
                    section.code = $(`input[name="sections[${sectionId}][code]"]`).val();
                    section.description = $(`textarea[name="sections[${sectionId}][description]"]`).val();
                    updateSectionDisplay(sectionId);
                }
            }

            function updateSectionDisplay(sectionId) {
                const section = sections[sectionId];
                const sectionItem = $(`.section-item[data-section-id="${sectionId}"]`);

                sectionItem.find('.section-name').text(section.name || 'New Section');
                sectionItem.find('.section-code').text(section.code || 'NEW_SECTION');
                sectionItem.find('.section-description').text(section.description || 'No description provided');
                sectionItem.find('.section-steps-count').text(`${section.steps.length} steps`);
            }

            function cloneSection() {
                if (!currentSectionId) return;

                const originalSection = sections[currentSectionId];
                sectionIndex++;
                const newSectionId = `section_${sectionIndex}`;
                const sectionNumber = sectionIndex;

                const clonedSteps = originalSection.steps.map(step => {
                    globalStepCounter++;
                    return {
                        ...step,
                        id: `step_${globalStepCounter}`,
                        globalNumber: globalStepCounter
                    };
                });

                const clonedSection = {
                    id: newSectionId,
                    name: originalSection.name + ' (Copy)',
                    code: originalSection.code + '_COPY',
                    description: originalSection.description,
                    steps: clonedSteps
                };

                sections[newSectionId] = clonedSection;

                const sectionItem = $(`
                <div class="section-item" data-section-id="${newSectionId}">
                    <div class="d-flex align-items-center p-3">
                        <div class="section-drag-handle me-2">
                            <i class="bi bi-grip-vertical"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-semibold section-name">${clonedSection.name}</div>
                            <div class="section-code text-muted">${clonedSection.code}</div>
                            <div class="text-muted small section-description">${clonedSection.description || 'No description provided'}</div>
                            <div class="d-flex justify-content-between align-items-center mt-1">
                                <span class="section-steps-count">${clonedSection.steps.length} steps</span>
                                <span class="section-number">Section ${sectionNumber}</span>
                            </div>
                        </div>
                    </div>
                </div>
            `);

                $('#sectionsContainer').append(sectionItem);
                sectionItem.click(function () {
                    selectSection(newSectionId);
                });

                selectSection(newSectionId);
                initializeSortable();
            }

            function deleteSection() {
                if (!currentSectionId) return;

                Swal.fire({
                    title: 'Are you sure you want to delete this section?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $(`.section-item[data-section-id="${currentSectionId}"]`).remove();
                        delete sections[currentSectionId];

                        const remainingSections = Object.keys(sections);
                        if (remainingSections.length > 0) {
                            selectSection(remainingSections[0]);
                        } else {
                            currentSectionId = null;
                            $('#cloneSection, #deleteSection').prop('disabled', true);
                            $('#sectionDetailsContainer').html(`
                            <div class="text-center py-5">
                                <i class="bi bi-list-ol text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-3">Select a section to configure its details</p>
                            </div>
                        `);
                        }

                        updateSectionNumbers();
                    }
                });
            }

            function updateSectionNumbers() {
                $('.section-item').each(function (index) {
                    $(this).find('.section-number').text(`Section ${index + 1}`);
                });
            }

            function initializeSortable() {
                if (typeof Sortable !== 'undefined') {
                    new Sortable(document.getElementById('sectionsContainer'), {
                        handle: '.section-drag-handle',
                        animation: 150,
                        onEnd: function (evt) {
                            updateSectionNumbers();
                        }
                    });
                }
            }

            function validateForm() {
                const sectionIds = Object.keys(sections);

                if (sectionIds.length === 0) {
                    Swal.fire({
                        title: 'No Sections',
                        text: 'Please add at least one section to the workflow.',
                        icon: 'warning'
                    });
                    return false;
                }

                for (const sectionId of sectionIds) {
                    const section = sections[sectionId];

                    if (!section.name.trim()) {
                        Swal.fire({
                            title: 'Section Name Required',
                            text: 'Please provide a name for all sections.',
                            icon: 'warning'
                        });
                        return false;
                    }

                    if (!section.code.trim()) {
                        Swal.fire({
                            title: 'Section Code Required',
                            text: 'Please provide a code for all sections.',
                            icon: 'warning'
                        });
                        return false;
                    }

                    if (section.steps.length === 0) {
                        Swal.fire({
                            title: 'Steps Required',
                            text: `Section "${section.name}" must have at least one step.`,
                            icon: 'warning'
                        });
                        return false;
                    }

                    for (const step of section.steps) {
                        if (step.is_entry_point && step.dependency_steps && step.dependency_steps.length > 0) {
                            Swal.fire({
                                title: 'Invalid Entry Point',
                                text: `Entry point "${step.step_name || 'Step ' + step.globalNumber}" cannot have parent dependencies.`,
                                icon: 'warning'
                            });
                            return false;
                        }
                    }
                }

                return true;
            }

            window.addStepToSection = addStepToSection;

            $('#addSection').click(addSection);
            $('#cloneSection').click(cloneSection);
            $('#deleteSection').click(deleteSection);

            $('#templateForm').on('submit', function (e) {
                e.preventDefault();

                if (currentSectionId && sections[currentSectionId]) {
                    saveCurrentStepData(currentSectionId);
                }

                if (validateForm()) {
                    $.ajax({
                        url: "{{ route('workflow-templates.store') }}",
                        type: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}",
                            data: sections,
                            title: function () {
                                return $('[name="title"]').val();
                            },
                            description: function () {
                                return $('[name="description"]').val();
                            },
                            status: function () {
                                return $('#status option:selected').val();
                            }
                        },
                        beforeSend: function () {
                            $('body').find('.LoaderSec').removeClass('d-none');
                        },
                        success: function (response) {
                            Swal.fire('Success', 'Workflow template saved succesfully', 'success');
                            location.href = "{{ route('workflow-templates.index') }}";
                        },
                        error: function (response) {
                            if ('responseJSON' in response && 'errors' in response.responseJSON) {
                                if ('name' in response.responseJSON.errors) {
                                    if (response.responseJSON.errors.name.length > 0) {
                                        Swal.fire('Error', response.responseJSON.errors.name[0], 'error');
                                    }
                                }
                            }
                        },
                        complete: function () {
                            $('body').find('.LoaderSec').addClass('d-none');
                        }
                    });
                }
            });

            initializeSortable();
        });
    </script>
@endpush