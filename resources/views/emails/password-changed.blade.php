<!DOCTYPE html>
<html>
<head>
    <title>{{ APP_NAME }} Password Changed</title>
    <link href='https://fonts.googleapis.com/css?family=Open Sans' rel='stylesheet'>
    <style type="text/css">
        .rc_mainWrap{
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
            <img class="rc_emailLogo" src="{{ url('assets/logo.webp') }}" border="0">
        </div>
        <div class="rc_titleText">
            {!! $fursaa_notify_title !!}
        </div>
        <div class="rc_bodyText">
            {!! $fursaa_notify_body !!}
            {{-- Action Button --}}
            @isset($actionText)
                <br/>
                <div class="rc_txtCenter">
                    <a href="{{$actionUrl}}" class="rc_box rc_action_button" target="_blank" rel="noopener">{{$actionText}}</a>
                </div>
                <br/>
                {{__('passwords.resetdiplayable', ['actionText' => $actionText])}}
                <span class="rc_box work-break-all">
                    <a href="{{$actionUrl}}" class="rc_box" style="color: #3869d4;" target="_blank" rel="noopener">
                        {{ $displayableActionUrl }}
                    </a>
                </span>
            @endisset
        </div>
        <div class="rc_emailFooter">
            <p>Please feel free to contact us at below channels for any questions you have.<br/>
            Best Regards, {{ APP_NAME }} IT Team.</p>
        </div>
    </div>
</body>
</html>
