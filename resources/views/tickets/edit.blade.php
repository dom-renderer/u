@extends('layouts.app-master')

@push('css')
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/twitter-bootstrap.min.css') }}" />
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/datatable-bootstrap.css') }}" />
<link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/css/quill.snow.css') }}" rel="stylesheet" />
<link href="https://unpkg.com/filepond@4/dist/filepond.min.css" rel="stylesheet" />
<link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css" rel="stylesheet" />
<style>
    .timeline-container {
        position: relative;
        padding: 20px 0;
    }

    .timeline-line {
        position: absolute;
        left: 30px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e0e0e0;
    }

    .timeline-date-label {
        position: relative;
        margin: 30px 0 20px 0;
        padding-left: 70px;
    }

    .timeline-date-badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
        color: white;
        background: #dc3545;
    }

    .timeline-date-badge.green {
        background: #28a745;
    }

    .timeline-item {
        position: relative;
        margin-bottom: 30px;
        padding-left: 70px;
    }

    .timeline-icon {
        position: absolute;
        left: 0;
        top: 0;
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 24px;
        z-index: 1;
        border: 3px solid white;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .timeline-icon.blue {
        background: #007bff;
    }

    .timeline-icon.green {
        background: #28a745;
    }

    .timeline-icon.orange {
        background: #ff9800;
    }

    .timeline-icon.purple {
        background: #9c27b0;
    }

    .timeline-icon.red {
        background: #dc3545;
    }

    .timeline-card {
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        margin-left: 10px;
    }

    .timeline-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
    }

    .timeline-title {
        font-weight: 600;
        font-size: 16px;
        color: #333;
        margin: 0;
    }

    .timeline-time {
        font-size: 13px;
        color: #666;
    }

    .timeline-content {
        color: #555;
        line-height: 1.6;
        margin-bottom: 15px;
    }

    .timeline-content-desc {
        color: #383838ff;
        line-height: 1.6;
        display: flex!important;
    }    

    .timeline-content p {
        margin-bottom: 10px;
    }

    .timeline-attachments {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 15px;
    }

    .timeline-attachment {
        width: 100px;
        height: 100px;
        border-radius: 6px;
        overflow: hidden;
        border: 1px solid #e0e0e0;
    }

    .timeline-attachment img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .timeline-empty {
        text-align: center;
        padding: 40px;
        color: #999;
    }
</style>
@endpush

@section('content')
@php
use App\Models\NewTicket;
$currentStatus = old('status', $ticket->status === NewTicket::STATUS_CLOSED ? NewTicket::STATUS_CLOSED : ($ticket->status === NewTicket::STATUS_ACCEPTED ? NewTicket::STATUS_ACCEPTED : NewTicket::STATUS_IN_PROGRESS));
@endphp
<div class="bg-light p-4 rounded">
    <h1>{{ $page_title }}</h1>
    <div class="mt-2">
        @include('layouts.partials.messages')
    </div>

    <form method="POST" action="{{ route('ticket-management.update', encrypt($ticket->id)) }}" id="ticketEditForm" class="mt-4" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        @if($isReopen)
        <input type="hidden" name="is_reopen" value="1">
        @endif

        <div class="row g-3 py-3">

            <div class="col-lg-8">
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div>
                                <h5 class="mb-1">{{ $ticket->subject }}</h5>
                                <div class="text-muted small">Created on {{ date('d F, Y H:i A', strtotime($ticket->created_at)) }}</div>
                            </div>
                            <span class="badge bg-success-subtle text-success border border-success">
                                {{ ucwords(str_replace('_', ' ', $ticket->status)) }}
                            </span>
                        </div>
                        <div class="mt-3 text-muted">{!! $ticket->description !!}</div>

                        @if(!empty($ticket->attachments))
                        <div class="mb-4">
                            <label class="form-label">Images</label>
                            <div class="d-flex flex-wrap gap-3">
                                @foreach($ticket->attachments as $image)
                                <div class="border rounded p-2" style="width: 140px;">
                                    <a href="{{ asset('storage/'.$image) }}" target="_blank">
                                        <img src="{{ asset('storage/'.$image) }}" class="img-fluid rounded" alt="Attachment">
                                    </a>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="card">
                    <div class="card-header bg-white">
                        <div class="d-flex align-items-center gap-2">
                            <div class="border-start border-3 border-success ps-2 fw-semibold">Reply Ticket</div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="extra_users" class="form-label"> Assign Users to Ticket </label>
                            <select name="extra_users[]" id="extra_users" multiple>
                                @foreach ($newTicketOwners as $to)
                                    @if(isset($to->user->id))
                                        <option value="{{ $to->user->id }}" selected> {{ $to->user->employee_id }} - {{ $to->user->name }} {{ $to->user->middle_name }} {{ $to->user->last_name }} </option>
                                    @endif
                                @endforeach
                            </select>
                            @error('extra_users')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Reply <span class="text-danger">*</span></label>
                            <div id="ticketReplyEditor" style="height: 200px;"></div>
                            <input type="hidden" name="reply" id="ticketReply">
                            @error('reply')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mt-3">
                            <div class="">
                                <label for="editAttachments" class="form-label">Upload Images</label>
                                <input type="file" name="attachments[]" id="editAttachments" class="filepond" accept="image/*" multiple>
                                <small class="text-muted d-block mt-1">The file size should not be more than 3MB.</small>
                                @error('attachments')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                                @error('attachments.*')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-3">
                                @if(!$isReopen)
                                <div class="mb-4">
                                    <label class="form-label d-block">Status <span class="text-danger">*</span></label>
                                    @if($ticket->status == NewTicket::STATUS_ACCEPTED)
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="status" id="statusInAccepted" value="{{ NewTicket::STATUS_ACCEPTED }}" {{ $currentStatus === NewTicket::STATUS_ACCEPTED ? 'checked' : '' }}>
                                            <label class="form-check-label" for="statusInAccepted">Accepted</label>
                                        </div>
                                    @else
                                        <input class="form-check-input" type="hidden" name="status" value="{{ NewTicket::STATUS_ACCEPTED }}">
                                    @endif
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="status" id="statusInProgress" value="{{ NewTicket::STATUS_IN_PROGRESS }}" {{ $currentStatus === NewTicket::STATUS_IN_PROGRESS ? 'checked' : '' }}>
                                        <label class="form-check-label" for="statusInProgress">In Progress</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="status" id="statusSolved" value="{{ NewTicket::STATUS_CLOSED }}" {{ $currentStatus === NewTicket::STATUS_CLOSED ? 'checked' : '' }}>
                                        <label class="form-check-label" for="statusSolved">Close</label>
                                    </div>
                                    @error('status')
                                    <div><span class="text-danger">{{ $message }}</span></div>
                                    @enderror
                                </div>
                                @else
                                    <label class="form-label d-block">Status <span class="text-danger">*</span></label>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="status" id="statusReOpen" value="{{ NewTicket::STATUS_ACCEPTED }}" {{ $currentStatus === NewTicket::STATUS_ACCEPTED ? 'checked' : '' }}>
                                        <label class="form-check-label" for="statusReOpen">Re-Open</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="status" id="statusClosed" value="{{ NewTicket::STATUS_CLOSED }}" {{ $currentStatus === NewTicket::STATUS_CLOSED ? 'checked' : '' }}>
                                        <label class="form-check-label" for="statusClosed">Close</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="status" id="statusInProgress" value="{{ NewTicket::STATUS_IN_PROGRESS }}" {{ $currentStatus === NewTicket::STATUS_IN_PROGRESS || $currentStatus === NewTicket::STATUS_ACCEPTED ? 'checked' : '' }}>
                                        <label class="form-check-label" for="statusInProgress">In Progress</label>
                                    </div>
                                    @error('status')
                                    <div><span class="text-danger">{{ $message }}</span></div>
                                    @enderror
                                @endif
                        </div>

                        <div class="mt-3 d-flex justify-content-end">
                            <a href="{{ route('ticket-management.index') }}" class="btn btn-secondary me-2">Back</a>
                            <button type="submit" class="btn btn-success">Update Ticket</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card mb-3">
                    <div class="card-header bg-white d-flex align-items-center justify-content-between">
                        <div class="fw-semibold">Ticket Information</div>
                    </div>
                    <div class="card-body">
                        <div class="row g-2 small">
                            <div class="col-6">Ticket ID</div>
                            <div class="col-6 text-muted">{{ $ticket->ticket_number }}</div>
                            <div class="col-6">Department</div>
                            <div class="col-6 text-muted">{{ optional($ticket->department)->name ?: '-' }}</div>
                            <div class="col-6">Particulars</div>
                            <div class="col-6 text-muted">{{ optional($ticket->particular)->name ?: '-' }}</div>
                            <div class="col-6">Issue</div>
                            <div class="col-6 text-muted">{{ optional($ticket->issue)->name ?: '-' }}</div>
                            <div class="col-6">Assigned Users</div>
                            <div class="col-6 text-muted">
                                    <strong>
                                    {!! implode('<br/>', $ticket->primaryOwners->map(function ($owner) {
                                        if (isset($owner->user->id)) {
                                            return trim($owner->user->employee_id . ' ' . $owner->user->name . ' ' . $owner->user->middle_name . ' ' . $owner->user->last_name);
                                        } else {
                                            return 'N/A';
                                        }
                                    })->values()->toArray()) !!}
                                </strong>
                            </div>
                            <div class="col-6">Extra Assigned Users</div>
                            <div class="col-6 text-muted">
                                    <strong>
                                    {!! implode('<br/>', $ticket->secondaryOwners->map(function ($owner) {
                                        if (isset($owner->user->id)) {
                                            return trim($owner->user->employee_id . ' ' . $owner->user->name . ' ' . $owner->user->middle_name . ' ' . $owner->user->last_name);
                                        } else {
                                            return 'N/A';
                                        }
                                    })->values()->toArray()) !!}
                                </strong>
                            </div>
                            <div class="col-6">Ticket Priority</div>
                            <div class="col-6"><span class="badge bg-danger">{{ ucwords(str_replace('_', ' ', $ticket->priority)) }}</span></div>
                            <div class="col-6">Open Date</div>
                            <div class="col-6 text-muted">{{ date('d F, Y H:i A', strtotime($ticket->created_at)) }}</div>
                            <div class="col-6">Ticket Status</div>
                            <div class="col-6"><span class="badge bg-success">{{ ucwords(str_replace('_', ' ', $ticket->status)) }}</span></div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header bg-white">
                        <div class="fw-semibold">Location Details</div>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <span class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center" style="width:64px;height:64px;"><i class="bi bi-person" style="font-size: 1.5rem;"></i></span>
                            <div>
                                <div class="fw-semibold">{{ $ticket->store->code ?? '' }} {{ $ticket->store->name ?? '' }} </div>
                                <div class="text-muted small">{{ $ticket->store->modeltype->name ?? '' }} | {{ $ticket->store->storetype->name ?? '' }}</div>
                            </div>
                        </div>
                        <div class="row g-2 small">
                            <div class="col-4">Email</div>
                            <div class="col-8 text-truncate"><a href="mailto:{{ $ticket->store->email ?? '' }}">{{ $ticket->store->email ?? 'N/A' }}</a></div>
                            <div class="col-4">Phone</div>
                            <div class="col-8 text-muted">{{ $ticket->store->mobile ?? 'N/A' }}</div>
                            <div class="col-4">Whatsapp Number</div>
                            <div class="col-8 text-muted">{{ $ticket->store->whatsapp ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <div class="">
        <div class="">
            <h5 class="mb-0">Ticket History</h5>
        </div>
        <div class="">
            @if($histories->isEmpty())
            <div class="timeline-empty">
                <p class="mb-0">No history available.</p>
            </div>
            @else
            @php
            $groupedHistories = $histories->groupBy(function($history) {
            return $history->created_at ? $history->created_at->format('Y-m-d') : 'unknown';
            });
            $currentDate = null;
            @endphp
            <div class="timeline-container">
                <div class="timeline-line"></div>
                @foreach($groupedHistories as $date => $dateHistories)
                @php
                $displayDate = \Carbon\Carbon::parse($date)->format('d M. Y');
                $isFirstDate = $currentDate === null;
                $currentDate = $date;
                @endphp
                <div class="timeline-date-label">
                    <span class="timeline-date-badge {{ $isFirstDate ? '' : 'green' }}">
                        {{ $displayDate }}
                    </span>
                </div>
                @foreach($dateHistories as $history)
                @php
                $type = $history->type ?? 'reply';
                $iconClass = 'bi-envelope';
                $iconColor = 'blue';

                if ($type === 'created') {
                $iconClass = 'bi-plus-circle';
                $iconColor = 'green';
                } elseif ($type === 'accepted') {
                $iconClass = 'bi-check-circle';
                $iconColor = 'green';
                } elseif (isset($history->data['reopened']) && $history->data['reopened']) {
                $iconClass = 'bi-arrow-clockwise';
                $iconColor = 'red';
                } elseif ($type === 'reply') {
                $iconClass = 'bi-chat-dots';
                $iconColor = 'orange';
                } elseif ($type === 'closed') {
                $iconClass = 'bi bi-x-circle';
                $iconColor = 'red';
                }

                $authorName = optional($history->author)->name ?: 'System';
                $timeDisplay = $history->created_at ? $history->created_at->format('H:i') : '';
                $timeAgo = $history->created_at ? $history->created_at->diffForHumans() : '';
                @endphp
                <div class="timeline-item">
                    <div class="timeline-icon {{ $iconColor }}">
                        <i class="bi {{ $iconClass }}"></i>
                    </div>
                    <div class="timeline-card">
                        <div class="timeline-header">
                            <h6 class="timeline-title">
                                @if($type === 'created')
                                {{ $authorName }} created this ticket
                                @elseif($type === 'closed')
                                {{ $authorName }} closed this ticket
                                @elseif($type === 'accepted')
                                {{ $authorName }} accepted this ticket
                                @elseif(isset($history->data['reopened']) && $history->data['reopened'])
                                {{ $authorName }} reopened this ticket
                                @else
                                {{ $authorName }} replied
                                @endif
                            </h6>
                            <span class="timeline-time">{{ $timeDisplay }}</span>
                        </div>
                        <div class="timeline-content">
                            {!! $history->description !!}
                        </div>
                        @if(isset($history->data['description']) && !empty($history->data['description']))
                        <div class="timeline-content-desc">
                            <strong> Comment : &nbsp;&nbsp; </strong>  {!! $history->data['description'] !!}
                        </div>
                        @endif
                        @if(!empty($history->attachments))
                        <div class="timeline-attachments">
                            @foreach($history->attachments as $image)
                            <a href="{{ asset('storage/'.$image) }}" target="_blank" class="timeline-attachment">
                                <img src="{{ asset('storage/'.$image) }}" alt="History Attachment">
                            </a>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('js')
<script src="{{ asset('assets/js/select2.min.js') }}"></script>
<script src="{{ asset('assets/js/quill.min.js') }}"></script>
<script src="https://unpkg.com/filepond@4/dist/filepond.min.js"></script>
<script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.js"></script>
<script src="https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.min.js"></script>
<script>
    $(function() {
        const csrfToken = "{{ csrf_token() }}";

        const quill = new Quill('#ticketReplyEditor', {
            theme: 'snow',
            modules: {
                toolbar: [
                    [{
                        header: [1, 2, false]
                    }],
                    ['bold', 'italic', 'underline'],
                    [{
                        list: 'ordered'
                    }, {
                        list: 'bullet'
                    }],
                    ['link'],
                    ['clean']
                ]
            }
        });

        const operatorUser = $('#extra_users').select2({
            placeholder: 'Please Select',
            allowClear: true,
            width: '100%',
            theme: 'classic',
            ajax: {
                url: "{{ route('users-list') }}",
                type: 'POST',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        searchQuery: params.term,
                        page: params.page || 1,
                        ignoreDesignation: 1,
                        _token: csrfToken
                    };
                },
                processResults: function(data, params) {
                    params.page = params.page || 1;
                    return {
                        results: $.map(data.items, function(item) {
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

        FilePond.registerPlugin(FilePondPluginImagePreview);
        FilePond.registerPlugin(FilePondPluginFileValidateSize);

        const filepondInput = document.querySelector('#editAttachments');
        const pond = FilePond.create(filepondInput, {
            maxFileSize: '3MB',
            imagePreviewHeight: 170,
            server: null,
            storeAsFile: true,
            allowMultiple: true,
            acceptedFileTypes: ['image/*']
        });

        $('#ticketEditForm').on('submit', function(e) {
            const html = quill.root.innerHTML.trim();
            const plain = quill.getText().trim();

            if (plain.length === 0) {
                e.preventDefault();
                alert('Reply is required.');
                return false;
            }

            $('#ticketReply').val(html);

            return true;
        });
    });
</script>
@endpush