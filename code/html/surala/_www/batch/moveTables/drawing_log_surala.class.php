<?php
require_once('/data/bat/moveTables/baseTable.class.php');

class drawing_log_surala extends baseTable {

	public function getTableName() {
		return "drawing_log_surala";
	}

	/**
	 * 移動元のデータを抽出（evacデータは別クラスで処理します：AUTOINCRIMENTが無い為）
	 */
	public function getSQL() {
		$sql = "";
		$sql .= " SELECT ".$this->getTableName().".* FROM ".$this->getTableName()." ".$this->getTableName()." ";
		$sql .= " INNER JOIN mv_3_student s ON ".$this->getTableName().".student_id = s.student_id";
		$sql .= " WHERE ".$this->getTableName().".move_flg = 0";

		return $sql;
	}

	/**
	 * 移動先のデータを消す（念のため）
	 */
	public function getDeleteSQL() {
		$del_list = array();
		$sql = "";
		$sql .= " DELETE t FROM ".$this->getTableName()." t ";
		$sql .= " INNER JOIN mv_3_student m ON m.student_id = t.student_id ";
		$sql .= " WHERE t.move_flg = 0";
		$del_list[] = $sql;

		return $del_list;
	}

	/**
	 * 移動元のデータに削除フラグを立てる
	 */
	public function deleteData($db_moto) {
		$sql  = " UPDATE ".$this->getTableName()." t ";
		$sql .= " INNER JOIN mv_3_student m ON m.student_id = t.student_id ";
		$sql .= " SET  ";
		$sql .= "  t.mk_flg = '1' ";
		$sql .= " ,t.mk_tts_id = 'mvent' ";
		$sql .= " ,t.mk_date = now() ";
		$sql .= " WHERE t.move_flg = 0";
		$sql .= ";";
		$db_moto->exec_query($sql);
	}

	// 管理番号を置き換える必要あり
	public function getSelectInsertSQL() {
		$sql  = "insert into ".$this->getTableName();
		$sql .= " select ";
		$sql .= "  ifnull(mv7.new_study_record_num, t.study_record_num) as study_record_num";
		$sql .= " ,ifnull(mv8.new_drawing_log_id, t.drawing_log_id) as drawing_log_id";
		$sql .= " ,student_id";
		$sql .= " ,course_num";
		$sql .= " ,stage_num";
		$sql .= " ,lesson_num";
		$sql .= " ,unit_num";
		$sql .= " ,block_num";
		$sql .= " ,problem_num";
		$sql .= " ,review";
		$sql .= " ,again";
		$sql .= " ,regist_time";
		$sql .= " ,move_flg";
		$sql .= " ,move_tts_id";
		$sql .= " ,move_date";
		$sql .= " ,mk_flg";
		$sql .= " ,mk_tts_id";
		$sql .= " ,mk_date";
		$sql .= " ,upd_syr_id";
		$sql .= " ,upd_tts_id";
		$sql .= " ,upd_date";
		$sql .= " ,ins_syr_id";
		$sql .= " ,ins_tts_id";
		$sql .= " ,ins_date";
		$sql .= " ,sys_bko";
		$sql .= "  from MV_".$this->getTableName(). " t ";
		$sql .= "  left join mv_7_study_record mv7 on mv7.study_record_num = t.study_record_num";
		$sql .= "  left join mv_8_drawing_log mv8 on mv8.drawing_log_id = t.drawing_log_id";

		return $sql;
	}

}
