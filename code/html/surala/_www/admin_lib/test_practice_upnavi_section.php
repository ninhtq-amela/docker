<?
/**
 * ベンチャー・リンク　すらら
 *
 * テスト用プラクティス管理　学力Upナビ子単元作成
 *
 * 履歴
 * 2012/06/08 初期設定
 *
 * @author Azet
 */

//	add koike


/**
 * HTMLを作成する機能
 *
 * AC:[A]管理者 UC1:[L07]テストを受ける UC2:[3]学力診断テスト.
 *
 * @author Azet
 * @return string HTML
 */
function start() {

	if (ACTION == "check") {
		$ERROR = check();
	}

	if (!$ERROR) {
		if (ACTION == "add") { $ERROR = add(); }
		elseif (ACTION == "change") { $ERROR = change(); }
		elseif (ACTION == "del") { $ERROR = change(); }
		elseif (ACTION == "↑") { $ERROR = up(); }
		elseif (ACTION == "↓") { $ERROR = down(); }
		elseif (ACTION == "export") { $ERROR = csv_export(); }				// 2012/07/03 add oda
		elseif (ACTION == "import") { list($html_,$ERROR) = csv_import(); }	// 2012/07/03 add oda
	}

	list($html,$L_COURSE,$L_STAGE) = select_course($ERROR);
	$html = $html_ . $html;													// 2012/07/03 add oda

	if (MODE == "add") {
		if (ACTION == "check") {
			if (!$ERROR) { $html .= check_html($L_COURSE,$L_STAGE); }
			else { $html .= addform($ERROR,$L_COURSE,$L_STAGE); }
		} elseif (ACTION == "add") {
			if (!$ERROR) { $html .= lesson_list($L_COURSE,$L_STAGE); }
			else { $html .= addform($ERROR,$L_COURSE,$L_STAGE); }
		} else {
			$html .= addform($ERROR,$L_COURSE,$L_STAGE);
		}
	} elseif (MODE == "詳細") {
		if (ACTION == "check") {
			if (!$ERROR) { $html .= check_html($L_COURSE,$L_STAGE); }
			else { $html .= viewform($ERROR,$L_COURSE,$L_STAGE); }
		} elseif (ACTION == "change") {
			if (!$ERROR) { $html .= lesson_list($L_COURSE,$L_STAGE); }
			else { $html .= viewform($ERROR,$L_COURSE,$L_STAGE); }
		} else {
			$html .= viewform($ERROR,$L_COURSE,$L_STAGE);
		}
	} elseif (MODE == "削除") {
		if (ACTION == "check") {
			if (!$ERROR) { $html .= check_html($L_COURSE,$L_STAGE); }
			else { $html .= viewform($ERROR,$L_COURSE,$L_STAGE); }
		} elseif (ACTION == "change") {
			if (!$ERROR) { $html .= lesson_list($L_COURSE,$L_STAGE); }
			else { $html .= viewform($ERROR,$L_COURSE,$L_STAGE); }
		} else {
			$html .= check_html($L_COURSE,$L_STAGE);
		}
	} else {
		if ($_POST[course_num]&&$_POST[upnavi_chapter_num]) {
			$html .= lesson_list($L_COURSE,$L_STAGE);
		}
	}

	return $html;
}


/**
 * コース選択
 *
 * AC:[A]管理者 UC1:[L07]テストを受ける UC2:[3]学力診断テスト.
 *
 * @author Azet
 * @param array $ERROR
 * @return string HTML
 */
function select_course($ERROR) {

	global $L_WRITE_TYPE;	//	add ookawara 2012/07/29
	//	add 2015/01/07 yoshizawa 課題要望一覧No.400対応
	global $L_EXP_CHA_CODE;
	//-------------------------------------------------

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	// 2012/07/03 add start oda
	$html = "";
	if(ACTION != "check"){	// add hasegawa 2015/11/17
		if ($ERROR) {
			for ($i = 0; $i < count($ERROR);$i++) {
				$html .= $ERROR[$i];
			}
		}
	}	// add hasegawa 2015/11/17

	// 2012/07/03 add end oda

	//コース
	if (!$_POST['course_num']) { $selected = "selected"; } else { $selected = ""; }
	$couse_num_html = "<option value=\"\" ". $selected.">選択して下さい</option>\n";
	foreach ($L_WRITE_TYPE AS $course_num_ => $course_name_) {
		if ($course_name_ == "") {
			continue;
		}
		$L_COURSE[$course_num_] = $course_name_;
		$selected = "";
		if ($_POST['course_num'] == $course_num_) {
			$selected = "selected";
		}
		$couse_num_html .= "<option value=\"".$course_num_."\" ".$selected.">".$course_name_."</option>\n";
	}

	if ($_POST[course_num]) {
		$sql  = "SELECT * FROM ".T_UPNAVI_CHAPTER.
				" WHERE course_num='$_POST[course_num]' AND mk_flg!='1' ORDER BY list_num;";
		if ($result = $cdb->query($sql)) {
			$max = $cdb->num_rows($result);
		}
		if (!$max) {
			$upnavi_chapter_num_html .= "<option value=\"\">--------</option>\n";
		} else {
			if (!$_POST['course_num']) { $selected = "selected"; } else { $selected = ""; }
			$upnavi_chapter_num_html .= "<option value=\"\" $selected>選択して下さい</option>\n";
			while ($list = $cdb->fetch_assoc($result)) {
				$L_STAGE[$list[upnavi_chapter_num]] = $list[upnavi_chapter_name];
				if ($_POST[upnavi_chapter_num] == $list[upnavi_chapter_num]) { $selected = "selected"; } else { $selected = ""; }
				$upnavi_chapter_num_html .= "<option value=\"{$list[upnavi_chapter_num]}\" $selected>{$list[upnavi_chapter_name]}</option>\n";
			}
		}
	} else {
		$upnavi_chapter_num_html .= "<option value=\"\">--------</option>\n";
	}

			$html .= "<br>\n";
			$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\" name=\"menu\">\n";
			$html .= "<input type=\"hidden\" name=\"mode\" value=\"set_course\">\n";
			$html .= "<table class=\"unit_form\">\n";
			$html .= "<tr class=\"unit_form_menu\">\n";
			$html .= "<td>コース</td>\n";
			$html .= "<td>親単元</td>\n";
			$html .= "</tr>\n";
			$html .= "<tr class=\"unit_form_cell\">\n";
			$html .= "<td><select name=\"course_num\" onchange=\"submit_course();\">\n";
			$html .= $couse_num_html;
			$html .= "</select></td>\n";
			$html .= "<td><select name=\"upnavi_chapter_num\" onchange=\"submit();\">\n";
			$html .= $upnavi_chapter_num_html;
			$html .= "</select></td>\n";
			$html .= "</tr>\n";
			$html .= "</table>\n";
			$html .= "</form>\n";

	if (!$_POST[course_num]) {
		$html .= "<br>\n";
		$html .= "子単元を設定するコースを選択してください。<br>\n";
	} elseif ($_POST[course_num]&&!$_POST[upnavi_chapter_num]) {
		$html .= "<br>\n";
		$html .= "子単元を設定する親単元を選択してください。<br>\n";
		// 2012/07/10 add start oda
		$html .= "インポートする場合は、csvファイル（S-JIS）を指定しCSVインポートボタンを押してください。<br>\n";
		$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" enctype=\"multipart/form-data\">\n";
		$html .= "<input type=\"hidden\" name=\"action\" value=\"import\">\n";
		$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
		$html .= "<input type=\"hidden\" name=\"upnavi_chapter_num\" value=\"".$_POST['upnavi_chapter_num']."\">\n";
		$html .= "<input type=\"file\" size=\"40\" name=\"import_file\"><br>\n";
		$html .= "<input type=\"submit\" value=\"CSVインポート\" style=\"float:left;\">\n";
		$html .= "</form>\n";
		$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
		$html .= "<input type=\"hidden\" name=\"action\" value=\"export\">\n";
		$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
		$html .= "<input type=\"hidden\" name=\"upnavi_chapter_num\" value=\"".$_POST['upnavi_chapter_num']."\">\n";
		//	add 2015/01/07 yoshizawa 課題要望一覧No.400対応
		//	プルダウンを作成
		$expList = "";
		if ( is_array($L_EXP_CHA_CODE) ) {
			$expList .= "<br /><br />\n";
			$expList .= "海外版の場合は、出力形式について[Unicode]選択して、CSVエクスポートボタンをクリックしてください。<br />\n";
			$expList .= "<b>出力形式：</b>";
			$expList .= "<select name=\"exp_list\">";
			foreach( $L_EXP_CHA_CODE as $key => $val ){
				$expList .= "<option value=\"".$key."\">".$val."</option>";
			}
			$expList .= "</select>";
			$html .= $expList;
		}
		//-------------------------------------------------
		$html .= "<input type=\"submit\" value=\"CSVエクスポート\">\n";
		$html .= "</form>\n";
		// 2012/07/10 add end oda
	}

	return array($html,$L_COURSE,$L_STAGE);
}


/**
 * レッスン一覧
 *
 * AC:[A]管理者 UC1:[L07]テストを受ける UC2:[3]学力診断テスト.
 *
 * @author Azet
 * @param array $L_COURSE
 * @param array $L_STAGE
 * @return string HTML
 */
function lesson_list($L_COURSE,$L_STAGE) {

	global $L_DISPLAY;
	//	add 2015/01/07 yoshizawa 課題要望一覧No.400対応
	global $L_EXP_CHA_CODE;
	//-------------------------------------------------
	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	//list($me_num,$onetime,$manager_level,$belong_num,$authority) = explode("<>",$_SESSION[myid]);
	if ($authority) { $L_AUTHORITY = explode("::",$authority); }

	if (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__add",$_SESSION['authority'])===FALSE)) {
		// 2012/07/03 add start oda
		$html .= "<br>\n";
		$html .= "インポートする場合は、csvファイル（S-JIS）を指定しCSVインポートボタンを押してください。<br>\n";
		$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" enctype=\"multipart/form-data\">\n";
		$html .= "<input type=\"hidden\" name=\"action\" value=\"import\">\n";
		$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
		$html .= "<input type=\"hidden\" name=\"upnavi_chapter_num\" value=\"".$_POST['upnavi_chapter_num']."\">\n";
		$html .= "<input type=\"file\" size=\"40\" name=\"import_file\"><br>\n";
		$html .= "<input type=\"submit\" value=\"CSVインポート\" style=\"float:left;\">\n";
		$html .= "</form>\n";
		$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
		$html .= "<input type=\"hidden\" name=\"action\" value=\"export\">\n";
		$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
		$html .= "<input type=\"hidden\" name=\"upnavi_chapter_num\" value=\"".$_POST['upnavi_chapter_num']."\">\n";
		//	add 2015/01/07 yoshizawa 課題要望一覧No.400対応
		//	プルダウンを作成
		$expList = "";
		if ( is_array($L_EXP_CHA_CODE) ) {
			$expList .= "<br /><br />\n";
			$expList .= "海外版の場合は、出力形式について[Unicode]選択して、CSVエクスポートボタンをクリックしてください。<br />\n";
			$expList .= "<b>出力形式：</b>";
			$expList .= "<select name=\"exp_list\">";
			foreach( $L_EXP_CHA_CODE as $key => $val ){
				$expList .= "<option value=\"".$key."\">".$val."</option>";
			}
			$expList .= "</select>";
			$html .= $expList;
		}
		//-------------------------------------------------
		$html .= "<input type=\"submit\" value=\"CSVエクスポート\">\n";
		$html .= "</form>\n";
		// 2012/07/03 add end oda
		$html .= "<br>\n";
		$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"add\">\n";
		$html .= "<input type=\"hidden\" name=\"course_num\" value=\"$_POST[course_num]\">\n";
		$html .= "<input type=\"hidden\" name=\"upnavi_chapter_num\" value=\"$_POST[upnavi_chapter_num]\">\n";
		$html .= "<input type=\"submit\" value=\"子単元新規登録\">\n";
		$html .= "</form>\n";
	}

	$sql  = "SELECT * FROM ".T_UPNAVI_SECTION.
			" WHERE mk_flg!='1' AND upnavi_chapter_num='$_POST[upnavi_chapter_num]' ORDER BY list_num;";
	if ($result = $cdb->query($sql)) {
		$max = $cdb->num_rows($result);
	}
	if (!$max) {
		$html .= "<br>\n";
		$html .= "今現在登録されている子単元は有りません。<br>\n";
		return $html;
	}
	$html .= "<br>\n";
	$html .= "<table class=\"stage_form\">\n";
	$html .= "<tr class=\"stage_form_menu\">\n";
	if (!ereg("practice__view",$authority)
		&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__sort",$_SESSION['authority'])===FALSE))
	) {
		$html .= "<th>↑</th>\n";
		$html .= "<th>↓</th>\n";
	}
	$html .= "<th>登録番号</th>\n";
	$html .= "<th>コース名</th>\n";
	$html .= "<th>親単元名</th>\n";
	$html .= "<th>子単元名</th>\n";
	$html .= "<th>表示・非表示</th>\n";
	if (!ereg("practice__view",$authority)
		&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__view",$_SESSION['authority'])===FALSE))
	) {
		$html .= "<th>詳細</th>\n";
	}
	if (!ereg("practice__del",$authority)
		&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__del",$_SESSION['authority'])===FALSE))
	) {
		$html .= "<th>削除</th>\n";
	}
	$html .= "</tr>\n";

	$i = 1;
	while ($list = $cdb->fetch_assoc($result)) {

		$upnavi_chapter_num = $list['upnavi_chapter_num'];
		$list_num = $list['list_num'];
		$LINE[$list_num] = $upnavi_chapter_num;
		foreach ($list AS $KEY => $VAL) {
			$$KEY = $VAL;
		}

		$up_submit = $down_submit = "&nbsp;";
		if ($i != 1) { $up_submit = "<input type=\"submit\" name=\"action\" value=\"↑\">\n"; }
		if ($i != $max) { $down_submit = "<input type=\"submit\" name=\"action\" value=\"↓\">\n"; }

		$html .= "<tr class=\"stage_form_cell\">\n";
		$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
		$html .= "<input type=\"hidden\" name=\"course_num\" value=\"{$_POST[course_num]}\">\n";
		$html .= "<input type=\"hidden\" name=\"upnavi_chapter_num\" value=\"{$upnavi_chapter_num}\">\n";
		$html .= "<input type=\"hidden\" name=\"upnavi_section_num\" value=\"{$upnavi_section_num}\">\n";
		if (!ereg("practice__view",$authority)
			&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__sort",$_SESSION['authority'])===FALSE))
		) {
			$html .= "<td>{$up_submit}</td>\n";
			$html .= "<td>{$down_submit}</td>\n";
		}
		$html .= "<td>{$upnavi_section_num}</td>\n";
		$html .= "<td>{$L_COURSE[$_POST[course_num]]}</td>\n";
		$html .= "<td>{$L_STAGE[$upnavi_chapter_num]}</td>\n";
		$html .= "<td>{$upnavi_section_name}</td>\n";
		$html .= "<td>{$L_DISPLAY[$display]}</td>\n";
		if (!ereg("practice__view",$authority)
			&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__view",$_SESSION['authority'])===FALSE))
		) {
			$html .= "<td><input type=\"submit\" name=\"mode\" value=\"詳細\"></td>\n";
		}
		if (!ereg("practice__del",$authority)
			&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__del",$_SESSION['authority'])===FALSE))
		) {
			$html .= "<td><input type=\"submit\" name=\"mode\" value=\"削除\"></td>\n";
		}
		$html .= "</form>\n";
		$html .= "</tr>\n";
		++$i;
	}
	$html .= "</table>\n";
	return $html;
}


/**
 * 新規登録フォーム
 *
 * AC:[A]管理者 UC1:[L07]テストを受ける UC2:[3]学力診断テスト.
 *
 * @author Azet
 * @param array $ERROR
 * @param array $L_COURSE
 * @param array $L_STAGE
 * @return string HTML
 */
function addform($ERROR,$L_COURSE,$L_STAGE) {

	$cdb = $GLOBALS['cdb'];

	global $L_WRITE_TYPE,$L_DISPLAY;

	//	コース名
	$sql  = "SELECT * FROM ".T_COURSE.
			" WHERE course_num='$_POST[course_num]' LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$course_name_ = $list['course_name'];
	}

	$html .= "<br>\n";
	$html .= "新規登録フォーム<br>\n";

	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}

	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"check\">\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"$_POST[course_num]\">\n";
	$html .= "<input type=\"hidden\" name=\"upnavi_chapter_num\" value=\"$_POST[upnavi_chapter_num]\">\n";
	//	フォーム表示テンプレート読み込み
	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(TEST_PRACTICE_UPNAVI_SECTION);

//	$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	$INPUTS[SECTIONNUM] = array('result'=>'plane','value'=>"---");
	$INPUTS[COURSENAME] = array('result'=>'plane','value'=>$L_COURSE[$_POST[course_num]]);
	$INPUTS[CHAPTERNAME] = array('result'=>'plane','value'=>$L_STAGE[$_POST[upnavi_chapter_num]]);
	$INPUTS[SECTION] = array('type'=>'text','name'=>'upnavi_section_name','value'=>$_POST['upnavi_section_name']);
	$INPUTS[UNITNUM] = array('type'=>'text','name'=>'unit_num','value'=>$_POST['unit_num']);							// 2012/07/04 add oda
	$INPUTS[REMARKS] = array('type'=>'textarea','name'=>'remarks','cols'=>'50','rows'=>'5','value'=>$_POST['remarks']);

	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("display");
	$newform->set_form_id("display");
	$newform->set_form_check($_POST['display']);
	$newform->set_form_value('1');
	$display = $newform->make();
	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("display");
	$newform->set_form_id("undisplay");
	$newform->set_form_check($_POST['display']);
	$newform->set_form_value('2');
	$undisplay = $newform->make();
	$display = $display . "<label for=\"display\">{$L_DISPLAY[1]}</label> " . $undisplay . "<label for=\"undisplay\">{$L_DISPLAY[2]}</label>";
	$INPUTS[DISPLAY] = array('result'=>'plane','value'=>$display);

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();
	$html .= "<input type=\"submit\" value=\"追加確認\">\n";
	$html .= "<input type=\"reset\" value=\"クリア\">\n";
	$html .= "</form>\n";
	$html .= "<br>\n";
	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"lesson_list\">\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"$_POST[course_num]\">\n";
	$html .= "<input type=\"hidden\" name=\"upnavi_chapter_num\" value=\"$_POST[upnavi_chapter_num]\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";
	return $html;
}


/**
 * 表示フォーム
 *
 * AC:[A]管理者 UC1:[L07]テストを受ける UC2:[3]学力診断テスト.
 *
 * @author Azet
 * @param array $ERROR
 * @param array $L_COURSE
 * @param array $L_STAGE
 * @return string HTML
 */
function viewform($ERROR,$L_COURSE,$L_STAGE) {

	global $L_WRITE_TYPE,$L_DISPLAY;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$action = ACTION;

	if ($action) {
		foreach ($_POST as $key => $val) { $$key = $val; }
	} else {
		$sql = "SELECT * FROM ".T_UPNAVI_SECTION.
			" WHERE upnavi_section_num='$_POST[upnavi_section_num]' AND mk_flg!='1' LIMIT 1;";
		$result = $cdb->query($sql);
		$list = $cdb->fetch_assoc($result);
		if (!$list) {
			$html .= "既に削除されているか、不正な情報が混ざっています。<br>$sql";
			return $html;
		}
		foreach ($list as $key => $val) {
			$$key = replace_decode($val);
		}
	}

	$html = "<br>\n";
	$html .= "詳細画面<br>\n";

	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}

	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"check\">\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"$_POST[course_num]\">\n";
	$html .= "<input type=\"hidden\" name=\"upnavi_chapter_num\" value=\"$_POST[upnavi_chapter_num]\">\n";
	$html .= "<input type=\"hidden\" name=\"upnavi_section_num\" value=\"$upnavi_section_num\">\n";
	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(TEST_PRACTICE_UPNAVI_SECTION);

	if (!$upnavi_section_num) { $upnavi_section_num = "---"; }
	$INPUTS[SECTIONNUM] = array('result'=>'plane','value'=>$upnavi_section_num);
	$INPUTS[COURSENAME] = array('result'=>'plane','value'=>$L_COURSE[$_POST[course_num]]);
	$INPUTS[CHAPTERNAME] = array('result'=>'plane','value'=>$L_STAGE[$_POST[upnavi_chapter_num]]);
	$INPUTS[SECTION] = array('type'=>'text','name'=>'upnavi_section_name','value'=>$upnavi_section_name);
	$INPUTS[UNITNUM] = array('type'=>'text','name'=>'unit_num','value'=>$unit_num);						// 2012/07/04 add oda


	$remarks = str_replace("&lt;","<",$remarks);
	$remarks = str_replace("&gt;",">",$remarks);
	$INPUTS[REMARKS] = array('type'=>'textarea','name'=>'remarks','cols'=>'50','rows'=>'5','value'=>$remarks);

	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("display");
	$newform->set_form_id("display");
	$newform->set_form_check($display);
	$newform->set_form_value('1');
	$male = $newform->make();
	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("display");
	$newform->set_form_id("undisplay");
	$newform->set_form_check($display);
	$newform->set_form_value('2');
	$female = $newform->make();
	$display = $male . "<label for=\"display\">{$L_DISPLAY[1]}</label> " . $female . "<label for=\"undisplay\">{$L_DISPLAY[2]}</label>";
	$INPUTS[DISPLAY] = array('result'=>'plane','value'=>$display);

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();
	$html .= "<input type=\"submit\" value=\"変更確認\">";
	$html .= "<input type=\"reset\" value=\"クリア\">";
	$html .= "</form>\n";
	$html .= "<br>\n";
	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"unit_list\">\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"$_POST[course_num]\">\n";
	$html .= "<input type=\"hidden\" name=\"upnavi_chapter_num\" value=\"$_POST[upnavi_chapter_num]\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";

	return $html;
}


/**
 * 確認する機能
 *
 * AC:[A]管理者 UC1:[L07]テストを受ける UC2:[3]学力診断テスト.
 *
 * @author Azet
 * @return array エラーの場合
 */
function check() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$mode = MODE;

	if (!$_POST['course_num']) { $ERROR[] = "登録する子単元のコース情報が確認できません。"; }
	if (!$_POST['upnavi_chapter_num']) { $ERROR[] = "登録する子単元の親単元情報が確認できません。"; }

	if (!$_POST['upnavi_section_name']) { $ERROR[] = "子単元名が未入力です。"; }
	elseif (!$ERROR) {
		if ($mode == "add") {
			$sql  = "SELECT * FROM ".T_UPNAVI_SECTION.
					" WHERE mk_flg!='1' AND upnavi_chapter_num='$_POST[upnavi_chapter_num]' AND upnavi_section_name='$_POST[upnavi_section_name]'";
		} else {
			$sql  = "SELECT * FROM ".T_UPNAVI_SECTION.
					" WHERE mk_flg!='1' AND upnavi_chapter_num='$_POST[upnavi_chapter_num]' AND upnavi_section_num!='$_POST[upnavi_section_num]'" .
					" AND upnavi_section_name='$_POST[upnavi_section_name]'";
		}
		if ($result = $cdb->query($sql)) {
			$count = $cdb->num_rows($result);
		}
		if ($count > 0) { $ERROR[] = "入力された子単元名は既に登録されております。"; }
	}
	if (mb_strlen($_POST['upnavi_section_name'], 'UTF-8') > 255) { $ERROR[] = "子単元が不正です。255文字以内で記述して下さい。"; }	//	add koike 2012/06/12
	// 2012/07/04 add start oda
	if ($_POST['unit_num'] && $_POST['course_num']) {

		// ユニット番号を分割
		$unit_list = explode("::",$_POST['unit_num']);
		$unit_count = count($unit_list);
		$unit_string = implode(",", $unit_list);

		$sql  = "SELECT * FROM ".T_UNIT.
				" WHERE state='0' ".
				"  AND course_num = '".$_POST['course_num']."'".
				"  AND unit_num IN (".$unit_string.");";
		if ($result = $cdb->query($sql)) {
			$count = $cdb->num_rows($result);
		}
		if ($count == 0) {
			$ERROR[] = "入力したユニット番号は存在しません。";
		} elseif ($count < $unit_count) {
			$ERROR[] = "入力したユニット番号の一部が存在しません。";
		}
	}
	// 2012/07/04 add end oda
	if (mb_strlen($_POST['remarks'], 'UTF-8') > 255) { $ERROR[] = "備考が不正です。255文字以内で記述して下さい。"; }	//	add koike 2012/06/12
	if (!$_POST['display']) { $ERROR[] = "表示・非表示が未選択です。"; }

	return $ERROR;
}


/**
 * 確認フォーム
 *
 * AC:[A]管理者 UC1:[L07]テストを受ける UC2:[3]学力診断テスト.
 *
 * @author Azet
 * @param array $L_COURSE
 * @param array $L_STAGE
 * @return string HTML
 */
function check_html($L_COURSE,$L_STAGE) {

	global $L_WRITE_TYPE,$L_DISPLAY;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$action = ACTION;

	if ($_POST) {
		foreach ($_POST as $key => $val) {
			if ($key == "action") {
				if (MODE == "add") { $val = "add"; }
				elseif (MODE == "詳細") { $val = "change"; }
			}
			$val = mb_convert_kana($val,"asKV","UTF-8");
			$HIDDEN .= "<input type=\"hidden\" name=\"$key\" value=\"$val\">\n";
		}
	}

	if ($action) {
		foreach ($_POST as $key => $val) {
			$val = mb_convert_kana($val,"asKV","UTF-8");
			$$key = $val;
		}
	} else {
		$HIDDEN .= "<input type=\"hidden\" name=\"action\" value=\"change\">\n";
		$sql = "SELECT * FROM ".T_UPNAVI_SECTION.
			" WHERE upnavi_section_num='$_POST[upnavi_section_num]' AND mk_flg!='1' LIMIT 1;";
		$result = $cdb->query($sql);
		$list = $cdb->fetch_assoc($result);
		if (!$list) {
			$html .= "既に削除されているか、不正な情報が混ざっています。";
			return $html;
		}
		foreach ($list as $key => $val) {
			$$key = replace_decode($val);
		}
	}

	if (MODE != "削除") { $button = "登録"; } else { $button = "削除"; }
	$html = "<br>\n";
	$html .= "確認画面：以下の内容で{$button}してもよろしければ{$button}ボタンをクリックしてください。<br>\n";

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(TEST_PRACTICE_UPNAVI_SECTION);

//	$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	if (!$upnavi_section_num) { $upnavi_section_num = "---"; }
	$INPUTS[SECTIONNUM] = array('result'=>'plane','value'=>$upnavi_section_num);
	$INPUTS[COURSENAME] = array('result'=>'plane','value'=>$L_COURSE[$_POST[course_num]]);
	$INPUTS[CHAPTERNAME] = array('result'=>'plane','value'=>$L_STAGE[$_POST[upnavi_chapter_num]]);
	$INPUTS[SECTION] = array('result'=>'plane','value'=>$upnavi_section_name);
	$INPUTS[UNITNUM] = array('result'=>'plane','value'=>$unit_num);			// 2012/07/04 add oda
	$remarks = str_replace("&lt;","<",$remarks);
	$remarks = str_replace("&gt;",">",$remarks);
	$INPUTS[REMARKS] = array('result'=>'plane','value'=>nl2br($remarks));
	$INPUTS[DISPLAY] = array('result'=>'plane','value'=>$L_DISPLAY[$display]);

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();
	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\" style=\"float:left\">\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"$course_num\">\n";
	$html .= $HIDDEN;
	$html .= "<input type=\"submit\" value=\"$button\">\n";
	$html .= "</form>";
	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";

	if ($action) {
		$HIDDEN2 = explode("\n",$HIDDEN);
		foreach ($HIDDEN2 as $key => $val) {
			if (ereg("name=\"action\"",$val)) {
				$HIDDEN2[$key] = "<input type=\"hidden\" name=\"action\" value=\"back\">";
				break;
			}
		}
		$HIDDEN2 = implode("\n",$HIDDEN2);

		$html .= $HIDDEN2;
	} else {
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"unit_list\">\n";
	}
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"$course_num\">\n";
	$html .= "<input type=\"hidden\" name=\"upnavi_chapter_num\" value=\"$upnavi_chapter_num\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";

	return $html;
}


/**
 * DB新規登録
 *
 * AC:[A]管理者 UC1:[L07]テストを受ける UC2:[3]学力診断テスト.
 *
 * @author Azet
 * @return array エラーの場合
 */
function add() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$action = ACTION;

	//	単元登録データ
	foreach ($_POST as $key => $val) {
		if ($key == "action") { continue; }
		$INSERT_DATA[$key] = "$val";
	}

	$INSERT_DATA[ins_date] = "now()";
	$INSERT_DATA[ins_tts_id] 		= $_SESSION['myid']['id'];
	$INSERT_DATA[upd_date] = "now()";
	$INSERT_DATA[upd_tts_id] 		= $_SESSION['myid']['id'];

	$ERROR = $cdb->insert(T_UPNAVI_SECTION,$INSERT_DATA);

	if (!$ERROR) {
		$upnavi_section_num = $cdb->insert_id();
		$INSERT_DATA[list_num] = $upnavi_section_num;
		$where = " WHERE upnavi_section_num='$upnavi_section_num' LIMIT 1;";

		$ERROR = $cdb->update(T_UPNAVI_SECTION,$INSERT_DATA,$where);
	}

	if (!$ERROR) { $_SESSION[select_menu] = MAIN . "<>" . SUB . "<><><>"; }
	return $ERROR;
}


/**
 * DB更新・削除 処理
 *
 * AC:[A]管理者 UC1:[L07]テストを受ける UC2:[3]学力診断テスト.
 *
 * @author Azet
 * @return array エラーの場合
 */
function change() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$action = ACTION;
	$INSERT_DATA = array();

	if (MODE == "詳細") {

		$INSERT_DATA[upnavi_section_name] = $_POST[upnavi_section_name];
		$INSERT_DATA[unit_num] = $_POST[unit_num];							// 2012/07/04 add oda
		$INSERT_DATA[remarks] = $_POST[remarks];
		$INSERT_DATA[display] = $_POST[display];
		$INSERT_DATA[upd_syr_id] = "updateline";
		$INSERT_DATA[upd_date] = "now()";
		$INSERT_DATA[upd_tts_id] 		= $_SESSION['myid']['id'];
		$where = " WHERE upnavi_section_num='$_POST[upnavi_section_num]' LIMIT 1;";

		$ERROR = $cdb->update(T_UPNAVI_SECTION,$INSERT_DATA,$where);

	} elseif (MODE == "削除") {
		$INSERT_DATA[display] = 2;
		$INSERT_DATA[mk_flg] = 1;
		$INSERT_DATA[mk_tts_id] 		= $_SESSION['myid']['id'];
		$INSERT_DATA[mk_date] = "now()";
		$where = " WHERE upnavi_section_num='$_POST[upnavi_section_num]' LIMIT 1;";

		$ERROR = $cdb->update(T_UPNAVI_SECTION,$INSERT_DATA,$where);

		// 2012/07/05 add start oda
		// 子単元・問題の関連テーブルを削除
		$INSERT_DATA = array();
		$INSERT_DATA['mk_flg']    = 1;
		$INSERT_DATA['mk_tts_id'] = $_SESSION['myid']['id'];
		$INSERT_DATA['mk_date']   = "now()";
		$where = " WHERE upnavi_section_num = '".$_POST['upnavi_section_num']."';";

		$ERROR = $cdb->update(T_UPNAVI_SECTION_PROBLEM,$INSERT_DATA,$where);
		// 2012/07/05 add end oda
	}

	if (!$ERROR) { $_SESSION[select_menu] = MAIN . "<>" . SUB . "<><><>"; }
	return $ERROR;
}


/**
 * UPNAVI_SECTIONを上がる機能
 *
 * AC:[A]管理者 UC1:[L07]テストを受ける UC2:[3]学力診断テスト.
 *
 * @author Azet
 * @return array エラーの場合
 */
function up() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$action = ACTION;

	$sql  = "SELECT * FROM ".T_UPNAVI_SECTION.
			" WHERE upnavi_section_num='$_POST[upnavi_section_num]' LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$m_upnavi_section_num = $list[upnavi_section_num];
		$m_upnavi_chapter_num = $list[upnavi_chapter_num];
		$m_list_num = $list['list_num'];
	}
	if (!$m_upnavi_section_num || !$m_list_num) { $ERROR[] = "移動する子単元情報が取得できません。"; }
	if (!$ERROR) {
		$sql  = "SELECT * FROM ".T_UPNAVI_SECTION.
				" WHERE mk_flg!='1' AND upnavi_chapter_num='$m_upnavi_chapter_num' AND list_num<'$m_list_num'" .
				" ORDER BY list_num DESC LIMIT 1;";
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
			$c_upnavi_section_num = $list[upnavi_section_num];
			$c_list_num = $list['list_num'];
		}
	}
	if (!$c_upnavi_section_num || !$c_list_num) { $ERROR[] = "移動される親単元情報が取得できません。"; }

	if (!$ERROR) {
		$INSERT_DATA[list_num] = $c_list_num;
		$INSERT_DATA[upd_syr_id] = "upline";
		$INSERT_DATA[upd_date] = "now()";
		$INSERT_DATA[upd_tts_id] 		= $_SESSION['myid']['id'];
		$where = " WHERE upnavi_section_num='$m_upnavi_section_num' LIMIT 1;";

		$ERROR = $cdb->update(T_UPNAVI_SECTION,$INSERT_DATA,$where);
	}

	if (!$ERROR) {
		$INSERT_DATA[list_num] = $m_list_num;
		$INSERT_DATA[upd_syr_id] = "upline";
		$INSERT_DATA[upd_date] = "now()";
		$INSERT_DATA[upd_tts_id] 		= $_SESSION['myid']['id'];
		$where = " WHERE upnavi_section_num='$c_upnavi_section_num' LIMIT 1;";

		$ERROR = $cdb->update(T_UPNAVI_SECTION,$INSERT_DATA,$where);
	}

	return $ERROR;
}


/**
 * UPNAVI_SECTION を下がる機能
 *
 * AC:[A]管理者 UC1:[L07]テストを受ける UC2:[3]学力診断テスト.
 *
 * @author Azet
 * @return array エラーの場合
 */
function down() {

	$cdb = $GLOBALS['cdb'];

	$action = ACTION;

	$sql  = "SELECT * FROM ".T_UPNAVI_SECTION.
			" WHERE upnavi_section_num='$_POST[upnavi_section_num]' LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$m_upnavi_section_num = $list[upnavi_section_num];
		$m_upnavi_chapter_num = $list[upnavi_chapter_num];
		$m_list_num = $list['list_num'];
	}
	if (!$m_upnavi_chapter_num || !$m_list_num) { $ERROR[] = "移動する子単元情報が取得できません。"; }
	if (!$ERROR) {
		$sql  = "SELECT * FROM ".T_UPNAVI_SECTION.
				" WHERE mk_flg!='1' AND upnavi_chapter_num='$m_upnavi_chapter_num' AND list_num>'$m_list_num'" .
				" ORDER BY list_num LIMIT 1;";
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
			$c_upnavi_section_num = $list[upnavi_section_num];
			$c_list_num = $list['list_num'];
		}
	}
	if (!$c_upnavi_section_num || !$c_list_num) { $ERROR[] = "移動される子単元情報が取得できません。"; }
	if (!$ERROR) {
		$INSERT_DATA[list_num] = $c_list_num;
		$INSERT_DATA[upd_syr_id] = "downline";
		$INSERT_DATA[upd_date] = "now()";
		$INSERT_DATA[upd_tts_id] 		= $_SESSION['myid']['id'];
		$where = " WHERE upnavi_section_num='$m_upnavi_section_num' LIMIT 1;";

		$ERROR = $cdb->update(T_UPNAVI_SECTION,$INSERT_DATA,$where);
	}

	if (!$ERROR) {
		$INSERT_DATA[list_num] = $m_list_num;
		$INSERT_DATA[upd_syr_id] = "downline";
		$INSERT_DATA[upd_date] = "now()";
		$INSERT_DATA[upd_tts_id] 		= $_SESSION['myid']['id'];
		$where = " WHERE upnavi_section_num='$c_upnavi_section_num' LIMIT 1;";

		$ERROR = $cdb->update(T_UPNAVI_SECTION,$INSERT_DATA,$where);
	}
	return $ERROR;
}

// 2012/07/02 add start oda
/**
 * csvエクスポート
 *
 * AC:[A]管理者 UC1:[L07]テストを受ける UC2:[3]学力診断テスト.
 *
 * @author Azet
 */
function csv_export() {
	global $L_CSV_COLUMN;

	// ＣＳＶファイル生成
	list($csv_line,$ERROR) = make_csv($L_CSV_COLUMN['upnavi_section'],1,1);

	if ($ERROR) { return $ERROR; }

	// ファイル名生成
// update start 2012/07/10 oda
//	$filename = "ms_upnavi_section_".$_POST['course_num']."_".$_POST['upnavi_chapter_num'].".csv";
	$filename = "ms_upnavi_section_".$_POST['course_num'];
	if ($_POST['upnavi_chapter_num']) {
		$filename .= "_".$_POST['upnavi_chapter_num'];
	}
	$filename .= ".csv";
// update end 2012/07/10 oda

	// ヘッダ設定
	header("Cache-Control: public");
	header("Pragma: public");
	header("Content-disposition: attachment;filename=$filename");
	if (stristr($HTTP_USER_AGENT, "MSIE")) {
		header("Content-Type: text/octet-stream");
	} else {
		header("Content-Type: application/octet-stream;");
	}
	echo $csv_line;

	exit;
}

/**
 * csv出力情報整形
 *
 * AC:[A]管理者 UC1:[L07]テストを受ける UC2:[3]学力診断テスト.
 *
 * 先頭出力文字
 * head_mode	1 カラム名
 * 				2 コメント名
 * 出力範囲
 * csv_mode	1 マスタ単位
 * 			2 単元全て
 *
 * @author Azet
 * @param array $L_CSV_COLUMN
 * @param integer $head_mode='1'
 * @param integer $csv_mode='1'
 * @return array
 */
function make_csv($L_CSV_COLUMN,$head_mode='1',$csv_mode='1') {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	// 変数クリア
	$csv_line = "";

	if (!is_array($L_CSV_COLUMN)) {
		$ERROR[] = "<br>CSV抽出項目が設定されていません。";
		return array($csv_line,$ERROR);
	}
	//	head line (一行目)
	foreach ($L_CSV_COLUMN as $key => $val) {
		if ($head_mode == 1) {
			$head_name = $key;
		} elseif ($head_mode == 2) {
			$head_name = $val;
		}
		$csv_line .= "\"".$head_name."\",";
	}
	$csv_line .= "\n";

	// SQL生成
	$where = "";
	$L_EXPORT_LIST = array();
	if ($csv_mode == 1) {
		$where  = " AND course_num = '".$_POST['course_num']."'";
// update start 2012/07/10 oda
//		$where .= " AND upnavi_chapter_num = '".$_POST['upnavi_chapter_num']."'";
		if ($_POST['upnavi_chapter_num']) {
			$where .= " AND upnavi_chapter_num = '".$_POST['upnavi_chapter_num']."'";
		}
// update end 2012/07/10 oda
	}
	$sql  = "SELECT * FROM " . T_UPNAVI_SECTION .
			" WHERE mk_flg = '0' ".
			$where.
			" ORDER BY course_num, upnavi_chapter_num, list_num;";

	$i = 0;
	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {

			// 子単元名　文字列変換
			$upnavi_section_name = str_replace("\r\n", "", $list['upnavi_section_name']);
			$upnavi_section_name = str_replace("&lt;","<",$upnavi_section_name);
			$upnavi_section_name = str_replace("&gt;",">",$upnavi_section_name);
			$upnavi_section_name = str_replace("&#65374;","～",$upnavi_section_name);

			// 備考　文字列変換
			$remarks = str_replace("\r\n", "", $list['remarks']);
			$remarks = str_replace("&lt;","<",$remarks);
			$remarks = str_replace("&gt;",">",$remarks);
			$remarks = str_replace("&#65374;","～",$remarks);

			// 2012/07/04 add start oda
			$unit_num = "";
			if ($list['unit_num']) {
				$unit_num = $list['unit_num'];
			}
			// 2012/07/04 add start oda

			// CSVデータ生成
			$csv_line .= "\"".$list['upnavi_section_num']."\",";
			$csv_line .= "\"".$list['course_num']."\",";
			$csv_line .= "\"".$list['upnavi_chapter_num']."\",";
			$csv_line .= "\"".$list['list_num']."\",";
			$csv_line .= "\"".$upnavi_section_name."\",";
			$csv_line .= "\"".$unit_num."\",";			// 2012/07/04 add oda
			$csv_line .= "\"".$remarks."\",";
			$csv_line .= "\"".$list['display']."\",";
			$csv_line .= "\"".$list['mk_flg']."\",";
			$csv_line .= "\n";

			$i++;
		}
		$cdb->free_result($result);
	}
	// 文字コード変換
	//	del 2015/01/07 yoshizawa 課題要望一覧No.400対応 下に新規で作成
	//$csv_line = mb_convert_encoding($csv_line,"sjis-win","UTF-8");
	//$csv_line = replace_decode_sjis($csv_line);
	//----------------------------------------------------------------

	//	add 2015/01/07 yoshizawa 課題要望一覧No.400対応
		//++++++++++++++++++++++//
		//	$_POST['exp_list']	//
		//	1 => SJIS			//
		//	2 => Unicode		//
		//++++++++++++++++++++++//
	//	utf-8で出力
	if ( $_POST['exp_list'] == 2 ) {
		//	Unicode選択時には特殊文字のみ変換
		$csv_line = replace_decode($csv_line);

	//	SJISで出力
	} else {
		$csv_line = mb_convert_encoding($csv_line,"sjis-win","UTF-8");
		$csv_line = replace_decode_sjis($csv_line);

	}
	//-------------------------------------------------

	// データ件数チェック
	if ($i == 0) {
		$ERROR[] = "<br>対象データが存在しません。";
	}

	return array($csv_line,$ERROR);
}


/**
 * csvインポート
 *
 * AC:[A]管理者 UC1:[L07]テストを受ける UC2:[3]学力診断テスト.
 *
 * @author Azet
 * @return array
 */
function csv_import() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	// アップロードファイルチェック
	$file_name = $_FILES['import_file']['name'];
	$file_tmp_name = $_FILES['import_file']['tmp_name'];
	$file_error = $_FILES['import_file']['error'];

	if (!$file_tmp_name) {
		$ERROR[] = "<br>ファイルが指定されておりません。";
	} elseif (!eregi("(.csv)$",$file_name)) {
		$ERROR[] = "<br>ファイルの拡張子が不正です。";
	} elseif ($file_error == 1) {
		$ERROR[] = "<br>アップロードできるファイルの容量が設定範囲を超えてます。サーバー管理者へ相談してください。";
	} elseif ($file_error == 3) {
		$ERROR[] = "<br>ファイルの一部分のみしかアップロードされませんでした。";
	} elseif ($file_error == 4) {
		$ERROR[] = "<br>ファイルがアップロードされませんでした。";
	}

	if ($ERROR) {
		if ($file_tmp_name && file_exists($file_tmp_name)) { unlink($file_tmp_name); }
		return $ERROR;
	}

	$L_IMPORT_LINE = array();
	$ERROR =array();

	// 2015/10/09 oda
	// 注意）PHP5でfgetcsvを利用する場合、エクスポート側で、
	//       日本語を含む項目はダブルクォーテションで括る事！
	//       ダブルクォーテションで括らない場合、正しく取り込めません。

	//アップロードファイル読込
	$handle = fopen($file_tmp_name,"r");

	// add start hasegawa 2015/10/27
	// php5の不具合でfgetcsvで日本語が文字化けしてしまう。
	// setlocaleでロケールを設定して対応する。
	$line1 = "";
	$judgeCharacterCode = "";
	$judge_handle = fopen($file_tmp_name,"r");
	while(!feof($judge_handle)){
    	$line1 = fgets($judge_handle,1000);
		// １行目は無視する
		if ($j > 0) {
			// 1バイト文字のみの場合には”ASCII”と判定されます。
			$judgeCharacterCode = mb_detect_encoding($line1);
			if($judgeCharacterCode == 'SJIS'){
				setlocale(LC_ALL, 'ja_JP.SJIS');
				break;
			} else if($judgeCharacterCode == 'UTF-8') {
				setlocale(LC_ALL, 'ja_JP.UTF-8');
				break;
			}
		}
		$j++;
	}
	// add end hasegawa 2015/10/27

	$i = 0;
	while(!feof($handle)){
    	$str = fgetcsv($handle,10000);

    	if ($i == 0) {
			$L_LIST_NAME = $str;
		} else {
			$L_IMPORT_LINE[$i] = $str;
		}
		$i++;
	}

	//読込んだら一時ファイル破棄
	unlink($file_tmp_name);

	//２行目以降＝登録データを形成
	for ($i = 1; $i < count($L_IMPORT_LINE);$i++) {
		unset($L_VALUE);
		unset($CHECK_DATA);
		unset($INSERT_DATA);

		// 空行／カンマ区切り以外の行は読み飛ばし
		if (count($L_IMPORT_LINE[$i]) == 1) {
			$ERROR[] = "<br>".$i."行目のcsv入力値が不正なのでスキップしました。";
			continue;
		}

		// データ読み込み　→　配列格納
		foreach ($L_IMPORT_LINE[$i] as $key => $val) {
			if ($L_LIST_NAME[$key] === "") { continue; }
			$val = trim($val);
			$val = ereg_replace("\"","&quot;",$val);
			$val = str_replace("<","&lt;",$val);
			$val = str_replace(">","&gt;",$val);
			$val = str_replace("～","&#65374;",$val);
			//	del 2014/12/08 yoshizawa 課題要望一覧No.394対応 下に新規で作成
			//$val = replace_encode_sjis($val);
			//$val = mb_convert_encoding($val,"UTF-8","sjis-win");
			//----------------------------------------------------------------
			//	add 2014/12/08 yoshizawa 課題要望一覧No.394対応
			//	データの文字コードがUTF-8だったら変換処理をしない
			$code = judgeCharacterCode ( $val );
			if ( $code != 'UTF-8' ) {
				$val = replace_encode_sjis($val);
				$val = mb_convert_encoding($val,"UTF-8","sjis-win");
			}
			//	add 2015/01/09 yoshizawa 課題要望一覧No.400対応
			//	sjisファイルをインポートしても2バイト文字はutf-8で扱われる
			else {
				//	記号は特殊文字に変換します
				$val = replace_encode($val);

			}
			//--------------------------------------------------

			//カナ変換
			$val = mb_convert_kana($val,"asKVn","UTF-8");
			if ($val == "&quot;") { $val = ""; }
			$val = addslashes($val);
			$CHECK_DATA[$L_LIST_NAME[$key]] = $val;
		}

		//　既存レコード存在チェック
		$sql = " SELECT ".
				"   upnavi_section_num, course_num ".
				" FROM ". T_UPNAVI_SECTION .
				" WHERE upnavi_chapter_num = '".$CHECK_DATA['upnavi_chapter_num']."'".
				"  AND  upnavi_section_num = '".$CHECK_DATA['upnavi_section_num']."'".
				"  AND  mk_flg='0' LIMIT 1;";
//echo " sql = ".$sql."<br>";
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
		}

		// 処理モード設定
		if ($list['upnavi_section_num'] && $list['course_num'] == $CHECK_DATA['course_num']) {
			$ins_mode = "upd";
		} else {
			$ins_mode = "add";
		}
//echo " ins_mode = ".$ins_mode."<br>";

		// 表示・非表示が未設定の場合は、表示で設定
		if (!$CHECK_DATA['display']) { $CHECK_DATA['display'] = 1; }

		//データチェック　→　エラー行は、読み飛ばし
		$DATA_ERROR[$i] = check_data($CHECK_DATA,$ins_mode,$i);
		if ($DATA_ERROR[$i]) { continue; }

		// ＳＱＬ用配列格納
		$INSERT_DATA = $CHECK_DATA;

		// 登録処理
		if ($ins_mode == "add") {

			$INSERT_DATA['ins_syr_id'] 		= "upload";
			$INSERT_DATA['ins_tts_id'] 		= "System";
			$INSERT_DATA['ins_date'] 		= "now()";
			$INSERT_DATA['upd_syr_id'] 		= "upload";
			$INSERT_DATA['upd_tts_id'] 		= $_SESSION['myid']['id'];
			$INSERT_DATA['upd_date'] 		= "now()";

			unset($INSERT_DATA['upnavi_section_num']);
			$SYS_ERROR[$i] = $cdb->insert(T_UPNAVI_SECTION,$INSERT_DATA);

		// 更新処理
		} else {
			$INSERT_DATA['upd_syr_id'] 		= "upload";
			$INSERT_DATA['upd_tts_id'] 		= $_SESSION['myid']['id'];
			$INSERT_DATA['upd_date'] 		= "now()";
			// 2012/07/05 add start oda
			// 削除の時は、削除管理情報も更新
			if ($INSERT_DATA['mk_flg'] == 1) {
				$INSERT_DATA['display'] 		= "2";
				$INSERT_DATA['mk_tts_id'] 		= $_SESSION['myid']['id'];
				$INSERT_DATA['mk_date'] 		= "now()";
			}
			// 2012/07/05 add end oda

			$where = " WHERE upnavi_section_num = '".$CHECK_DATA['upnavi_section_num']."' LIMIT 1;";
			$SYS_ERROR[$i] = $cdb->update(T_UPNAVI_SECTION,$INSERT_DATA,$where);

			// 2012/07/05 add start oda
			if ($INSERT_DATA['mk_flg'] == 1) {
				// 子単元・問題の関連テーブルを削除
				$DELETE_DATA = array();
				$DELETE_DATA['mk_flg']    = 1;
				$DELETE_DATA['mk_tts_id'] = $_SESSION['myid']['id'];
				$DELETE_DATA['mk_date']   = "now()";
				$where = " WHERE upnavi_section_num = '".$CHECK_DATA['upnavi_section_num']."';";

				$ERROR = $cdb->update(T_UPNAVI_SECTION_PROBLEM,$DELETE_DATA,$where);
			}
			// 2012/07/05 add end oda
		}
		if ($SYS_ERROR[$i]) { $SYS_ERROR[$i][] = "<br>".$i."行目 上記システムエラーによりスキップしました。"; }
	}

	//各エラー結合
	if(is_array($DATA_ERROR)) {
		foreach($DATA_ERROR as $key => $val) {
			if (!$DATA_ERROR[$key]) { continue; }
			$ERROR = array_merge($ERROR,$DATA_ERROR[$key]);
		}
	}
	if(is_array($SYS_ERROR)) {
		foreach($SYS_ERROR as $key => $val) {
			if (!$SYS_ERROR[$key]) { continue; }
			$ERROR = array_merge($ERROR,$SYS_ERROR[$key]);
		}
	}
	if (!$ERROR) {
		if (count($L_IMPORT_LINE) <= 1) {
			$html = "<br>登録するデータが存在しません。";
		} else {
			$html = "<br>正常に全て登録が完了しました。";
		}
	} else {
		$html .= "<br>エラーのある行数以外の登録が完了しました。";
	}

	return array($html,$ERROR);
}


/**
 * csvインポートチェック
 *
 * AC:[A]管理者 UC1:[L07]テストを受ける UC2:[3]学力診断テスト.
 *
 * @author Azet
 * @param array &$CHECK_DATA
 * @param mixed $ins_mode
 * @param integer $line_num
 * @return array エラーの場合
 */
function check_data(&$CHECK_DATA,$ins_mode,$line_num) {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	// コースチェック
	if (!$CHECK_DATA['course_num']) {
		$ERROR[] = "<br>".$line_num."行目 コース番号が未入力です。";
	} else {
		if ($CHECK_DATA['course_num'] != $_POST['course_num']) {
			$ERROR[] = "<br>".$line_num."行目 ＣＳＶのコース番号と画面で選択した編集コースが異なります。";
		}
	}

	// 親単元番号チェック
	if (!$CHECK_DATA['upnavi_chapter_num']) {
		$ERROR[] = "<br>".$line_num."行目 親単元番号が未入力です。";
	} else {
//		if ($CHECK_DATA['upnavi_chapter_num'] != $_POST['upnavi_chapter_num']) {										// 2012/07/10 del oda
		if ($CHECK_DATA['upnavi_chapter_num'] != $_POST['upnavi_chapter_num'] && $_POST['upnavi_chapter_num']) {		// 2012/07/10 update oda
			$ERROR[] = "<br>".$line_num."行目 ＣＳＶの親単元番号と画面で選択した親単元が異なります。";
		} else {
			$sql = "  SELECT ".
					"   upnavi_chapter_num ".
					" FROM ". T_UPNAVI_CHAPTER .
					" WHERE ".
					"      upnavi_chapter_num = '".$CHECK_DATA['upnavi_chapter_num']."'".
					"  AND course_num = '".$CHECK_DATA['course_num']."'".
					"  AND mk_flg='0' LIMIT 1;";
//echo " sql = ".$sql."<br>";
			if ($result = $cdb->query($sql)) {
				$list = $cdb->fetch_assoc($result);
				if (!$list['upnavi_chapter_num']) {
					$ERROR[] = "<br>".$line_num."行目 親単元が存在しません。";
				}
			}
		}
	}

	// 表示順チェック
	if (!$CHECK_DATA['list_num']) {
		$ERROR[] = "<br>".$line_num."行目 表示順が未入力です。";
	} else {
		if (preg_match("/[^0-9]/",$CHECK_DATA['list_num'])) {
			$ERROR[] = "<br>".$line_num."行目 表示順は数字以外の指定はできません。";
		}
	}

	// 単元名チェック
	if (!$CHECK_DATA['upnavi_section_name']) { $ERROR[] = "<br>".$line_num."行目 子単元名が未入力です。"; }

	//　既存レコード存在チェック（同一名称チェック）
	if ($ins_mode === "add") {
		$sql = "  SELECT ".
				"   upnavi_section_num ".
				" FROM ". T_UPNAVI_SECTION .
				" WHERE upnavi_section_name = '".$CHECK_DATA['upnavi_section_name']."'".
				"  AND  upnavi_chapter_num = '".$CHECK_DATA['upnavi_chapter_num']."'".
				"  AND  course_num = '".$CHECK_DATA['course_num']."'".
				"  AND  mk_flg='0' LIMIT 1;";
//echo " sql = ".$sql."<br>";
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
			if ($list['upnavi_section_num']) {
				$ERROR[] = "<br>".$line_num."行目 既に登録済の子単元名です。";
			}
		}
	}

	// 2012/07/04 add start oda
	// ユニット番号チェック
	if ($CHECK_DATA['unit_num'] && $CHECK_DATA['course_num']) {

		// ユニット番号を分割
		$unit_list = explode("::",$CHECK_DATA['unit_num']);
		$unit_count = count($unit_list);
		$unit_string = implode(",", $unit_list);

		$sql  = "SELECT * FROM ".T_UNIT.
				" WHERE state='0' ".
				"  AND course_num = '".$CHECK_DATA['course_num']."'".
				"  AND unit_num IN (".$unit_string.");";
//echo " sql = ".$sql."<br>";
		if ($result = $cdb->query($sql)) {
			$count = $cdb->num_rows($result);
		}
		if ($count == 0) {
			$ERROR[] = "<br>".$line_num."行目 ユニット番号は存在しません。";
		} elseif ($count < $unit_count) {
			$ERROR[] = "<br>".$line_num."行目 ユニット番号の一部が存在しません。";
		}
	}
	// 2012/07/04 add end oda

	// 表示・非表示チェック
	if (!$CHECK_DATA['display']) {
		$ERROR[] = "<br>".$line_num."行目 表示・非表示が未入力です。";
	} else {
		if (preg_match("/[^0-9]/",$CHECK_DATA['display'])) {
			$ERROR[] = "<br>".$line_num."行目 表示・非表示は数字以外の指定はできません。";
		} elseif ($CHECK_DATA['display'] < 1 || $CHECK_DATA['display'] > 2) {
			$ERROR[] = "<br>".$line_num."行目 表示・非表示は1（表示）か2（非表示）の数字以外の指定はできません。";
		}
	}

	// 削除フラグチェック
	if ($CHECK_DATA['mk_flg'] == "") {								// add oda 2014/08/12 課題要望一覧No335 削除フラグ未設定のチェックを追加
		$ERROR[] = "<br>".$line_num."行目 削除フラグが、未入力です。";		// add oda 2014/08/12 課題要望一覧No335
	} elseif (preg_match("/[^0-9]/",$CHECK_DATA['mk_flg'])) {
		$ERROR[] = "<br>".$line_num."行目 削除フラグは数字以外の指定はできません。";
	} elseif ($CHECK_DATA['mk_flg'] != 0 && $CHECK_DATA['mk_flg'] != 1) {
		$ERROR[] = "<br>".$line_num."行目 削除フラグは0か1の数字以外の指定はできません。";
	}

	return $ERROR;
}
// 2012/07/02 add end oda
?>
