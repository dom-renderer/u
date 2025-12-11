
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ APP_NAME }} | Daily Tickets</title>
    <style>
        * {
            box-sizing: border-box;
        }
        
        body {
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            font-size: 1rem;
            font-weight: 400;
            line-height: 1.5;
            color: #212529;
            background-color: #fff;
        }
        
        .wrapper {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .section {
            margin-bottom: 2rem;
        }
        
        /* Navigation Tabs */
        .nav {
            display: flex;
            flex-wrap: wrap;
            padding-left: 0;
            margin-bottom: 0;
            list-style: none;
        }
        
        .nav-tabs {
            border-bottom: 1px solid #dee2e6;
        }
        
        .nav-item {
            margin-bottom: -1px;
        }
        
        .col-3 {
            flex: 0 0 auto;
            width: 25%;
        }
        
        .nav-link {
            display: block;
            padding: 0.5rem 1rem;
            color: #0d6efd;
            text-decoration: none;
            background: none;
            border: 1px solid transparent;
            border-top-left-radius: 0.375rem;
            border-top-right-radius: 0.375rem;
            cursor: pointer;
            font-size: 1rem;
            width: 100%;
            text-align: center;
        }
        
        .nav-link:hover {
            color: #0a58ca;
            border-color: #e9ecef #e9ecef #dee2e6;
        }
        
        .nav-link.active {
            color: #495057;
            background-color: #fff;
            border-color: #dee2e6 #dee2e6 #fff;
        }
        
        /* Tab Content */
        .tab-content {
            margin-top: 1rem;
        }
        
        .tab-pane {
            display: none;
        }
        
        .tab-pane.show.active {
            display: block;
        }
        
        /* Table Styles */
        .table {
            width: 100%;
            margin-bottom: 1rem;
            color: #212529;
            border-collapse: collapse;
        }
        
        .table th,
        .table td {
            padding: 0.5rem;
            vertical-align: top;
            border-top: 1px solid #dee2e6;
            text-align: left;
        }
        
        .table thead th {
            vertical-align: bottom;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            background-color: #f8f9fa;
        }
        
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(0, 0, 0, 0.05);
        }
        
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .nav-item {
                width: 100%;
                margin-bottom: 0.5rem;
            }
            
            .col-3 {
                width: 100%;
            }
            
            .table {
                font-size: 0.875rem;
            }
            
            .table th,
            .table td {
                padding: 0.25rem;
            }
        }
        
        /* Custom Styles */
        .wrapper {
            background-color: #f8f9fa;
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        
        .section {
            background-color: #fff;
            padding: 1.5rem;
            border-radius: 0.375rem;
        }
        
        /* Priority and Status Badges */
        .badge {
            display: inline-block;
            padding: 0.35em 0.65em;
            font-size: 0.75em;
            font-weight: 700;
            line-height: 1;
            color: #fff;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 0.375rem;
        }
        
        .badge-primary { background-color: #0d6efd; }
        .badge-success { background-color: #198754; }
        .badge-warning { background-color: #ffc107; color: #000; }
        .badge-danger { background-color: #dc3545; }
    </style>
</head>
<body>
    <div class="wrapper">

        <div class="section">
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item col-3" role="presentation">
                    <button class="nav-link active" id="home-tab" onclick="showTab('home', this)" type="button" role="tab" aria-controls="home" aria-selected="true">Pending</button>
                </li>
            </ul>
            
            <div class="tab-content" id="myTabContent">
                <!-- Pending Tickets Tab -->
                <div class="tab-pane show active" id="home" role="tabpanel" aria-labelledby="home-tab">
                    <div class="table-responsive">
                        <table class="table table-striped" id="ticket-table-a">
                            <thead>
                                <tr>
                                    <th>Ticket ID</th>
                                    <th>Title</th>
                                    <th>Location</th>
                                    <th>City</th>
                                    <th>Department</th>
                                    <th>Priority</th>
                                    <th>DoM</th>
                                    <th>Date Opened</th>
                                    {{-- <th>Day Opened</th> --}}
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse (\App\Models\Ticket::with(['tsk' => function ($builder) {
                                    $builder->withTrashed();
                                }, 'tsk.parent' => function ($builder) {
                                    $builder->withTrashed();
                                },'tsk.parent.parent' => function ($builder) {
                                    $builder->withTrashed();
                                }, 'tsk.parent.actstore' => function ($builder) {
                                    $builder->withTrashed();
                                }, 'tsk.parent.user' => function ($builder) {
                                    $builder->withTrashed();
                                }])
                                ->whereNotNull('task_id')
                                ->where(\DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d')"), '>=', date('Y-m-d'))
                                ->where('status_id', 1)->where(function ($innerBuilder) {
                                    $innerBuilder->whereNull('completed_at');
                                })
                                ->get()
                                as $row)
                                <tr>
                                    <td> {{ '<a href="'.route('tickets.show',$row->ticket_number).'">'.$row->ticket_number.'</a>' }} </td>
                                    <td> {{ $row->subject }} </td>
                                    <td> {{ $row->department->name ?? '' }} </td>
                                    <td> {{ $row->priority->name ?? '' }} </td>
                                    <td> {{ isset($row->tsk->parent->actstore->id) ? ($row->tsk->parent->actstore->code . ' - ' . $row->tsk->parent->actstore->name) : '' }} </td>
                                    <td> {{ $row->tsk->parent->actstore->thecity->city_name ?? '' }} </td>
                                    <td> {{ $row->tsk->parent->user->name ?? '' }} </td>
                                    <td> {{ \Carbon\Carbon::parse($row->created_at)->format('d-m-Y H:i') }} </td>
                                    {{-- <td> {{ \Carbon\Carbon::parse($row->created_at)->diffInDays(now()) }} </td> --}}
                                    <td> {{ $row->status->name ?? '' }} </td>
                                </tr>

                                @empty
                                    
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>





        <div class="section">
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item col-3" role="presentation">
                    <button class="nav-link active" id="profile-tab" onclick="showTab('profile', this)" type="button" role="tab" aria-controls="profile" aria-selected="false">In-Progress</button>
                </li>
            </ul>
            
            <div class="tab-content" id="myTabContent">
                <!-- Pending Tickets Tab -->
                <div class="tab-pane show active" id="home" role="tabpanel" aria-labelledby="home-tab">
                    <div class="table-responsive">
                        <table class="table table-striped" id="ticket-table-b">
                            <thead>
                                <tr>
                                    <th>Ticket ID</th>
                                    <th>Title</th>
                                    <th>Department</th>
                                    <th>Priority</th>
                                    <th>Location</th>
                                    <th>City</th>
                                    <th>DoM</th>
                                    <th>Date Opened</th>
                                    {{-- <th>Day Opened</th> --}}
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse (\App\Models\Ticket::with(['tsk' => function ($builder) {
                                    $builder->withTrashed();
                                }, 'tsk.parent' => function ($builder) {
                                    $builder->withTrashed();
                                },'tsk.parent.parent' => function ($builder) {
                                    $builder->withTrashed();
                                }, 'tsk.parent.actstore' => function ($builder) {
                                    $builder->withTrashed();
                                }, 'tsk.parent.user' => function ($builder) {
                                    $builder->withTrashed();
                                }])
                                ->whereNotNull('task_id')
                                ->where(\DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d')"), '>=', date('Y-m-d'))
                                ->where('status_id', 2)->where(function ($innerBuilder) {
                                    $innerBuilder->whereNull('completed_at');
                                })
                                ->get()
                                as $row)

                                <tr>
                                    <td> {{ '<a href="'.route('tickets.show',$row->ticket_number).'">'.$row->ticket_number.'</a>' }} </td>
                                    <td> {{ $row->subject }} </td>
                                    <td> {{ $row->department->name ?? '' }} </td>
                                    <td> {{ $row->priority->name ?? '' }} </td>
                                    <td> {{ isset($row->tsk->parent->actstore->id) ? ($row->tsk->parent->actstore->code . ' - ' . $row->tsk->parent->actstore->name) : '' }} </td>
                                    <td> {{ $row->tsk->parent->actstore->thecity->city_name ?? '' }} </td>
                                    <td> {{ $row->tsk->parent->user->name ?? '' }} </td>
                                    <td> {{ \Carbon\Carbon::parse($row->created_at)->format('d-m-Y H:i') }} </td>
                                    {{-- <td> {{ \Carbon\Carbon::parse($row->created_at)->diffInDays(now()) }} </td> --}}
                                    <td> {{ $row->status->name ?? '' }} </td>
                                </tr>

                                @empty
                                    
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>















            <div class="section">
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item col-3" role="presentation">
                    <button class="nav-link active" id="contact-tab" onclick="showTab('contact', this)" type="button" role="tab" aria-controls="contact" aria-selected="false">Completed</button>
                </li>
            </ul>
            
            <div class="tab-content" id="myTabContent">
                <!-- Pending Tickets Tab -->
                <div class="tab-pane show active" id="home" role="tabpanel" aria-labelledby="home-tab">
                    <div class="table-responsive">
                                                <table class="table table-striped" id="ticket-table-c">
                            <thead>
                                <tr>
                                    <th>Ticket ID</th>
                                    <th>Title</th>
                                    <th>Department</th>
                                    <th>Priority</th>
                                    <th>Location</th>
                                    <th>City</th>
                                    <th>DoM</th>
                                    <th>Date Opened</th>
                                    {{-- <th>Day Opened</th> --}}
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse (\App\Models\Ticket::with(['tsk' => function ($builder) {
                                    $builder->withTrashed();
                                }, 'tsk.parent' => function ($builder) {
                                    $builder->withTrashed();
                                },'tsk.parent.parent' => function ($builder) {
                                    $builder->withTrashed();
                                }, 'tsk.parent.actstore' => function ($builder) {
                                    $builder->withTrashed();
                                }, 'tsk.parent.user' => function ($builder) {
                                    $builder->withTrashed();
                                }])
                                ->whereNotNull('task_id')
                                ->whereNotNull('completed_at')->where('completed_at', '!=', '')
                                ->where(\DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d')"), '>=', date('Y-m-d'))
                                ->get()
                                as $row)

                                <tr>
                                    <td> {{ '<a href="'.route('tickets.show',$row->ticket_number).'">'.$row->ticket_number.'</a>' }} </td>
                                    <td> {{ $row->subject }} </td>
                                    <td> {{ $row->department->name ?? '' }} </td>
                                    <td> {{ $row->priority->name ?? '' }} </td>
                                    <td> {{ isset($row->tsk->parent->actstore->id) ? ($row->tsk->parent->actstore->code . ' - ' . $row->tsk->parent->actstore->name) : '' }} </td>
                                    <td> {{ $row->tsk->parent->actstore->thecity->city_name ?? '' }} </td>
                                    <td> {{ $row->tsk->parent->user->name ?? '' }} </td>
                                    <td> {{ \Carbon\Carbon::parse($row->created_at)->format('d-m-Y H:i') }} </td>
                                    {{-- <td> {{ \Carbon\Carbon::parse($row->created_at)->diffInDays(now()) }} </td> --}}
                                    <td> {{ $row->status->name ?? '' }} </td>
                                </tr>

                                @empty
                                    
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>















                <div class="section">
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item col-3" role="presentation">
                    <button class="nav-link active" id="stale-tab" onclick="showTab('stale', this)" type="button" role="tab" aria-controls="stale" aria-selected="false">Stale</button>
                </li>
            </ul>
            
            <div class="tab-content" id="myTabContent">
                <!-- Pending Tickets Tab -->
                <div class="tab-pane show active" id="home" role="tabpanel" aria-labelledby="home-tab">
                    <div class="table-responsive">
                                                <table class="table table-striped" id="ticket-table-d">
                            <thead>
                                <tr>
                                    <th>Ticket ID</th>
                                    <th>Title</th>
                                    <th>Department</th>
                                    <th>Priority</th>
                                    <th>Location</th>
                                    <th>City</th>
                                    <th>DoM</th>
                                    <th>Date Opened</th>
                                    <th>Day Opened</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse (\App\Models\Ticket::with(['tsk' => function ($builder) {
                                    $builder->withTrashed();
                                }, 'tsk.parent' => function ($builder) {
                                    $builder->withTrashed();
                                },'tsk.parent.parent' => function ($builder) {
                                    $builder->withTrashed();
                                }, 'tsk.parent.actstore' => function ($builder) {
                                    $builder->withTrashed();
                                }, 'tsk.parent.user' => function ($builder) {
                                    $builder->withTrashed();
                                }])
                                ->whereNotNull('task_id')
                                ->where(\DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d')"), '<=', date('Y-m-d', strtotime('-2 days')))
                                ->whereNull('completed_at')
                                ->get()
                                as $row)

                                <tr>
                                    <td> {{ '<a href="'.route('tickets.show',$row->ticket_number).'">'.$row->ticket_number.'</a>' }} </td>
                                    <td> {{ $row->subject }} </td>
                                    <td> {{ $row->department->name ?? '' }} </td>
                                    <td> {{ $row->priority->name ?? '' }} </td>
                                    <td> {{ isset($row->tsk->parent->actstore->id) ? ($row->tsk->parent->actstore->code . ' - ' . $row->tsk->parent->actstore->name) : '' }} </td>
                                    <td> {{ $row->tsk->parent->actstore->thecity->city_name ?? '' }} </td>
                                    <td> {{ $row->tsk->parent->user->name ?? '' }} </td>
                                    <td> {{ \Carbon\Carbon::parse($row->created_at)->format('d-m-Y H:i') }} </td>
                                    <td> {{ \Carbon\Carbon::parse($row->created_at)->diffInDays(now()) }} </td>
                                    <td> {{ $row->status->name ?? '' }} </td>
                                </tr>

                                @empty
                                    
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>



















    </div>

    <script>
        function showTab(tabId, element) {
            // var tabPanes = document.querySelectorAll('.tab-pane');
            // tabPanes.forEach(function(pane) {
            //     pane.classList.remove('show', 'active');
            // });
            
            // var navLinks = document.querySelectorAll('.nav-link');
            // navLinks.forEach(function(link) {
            //     link.classList.remove('active');
            //     link.setAttribute('aria-selected', 'false');
            // });
            
            // var selectedPane = document.getElementById(tabId);
            // if (selectedPane) {
            //     selectedPane.classList.add('show', 'active');
            // }
            
            // element.classList.add('active');
            // element.setAttribute('aria-selected', 'true');
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            var firstTab = document.querySelector('.nav-link.active');
            if (firstTab) {
                var tabId = firstTab.getAttribute('aria-controls') || 'home';
                showTab(tabId, firstTab);
            }
        });
    </script>
</body>
</html>