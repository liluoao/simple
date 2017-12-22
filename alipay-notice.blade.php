<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>{{$msg}}</title>
    <meta name="viewport" content="width=device-width" />
    <link rel="stylesheet" type="text/css" href="../../css/weui.css">
    <style type="text/css">
        .weui-msg {
            margin-top: 50px;
        }

        .blue {
            color: #1CACEB;
        }
    </style>
</head>

<body>
<div class="weui-msg">
    <!-- 成功页面 -->
    @if ($type === 'success')
    <div class="weui-msg__icon-area"><i class="weui-icon-success weui-icon_msg blue"></i></div>
    @elseif ($type === 'info')
    <!-- 提示页面 -->
    <div class="weui-msg__icon-area"><i class="weui-icon-info weui-icon_msg blue"></i></div>
    @endif
    <div class="weui-msg__text-area">
        <h2 class="weui-msg__title">{{$msg}}</h2>
    </div>
</div>
</body>

</html>
