<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{$data['title']}}</title>
</head>
<body style="background: #ffc65a;">
<div style="text-align:center;">
    <table width="650" cellpadding="0" cellspacing="0" border="0" style="margin:0 auto;">
        <tbody>
        <tr>
            <td>
                <div style="width:650px;text-align:left;font:12px/15px simsun;color:#000;background:#fff;">
                    <div style="background: url('{!! url('themes/default/assets/images/sign-emailbg.png') !!}') no-repeat;min-height: 474px;padding: 43px;">
                        <div style="text-align: center;margin: 12px 0 50px 0">
                            @if(isset(Theme::get('site_config')['site_logo_1']))
                            <a href="javascript:;">
                                @if(isset(Theme::get('site_config')['site_logo_1']) && is_file(Theme::get('site_config')['site_logo_1']))
                                    <img src="{!! $message->embed(url(Theme::get('site_config')['site_logo_1']))!!}" alt="">

                                @else
                                    <img src="{!! url('themes/default/assets/images/sign-logo.png') !!}" alt="">
                                @endif
                            </a>
                            @endif
                        </div>
                        <div style="font-size: 14px;color: #515151;">
                            {!! htmlspecialchars_decode($data['message']) !!}
                        </div>
                    </div>
                </div>
            </td>
        </tr>
        </tbody>
    </table>
</div>
</body>
</html>