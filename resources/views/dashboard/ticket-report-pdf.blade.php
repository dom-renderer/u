<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tickets Report</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background-color: white;
            padding: 0;
            font-size: 12px;
        }
        
        .email-container {
            max-width: 100%;
            margin: 0 auto;
            background-color: white;
        }
        
        .header {
            background-color: #2d7d32;
            color: white;
            padding: 15px 20px;
            position: relative;
            text-align: center;
        }
        
        .logo img {
            width: 40px;
            height: auto;
        }
        
        .title {
            background-color: #1976d2;
            color: white;
            padding: 8px 16px;
            border-radius: 4px;
            font-size: 16px;
            font-weight: bold;
            display: inline-block;
        }
        
        .content {
            padding: 20px;
            color: #333;
        }
        
        .task-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background-color: white;
            font-size: 11px;
        }
        
        .task-table th {
            background-color: #e9ecef;
            padding: 8px 4px;
            text-align: left;
            font-weight: bold;
            color: #495057;
            border: 1px solid #dee2e6;
            font-size: 10px;
            vertical-align: top;
            word-wrap: break-word;
        }
        
        .task-table td {
            padding: 8px 4px;
            border: 1px solid #dee2e6;
            vertical-align: top;
            word-wrap: break-word;
            max-width: 120px;
        }
        
        .item-name {
            max-width: 150px;
            word-break: break-word;
        }
        
        .dom-name {
            max-width: 120px;
            word-break: break-word;
        }
        
        .location-name {
            max-width: 100px;
            word-break: break-word;
        }
        
        .footer-text {
            margin-bottom: 15px;
            color: #666;
            line-height: 1.4;
            font-size: 12px;
        }
        
        .portal-button {
            background-color: #2d7d32;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        
        .bottom-footer {
            background-color: #2d7d32;
            color: white;
            padding: 12px;
            text-align: center;
            font-size: 11px;
        }
        
        @media print {
            body {
                background-color: white;
                padding: 0;
            }
            
            .email-container {
                box-shadow: none;
                border-radius: 0;
            }
            
            .task-table {
                page-break-inside: avoid;
            }
            
            .task-table th,
            .task-table td {
                padding: 6px 3px;
                font-size: 9px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <div class="logo">
                <img src="{{ public_path('assets/logo.webp') }}" alt="Tea Post Logo">
            </div>
        </div>
        
        <div class="content">

            <table>
                <tbody>
                    <tr>
                        <td colspan="2"> Report Generated Date </td>
                        <td colspan="8"> 
                            <div style="width: 100%;text-align:right;float: right;">
                                {{ date('d F Y, H:i') }}
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>


            <center>
                <h3>
                    <div>Opened Tickets</div>
                </h3>
            </center> <br> <br>

            <table class="task-table">
                <thead>
                    <tr>
                        <th class="item-name">Ticket ID</th>
                        <th class="dom-name">Title</th>
                        <th class="location-name">Location</th>
                        <th>City</th>
                        <th>Department</th>
                        <th>Priority</th>
                        <th>DoM</th>
                        <th>Date Opened</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pending as $item)
                    <tr>
                        <td><a href="{{ route('tickets.show',$item->ticket_number) }}"> {{ $item->ticket_number }} </a></td>
                        <td>{{ $item->subject }}</td>
                        <td>{{ isset($item->tsk->parent->actstore->id) ? ($item->tsk->parent->actstore->code . ' - ' . $item->tsk->parent->actstore->name) : '' }}</td>
                        <td>{{ $item->tsk->parent->actstore->thecity->city_name ?? '' }}</td>
                        <td>{{ $item->department->name ?? '' }}</td>
                        <td>{{ $item->priority->name ?? '' }}</td>
                        <td>{{ $item->tsk->parent->user->name ?? '' }}</td>
                        <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d-m-Y H:i') }}</td>
                        <td>{{ $item->status->name ?? '' }}</td>
                    </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 20px;">No data found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>


             <center>
                <h3>
                    <div>In-Progress Tickets</div>
                </h3>
            </center> <br> <br>

            <table class="task-table">
                <thead>
                    <tr>
                        <th class="item-name">Ticket ID</th>
                        <th class="dom-name">Title</th>
                        <th class="location-name">Location</th>
                        <th>City</th>
                        <th>Department</th>
                        <th>Priority</th>
                        <th>DoM</th>
                        <th>Date Opened</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($inprogress as $item)
                    <tr>
                        <td><a href="{{ route('tickets.show',$item->ticket_number) }}"> {{ $item->ticket_number }} </a></td>
                        <td>{{ $item->subject }}</td>
                        <td>{{ isset($item->tsk->parent->actstore->id) ? ($item->tsk->parent->actstore->code . ' - ' . $item->tsk->parent->actstore->name) : '' }}</td>
                        <td>{{ $item->tsk->parent->actstore->thecity->city_name ?? '' }}</td>
                        <td>{{ $item->department->name ?? '' }}</td>
                        <td>{{ $item->priority->name ?? '' }}</td>
                        <td>{{ $item->tsk->parent->user->name ?? '' }}</td>
                        <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d-m-Y H:i') }}</td>
                        <td>{{ $item->status->name ?? '' }}</td>
                    </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 20px;">No data found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            
             <center>
                <h3>
                    <div>On-Hold Tickets</div>
                </h3>
            </center> <br> <br>

            <table class="task-table">
                <thead>
                    <tr>
                        <th class="item-name">Ticket ID</th>
                        <th class="dom-name">Title</th>
                        <th class="location-name">Location</th>
                        <th>City</th>
                        <th>Department</th>
                        <th>Priority</th>
                        <th>DoM</th>
                        <th>Date Opened</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($onhold as $item)
                    <tr>
                        <td><a href="{{ route('tickets.show',$item->ticket_number) }}"> {{ $item->ticket_number }} </a></td>
                        <td>{{ $item->subject }}</td>
                        <td>{{ isset($item->tsk->parent->actstore->id) ? ($item->tsk->parent->actstore->code . ' - ' . $item->tsk->parent->actstore->name) : '' }}</td>
                        <td>{{ $item->tsk->parent->actstore->thecity->city_name ?? '' }}</td>
                        <td>{{ $item->department->name ?? '' }}</td>
                        <td>{{ $item->priority->name ?? '' }}</td>
                        <td>{{ $item->tsk->parent->user->name ?? '' }}</td>
                        <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d-m-Y H:i') }}</td>
                        <td>{{ $item->status->name ?? '' }}</td>
                    </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 20px;">No data found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>


             <center>
                <h3>
                    <div>Completed Tickets</div>
                </h3>
            </center> <br> <br>

            <table class="task-table">
                <thead>
                    <tr>
                        <th class="item-name">Ticket ID</th>
                        <th class="dom-name">Title</th>
                        <th class="location-name">Location</th>
                        <th>City</th>
                        <th>Department</th>
                        <th>Priority</th>
                        <th>DoM</th>
                        <th>Date Opened</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($completed as $item)
                    <tr>
                        <td><a href="{{ route('tickets.show',$item->ticket_number) }}"> {{ $item->ticket_number }} </a></td>
                        <td>{{ $item->subject }}</td>
                        <td>{{ isset($item->tsk->parent->actstore->id) ? ($item->tsk->parent->actstore->code . ' - ' . $item->tsk->parent->actstore->name) : '' }}</td>
                        <td>{{ $item->tsk->parent->actstore->thecity->city_name ?? '' }}</td>
                        <td>{{ $item->department->name ?? '' }}</td>
                        <td>{{ $item->priority->name ?? '' }}</td>
                        <td>{{ $item->tsk->parent->user->name ?? '' }}</td>
                        <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d-m-Y H:i') }}</td>
                        <td>{{ $item->status->name ?? '' }}</td>
                    </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 20px;">No data found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>


             <center>
                <h3>
                    <div>Stale Tickets</div>
                </h3>
            </center> <br> <br>

            <table class="task-table">
                <thead>
                    <tr>
                        <th class="item-name">Ticket ID</th>
                        <th class="dom-name">Title</th>
                        <th class="location-name">Location</th>
                        <th>City</th>
                        <th>Department</th>
                        <th>Priority</th>
                        <th>DoM</th>
                        <th>Date Opened</th>
                        <th>Day Opened</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($stale as $item)
                    <tr>
                        <td><a href="{{ route('tickets.show',$item->ticket_number) }}"> {{ $item->ticket_number }} </a></td>
                        <td>{{ $item->subject }}</td>
                        <td>{{ isset($item->tsk->parent->actstore->id) ? ($item->tsk->parent->actstore->code . ' - ' . $item->tsk->parent->actstore->name) : '' }}</td>
                        <td>{{ $item->tsk->parent->actstore->thecity->city_name ?? '' }}</td>
                        <td>{{ $item->department->name ?? '' }}</td>
                        <td>{{ $item->priority->name ?? '' }}</td>
                        <td>{{ $item->tsk->parent->user->name ?? '' }}</td>
                        <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d-m-Y H:i') }}</td>
                        <td>{{ \Carbon\Carbon::parse($item->created_at)->diffInDays(now()) }}</td>
                        <td>{{ $item->status->name ?? '' }}</td>
                    </tr>
                    @empty
                        <tr>
                            <td colspan="10" style="text-align: center; padding: 20px;">No data found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            
            <div class="footer-text" style="margin-top: 60px!important;">
                You can log in to the admin portal for full details and take necessary actions.
            </div>
            
            <a href="{{ route('flagged-items-dashboard') }}" class="portal-button">Go to Portal</a>
        </div>
        
        <div class="bottom-footer">
            © {{ date('Y') }} {{ APP_NAME }}
        </div>
    </div>
</body>
</html>