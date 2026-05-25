<?php 
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// 1. 전송된 폼 데이터 안전하게 가져오기
$name    = isset($_REQUEST['wr_name']) ? clean_xss_tags($_REQUEST['wr_name']) : '미입력';
$email   = isset($_POST['wr_1']) ? clean_xss_tags($_POST['wr_1']) : '';
$phone   = isset($_POST['wr_2']) ? clean_xss_tags($_POST['wr_2']) : '미입력';
$content = isset($_POST['wr_content']) ? nl2br(clean_xss_tags($_POST['wr_content'])) : '';
$subject = isset($_POST['wr_subject']) ? clean_xss_tags($_POST['wr_subject']) : '[BOSON BIOSCIENCE] 새로운 제보가 등록되었습니다.';

if ($w == '') {
	sql_query("
		update {$write_table}
		set wr_10 = '제보 접수',wr_name='".$name."',wr_password='".get_encrypt_string($_REQUEST['wr_password'])."',wr_option='html1,secret'
		where wr_id = '{$wr_id}'
	");
}

if(!$is_admin){

    include_once(G5_LIB_PATH.'/mailer.lib.php');




    // 2. 메일 발송 정보 설정
    $fname    = $name;
	
    // 발송자 이메일이 없을 경우 최고관리자 메일로 대체하여 발송 오류 방지
    $fmail    = $config['cf_1']; 
   
	// 수신자: 환경설정에 등록된 관리자 메일 (구글 메일)
    $to_email = $config['cf_admin_email']; 

    // 3. 메일 본문(HTML) 템플릿 작성
    $html_content = '
    <div style="margin:20px; padding:20px; border:1px solid #ddd; background:#f9f9f9; max-width:600px;">
        <h2 style="color:#333; font-size:18px; margin-bottom:20px;">새로운 제보하기가 접수되었습니다.</h2>
        <ul style="list-style:none; padding:0; margin:0; line-height:1.8; color:#555;">
            <li style="margin:0;"><strong>이름 :</strong> '.$name.'</li>
            <li style="margin:0;"><strong>연락처 :</strong> '.$phone.'</li>
            <li style="margin:0;"><strong>이메일 :</strong> '.$email.'</li>
            <li style="margin:0;"><strong>제목 :</strong> '.$subject.'</li>
        </ul>
        <hr style="border:0; border-top:1px solid #ccc; margin:20px 0;">
        <p style="font-weight:bold; color:#333;">문의 내용 :</p>
        <div style="color:#555; line-height:1.6;">'.$content.'</div>
    </div>
    ';

	// 4. g5_file 테이블에서 첨부파일 정보 읽어오기
    $attach_files = array();
    
    // $bo_table과 $wr_id 변수가 세팅되어 있는지 확인 (글쓰기 완료 후 실행되는 시점 기준)
    if (isset($bo_table) && isset($wr_id)) {
        $sql = " select bf_file, bf_source from {$g5['board_file_table']} where bo_table = '{$bo_table}' and wr_id = '{$wr_id}' order by bf_no asc ";
        $result = sql_query($sql);

        
        
        while ($row = sql_fetch_array($result)) {
            // 실제 서버에 저장된 파일 경로
            $file_path = G5_DATA_PATH.'/file/'.$bo_table.'/'.$row['bf_file'];
            
            // 물리적으로 파일이 존재하는지 확인 후 배열에 담기
            if (is_file($file_path)) {
                $attach_files[] = array(
                    'path' => $file_path,
                    'name' => $row['bf_source']
                );
            }

        }
    }

    // 5. 메일 발송 (7번째 파라미터에 $attach_files 배열 추가)
    mailer($fname, $fmail, $to_email, $subject, $html_content, 1, $attach_files);

	//echo "$fname, $fmail, $to_email, $subject, $html_content";
	//exit();

	alert('접수되었습니다.', G5_URL.'/bbs/write.php?bo_table=inquiry');

    // 보통 G5_URL(메인)으로 이동
}
?>