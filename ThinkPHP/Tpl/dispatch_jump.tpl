<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>跳转提示</title>
<style type="text/css">
*{ padding: 0; margin: 0; }
body{ background: #f3f3f4; font-family: '微软雅黑'; color: #fff; font-size: 16px; position:relative; }
.system-message{background:#34495E; padding: 15px 45px 25px 45px; width:400px;position:absolute;border-radius: 6px;box-shadow: -20px 20px 40px #888888;}
.system-message h1{ font-size: 35px; font-weight: normal; line-height: 100px; margin-bottom: 8px }
.system-message .jump{ padding-top: 10px;margin-bottom:20px}
.system-message .jump a{ color: #333;}
.system-message .success,.system-message .error{ line-height: 1.5em; font-size: 24px }
.system-message .detail{ font-size: 12px; line-height: 20px; margin-top: 12px; display:none}
#wait {
    font-size:26px;
}
#btn-stop,#href{
    display: inline-block;
    margin-right: 10px;
    font-size: 16px;
    line-height: 18px;
    text-align: center;
    vertical-align: middle;
    cursor: pointer;
    border: 0 none;
    background-color: #8B0000;
    padding: 10px 20px;
    color: #fff;
    font-weight: bold;
    border-color: transparent;
    text-decoration:none;
}
 
#btn-stop:hover,#href:hover{
    background-color: #ff0000;
}
span{
	font-size:30px;
}
</style>
</head>
<body>
<div class="system-message" id="system-message">
<present name="message">
<h1>操作成功 !</h1>
<p class="success"><?php echo($message); ?></p>
<else/>
<h1>操作出错 !</h1>
<p class="error"><?php echo($error); ?></p>
</present>
<p class="detail"></p>
<p class="jump">
<b id="wait"><?php echo($waitSecond); ?></b> 秒后页面将自动跳转
</p>
<div>
    <a id="href" id="btn-now" href="<?php echo($jumpUrl); ?>">立即跳转</a>
    <button id="btn-stop" type="button" onclick="stop()">停止跳转</button>
    <a id="href" id="btn-now" href="<?php echo(U('Login/index')); ?>">重新登录</a>
</div>
</div>
<script type="text/javascript">
// 获取浏览器窗口
var windowScreen = document.documentElement;
// 获取mcontent的div元素
var content_div = document.getElementById("system-message");
var content_left = (windowScreen.clientWidth - content_div.clientWidth)/2 + "px";
var content_top = (windowScreen.clientHeight - content_div.clientHeight-80)/2 + "px";
content_div.style.left = content_left;
content_div.style.top = content_top;

(function(){
var wait = document.getElementById('wait'),href = document.getElementById('href').href;
var interval = setInterval(function(){
     var time = --wait.innerHTML;
     if(time <= 0) {
     location.href = href;
     clearInterval(interval);
     };
     }, 1000);
  window.stop = function (){
         console.log(111);
            clearInterval(interval);
}
})();
</script>
</body>
</html>