<?
//에러 출력
function ErrMsg($HeaderStatusCode,$FontColor){
	global $Env;
	//$ErrMsg=$HeaderStatusCode." " .$Env["HeaderStatusCode"][$HeaderStatusCode];
	$ErrMsg=$HeaderStatusCode." ";
	echo " ▶<font color=".$FontColor.">".$ErrMsg."</font>";
	flush();
}


//체크 출력
function PrintCheckNum($TargetUrl,$Parent_doc,$FormMethod){
	global $Env,$EnvAct;
	$Env["CheckNum"]++;
	if($Env["CheckNum"]>$EnvAct["CheckNumLimit"]){
		echo "\n\n<hr>Execute CHECK limit (".$EnvAct["CheckNumLimit"]." ) over ";
		EndView();
		exit;
	}
	$tParent_doc="";
	if($Env["f_debug_yn"]){
		$tParent_doc="<BR><font style=\"font-size:8pt\" color=gray>".$Parent_doc."</font>";
	}else{
		$tParent_doc="<!--".$Parent_doc."-->";
	}
	echo "\n<br>CHECK ";
	if($FormMethod=="POST")echo "<font color=blue>FORM</font>";
	echo sprintf("%3d : %-50s %s %s",$Env["CheckNum"],$TargetUrl,$Env["f_scrollbar"],$tParent_doc);
	flush();
}


//헤더 출력
function PrintHeader(){
	global $Env;
	global $isExecuteWeb;

	?>	
	<html>
	<head>
	<title><?=$Env["pro_info"]?> (Ver <?=$Env["ver_info"]?>)</title>
	<style>
	td,body{font-size:9pt}
	</style>
	</head>
	<body bgcolor=white>
	<a href="http://sourceforge.net/projects/wstool" target="_blank"><?=$Env["pro_info"]?> (Ver <?=$Env["ver_info"]?>)</a>
	<?
}


//검사 시작시 환경정보 출력
function StartView(){
	global $Env;
	global $EnvAct,$slist,$whitespace;
	global $TargetHost,$TargetPort,$TargetType,$RootUrl;
	global $f_auth_cookie;

	//헤더 출력
	PrintHeader();

	?>
<table width=100% border=0 cellpadding=5 cellspacing=0 bgcolor=darkblue><tr><td><font color=white><b>Environment</td></tr></table>

	<table width=800 border=0 cellpadding=0 cellspacing=0 bgcolor=gray><tr><td>
	<table width=800 border=0 cellpadding=3 cellspacing=1>
		<tr><td align=center width=150 bgcolor=#ededed>FORM check</td><td bgcolor=white><?=$EnvAct["form_check_yn"]?></td></tr>
		<tr><td align=center width=150 bgcolor=#ededed>Check limit count</td><td bgcolor=white><?=$EnvAct["CheckNumLimit"]?></td></tr>
		<tr><td align=center width=150 bgcolor=#ededed>Execute limit time</td><td bgcolor=white><?=$EnvAct["runtime"]?>초</td></tr>
		<tr><td align=center width=150 bgcolor=#ededed>Check count same CGI, different parameter </td><td bgcolor=white><?=$EnvAct["LimitParamCnt"]?></td></tr>
		<tr><td align=center width=150 bgcolor=#ededed>Force check for 404 ERROR</td>
		<td bgcolor=white><?=$EnvAct["force_404_yn"]?></td></tr>
		<tr><td align=center width=150 bgcolor=#ededed>FORM ACTION check with (JAVA)SCRIPT</td>
		<td bgcolor=white><?=$EnvAct["script_action_yn"]?></td></tr>
		<tr><td align=center width=150 bgcolor=#ededed>FORM NULL ACTION self action CGI</td>
		<td bgcolor=white><?=$EnvAct["self_action_yn"]?></td></tr>

		<tr><td align=center width=150 bgcolor=#ededed>SRC link CGI list</td><td bgcolor=white><?=str_replace("|",", ",$slist)?></td></tr>
		<tr><td align=center width=150 bgcolor=#ededed>Only Sub-folder check</td><td bgcolor=white><?=$EnvAct["folder_yn"]?></td></tr>
		<tr><td align=center width=150 bgcolor=#ededed>ERR 4XX check</td><td bgcolor=white><?=$EnvAct["ERR_4xx_YN"]?></td></tr>
		<tr><td align=center width=150 bgcolor=#ededed>ERR 5xx check</td><td bgcolor=white><?=$EnvAct["ERR_5xx_YN"]?></td></tr>
		<tr><td align=center width=150 bgcolor=#ededed>ERR 500 & SQL Injection</td><td bgcolor=white><?=$EnvAct["ERR_500SQL_YN"]?></td></tr>
		<tr><td align=center width=150 bgcolor=#ededed>ERR XSS(cross site scripting) </td><td bgcolor=white><?=$EnvAct["ERR_200XSS_YN"]?></td></tr>
		<tr><td align=center width=150 bgcolor=#ededed>ADMIN folder scan</td><td bgcolor=white><?=$EnvAct["ADMIN_FOLDER_YN"]?></td></tr>
		<tr><td align=center width=150 bgcolor=#ededed>Exception URL</td><td bgcolor=white><?=trim($f_EXCEPT_URL)?></td></tr>
	</table>
	</td></tr>
	</table>


	<BR>
	<table width=100% border=0 cellpadding=5 cellspacing=0 bgcolor=darkblue><tr><td><font color=white><b>Server info</td></tr></table>
	<table width=800 border=0 cellpadding=0 cellspacing=0 bgcolor=gray><tr><td>
	<table width=800 border=0 cellpadding=3 cellspacing=1>
		<tr><td align=center width=150 bgcolor=#ededed>Domain or IP</td><td bgcolor=white><?=htmlspecialchars($TargetHost)?></td></tr>
		<tr><td align=center width=150 bgcolor=#ededed>PORT</td><td bgcolor=white><?=htmlspecialchars($TargetPort)?></td></tr>
		<tr><td align=center width=150 bgcolor=#ededed>Server type</td><td bgcolor=white><?=htmlspecialchars($TargetType)?></td></tr>
		<tr><td align=center width=150 bgcolor=#ededed>Start PATH</td><td bgcolor=white><?=htmlspecialchars($RootUrl)?></td></tr>
		<tr><td align=center width=150 bgcolor=#ededed>Authentication COOKIE</td><td bgcolor=white><?=$f_auth_cookie?></td></tr>
	</table>
	</td></tr>
	</table>

	<?
	flush();
}


//검사 종료후 메시지
function EndView(){
	global $Env,$EnvAct;
	global $time_start;
	$time_end = getmicrotime();
	$Env["time_load"] = $time_end - $time_start;

	//admin폴더 검색하기(일반페이지 스캔 완료후 admin검색실시)
	//echo "<BR>ADMIN_FOLDER_YN:".$EnvAct["ADMIN_FOLDER_YN"];
	if($EnvAct["ADMIN_FOLDER_YN"]=="Y"){
		check_adminfolder();
	}

	//리프토 출력
	errReport();
}

//첫화면 (폼) 출력
function IntroView(){
	global $Env,$PHP_SELF,$EnvAct;
	global $isExecuteWeb;

	if($isExecuteWeb){
		//헤더 출력
		PrintHeader();
	?>
<table width=100% border=0 cellpadding=5 cellspacing=0 bgcolor=darkblue><tr><td><font color=white><b>Server info</td></tr></table>
	<table width=800 border=0 cellpadding=0 cellspacing=0 bgcolor=gray>
	<script language=javascript>
		var tmp=true;
		function check_form(){
			tf=document.tform;
			if(tmp){
				tmp=false;
				tf.submit();
			}			
		}
	</script>
	<form name="tform" action="<?=$PHP_SELF?>" method="post" onsubmit="return false;">
	<tr><td>
	<table width=800 border=0 cellpadding=3 cellspacing=1>
		<tr><td align=center width=150 bgcolor=#ededed>Domain or IP</td><td bgcolor=#efefef><input type="text" name="f_host"  size="30" value="<?=htmlspecialchars($f_host)?>"> (Don't input "http://")</td></tr>
		<tr><td align=center width=150 bgcolor=#ededed>Port</td><td bgcolor=#efefef><input type="text" name="f_port" size="30" value="<?
		if($f_port!="")echo htmlspecialchars($f_port);
		else echo "80";
		?>"></td></tr>
		<tr><td align=center width=150 bgcolor=#ededed>METHOD</td><td bgcolor=#efefef>
		<select name="f_method">
		<option value="GET" selected>GET</option>
		<option value="POST">POST</option>
		</select>		
		</td></tr>
		<tr><td align=center width=150 bgcolor=#ededed>Start Path</td><td bgcolor=#efefef><input type="text" name="f_html_doc" size="30" value="<?
		if($f_html_doc!="")echo htmlspecialchars($f_html_doc);
		else echo "/";	
		?>"> (Start with "/", <input type="checkbox" name="f_subfolder_yn" value="Y">Only sub-folder)</td></tr>
		<tr><td colspan=2 align=center bgcolor=white>
		<table border=0 cellpadding=0 width=100%><tr>
			<td width="30%"></td>
			<td width="10%"><input type="button" onclick="check_form();" value="OK"></td>
			<td width="60%" align=right>
			<input type="button" onclick="div_con('div_auth');" value="Authentication(Login)">
			<input type="button" onclick="div_con('div_cookie');" value="Inject COOKIE">
			<input type="button" onclick="div_con('div_ext');" value="Extension">
			</td>
		</td>
		<script language=javascript>
			function div_con(tmp){
				if(document.getElementById(tmp).style.display=="none"){
					document.getElementById(tmp).style.display="";
				}else{
					document.getElementById(tmp).style.display="none";
				}
			}
		</script>
	</table>
	</td>
	</tr>
	</table>
	</td>
	</tr>
	</table>
	

	<!--확장s-->
	<div id="div_ext" style="display:none;position:relative;">
	<BR>
	<B>* Extension</B>
	<table width=800 border=0 cellpadding=0 cellspacing=0 bgcolor=gray><tr><td>
	<table width=800 border=0 cellpadding=3 cellspacing=1>
		<tr><td align=center width=150 bgcolor=#ededed>FORM check</td>
		<td bgcolor=white>
			<input type="radio" name="f_form_check_yn" value="Y">Yes
			<input type="radio" name="f_form_check_yn" value="N" checked>No
		</td></tr>
		<tr><td align=center width=150 bgcolor=#ededed>Check limit count</td>
		<td bgcolor=white>
			<input type="text" name="f_CheckNumLimit" value="<?=$EnvAct["CheckNumLimit"]?>">
		</td></tr>
		<tr><td align=center width=150 bgcolor=#ededed>Execute limit time</td>
		<td bgcolor=white>
			<input type="text" name="f_runtime" value="<?=$EnvAct["runtime"]?>">초
		</td></tr>
		<tr><td align=center width=150 bgcolor=#ededed>Check count same CGI, different parameter</td>
		<td bgcolor=white>
			<input type="text" name="f_LimitParamCnt" value="<?=$EnvAct["LimitParamCnt"]?>">
		</td></tr>
		<tr><td align=center width=150 bgcolor=#ededed>orce check for 404 ERROR</td>
		<td bgcolor=white>
			<input type="radio" name="f_force_404_yn" value="Y"<? if($EnvAct["force_404_yn"]=="Y")echo " checked";?>>Yes
			<input type="radio" name="f_force_404_yn" value="N"<? if($EnvAct["force_404_yn"]<>"Y")echo " checked";?>>No	</td></tr>
		<tr><td align=center width=150 bgcolor=#ededed>FORM ACTION check with (JAVA)SCRIPT/td>
		<td bgcolor=white>
			<input type="radio" name="f_script_action_yn" value="Y"<? if($EnvAct["script_action_yn"]=="Y")echo " checked";?>>Yes
			<input type="radio" name="f_script_action_yn" value="N"<? if($EnvAct["script_action_yn"]<>"Y")echo " checked";?>>No
		</td></tr>
		<tr><td align=center width=150 bgcolor=#ededed>FORM NULL ACTION self action CGI</td>
		<td bgcolor=white>
			<input type="radio" name="f_self_action_yn" value="Y"<? if($EnvAct["self_action_yn"]=="Y")echo " checked";?>>Yes
			<input type="radio" name="f_self_action_yn" value="N"<? if($EnvAct["self_action_yn"]<>"Y")echo " checked";?>>No
		</td></tr>
		<tr><td align=center width=150 bgcolor=#ededed>Check ERROR</td>
		<td bgcolor=white>
			<input type="checkbox" name="f_ERR_4xx_YN" value="Y"<? if($EnvAct["ERR_4xx_YN"]=="Y")echo " checked";?>>404 Page Not Found<BR>
			<input type="checkbox" name="f_ERR_5xx_YN" value="Y"<? if($EnvAct["ERR_5xx_YN"]=="Y")echo " checked";?>>500 Server Error<BR>
			<input type="checkbox" name="f_ERR_500SQL_YN" value="Y"<? if($EnvAct["ERR_500SQL_YN"]=="Y")echo " checked";?>>500 Sql Server<BR>
			<input type="checkbox" name="f_ERR_200XSS_YN" value="Y"<? if($EnvAct["ERR_200XSS_YN"]=="Y")echo " checked";?>>XSS
			
		</td></tr>
		<tr><td align=center width=150 bgcolor=#ededed>ADMIN folder scan<</td>
		<td bgcolor=white>
			<input type="radio" name="f_ADMIN_FOLDER_YN" value="Y"<? if($EnvAct["ADMIN_FOLDER_YN"]=="Y")echo " checked";?>>Yes 
			<input type="radio" name="f_ADMIN_FOLDER_YN" value="N"<? if($EnvAct["ADMIN_FOLDER_YN"]<>"Y")echo " checked";?>>No<BR>
		</td></tr>
		<tr><td align=center width=150 bgcolor=#ededed>Exception URL</td>
		<td bgcolor=white>
			<input type="text" name="f_EXCEPT_URL" style="width:400px" value="<?=$EnvAct["EXCEPT_URL"]?>">Comma(,) split
		</td></tr>
		<tr><td align=center width=150 bgcolor=#ededed>Scan link depth</td>
		<td bgcolor=white>
			<input type="text" name="f_LinkDepth" style="width:30px" value="<?=$EnvAct["LinkDepth"]?>">
		</td></tr>

			</table>
	</td></tr></table>
	</div><!--확장e-->


	<!--쿠키삽입s-->
	<div id="div_cookie" style="display:none;position:relative;">
	<BR>
	<B>* Authentication(Login) COOKIE</B>
	<table width=800 border=0 cellpadding=0 cellspacing=0 bgcolor=gray><tr><td>
	<table width=800 border=0 cellpadding=3 cellspacing=1>
			<tr><td bgcolor=#efefef align=center width=150>Inject COOKIE</td><td colspan=4  bgcolor=white><input type="text" name="f_cookie_injection" size=70></td></tr>
			</table>
	</td></tr></table>
	</div><!--쿠키삽입e-->

	<!--인증정보s-->
	<div id="div_auth" style="display:none;position:relative;">
	<BR>
	<B>* Authentication(Login) COOKIE</B>
	<table width=800 border=0 cellpadding=0 cellspacing=0 bgcolor=gray><tr><td>
	<table width=800 border=0 cellpadding=3 cellspacing=1>
			<tr><td bgcolor=#efefef align=center width=150><B>Authentication domain(or IP)</td><td colspan=4  bgcolor=white><input type="text" name="f_auth_host" size=30> (Don't input "http://")</td></tr>
			<tr><td bgcolor=#efefef align=center width=150><B>Authentication path</td><td colspan=4  bgcolor=white><input type="text" name="f_auth_url" size=60> (Start with "/")</td></tr>
			<tr><td  bgcolor=#efefef align=center >METHOD</td><td colspan=4  bgcolor=white><input type="text" name="f_auth_method" size=40 value="POST"></td></tr>
			<tr><td bgcolor=#efefef align=center >PORT</td><td colspan=4  bgcolor=white><input type="text" name="f_auth_port" size=40 value="80"> (SSL:443) </td></tr>
			<?
			for($t=1;$t<=10;$t++){
			?>
			<tr><td bgcolor=#efefef align=right width=150>INPUT name:<?=$t?></td><td  bgcolor=white><input type="text" name="f_auth_input_name[]" size=30></td>
				<td bgcolor=#efefef align=right width=150>INPUT value:<?=$t?></td><td  bgcolor=white><input type="text" name="f_auth_input_value[]" size=30></td></tr>
			<?
			}
			?>
			</table>
	</td></tr></table>
	</div><!--인증정보e-->
		
	<BR><BR>
	<!--
	//////////////////////////////////////////////////////////////////////////////////////////////
	//	프로그램 설명
	//////////////////////////////////////////////////////////////////////////////////////////////
	-->
	<!--개발중안내-->
	<table width=100% border=0 cellpadding=5 cellspacing=0 bgcolor=darkblue><tr><td>
	<font color=white><b>Scheduled UPDATE</td></tr></table>
	<table width=800 border=0 cellpadding=0 cellspacing=0 bgcolor=gray>
	<tr><td>
	<table width=800 border=0 cellpadding=3 cellspacing=1>
		<tr><td bgcolor=#efefef width=100 align=center valign=top>2005</td>
			<td bgcolor=white>
			-Google scan<BR>
			-SUB-DOMAIN scan<BR>
			-DETAIL ERROR CODE:error link,error msg,error CGI<BR>
			-META TAG analysis<BR>
			-When "Directory Listing Denied", asp,php list scan<BR>
			-window.open script scan
			</td>
		</tr>
	</table>
	</td></tr></table>
	<!--업데이트 안내-->
	<table width=100% border=0 cellpadding=5 cellspacing=0 bgcolor=darkblue><tr><td>
	<font color=white><b>Last UPDATE</td></tr></table>
	<table width=800 border=0 cellpadding=0 cellspacing=0 bgcolor=gray>
	<tr><td>
	<table width=800 border=0 cellpadding=3 cellspacing=1>
		<tr><td bgcolor=#efefef width=100 align=center valign=top>2005.11.10</td>
			<td bgcolor=white>X
			</td>
		</tr>
		<tr><td bgcolor=#efefef width=100 align=center valign=top>2005.11.10</td>
			<td bgcolor=white>Version 0.13 <BR>
				-Admin folder scan(/admin, /manager)<BR>
				-XSS scan<BR>
				-FORM ACTION check with (JAVA)SCRIPT
			</td>
		</tr>
		<tr><td bgcolor=#efefef width=100 align=center valign=top>2005.11.10</td>
			<td bgcolor=white>Version 0.12 <BR>
				-SSL authentication scan<BR>
				-FORM Fileupload scan
			</td>
		</tr>
		<tr><td bgcolor=#efefef width=100 align=center valign=top>2005.11.07</td>
			<td bgcolor=white>Version 0.12 <BR>
				-FORM Injection Scan<BR>
				-Set detail Config (Limit link number, CGI extension)
			</td>
		</tr>
		<tr><td bgcolor=#efefef width=100 align=center valign=top>2005.11.02</td>
			<td bgcolor=white>Version 0.11 <BR>
				-Scan after login authentication<BR>
				-Inject force COOKIES scan
			</td>
		</tr>
		<tr><td bgcolor=#efefef width=100 align=center valign=top>2005.10.31</td>
			<td bgcolor=white>Version 0.1 launch<BR>
				-404error search, 500error search, SQL injection scan<BR>
				-location, href, src, action HTML LINK scan<BR>
				-Set sub-folder scan<BR>
			</td>
		</tr>
	</table>
	</td></tr></form></table>

	<BR><BR>
	<li><a href="http://sourceforge.net/projects/wstool">http://sourceforge.net/projects/wstool</a>
	<li><a href="http://my.dreamwiz.com/zero12a/wstool/">http://my.dreamwiz.com/zero12a/wstool/</a>

	<?
	}else{
		?><?=$Env["pro_info"]?> (Ver <?=$Env["ver_info"]?>)
		Usage: ws_main.php (Host|IP) PORT (GET|POST) URL

		ex> ws_main.php 127.0.0.1 80 GET /main.asp > save_report.html

	<?
	}
}
?>