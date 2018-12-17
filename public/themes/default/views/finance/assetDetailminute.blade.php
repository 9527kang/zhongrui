<div class="g-main">
	<div>
		<div class="s-iconword pull-left"></div>
		<h4 class="text-size22 cor-blue u-title">&nbsp;&nbsp;&nbsp;&nbsp;收支明细</h4>
	</div>
	<div class="space"></div>
	<div class="well z-active text-size14 clearfix s-wells">
		<div class="pull-left">
			{{--<img class="img-wdhg62" src="{!! Theme::asset()->url('images/img-head.png') !!}" alt="">--}}
			<img class="img-wdhg62" src="@if(!empty($avatar)) {!! url($avatar) !!}  @else{!! Theme::asset()->url('images/default_avatar.png') !!}@endif" alt="">
		</div>
		<div class="pull-left g-txtcontent cor-gray51">
			<h4>
				@if(in_array($info->action,array_keys($action_arr)))
					{{$action_arr[$info->action]}}
				@endif
			</h4>
			<p>支付方式 :
				@if(in_array($info->pay_type,array_keys($pay_type)))
					{{$pay_type[$info->pay_type]}}
				@endif
			</p>
			@if($info->pay_type != 1)
                <p>支付帐号 :
                    {!! CommonClass::starReplace($info->pay_account, -5) !!}
                </p>
            @endif
		</div>
	</div>
	<div class="space-4"></div>
	<div class="f-table">
		<table class="table table-hover text-size14 cor-gray51 f-border table638">
			<thead>
			<tr>
				<th class="text-center">状态</th>
				<th class="text-center">金额（元）</th>
			</tr>
			</thead>
			<tbody class="text-center">
			<tr>
				<td>交易成功</td>
				<td class="cor-orange">{!! $info->cash !!}</td>
			</tr>
			</tbody>
		</table>
	</div>
	<div class="clearfix">
		<span class="pull-right cor-gray51">时间：{!! $info->created_at !!}</span>
	</div>
	<div class="space"></div>
	<div class="space"></div>
	<div class="text-center clearfix">
		<a href="{!! url('finance/assetDetail') !!}" class="s-btnclick bg-blue btn-sm text-size16">返回</a>
	</div>
</div>