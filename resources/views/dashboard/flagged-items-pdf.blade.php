<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flagged Items Report</title>
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
                    <div>Flagged Items</div>
                </h3>
            </center> <br> <br>

            <table class="task-table">
                <thead>
                    <tr>
                        <th class="item-name">Item Name</th>
                        <th class="item-name">Checklist</th>
                        <th class="dom-name">DoM</th>
                        <th class="location-name">Location</th>
                        <th>City</th>
                        <th>State</th>
                        <th>Initial Status</th>
                        <th>Latest Status</th>
                        <th>Last Updated</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($data as $items)
                    @if(isset($items[0]))
                    @foreach ($items as $item)
                    <tr>
                        <td class="item-name">{{ $item['item_name'] ?? '' }}</td>
                        <td class="item-name">{{ $item['clistname'] ?? '' }}</td>
                        <td class="dom-name">{{ $item['dom_name'] ?? '' }}</td>
                        <td class="location-name">{{ $item['location_name'] ?? '' }}</td>
                        <td>{{ $item['city_name'] ?? '' }}</td>
                        <td>{{ $item['state_name'] ?? '' }}</td>
                        <td>{{ $item['initial_status_name'] ?? '' }}</td>
                        <td>{{ $item['latest_status_name'] ?? '' }}</td>
                        <td>{{ $item['last_updated'] ?? '' }}</td>
                    </tr>
                    @endforeach
                    @endif
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 20px;">No data found</td>
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