<?php
	// 설정파일
	include_once "../config.belif";

	switch ($_REQUEST['exec'])
	{
		case "login" :
			$mb_id = $_REQUEST['mb_id'];
			$mb_pw = $_REQUEST['mb_pw'];

			$query = "SELECT * FROM ".$_gl['member_info_table']." WHERE mb_name='".$mb_id."' AND mb_phone='".$mb_pw."'";
			$result 		= mysqli_query($my_db, $query);
			$member_info	= mysqli_fetch_array($result);
			if ($member_info)
			{
				// 회원아이디 세션 생성
				$_SESSION['ss_mb_name'] = $member_info['mb_ipaddr'];
				echo "<script>location.href='./entry_list.php';</script>";
			}else{
				echo "<script>alert('로그인에 실패하였습니다.');</script>";
				echo "<script>history.back();</script>";
			}
		break;

		case "logout" :
			session_destroy();
			echo "<script>location.href='./index.php';</script>";
		break;

		case "send_sms" :
			$phone			= $_REQUEST['phone'];
			$phone_arr		= explode("-",$phone);
			$cel				= $phone_arr[0].$phone_arr[1].$phone_arr[2];

			/*
			$httpmethod = "POST";
			$url = "http://api.openapi.io/ppurio_test/1/message_test/lms/minivertising";
			$clientKey = "MS0xMzY1NjY2MTAyNDk0LTA2MWE4ZDgyLTZhZmMtNGU5OS05YThkLTgyNmFmYzVlOTkzZQ==";
			$contentType = "Content-Type: application/x-www-form-urlencoded";
			*/
			$httpmethod = "POST";
			$url = "http://api.openapi.io/ppurio/1/message/lms/minivertising";
			$clientKey = "MTAyMC0xMzg3MzUwNzE3NTQ3LWNlMTU4OTRiLTc4MGItNDQ4MS05NTg5LTRiNzgwYjM0ODEyYw==";
			$contentType = "Content-Type: application/x-www-form-urlencoded";

			$response = sendRequest($httpmethod, $url, $parameters, $clientKey, $contentType, $phone);

			//echo("<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />");
			$json_data = json_decode($response, true);

			//print_r($json_data);
			/*
			받아온 결과값을 DB에 저장 및 Variation
			*/
			$query = "INSERT INTO ".$_gl['sms_info_table']."(send_phone, send_status, cmid, send_regdate) values('".$phone."','".$json_data['result_code']."','".$json_data['cmid']."','".date("Y-m-d H:i:s")."')";
			$result 		= mysqli_query($my_db, $query);

			$query2 = "UPDATE ".$_gl['member_info_table']." SET mb_lms='Y' WHERE mb_phone='".$phone."'";
			$result2 		= mysqli_query($my_db, $query2);


			$flag = "N";
			if ($result)
				echo $flag = "Y";
			else
				echo $flag = "N";
		break;

		case "all_send_sms" :
			$query = "SELECT mb_phone, mb_s_url, mb_nansu FROM ".$_gl['member_info_table']." WHERE mb_winner='Y' AND mb_use='N' AND mb_s_url<>'' AND mb_phone='010-3051-2171'";
			//$query = "SELECT mb_phone, mb_s_url, mb_nansu FROM ".$_gl['member_info_table']." WHERE mb_winner='Y' AND mb_use='N' AND mb_s_url<>''";
			$result 		= mysqli_query($my_db, $query);

			$httpmethod = "POST";
			$url = "http://api.openapi.io/ppurio/1/message/lms/minivertising";
			$clientKey = "MTAyMC0xMzg3MzUwNzE3NTQ3LWNlMTU4OTRiLTc4MGItNDQ4MS05NTg5LTRiNzgwYjM0ODEyYw==";
			$contentType = "Content-Type: application/x-www-form-urlencoded";

			while ($data = @mysqli_fetch_array($result))
			{
				$phone			= $data['mb_phone'];
				$s_url				= $data['mb_s_url'];
				$nansu			= $data['mb_nansu'];

				$response = sendRequest($httpmethod, $url, $parameters, $clientKey, $contentType, $phone, $s_url, $nansu);

				echo("<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />");
				$json_data = json_decode($response, true);

				//print_r($json_data);
				/*
				받아온 결과값을 DB에 저장 및 Variation
				*/
				$query3 = "INSERT INTO ".$_gl['sms_info_table']."(send_phone, send_status, cmid, send_regdate) values('".$phone."','".$json_data['result_code']."','".$json_data['cmid']."','".date("Y-m-d H:i:s")."')";
				$result3 		= mysqli_query($my_db, $query3);

				$query2 = "UPDATE ".$_gl['member_info_table']." SET mb_lms='Y' WHERE mb_phone='".$phone."'";
				$result2 		= mysqli_query($my_db, $query2);
			}

			$flag = "N";
			if ($result)
				echo $flag = "Y";
			else
				echo $flag = "N";
		break;

		case "view_comment" :
			$idx		= $_REQUEST['idx'];
			$viewYN	= $_REQUEST['viewYN'];

			if ($viewYN == "Y")
			{
				$query	= "UPDATE ".$_gl['comment_info_table']." SET viewYN='N' WHERE idx='".$idx."'";
				$result		= mysqli_query($my_db, $query);
			}else{
				$query	= "UPDATE ".$_gl['comment_info_table']." SET viewYN='Y' WHERE idx='".$idx."'";
				$result		= mysqli_query($my_db, $query);
			}

			$flag = "N";
			if ($result)
				$flag = "Y";
			else
				$flag = "N";

			echo $flag;
		break;
	}
?>