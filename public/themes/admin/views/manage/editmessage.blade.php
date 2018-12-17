
<h3 class="header smaller lighter blue mg-bottom20 mg-top12">模板编辑</h3>

<form action="/manage/editMessage" method="post">
    {{ csrf_field() }}
    <div class="g-backrealdetails clearfix bor-border">
        <div class="bankAuth-bottom clearfix col-xs-12">
            <p class="col-xs-1 text-right">信息邮件代号</p>
            <p class="col-xs-10 text-left">
                <input type="text" name="code_name" value="{{$message_info['code_name']}}" readonly="readonly">
            </p>
        </div>
        <div class="bankAuth-bottom clearfix col-xs-12">
            <p class="col-xs-1 text-right">信息邮件类型</p>
            <p class="col-xs-10 text-left">
                <input type="text" name="name" value="{{$message_info['name']}}">
                <input type="hidden" name="id" value="{{$id}}">
            </p>
        </div>

        <div class="bankAuth-bottom clearfix col-xs-12">
            <p class="col-xs-1 text-right">信息邮件内容</p>
            <div class="col-xs-8 text-left">
                <!--编辑器-->
                <div class="clearfix">
                    <script id="editor" name="content" type="text/plain">{!! htmlspecialchars_decode($message_info['content']) !!}</script>
                    {{ $errors->first('content') }}
                </div>
            </div>
            <div class="space-6 col-xs-12"></div>
        </div>

        <div class="bankAuth-bottom clearfix col-xs-12">
            <p class="col-xs-1 text-right">短信模板编号</p>
            <p class="col-xs-10 text-left">
                <input type="text" name="code_mobile" value="{{$message_info['code_mobile']}}">
            </p>
        </div>

        <div class="bankAuth-bottom clearfix col-xs-12">
            <p class="col-xs-1 text-right">短信模板内容</p>
            <p class="col-xs-10 text-left">
                <textarea name="mobile_code_content">{{$message_info['mobile_code_content']}}</textarea>
            </p>
        </div>

        <div class="col-xs-12">
            <div class="clearfix row bg-backf5 padding20 mg-margin12">
                <div class="col-xs-12">
                    <div class="col-md-1 text-right"></div>
                    <div class="col-md-10">
                        <button class="btn btn-primary sub_article" type="submit">提交</button>
                        <a href="javascript:history.back()" title="" class=" add-case-concel">返回</a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</form>

{!! Theme::asset()->container('custom-css')->usepath()->add('backstage', 'css/backstage/backstage.css') !!}

{!! Theme::widget('ueditor')->render() !!}