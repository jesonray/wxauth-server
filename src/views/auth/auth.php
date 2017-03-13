<?php
use yii\helpers\Url;

\yii\web\JqueryAsset::register($this);
?>
<div class="icon-box" style="margin-top:14vh;text-align:center;line-height:2;">
    <i class="weui-icon-waiting weui-icon_msg"></i>
    <div class="weui_text_area">
        <h3 class="weui_msg_title" style="margin-top:1em;">登陆<?php echo $task->app->name;?></h3>
        <p class="weui_msg_desc">接受请点击确定，否则请点击取消</p>
    </div>
    <div class="weui_opr_area" style="margin:2em;">
        <p class="weui_btn_area">
            <a id="btn-allow" class="weui-btn weui-btn_primary">确定</a>
            <a id="btn-deny" class="weui-btn weui-btn_default">取消</a>
        </p>
    </div>
</div>
<?php \raysoft\widgets\JsBlock::begin()?>
<script>
jQuery(function($){
    $('#btn-allow').on('click', function(){
        confirm('allow', function(){
            window.location.href = '<?php echo Url::to(['result', 'status'=>'success'])?>';
        });
    });
    $('#btn-deny').on('click', function(){
        confirm('deny', function(){
            WeixinJSBridge.invoke('closeWindow');
        })
    });
    function confirm(action, callback){
        $.getJSON('<?php echo Url::to(['confirm'])?>', {action:action, token:"<?php echo $task->token?>"}, function(json){
            if( json.code==200 ) {
                callback();
            } else {
                window.location.href = '<?php echo Url::to(['result', 'status'=>'error'])?>';
            }
        });
    }
});
</script>
<?php \raysoft\widgets\JsBlock::end()?>