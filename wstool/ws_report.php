<?



//500에러 출력
function errReport(){
	global $Env;
	global $errLink;
	global $TargetHost;
	global $PHP_SELF;
	global $_SERVER;
	global $Summary;//결과요약


	echo "\n\n<BR><BR><table width=100% border=0 cellpadding=5 cellspacing=0 bgcolor=darkblue><tr><td><font color=white><b>[Error Report]----------------------------------------------------------------------------</td></tr></table>\n";
	echo "\n\n\n<BR>Execute time(secound):<font color=blue>".$Env["time_load"]."</font>";
	?>
	<table width=800 border=0 cellpadding=0 cellspacing=0 bgcolor=gray><tr>
	
	<td>
	<table width=800 border=0 cellpadding=0 cellspacing=1>
	<?
	for($j=0;$j<count($errLink);$j++){
		if(strlen($errLink[$j][5])>0){
				$pathlink=$errLink[$j][0]."?".GetQueryUrlEncode($errLink[$j][5],$ForceEncode=false);
		}else{
				$pathlink=$errLink[$j][0];
		}

		echo "\n<tr><td colspan=4 bgcolor=#e1e1e1 style=\"word-break:break-all\">".sprintf("%3d : ",$j+1)."<a href=\"http://".$TargetHost.$pathlink."\" target=\"_blank\">".htmlspecialchars($pathlink)."</a></td></tr>";
		echo "\n<tr><td align=right bgcolor=white valign=top>Referer</td><td colspan=3 bgcolor=white style=\"word-break:break-all\"><a href=\"http://".$TargetHost.$errLink[$j][4]."\" target=\"_blank\"><font color=gray>".htmlspecialchars($errLink[$j][4])."</font></a></td></tr>";
		for($k=0;$k<count($errLink[$j][3]);$k++){
			$Summary["ERRCODE"][$errLink[$j][3][$k][1]]++;//에러코드별 갯수정리(전체)
			if($Summary["ErrSourceHash"][$errLink[$j][3][$k][1]][$errLink[$j][0]]!="Y")
			{
				$Summary["ErrSource"][$errLink[$j][3][$k][1]]++;//에러코드별 갯수정리(한개 소스는 1번 카운팅)
			}
			$Summary["ErrAllCnt"]++;
			echo "\n\t\t<tr bgcolor=white  onclick=\"";
			if($errLink[$j][6]=="POST"){
				echo "go_post('http://".$TargetHost.$errLink[$j][0]."','".$errLink[$j][6]."','".$errLink[$j][3][$k][3][0]["enctype"]."','".str_replace("'","\'",$errLink[$j][3][$k][2])."','".str_replace("'","\'",MergeQueryNFormNType("",$errLink[$j][3][$k][3]))."')";
			}else{
				echo "go_link('http://".$TargetHost.$errLink[$j][0]."','".str_replace("'","\'",$errLink[$j][3][$k][0])."')";
			}
			echo "\" onmouseover=\"this.style.backgroundColor='#EFEFEF'\" onmouseout=\"this.style.backgroundColor='white'\"><td width=50  align=right valign=top><font color=red>".($k+1)."</font></td>";
			
			echo "<td   width=50 align=center>".$errLink[$j][6]."</td>";
			echo "<td   width=540 style=\"word-break:break-all\">"
			.htmlspecialchars($errLink[$j][3][$k][0])."</td>";
			echo "<td width=170  style=\"word-break:break-all\">▶".$errLink[$j][3][$k][1]." ".$Env["HeaderStatusCode"][$errLink[$j][3][$k][1]] ."</td></tr>";

			//distinct 소스에러 (한 에러코드에 한소스는 한번만)
			$Summary["ErrSourceHash"][$errLink[$j][3][$k][1]][$errLink[$j][0]]="Y";
		}
	}
	if(count($errLink)==0){
		?>
		<tr><td height=50 bgcolor=white align=center><font color=blue>not found error</td></tr>
		<?
	}
	?></table></td></tr></table>
	<BR>All error count:<font color=red><B><?=$Summary["ErrAllCnt"]?></B></font><BR>
	<?
	if(is_array($Summary["ERRCODE"])){
		ECHO "<BR>◈<font color=blue><b>Error code level</b></font>";
		while (list($key, $value) = each($Summary["ERRCODE"])) {
			echo "&nbsp;&nbsp;&nbsp;&nbsp;<li> (".$key.") ".$Env["HeaderStatusCode"][$key]." Error count:<B>".$value."</B>";
		}
	}
	if(is_array($Summary["ErrSource"])){
		ECHO "<BR>◈<font color=blue><b>CGI level</b></font>";
		while (list($key, $value) = each($Summary["ErrSource"])) {
			echo "&nbsp;&nbsp;&nbsp;&nbsp;<li> (".$key.") ".$Env["HeaderStatusCode"][$key]." Error count:<B>".$value."</B>";
		}
	}

	?>
	<!--POST방식 전송하기s-->
	<form action="http://174.100.139.72/security/ws_reportform.php" name="postform" method="post" target="_blank">
		<input type="hidden" name="f_action" value="">
		<input type="hidden" name="f_method" value="">
		<input type="hidden" name="f_enctype" value="">
		<input type="hidden" name="f_querystring" value="">
		<input type="hidden" name="f_inputstring" value="">
	</form>
	<script language=javascript>

		function go_post(action,method,enctype,querystring,inputstring){
			var win = window.open("","post_win","width=500,height=600,scrollbars=yes,resizable=yes");
			var aryQS = new Array() ;
			var aryNV;
			var aryNV2;
		
			var body = "";
			var input_type;

			aryQS = inputstring.split("&");
			body = "<html>\<head>\n<style>\nbody,td{font-size:9pt}\n</style><body onload=\"window.focus();\">\n";
			body = body + "<BR>ACTION : <a target=\"_blank\" href=\"" + action + "\">" + action +  "</a>";
			body = body + "<BR>METHOD : " + method;
			body = body + "<BR>ENCTYPE : " + enctype;
			body = body + "<table border=1 width=100%>\n<form target=\"_blank\" name=tform enctype=\"" + enctype + "\" method=\"" + method + "\" action=\"" + action + "?" + querystring + "\">\n";
			body = body + "<tr bgcolor=silver align=center><td width=25%><B>Input Name</td><td width=10%><B>TYPE</td><td width=65%><B>Input Value</td></tr>";
			for(var i=0;i < aryQS.length ; i++){
				aryNV2= new Array();
				aryNV= new Array();
				aryNV2 = aryQS[i].split("ⓣ");
				aryNV = aryNV2[0].split("=");

				input_type = "TEXT";
				//alert(aryNV2.length);
				//alert(aryNV2[1]);
				if(aryNV[0] != ""){
					if(aryNV2.length == 2 && aryNV2[1] != "") input_type = aryNV2[1].toUpperCase() ;

					if(input_type == "HIDDEN" || input_type == "IMAGE" || input_type == "RADIO" || input_type == "CHECKBOX"){
						body = body + "<tr><td align=center bgcolor=efefef>" + aryNV[0] + "</td><td>" + input_type + "</td><td><input type=\"TEXT\" name=\"" + aryNV[0] + "\" value=\"" + aryNV[1] + "\" style=\"width:100%\"></td></tr>\n";
					}else if(input_type == "TEXTAREA"){
						body = body + "<tr><td align=center bgcolor=efefef>" + aryNV[0] + "</td><td>" + input_type + "</td><td><TEXTAREA  style=\"width:100%\" rows=3 name=\"" + aryNV[0] + "\">" + aryNV[1] + "</TEXTAREA></td></tr>\n";
					}else if(aryNV.length == 2){
						body = body + "<tr><td align=center bgcolor=efefef>" + aryNV[0] + "</td><td>" + input_type + "</td><td><input type=\"" + input_type + "\" name=\"" + aryNV[0] + "\" value=\"" + aryNV[1] + "\" style=\"width:100%\"></td></tr>\n";
					}else{
						body = body + "<tr><td align=center bgcolor=efefef>" + aryQS[i] + "</td><td>" + input_type + "</td><td><input type=\"" + input_type + "\" name=\"" + aryQS[i] + "\" value=\"\" style=\"width:100%\"></td></tr>\n";
					}
				}
			}

			
			body = body + "<tr><td colspan=3><input type=submit></td></tr>\n</form>\n</table>\n" ;
			win.document.open();
			win.document.write(body);
			win.document.close();
		}


		function go_link(action,querystring){
			var win = window.open("","link_win","width=500,height=600,scrollbars=yes,resizable=yes");
			var aryQS = new Array() ;
			aryQS = querystring.split("&");
			var aryNV;

			var body = "";
			body = "<html>\<head>\n<style>\nbody,td{font-size:9pt}\n</style><body onload=\"window.focus();\">\n";
			body = body + "<BR>ACTION : <a target=\"_blank\" href=\"" + action + "\">" + action +  "</a>";
			body = body + "<BR>METHOD : GET";
			body = body + "<BR>ENCTYPE : ";
			
			body = body + "<table border=1 width=100%>\n<form target=\"_blank\" name=tform method=GET action=\"" + action + "\">\n";
			body = body + "<tr bgcolor=silver align=center><td width=25%><B>Query Name</td><td width=22%><B>TYPE</td><td width=53%><B>Query Value</td></tr>";
			for(var i=0;i < aryQS.length ; i++){
				aryNV= new Array();
				aryNV = aryQS[i].split("=");
				if(aryNV[0] != ""){
					if(aryNV.length == 2){
						body = body + "<tr><td align=center bgcolor=efefef>" + aryNV[0] + "</td><td>QUERY STRING</td><td><input type=text name=\"" + aryNV[0] + "\" value=\"" + aryNV[1] + "\" style=\"width:100%\"></td></tr>\n";
					}else{
						body = body + "<tr><td align=center bgcolor=efefef>" + aryQS[i] + "</td><td>QUERY STRING</td><td><input type=text name=\"" + aryQS[i] + "\" value=\"\" style=\"width:100%\"></td></tr>\n";
					}
				}
			}
			body = body + "<tr><td colspan=3><input type=submit></td></tr>\n</form>\n</table>\n" ;
			win.document.open();
			win.document.write(body);
			win.document.close();
		}

	</script>
	<!--POST방식 전송하기e-->



	<BR><BR>
	<hr>
	<?=$Env["f_scrollbar"]?>
	<center><input type="button" onclick="location='<?=$PHP_SELF?>';" value="Main">

	</center>
	<BR><BR>
	<li>Download: <a href="http://sourceforge.net/projects/wstool">http://sourceforge.net/projects/wstool</a>
	<li>Project homepage: <a href="http://wstool.sourceforge.net/">http://wstool.sourceforge.net/</a>
	</body>
	</html>
	<?
}

?>