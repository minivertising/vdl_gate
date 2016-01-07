<?php
include_once "config.php";

switch ($_REQUEST['exec'])
{
	case "insert_share_info" :
		$sns_media	= $_REQUEST['sns_media'];
		$sns_phone	= $_SESSION['mb_phone'];
		$sns_flag		= $_REQUEST['sns_flag'];

		$query 		= "INSERT INTO ".$_gl['share_info_table']."(sns_media, sns_ipaddr, sns_gubun, sns_phone, sns_flag, inner_media, sns_regdate) values('".$sns_media."','".$_SERVER['REMOTE_ADDR']."','".$gubun."','".$sns_phone."','".$sns_flag."','".$_SESSION['ss_media']."','".date("Y-m-d H:i:s")."')";
		$result 	= mysqli_query($my_db, $query);

		if ($result)
			$flag = "Y";
		else
			$flag = "N";

		echo $flag;

	break;

	case "insert_info" :
		$mb_name			= $_REQUEST['mb_name'];
		$mb_phone			= $_REQUEST['mb_phone'];
		$sel_nominee		= $_REQUEST['sel_nominee'];
		$media				= $_SESSION['ss_media'];

		$dupli_query 	= "SELECT * FROM ".$_gl['member_info_table']." WHERE mb_phone='".$mb_phone."' AND mb_sel_nominees='".$sel_nominee."'";
		$dupli_result 	= mysqli_query($my_db, $dupli_query);
		$dupli_cnt	= mysqli_num_rows($dupli_result);

		$all_dupli_query 	= "SELECT * FROM ".$_gl['member_info_table']." WHERE mb_phone='".$mb_phone."' AND mb_upload_url is not NULL";
		$all_dupli_result 	= mysqli_query($my_db, $all_dupli_query);
		$all_dupli_cnt	= mysqli_num_rows($all_dupli_result);

		$vote_dupli_query 	= "SELECT * FROM ".$_gl['voter_info_table']." WHERE vote_phone='".$mb_phone."'";
		$vote_dupli_result 	= mysqli_query($my_db, $vote_dupli_query);
		$vote_dupli_cnt	= mysqli_num_rows($vote_dupli_result);


		// 개인정보 입력은 했으나, 사진 등록을 안했는지 체크
		$dupli_no_query 	= "SELECT * FROM ".$_gl['member_info_table']." WHERE mb_phone='".$mb_phone."' AND mb_sel_nominees='".$sel_nominee."' AND mb_upload_url is NULL";
		$dupli_no_result 	= mysqli_query($my_db, $dupli_no_query);
		$dupli_no_cnt	= mysqli_num_rows($dupli_no_result);



		if ($dupli_cnt > 0)
		{
			if ($dupli_no_cnt > 0)
			{
				$query2 	= "DELETE FROM ".$_gl['member_info_table']." WHERE mb_phone='".$mb_phone."' AND mb_sel_nominees='".$sel_nominee."'";
				$result2 	= mysqli_query($my_db, $query2);
				$query 	= "INSERT INTO ".$_gl['member_info_table']."(mb_ipaddr,mb_name,mb_phone,mb_sel_nominees,mb_regdate,mb_gubun,mb_media) values('".$_SERVER['REMOTE_ADDR']."','".$mb_name."','".$mb_phone."','".$sel_nominee."','".date("Y-m-d H:i:s")."','".$gubun."','".$media."')";
				$result 	= mysqli_query($my_db, $query);

				// Y1 = 3,000원 할인쿠폰, Y = 무료 배송 쿠폰
				if ($result)
					$flag	= "Y1";
				else
					$flag	= "N";

				if ($all_dupli_cnt == 0)
				{
					if ($vote_dupli_cnt > 0)
						$flag	= "Y1";
					else
						$flag	= "Y"; // 수정 Y
				}

				// 전화번호 세션 생성
				$_SESSION['mb_phone']		= $mb_phone;
				// 선택한 카테고리 세션 생성
				$_SESSION['sel_nominee']		= $sel_nominee;
				// 당첨정보 생성
				$_SESSION['nominee_gubun']		= $flag;

			}else{
				$flag	= "D";
			}
			if ($all_dupli_cnt > 4)
				$flag	= "AD";
		}else{
			$query 	= "INSERT INTO ".$_gl['member_info_table']."(mb_ipaddr,mb_name,mb_phone,mb_sel_nominees,mb_regdate,mb_gubun,mb_media) values('".$_SERVER['REMOTE_ADDR']."','".$mb_name."','".$mb_phone."','".$sel_nominee."','".date("Y-m-d H:i:s")."','".$gubun."','".$media."')";
			$result 	= mysqli_query($my_db, $query);


			// Y1 = 3,000원 할인쿠폰, Y = 무료 배송 쿠폰
			if ($result)
				$flag	= "Y1";
			else
				$flag	= "N";

			if ($all_dupli_cnt == 0)
			{
				if ($vote_dupli_cnt > 0)
					$flag	= "Y1";
				else
					$flag	= "Y"; // 수정 Y
			}

			// 전화번호 세션 생성
			$_SESSION['mb_phone']		= $mb_phone;
			// 선택한 카테고리 세션 생성
			$_SESSION['sel_nominee']		= $sel_nominee;
			// 당첨정보 생성
			$_SESSION['nominee_gubun']		= $flag;


		}
		echo $flag;
	break;

	case "insert_pic_info" :
		$mb_baby_name	= $_REQUEST['mb_baby_name'];
		$mb_baby_month	= $_REQUEST['mb_baby_month'];
		$mb_pic				= $_REQUEST['mb_pic'];
		$mb_youtube_url	= $_REQUEST['mb_youtube_url'];

		if ($mb_pic)
		{
			$upload_flag	= "P";
			$mb_pic_arr		= explode(".", $mb_pic);
			//$upload_url	= "http://localhost/babience_awards/files/".$_SESSION['sel_nominee']."/".$_SESSION['mb_phone']."/".$_SESSION['mb_phone'].".".$mb_pic_arr[1];
			//$thumb_url	= "http://localhost/babience_awards/files/".$_SESSION['sel_nominee']."/".$_SESSION['mb_phone']."/thumbnail/".$_SESSION['mb_phone'].".".$mb_pic_arr[1];
			$upload_url	= "http://awards.babience-event.com/files/".$_SESSION['sel_nominee']."/".$_SESSION['mb_phone']."/".$_SESSION['mb_phone'].".".$mb_pic_arr[1];
			$thumb_url	= "http://awards.babience-event.com/files/".$_SESSION['sel_nominee']."/".$_SESSION['mb_phone']."/thumbnail/".$_SESSION['mb_phone'].".".$mb_pic_arr[1];
			$imgSize		= getimagesize($upload_url);
			$imgWidth	= $imgSize[0];
			$imgHeight	=$imgSize[1];

		}else{
			$upload_flag	= "V";
			$upload_url	= $mb_youtube_url;
			if (strpos($upload_url, "youtube.com") !== false)
			{
				$thumb_url_arr	= explode("v=",$upload_url);
				$upload_url		= "https://www.youtube.com/embed/".$thumb_url_arr[1];
				$thumb_url		= "http://img.youtube.com/vi/".$thumb_url_arr[1]."/2.jpg";
			}else{
				$thumb_url_arr	= explode("/",$upload_url);
				$upload_url		= "https://www.youtube.com/embed/".$thumb_url_arr[3];
				$thumb_url		= "http://img.youtube.com/vi/".$thumb_url_arr[3]."/2.jpg";
			}
			$imgWidth	= "0";
			$imgHeight	="0";

		}

		if ($_SESSION['nominee_gubun'] == "Y")
		{
			// 배송비 중복 당첨여부 체크
			$dupli_bann_query		= "SELECT * FROM ".$_gl['bann_info_table']." WHERE bann_phone='".$_SESSION['mb_phone']."'";
			$dupli_bann_result		= mysqli_query($my_db, $dupli_bann_query);
			$dupli_bann_num		= mysqli_num_rows($dupli_bann_result);
			
			if ($dupli_bann_num == 0)
			{
				$nominee_gubun	= "Y"; // 수정
				$serial	= BA_getSerial("N||DELIVERY"); // 수정 
			}else{
				$nominee_gubun	= "Y1"; // 수정
				$serial	= BA_getSerial("N||DISCOUNT");
			}
			$vote_query 	= "SELECT * FROM ".$_gl['voter_info_table']." WHERE vote_phone='".$_SESSION['mb_phone']."'";
			$vote_result 	= mysqli_query($my_db, $vote_query);
			$vote_cnt	= mysqli_num_rows($vote_result);
			
			if ($vote_cnt == 0)
				send_lms($_SESSION['mb_phone'], $serial);
		}else{
			$nominee_gubun	= "Y1"; // 수정
			$serial	= BA_getSerial("N||DISCOUNT");
		}

		$query 	= "UPDATE ".$_gl['member_info_table']." SET mb_baby_name='".$mb_baby_name."', mb_baby_month='".$mb_baby_month."', mb_upload_flag='".$upload_flag."', mb_upload_url='".$upload_url."', mb_thumb_url='".$thumb_url."', mb_winner='".$nominee_gubun."', mb_serial='".$serial."', img_width='".$imgWidth."', img_height='".$imgHeight."' WHERE mb_phone='".$_SESSION['mb_phone']."' AND mb_sel_nominees='".$_SESSION['sel_nominee']."'";
		$result 	= mysqli_query($my_db, $query);

		if ($result)
			$flag	= "Y";
		else
			$flag	= "N";
		echo $nominee_gubun;
	break;

	case "insert_vote_info" :
		$vote_name		= $_REQUEST['vote_name'];
		$vote_phone	= $_REQUEST['vote_phone'];
		$vote_idx			= $_REQUEST['vote_idx'];
		$media				= $_SESSION['ss_media'];

		$dupli_query 	= "SELECT * FROM ".$_gl['voter_info_table']." WHERE vote_phone='".$vote_phone."' AND vote_regdate like '%".date("Y-m-d")."%'";
		$dupli_result 	= mysqli_query($my_db, $dupli_query);
		$dupli_cnt	= mysqli_num_rows($dupli_result);

		$all_query 	= "SELECT * FROM ".$_gl['voter_info_table']." WHERE vote_phone='".$vote_phone."'";
		$all_result 	= mysqli_query($my_db, $all_query);
		$all_cnt	= mysqli_num_rows($all_result);

		

		if ($dupli_cnt > 0)
		{
			$flag	= "D||no";
		}else{
			$winnerYN	= BA_winner_draw($vote_phone);
			//$winnerYN2	= "N||DISCOUNT";
			$serial		= BA_getSerial($winnerYN);
			$query 	= "INSERT INTO ".$_gl['voter_info_table']."(vote_ipaddr,vote_name,vote_phone,vote_sel_idx,vote_winner,vote_regdate,vote_gubun,vote_media,vote_serial) values('".$_SERVER['REMOTE_ADDR']."','".$vote_name."','".$vote_phone."','".$vote_idx."','".$winnerYN."','".date("Y-m-d H:i:s")."','".$gubun."','".$media."','".$serial."')";
			$result 	= mysqli_query($my_db, $query);

			$query2 	= "UPDATE ".$_gl['member_info_table']." SET mb_vote=mb_vote+1 WHERE idx='".$vote_idx."'";
			$result2 	= mysqli_query($my_db, $query2);

			$nominee_query 	= "SELECT * FROM ".$_gl['member_info_table']." WHERE mb_phone='".$vote_phone."'";
			$nominee_result 	= mysqli_query($my_db, $nominee_query);
			$nominee_cnt	= mysqli_num_rows($nominee_result);

			if ($all_cnt == 0)
			{
				if ($nominee_cnt == 0)
					send_lms2($vote_phone, $serial);
			}
			if ($winnerYN == "N||DELIVERY")
			{
				if ($nominee_cnt == 0)
					send_lms2($vote_phone, $serial);
			}
			$flag	= $winnerYN;
		}
		echo $flag;
	break;

	case "send_lms" :
		$mb_phone			= $_REQUEST['mb_phone'];
		$mb_gift				= $_REQUEST['mb_gift'];
		$mb_nation			= $_REQUEST['mb_nation'];

		$gift_query		= "SELECT * FROM ".$_gl['member_info_table']." WHERE mb_phone='".$mb_phone."'";
		$gift_result		= mysqli_query($my_db, $gift_query);
		$gift_num		= mysqli_num_rows($gift_result);

		$winner_query		= "SELECT * FROM ".$_gl['member_info_table']." WHERE mb_phone='".$mb_phone."' AND mb_winner like '%Y%'";
		$winner_result		= mysqli_query($my_db, $winner_query);
		$winner_num		= mysqli_num_rows($winner_result);

		if ($mb_gift == "0D")
			$result	= "N";
		else if ($mb_gift == "2D")
			$result	= "N";
		else if ($mb_gift == "3D")
			$result	= "N";
		else
			$result	= send_lms($mb_phone, $mb_gift, $mb_nation);
		echo $gift_num;
	break;

	case "use_coupon" :
		$mb_phone			= $_REQUEST['mb_phone'];

		$query 	= "UPDATE ".$_gl['member_info_table']." SET mb_use='Y' WHERE mb_phone='".$mb_phone."'";
		$result 	= mysqli_query($my_db, $query);

		if ($result)
			$flag	= "Y";
		else
			$flag	= "N";
		
		echo $flag;
	break;
}
?>