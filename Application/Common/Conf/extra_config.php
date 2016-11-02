<?php
return array(
	//计量单位配置
	'UNIT_TYPE'=>array(
		'1'=>'份',
		'2'=>'例',
		'3'=>'斤',
	),
	//积分变更类型配置
	'SCORE_TYPE'=>array(
		1=>array('name'=>'注册送积分','num'=>50),
		2=>array('name'=>'添加商户','num'=>20),
		3=>array('name'=>'评论商铺','num'=>10),
		4=>array('name'=>'恶意评论','num'=>-10),
		5=>array('name'=>'在商家消费获取积分'),
		6=>array('name'=>'顾客消费'),
		7=>array('name'=>'积分换购','type'=>'score_good'),
		8=>array('name'=>'发布每日推荐','num'=>-50),
		9=>array('name'=>'后台管理员修改'),
        10=>array('name'=>'购买积分'),
		'need_target'=>'2,3,4,5,6,7,8',
	),
	
	//短信发送配置
	'SMS_SWITCH'		=>'on',//短信发送服务开关
	'SMS_USERNAME'		=>'wdxa8624',//用户名
	'SMS_PASSWORD'		=>'wdxa8624',//密码
	'SMS_CPID'			=>'8347',//
	'SMS_TIMESTAMP'		=>'1383201830',//
	'SMS_CHANNEL'		=>'5932',//

	//短信验证码配置
	'VERIFY_SWITCH'		=>'on',//验证码开关
	'VERIFY_TEMPLATE'	=>'欢迎您使用手机认证,您的验证码为:',//信息模版
	'VERIFY_COUNT'		=>'3',//一天验证次数限制

	//邮件服务器的配置
	'SMTP_SERVER' =>'smtp.163.com', //邮件服务器
	'SMTP_PORT' =>25, //邮件服务器端口

	'SMTP_USER_EMAIL' =>'wdxa8624@163.com', //SMTP服务器的用户邮箱(一般发件人也得用这个邮箱)
	'SMTP_USER'=>'wdxa8624@163.com', //SMTP服务器账户名
	'SMTP_PWD'=>'liu6218285', //SMTP服务器账户密码
	'SMTP_MAIL_TYPE'=>'HTML', //发送邮件类型:HTML,TXT(注意都是大写)
	'SMTP_TIME_OUT'=>30, //超时时间
	'SMTP_AUTH'=>true, //邮箱验证(一般都要开启)
);
