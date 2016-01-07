<?
	// 유입매체 정보 입력
	function BR_InsertTrackingInfo($media, $gubun)
	{
		global $_gl;
		global $my_db;

		$query		= "INSERT INTO ".$_gl['tracking_info_table']."(tracking_media, tracking_refferer, tracking_ipaddr, tracking_date, tracking_gubun) values('".$media."','".$_SERVER['HTTP_REFERER']."','".$_SERVER['REMOTE_ADDR']."',now(),'".$gubun."')";
		$result		= mysqli_query($my_db, $query);
	}

	function BA_winner_draw($mb_phone)
	{
		global $_gl;
		global $my_db;

		$water_winner		= 30;	// 베이비 워터 1박스
		$skin_winner			= 30;	// 스킨케어 2종 세트(로션+워시)
		$clean_winner		= 30;	// 클린 4종세트(세제섬유유연제용기+리필
		$delivery_winner	= 50000;	// 무료 배송 쿠폰
		$discount_winner	= 50000;	// 3천원 할인 쿠폰

		$water_array = array(200000);
		$skin_array = array(200000);
		$clean_array = array(200000);
		$delivery_array = array(200000);
		$discount_array = array(200000);
		$delivery_array = array("Y","N");
		$discount_array = array("Y","N");

		// 오늘의 이벤트 참여자 수 구하기
		$total_query		= "SELECT * FROM ".$_gl['voter_info_table']." WHERE vote_regdate like '%".date("Y-m-d")."%'";
		$total_result		= mysqli_query($my_db, $total_query);
		$total_num		= mysqli_num_rows($total_result);

		// 중복 당첨 체크
		$dupli_query		= "SELECT * FROM ".$_gl['voter_info_table']." WHERE vote_phone='".$mb_phone."' AND vote_winner like '%Y%'";
		$dupli_result		= mysqli_query($my_db, $dupli_query);
		$dupli_num		= mysqli_num_rows($dupli_result);

		// 중복 참여자 체크
		$dupli0_query		= "SELECT * FROM ".$_gl['voter_info_table']." WHERE vote_phone='".$mb_phone."'";
		$dupli0_result		= mysqli_query($my_db, $dupli0_query);
		$dupli0_num		= mysqli_num_rows($dupli0_result);
		
		// 후보자 중복 참여자 체크
		$dupli1_query		= "SELECT * FROM ".$_gl['member_info_table']." WHERE mb_phone='".$mb_phone."'";
		$dupli1_result		= mysqli_query($my_db, $dupli1_query);
		$dupli1_num		= mysqli_num_rows($dupli1_result);

		// 배송비 중복 당첨여부 체크
		$dupli_bann_query		= "SELECT * FROM ".$_gl['bann_info_table']." WHERE bann_phone='".$mb_phone."'";
		$dupli_bann_result		= mysqli_query($my_db, $dupli_bann_query);
		$dupli_bann_num		= mysqli_num_rows($dupli_bann_result);

		if ($dupli_num > 0)
		{
			$winner = "N||DISCOUNT";
		}else{
			$winner = "N||DISCOUNT";
			if ($dupli0_num == 0)
			{
				if ($dupli_bann_num == 0 || $dupli1_num == 0)
					$winner = "N||DELIVERY";
				else
					$winner = "N||DISCOUNT";
			}

			if ($winner != "N||DELIVERY")
			{
				if ($dupli0_num < 3)
				{
					$winner = "N||DISCOUNT";
				}else{
					foreach ($water_array as $key => $val)
					{
						if ($total_num == $val)
						{
							$winner = "Y||WATER";
							break;
						}
						$winner = "N||DISCOUNT";
							//$winner = "Y||WATER";
					}

					foreach ($skin_array as $key => $val)
					{
						if ($total_num == $val)
						{
							$winner = "Y||SKIN";
							break;
						}
						$winner = "N||DISCOUNT";
							//$winner = "Y||WATER";
					}

					foreach ($clean_array as $key => $val)
					{
						if ($total_num == $val)
						{
							$winner = "Y||CLEAN";
							break;
						}
						$winner = "N||DISCOUNT";
							//$winner = "Y||WATER";
					}
				}
			}
		}
		return $winner;
	}



	function BC_getSerial()
	{
		global $_gl;
		global $my_db;

		$query		= "SELECT serial_code FROM ".$_gl['serial_info_table']." WHERE useYN='N' limit 1";
		$result		= mysqli_query($my_db, $query);
		$data			= mysqli_fetch_array($result);

		$query2		= "UPDATE ".$_gl['serial_info_table']." SET useYN='Y' WHERE serial_code='".$data[serial_code]."'";
		$result2		= mysqli_query($my_db, $query2);

		return $data['serial_code'];
	}

	// LMS 발송 
	function send_lms($phone)
	{
		global $_gl;
		global $my_db;

		$s_url		= "http://www.belif-factory.com/MOBILE/coupon_page.belif?mid=".$phone;
		$httpmethod = "POST";
		$url = "http://api.openapi.io/ppurio/1/message/lms/minivertising";
		$clientKey = "MTAyMC0xMzg3MzUwNzE3NTQ3LWNlMTU4OTRiLTc4MGItNDQ4MS05NTg5LTRiNzgwYjM0ODEyYw==";
		$contentType = "Content-Type: application/x-www-form-urlencoded";

		$response = sendRequest($httpmethod, $url, $clientKey, $contentType, $phone, $s_url);

		$json_data = json_decode($response, true);

		/*
		받아온 결과값을 DB에 저장 및 Variation
		*/
		$query3 = "INSERT INTO sms_info(send_phone, send_status, cmid, send_regdate) values('".$phone."','".$json_data['result_code']."','".$json_data['cmid']."','".date("Y-m-d H:i:s")."')";
		$result3 		= mysqli_query($my_db, $query3);

		$query2 = "UPDATE member_info SET mb_lms='Y' WHERE mb_phone='".$phone."'";
		$result2 		= mysqli_query($my_db, $query2);

		if ($json_data['result_code'] == "200")
			$flag = "Y";
		else
			$flag = "N";

		return $flag;
	}

	function sendRequest($httpMethod, $url, $clientKey, $contentType, $phone, $s_url) {

		//create basic authentication header
		$headerValue = $clientKey;
		$headers = array("x-waple-authorization:" . $headerValue);

		$params = array(
			'send_time' => '', 
			'send_phone' => '07048881164', 
			'dest_phone' => $phone, 
			//'dest_phone' => '01099017644',
			'send_name' => '', 
			'dest_name' => '', 
			'subject' => '(광고)빌리프 수분 폭탄 공장에서 알림',
			'msg_body' => "
축하드려요 빌리를 찾으셨군요!
찾아 주신 보답으로 촉촉 화사함 가득한 수분 폭탄 쿠션 미니어처 쿠폰을 드립니다.
선택하신 빌리프 매장에 방문해 아래의 링크를 보여주시면 수분 폭탄 쿠션 미니어처를 선물로 드려요!

수분 폭탄 쿠션 미니어처 쿠폰(개인별 당첨된 경품과 URL로 전달)
".$s_url."
 
▶ 쿠폰 사용 기간
2016년 1월 11일~1월 31일
 
▶ 유의 사항
타 행사와 중복 적용이 불가하며 매장에 따라 조기 소진될 수 있습니다.
(1인 1회, 중복 불가)
 
▶문의처
쿠폰 관련 : 02-532-2475 (평일 10~18시)
매장 관련 : 080-023-7007 (평일 10~18시)
"
		);

		//curl initialization
		$curl = curl_init();

		//create request url
		//$url = $url."?".$parameters;

		curl_setopt ($curl, CURLOPT_URL , $url);
		curl_setopt ($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt ($curl, CURLOPT_HTTPHEADER, $headers);
		curl_setopt ($curl, CURLINFO_HEADER_OUT, true);
		curl_setopt ($curl, CURLOPT_SSL_VERIFYPEER, false);

		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
		curl_setopt($curl, CURLOPT_TIMEOUT, 30);

		$response = curl_exec($curl);

		$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$responseHeaders = curl_getinfo($curl, CURLINFO_HEADER_OUT);


		curl_close($curl);

		return $response;
	}

?>