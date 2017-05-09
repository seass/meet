<?php if (!defined('THINK_PATH')) exit();?><!doctype html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php echo ($meta_title); ?>|会议管理平台</title>
    <link href="/onethink/Public/favicon.ico" type="image/x-icon" rel="shortcut icon">
    <link rel="stylesheet" type="text/css" href="/onethink/Public/Admin/css/base.css" media="all">
    <link rel="stylesheet" type="text/css" href="/onethink/Public/Admin/css/common.css" media="all">
    <link rel="stylesheet" type="text/css" href="/onethink/Public/Admin/css/module.css">
    <link rel="stylesheet" type="text/css" href="/onethink/Public/Admin/css/style.css" media="all">
	<link rel="stylesheet" type="text/css" href="/onethink/Public/Admin/css/<?php echo (C("COLOR_STYLE")); ?>.css" media="all">
     <!--[if lt IE 9]>
    <script type="text/javascript" src="/onethink/Public/static/jquery-1.10.2.min.js"></script>
    <![endif]--><!--[if gte IE 9]><!-->
    <script type="text/javascript" src="/onethink/Public/static/jquery-2.0.3.min.js"></script>
    <script type="text/javascript" src="/onethink/Public/Admin/js/jquery.mousewheel.js"></script>
    <!--<![endif]-->
    
<style type="text/css">
body{
	width:1400px;
}
.data-table thead th, .data-table tbody td{
	padding:3px;
}
</style>

</head>
<body>
    <!-- 头部 -->
    <div class="header">
        <!-- Logo -->
        <span class="logo" style="font-size: 20px;color: #86db00;">会议管理平台</span>
        <!-- /Logo -->

        <!-- 主导航 -->
        <ul class="main-nav">
            <?php if(is_array($__MENU__["main"])): $i = 0; $__LIST__ = $__MENU__["main"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$menu): $mod = ($i % 2 );++$i;?><li class="<?php echo ((isset($menu["class"]) && ($menu["class"] !== ""))?($menu["class"]):''); ?>"><a href="<?php echo (u($menu["url"])); ?>"><?php echo ($menu["title"]); ?></a></li><?php endforeach; endif; else: echo "" ;endif; ?>
        </ul>
        <!-- /主导航 -->

        <!-- 用户栏 -->
        <div class="user-bar">
            <a href="javascript:;" class="user-entrance"><i class="icon-user"></i></a>
            <ul class="nav-list user-menu hidden">
                <li class="manager">你好，<em title="<?php echo session('user_auth.username');?>"><?php echo session('user_auth.username');?></em></li>
                <li><a href="<?php echo U('User/updatePassword');?>">修改密码</a></li>
                <li><a href="<?php echo U('User/updateNickname');?>">修改昵称</a></li>
                <li><a href="<?php echo U('Public/logout');?>">退出</a></li>
            </ul>
        </div>
    </div>
    <!-- /头部 -->

    <!-- 边栏 -->
    <div class="sidebar">
        <!-- 子导航 -->
        
            <div id="subnav" class="subnav">
                <?php if(!empty($_extra_menu)): ?>
                    <?php echo extra_menu($_extra_menu,$__MENU__); endif; ?>
                <?php if(is_array($__MENU__["child"])): $i = 0; $__LIST__ = $__MENU__["child"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$sub_menu): $mod = ($i % 2 );++$i;?><!-- 子导航 -->
                    <?php if(!empty($sub_menu)): if(!empty($key)): ?><h3><i class="icon icon-unfold"></i><?php echo ($key); ?></h3><?php endif; ?>
                        <ul class="side-sub-menu">
                            <?php if(is_array($sub_menu)): $i = 0; $__LIST__ = $sub_menu;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$menu): $mod = ($i % 2 );++$i;?><li>
                                    <a class="item" href="<?php echo (u($menu["url"])); ?>"><?php echo ($menu["title"]); ?></a>
                                </li><?php endforeach; endif; else: echo "" ;endif; ?>
                        </ul><?php endif; ?>
                    <!-- /子导航 --><?php endforeach; endif; else: echo "" ;endif; ?>
            </div>
        
        <!-- /子导航 -->
    </div>
    <!-- /边栏 -->

    <!-- 内容区 -->
    <div id="main-content">
        <div id="top-alert" class="fixed alert alert-error" style="display: none;">
            <button class="close fixed" style="margin-top: 4px;">&times;</button>
            <div class="alert-content">这是内容</div>
        </div>
        <div id="main" class="main">
            
            <!-- nav -->
            <?php if(!empty($_show_nav)): ?><div class="breadcrumb">
                <span>您的位置:</span>
                <?php $i = '1'; ?>
                <?php if(is_array($_nav)): foreach($_nav as $k=>$v): if($i == count($_nav)): ?><span><?php echo ($v); ?></span>
                    <?php else: ?>
                    <span><a href="<?php echo ($k); ?>"><?php echo ($v); ?></a>&gt;</span><?php endif; ?>
                    <?php $i = $i+1; endforeach; endif; ?>
            </div><?php endif; ?>
            <!-- nav -->
            

            
	<!-- 标题栏 -->
	<div class="main-title">
		<h2>会议人员列表</h2>
	</div>
	<div class="cf">
		<div class="fl">
            <a class="btn" href="<?php echo U('MeetMember/add');?>">新增</a>
            <a class="btn" href="<?php echo U('MeetMember/import');?>">导入</a>
            <button class="btn ajax-post" url="<?php echo U('MeetMember/changeStatus',array('method'=>'resumeMeetMember'));?>" target-form="ids">启用</button>
            <button class="btn ajax-post" url="<?php echo U('MeetMember/changeStatus',array('method'=>'forbidMeetMember'));?>" target-form="ids">禁用</button>
            <button class="btn ajax-post confirm" url="<?php echo U('MeetMember/changeStatus',array('method'=>'deleteMeetMember'));?>" target-form="ids">删 除</button>
        
        		<select name="classes_id" class='allotClasses'>
					<option value="0">请选择分配的班级</option>
					<?php $_result=get_classes_meet_list();if(is_array($_result)): $i = 0; $__LIST__ = $_result;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["meet_name"]); ?>-<?php echo ($vo["classes_name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
			</select>
        		<button class="btn ajax-post" url="<?php echo U('MeetMember/allotClasses');?>" target-form="allotClasses">分班</button>
        		
        		<button class="btn ajax-post" url="<?php echo U('MeetMember/setRoommate');?>" target-form="ids">设置室友</button>
        </div>

        <!-- 高级搜索 -->
		<div class="search-form fr cf">
			<div class="sleft">
				<select name="_meet_id" style="float:left;margin-right:10px;" class="search_field" >
					<option value="0">请选择会议名称</option>
					<?php $_result=get_meet_list();if(is_array($_result)): $i = 0; $__LIST__ = $_result;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>" ><?php echo ($vo["meet_name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
				</select>
				<!--  <select name="_sign" style="float:left;margin-right:10px;" class="search_field" >
					<option value="0">签到状态</option>
					<option value="1" >已签到</option>
					<option value="2" >未签到</option>
				</select>-->
				<input type="text" name="_key" class="search-input search_field" value="<?php echo I('_key');?>" placeholder="支持会议名称/人员名称／手机号">
				<a class="sch-btn" href="javascript:;" id="search" url="<?php echo U('index');?>"><i class="btn-search"></i></a>
			</div>
		</div>
    </div>
    <!-- 数据列表 -->
    <div class="data-table">
	<table class="" >
    <thead>
        <tr>
			<th class="row-selected"><input class="check-all" type="checkbox"/></th>
			<!-- <th class="">ID</th> -->
			<th class="" style="width:40px">大区</th>
			<th class="" style="width:40px">城市</th>
			<th class="" style="width:150px">门店</th>
			<th class="" style="width:40px">门店代码</th>
			<th class="" style="width:150px">会议名称</th>
			<!--<th class="">会议报名开始时间</th>
			<th class="">会议报名结束时间</th>-->
			<th class="" style="width:40px">班级</th>
			<th class="" style="width:40px">参会编号</th>
			<th class="" style="width:40px">职务</th>
			<th class="" style="width:50px">姓名</th>
			<th class="" style="width:50px">手机号</th>
			<th class="">证件号</th>
			<th class="">性别</th>
			<th class="" style="width:60px">头像</th>
			<th class="" style="width:40px">二维码</th>
			<th class="" style="width:60px">住宿类型</th>
			<th class="" style="width:40px">室友名称</th>
			<th class="" style="width:40px">审核状态</th>
			<th class="" style="width:40px">状态</th>
			<th class="" style="width:200px">操作</th>
		</tr>
    </thead>
    <tbody>
		<?php if(!empty($_list)): if(is_array($_list)): $i = 0; $__LIST__ = $_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr>
            <td><input class="ids allotClasses" type="checkbox" name="id[]" value="<?php echo ($vo["id"]); ?>" /></td>
			<!-- <td><?php echo ($vo["id"]); ?></td> -->
			<td><?php echo ($vo["region_name"]); ?></td>
			<td><?php echo ($vo["city_name"]); ?></td>
			<td><?php echo ($vo["store_name"]); ?></td>
			<td><?php echo ($vo["store_code"]); ?></td>
			<td><?php echo ($vo["meet_name"]); ?></td>
			<!--  <td><?php echo ($vo["begin_time"]); ?></td>
			<td><?php echo ($vo["end_time"]); ?></td>-->
			<td><?php echo ($vo["classes_name"]); ?></td>
			<td><?php echo ($vo["user_no"]); ?></td>
			<td><?php echo ($vo["position"]); ?></td>
			<td><?php echo ($vo["realname"]); ?></td>
			<td><?php echo ($vo["phone"]); ?></td>
			<td><?php echo ($vo["idcard"]); ?></td>
			<td><?php if($vo["sex"] == 1): ?>男<?php else: ?>女<?php endif; ?></td>
			<td>
				<?php if($vo["headimg"] == null): ?>未上传
				<?php else: ?>
					<div class="upload-img-box">
						<a >查看</a>
						<div style="display:none;">
							<img src="/onethink<?php echo (get_cover($vo["headimg"],'path')); ?>">
						</div>
					</div><?php endif; ?>
			</td>
			<td><div class="upload-img-box">
						<a >查看</a>
						<div style="display:none;">
							<img src="/onethink<?php echo ($vo["qrcode"]); ?>">
						</div>
				</div>
			</td>
			<td><?php if($vo["hotel_type"] == 0): ?>不住宿<?php elseif($vo["hotel_type"] == 1): ?>合住 <?php else: ?>单住<?php endif; ?></td>
			<td><?php echo ($vo["roommate_name"]); ?></td>
			<td><?php if($vo["is_audit"] == 0): ?>不通过<?php else: ?>通过<?php endif; ?></td>
			<td><?php echo ($vo["status_text"]); ?></td>
			<td>
				<a href="javascript:signIn(<?php echo $vo['id'];?>)">签到</a>
				<a href="<?php echo U('MeetMember/edit?id='.$vo['id']);?>">编辑</a>
				<?php if($vo["status"] == 1): ?><a href="<?php echo U('MeetMember/changeStatus?method=forbidMeetMember&id='.$vo['id']);?>" class="ajax-get">禁用</a>
				<?php else: ?>
				<a href="<?php echo U('MeetMember/changeStatus?method=resumeMeetMember&id='.$vo['id']);?>" class="ajax-get">启用</a><?php endif; ?>
				<a href="<?php echo U('MeetMember/changeStatus?method=deleteMeetMember&id='.$vo['id']);?>" class="confirm ajax-get">删除</a>
                </td>
		</tr><?php endforeach; endif; else: echo "" ;endif; ?>
		<?php else: ?>
		<td colspan="21" class="text-center"> Oh! 暂时还没有内容! </td><?php endif; ?>
	</tbody>
    </table>
	</div>
    <div class="page">
        <?php echo ($_page); ?>
    </div>

        </div>
        <div class="cont-ft">
            <div class="copyright">
                <div class="fl">感谢使用会议管理平台</div>
                <div class="fr">V<?php echo (ONETHINK_VERSION); ?></div>
            </div>
        </div>
    </div>
    <!-- /内容区 -->
    <script type="text/javascript">
    (function(){
        var ThinkPHP = window.Think = {
            "ROOT"   : "/onethink", //当前网站地址
            "APP"    : "/onethink/index.php?s=", //当前项目地址
            "PUBLIC" : "/onethink/Public", //项目公共目录地址
            "DEEP"   : "<?php echo C('URL_PATHINFO_DEPR');?>", //PATHINFO分割符
            "MODEL"  : ["<?php echo C('URL_MODEL');?>", "<?php echo C('URL_CASE_INSENSITIVE');?>", "<?php echo C('URL_HTML_SUFFIX');?>"],
            "VAR"    : ["<?php echo C('VAR_MODULE');?>", "<?php echo C('VAR_CONTROLLER');?>", "<?php echo C('VAR_ACTION');?>"]
        }
    })();
    </script>
    <script type="text/javascript" src="/onethink/Public/static/think.js"></script>
    <script type="text/javascript" src="/onethink/Public/Admin/js/common.js"></script>
    <script type="text/javascript">
        +function(){
            var $window = $(window), $subnav = $("#subnav"), url;
            $window.resize(function(){
                $("#main").css("min-height", $window.height() - 130);
            }).resize();

            /* 左边菜单高亮 */
            url = window.location.pathname + window.location.search;
            url = url.replace(/(\/(p)\/\d+)|(&p=\d+)|(\/(id)\/\d+)|(&id=\d+)|(\/(group)\/\d+)|(&group=\d+)/, "");
            $subnav.find("a[href='" + url + "']").parent().addClass("current");

            /* 左边菜单显示收起 */
            $("#subnav").on("click", "h3", function(){
                var $this = $(this);
                $this.find(".icon").toggleClass("icon-fold");
                $this.next().slideToggle("fast").siblings(".side-sub-menu:visible").
                      prev("h3").find("i").addClass("icon-fold").end().end().hide();
            });

            $("#subnav h3 a").click(function(e){e.stopPropagation()});

            /* 头部管理员菜单 */
            $(".user-bar").mouseenter(function(){
                var userMenu = $(this).children(".user-menu ");
                userMenu.removeClass("hidden");
                clearTimeout(userMenu.data("timeout"));
            }).mouseleave(function(){
                var userMenu = $(this).children(".user-menu");
                userMenu.data("timeout") && clearTimeout(userMenu.data("timeout"));
                userMenu.data("timeout", setTimeout(function(){userMenu.addClass("hidden")}, 100));
            });

	        /* 表单获取焦点变色 */
	        $("form").on("focus", "input", function(){
		        $(this).addClass('focus');
	        }).on("blur","input",function(){
				        $(this).removeClass('focus');
			        });
		    $("form").on("focus", "textarea", function(){
			    $(this).closest('label').addClass('focus');
		    }).on("blur","textarea",function(){
			    $(this).closest('label').removeClass('focus');
		    });

            // 导航栏超出窗口高度后的模拟滚动条
            var sHeight = $(".sidebar").height();
            var subHeight  = $(".subnav").height();
            var diff = subHeight - sHeight; //250
            var sub = $(".subnav");
            if(diff > 0){
                $(window).mousewheel(function(event, delta){
                    if(delta>0){
                        if(parseInt(sub.css('marginTop'))>-10){
                            sub.css('marginTop','0px');
                        }else{
                            sub.css('marginTop','+='+10);
                        }
                    }else{
                        if(parseInt(sub.css('marginTop'))<'-'+(diff-10)){
                            sub.css('marginTop','-'+(diff-10));
                        }else{
                            sub.css('marginTop','-='+10);
                        }
                    }
                });
            }
        }();
    </script>
    
	<script src="/onethink/Public/static/thinkbox/jquery.thinkbox.js"></script>

	<script type="text/javascript">
		function signIn(id){
			if(!confirm("确定进行签到吗？")){
				return false;
			}
			$.post("<?php echo U('Sign');?>",{'meet_member_id':id},
					function(result){
				if(result.status){
					alert('签到成功');
				}else{
					alert('签到失败');
				}
	  		},'json');
		}
	//搜索功能
	$("#search").click(function(){
		var url = $(this).attr('url');
        var query  = $('.search-form').find('.search_field').serialize();
        query = query.replace(/(&|^)(\w*?\d*?\-*?_*?)*?=?((?=&)|(?=$))/g,'');
        query = query.replace(/^&/g,'');
        if( url.indexOf('?')>0 ){
            url += '&' + query;
        }else{
            url += '?' + query;
        }
		window.location.href = url;
	});
	//回车搜索
	$(".search-input").keyup(function(e){
		if(e.keyCode === 13){
			$("#search").click();
			return false;
		}
	});
	Think.setValue('_meet_id',<?php echo ((isset($_meet_id) && ($_meet_id !== ""))?($_meet_id):0); ?>);
	Think.setValue('_sign',<?php echo ((isset($_sign) && ($_sign !== ""))?($_sign):0); ?>);
    //导航高亮
    highlight_subnav('<?php echo U('MeetMember/index');?>');
	</script>

</body>
</html>