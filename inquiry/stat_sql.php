<?php
include_once('../../../common.php'); // 경로 중요!

// 관리자 체크 (필수)
if (!$is_admin) {
    alert('관리자만 수정 가능합니다.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['wr_10'])) {

    $wr_id = (int)$_POST['wr_id'];
    $bo_table = $_POST['bo_table'];
    $wr_10 = sql_real_escape_string($_POST['wr_10']);

    sql_query("
        UPDATE {$g5['write_prefix']}{$bo_table}
        SET wr_10 = '{$wr_10}'
        WHERE wr_id = '{$wr_id}'
    ");

    // 수정 후 해당 글로 이동
    goto_url(get_pretty_url($bo_table, $wr_id));
}