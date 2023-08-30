<head>
    <link href="https://fonts.googleapis.com/css?family=Ubuntu&display=swap" rel="stylesheet">
    <style type="text/css">
        body {margin: 0; padding: 0; min-width: 100%!important; font-family: 'Ubuntu', sans-serif;}
        img {height: auto;}
        .content {width: 100%; max-width: 660px;}
        .header {padding: 40px 30px 20px 30px;}
        .innerpadding {padding: 30px 30px 30px 30px; line-height: 25px;}
        .borderbottom {border-bottom: 1px solid #f2eeed;}
        .subhead {font-size: 15px; color: #ffffff; letter-spacing: 10px;}
        .h1, .h2, .bodycopy {color: #153643;}
        .h1 {font-size: 33px; line-height: 38px; font-weight: bold;}
        .h2 {padding: 0 0 15px 0; font-size: 24px; line-height: 28px; font-weight: bold;}
        .bodycopy {font-size: 16px; line-height: 22px;}
        .button {text-align: center; font-size: 18px; font-weight: bold; padding: 0 30px 0 30px;}
        .button a {color: #ffffff; text-decoration: none;}
        .footer {padding: 20px 30px 20px 30px;}
        .footercopy {font-size: 14px; color: #ffffff;}
        .footercopy a {color: #ffffff; text-decoration: underline;}
        @media  only screen and (max-width: 550px), screen and (max-device-width: 550px) {
            body[yahoo] .hide {display: none!important;}
            body[yahoo] .buttonwrapper {background-color: transparent!important;}
            body[yahoo] .button {padding: 0px!important;}
            body[yahoo] .button a {background-color: #effb41; padding: 15px 15px 13px!important;}
            body[yahoo] .unsubscribe {display: block; margin-top: 20px; padding: 10px 50px; background: #2f3942; border-radius: 5px; text-decoration: none!important; font-weight: bold;}
        }
    </style>
</head>
<body>
<br>
<table bgcolor="#ffffff" class="content" align="center" cellpadding="0" cellspacing="0" border="0">
    <tbody><tr>
<!--        {{--        <td bgcolor="#F0F0F0" class="header">--}}-->
<!--            {{--            <table width="70" align="center" border="0" cellpadding="0" cellspacing="0">--}}-->
<!--                {{--                <tbody><tr>--}}-->
<!--                    {{--                    <td height="70" style="padding: 0 10px 0px 0;">--}}-->
<!--                        {{--                        <a href="#"><img class="fix" src="--><?php //echo $message->embed($attributes['logo']); ?><!--" width="150" border="0" alt=""></a>--}}-->
<!--                        {{--                    </td>--}}-->
<!--                    {{--                </tr>--}}-->
<!--                {{--                </tbody></table>--}}-->
<!--            {{--        </td>--}}-->
    </tr>
    <tr>
    </tr>
    <td class="innerpadding bodycopy">
    </td>
    <tr>
        <td class="innerpadding borderbottom" style="padding-top: 0px;">
            Hi ,
            <br />
            <br />
            Your new Order from {{ $marketName }} is placed with OrderID {{ $attributes['order_id'] }}.  <br><br>
            Thanks & Regards <br>
            Owleto
        </td>
    </tr>
    <tr>
        <td class="footer" bgcolor="#F0F0F0">
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                <tbody>
                <tr>
                    <td align="center" class="footercopy" style="color: black;">
                        © {{ date('Y') }} Owleto, All rights reserved.
                    </td>
                </tr>
                </tbody>
            </table>
        </td>
    </tr>
    </tbody>
</table>
</body>
