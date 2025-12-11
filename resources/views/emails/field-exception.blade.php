<!DOCTYPE html>
<html>
<head>
    <title>Task Exception Field Report</title>
    <link href='https://fonts.googleapis.com/css?family=Open Sans' rel='stylesheet'>
    <style type="text/css">
        .rc_mainWrap{
            min-width: 100%;
            display: block;
            float: left;
            padding: 10px;
            border: #065e2e 10px solid;
            font-family: 'Open Sans';
            line-height: 27px;

        }
        .rc_titleText{
            display: block;
            font-size: 18px;
            font-weight: 700;
            color: #001f3f;
            text-align: center;
        }
        .rc_bodyText{
            padding: 10px 0px;
        }
        .rc_btn{
            display: block;
            text-align: center;
            background: #f39c12;
            padding: 5px;
            color: #000;
            font-weight: 700;
            border-radius: 10px;
        }
        .rc_txtCenter{
            text-align: center;
        }
        .rc_emailLogo{
            width: 30%;
        }
        .rc_emailFooter{
            padding-top: 20px;
            font-size: 12px;
        }
        .rc_box {
            box-sizing: border-box;
            font-family: -apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif,'Apple Color Emoji','Segoe UI Emoji','Segoe UI Symbol';
        }
        .rc_action_button {
            border-radius: 4px;color: #fff;display: inline-block;overflow: hidden;text-decoration: none;background-color: #2d3748;border-bottom: 8px solid #2d3748;border-left: 18px solid #2d3748;border-right: 18px solid #2d3748;border-top: 8px solid #2d3748;
        }
        .work-break-all {
            word-break: break-all;
        }
    </style>
</head>
<body>
    <div class="rc_mainWrap">
        <div class="rc_txtCenter">
            <img class="rc_emailLogo" src="{{ url('assets/logo.webp') }}" border="0" style="height: 70px;width:70px;">
        </div>
        <div class="rc_titleText">
            {{ 'Task Exception Fields Report' }}
        </div>
        <div class="rc_bodyText">
           
        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 auto; background:#fff; border-radius:8px; overflow:hidden; border:1px solid #dee2e6;">
            <thead>
            <tr style="background-color:#198754; color:#fff; text-align:left;">
                <th style="padding:12px;">#</th>
                <th style="padding:12px;">Item Name</th>
                <th style="padding:12px;">DoM</th>
                <th style="padding:12px;">Location</th>
                <th style="padding:12px;">City</th>
                <th style="padding:12px;">State</th>
                <th style="padding:12px;">Initial Status</th>
                <th style="padding:12px;">Latest Status</th>
                <th style="padding:12px;">Last Updated</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($data as $row)
            <tr style="background-color:#ffb169; border-bottom:1px solid #dee2e6;">
                <td style="padding:10px;"> {{ $loop->iteration }} </td>
                <td style="padding:10px;">{{ $row['item_name'] }}</td>
                <td style="padding:10px;">{{ $row['dom_name'] }}</td>
                <td style="padding:10px;">{{ $row['location_name'] }}</td>
                <td style="padding:10px;">{{ $row['city_name'] }}</td>
                <td style="padding:10px;">{{ $row['state_name'] }}</td>
                <td style="padding:10px;">{{ $row['initial_status_name'] }}</td>
                <td style="padding:10px; font-weight:bold; color:#198754;">{{ $row['latest_status_name'] }}</td>
                <td style="padding:10px; font-size:12px; color:#6c757d;">{{ $row['last_updated'] }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="padding:15px; text-align:center; color:#6c757d; font-style:italic;">
                Looks like there is no data
                </td>
            </tr>
            @endforelse
            </tbody>
        </table>

        </div>
        <div class="rc_emailFooter">
            <p>Please feel free to contact us at below channels for any questions you have.<br/>
            Best Regards, {{ APP_NAME }} IT Team.</p>
        </div>
    </div>
</body>
</html>