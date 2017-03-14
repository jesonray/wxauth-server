<?php
$this->title = '登陆授权';
?>
<div class="icon-box" style="margin-top:14vh;text-align:center;line-height:2;">
<?php if($status=='success'){?>
    <i class="weui-icon-success weui-icon_msg"></i>
    <div class="icon-box__ctn">
        <h3 style="margin-top:1em">操作成功</h3>
        <p class="icon-box__desc">将于2秒后关闭此页面</p>
    </div>
    <script>
        setTimeout(function(){
            WeixinJSBridge.invoke('closeWindow');
        }, 2000);
    </script>
<?php } else {?>
    <i class="weui-icon-warning weui-icon_msg"></i>
    <div class="icon-box__ctn">
        <h3 style="margin-top:1em">操作失败</h3>
        <p class="icon-box__desc">将于2秒后关闭此页面</p>
    </div>
    <script>
        setTimeout(function(){
            WeixinJSBridge.invoke('closeWindow');
        }, 2000);
    </script>
<?php }?>
</div>