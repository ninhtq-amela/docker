<?php
/**
 * すらら
 *
 * ゲーミフィケーション アバター管理
 *
 * 履歴
 * 2019/03/13 make file hirose 生徒TOP改修
 *
 * @author Azet
 */


/**
 * HTMLを作成する機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function start() {

	// サブセション取得
	$ERROR = sub_session();
//	pre(MODE);
//	pre(ACTION);
        
		// チェック処理
		if (ACTION == "check") {
			$ERROR = check($ERROR);
		}

		// DB登録・修正・削除
		if (!$ERROR) {
			if (ACTION == "add") {
				$ERROR = add();
			} elseif (ACTION == "change" || ACTION == "del") {
				$ERROR = change();
			}
		}
		// 登録処理
		if (MODE == "add") {
			if (ACTION == "check") {
				if (!$ERROR) {
					$html .= check_html();		 // 確認画面
				} else {
					$html .= addform($ERROR); 	 // 新規登録画面
				}
			} elseif (ACTION == "add") {
				if (!$ERROR) {
					$html .= avatar_list($ERROR); // 一覧表示
				} else {
					$html .= addform($ERROR);  	 // 新規登録画面
				}
			} else {
				$html .= addform($ERROR);  		 // 新規登録画面
			}
		// 詳細画面遷移
		} elseif (MODE == "詳細") {
			if (ACTION == "check") {
				if (!$ERROR) {
					$html .= check_html();		 // 確認画面
				} else {
					$html .= viewform($ERROR); 	 // 詳細画面
				}
			} elseif (ACTION == "change") {
				if (!$ERROR) {
					$html .= avatar_list($ERROR); // 一覧表示
				} else {
					$html .= viewform($ERROR);	 // 詳細画面
				}
			} else {
				$html .= viewform($ERROR); 		 // 詳細画面
			}
		// 削除処理
		} elseif (MODE == "削除") {
            if (ACTION == "check") {
				if (!$ERROR) {
					$html .= check_html(); 		// 確認画面
				}
				else {
					$html .= viewform($ERROR);  // 詳細画面
				}
			} elseif (ACTION == "change") {
				if (!$ERROR) {
					$html .= avatar_list($ERROR);// 一覧表示
				}
				else {
					$html .= viewform($ERROR); 	// 詳細画面
				}
			} else {
				$html .= check_html();			// 確認画面
			}
        } elseif (MODE == "gamification_avatar_import") {
			$ERROR = gamification_avatar_import();
			$html .= avatar_list($ERROR);
		// 一覧表示
		} else {
			$html .= avatar_list($ERROR);
		}

	return $html;
}


/**
 * 一覧表示処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @return string HTML
 */
function avatar_list($ERROR) {
	// グローバル変数
	global $L_PAGE_VIEW,$L_DISPLAY,$L_GAM_RARITY;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

        // 権限取得
	if ($authority) { $L_AUTHORITY = explode("::",$authority); }//??いる??

        $html .= "<h3>ゲーミフィケーション アバターマスタ</h3>\n";

	// エラーメッセージが存在する場合、メッセージ表示
	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
        } elseif (count($ERROR) == 0 && MODE == 'gamification_avatar_import') {
                $html .= "<b>データを登録しました</b><br>\n";
        }

	if (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__add",$_SESSION['authority'])===FALSE)) {
		$html .= "インポートする場合はゲーミフィケーションアバターtxtファイル(S-JIS)を指定しCSVインポートを押してください<br>";
		$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" enctype=\"multipart/form-data\">\n";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"gamification_avatar_import\">\n";
		$html .= "<input type=\"file\" size=\"40\" name=\"gamification_avatar_file\">\n";
		$html .= "<input type=\"submit\" value=\"CSVインポート\">\n";
		$html .= "</form>\n";
		$html .= "<br />\n";
		$html .= "<form action=\"/admin/gamification_avatar_make_csv.php\" method=\"POST\">";
		$expList = "";
		if ( is_array($L_EXP_CHA_CODE) ) {
			$expList .= "海外版の場合は、出力形式について[Unicode]選択して、ダウンロードボタンをクリックしてください。<br />\n";
			$expList .= "<b>出力形式：</b>";
			$expList .= "<select name=\"exp_list\">";
			foreach( $L_EXP_CHA_CODE as $key => $val ){
				$expList .= "<option value=\"".$key."\">".$val."</option>";
			}
			$expList .= "</select>";
			$html .= $expList;
		}
		$html .= "<input type=\"submit\" value=\"CSVエクスポート\">";
		$html .= "</form>";
		$html .= "<br />\n";
		$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"add\">\n";
		$html .= "<input type=\"submit\" value=\"アバターマスタ新規登録\">\n";
		$html .= "</form>\n";
		$html .= "<br />\n";
	}
        
        $ftp_path = FTP_URL.'gamification/avatar/';
        $html .= FTP_EXPLORER_MESSAGE;
        $html .= "<a href=\"".$ftp_path."\" target=\"_blank\">ゲーミフィケーションアバターフォルダー($ftp_path)</a><br>\n";
        $html .= "<a href=\"#\" class=\"gamification_notes_open\">画像命名規約</a><br>\n";
        $html .= "<div style=\"font-size:14px; display:none;\" class=\"gamification_notes\">";
        $html .= "<p>画像の登録にはアバターの登録を行ったうえで下記命名規約に沿ってご登録下さい。</p>\n";
        $html .= "<b>画像格納先:/data/home/contents/www/material/gamification/avatar/</b>\n";
        $html .= "<table>\n";
        $html .= "<tr><th colspan=\"2\">【命名規約】</th></tr>\n";
        $html .= "<tr><td>デフォルト</td><td>avatar_アバターID.png</td></tr>\n";
        $html .= "</table><br>\n";
        $html .= "\n";
        $html .= "<a href=\"#\" class=\"gamification_notes_close\">閉じる</a>\n";
        $html .= "</div>";
        $html .= "<script>";
        $html .= "$('.gamification_notes_open').click(function() {";
        $html .= "$('.gamification_notes').show();";
        $html .= "});";
        $html .= "$('.gamification_notes_close').click(function() {";
        $html .= "$('.gamification_notes').hide();";
        $html .= "});";
        $html .= "</script>";     
        // 表示ページ制御
	$s_page_view_html .= "&nbsp;&nbsp;&nbsp;表示数<select name=\"s_page_view\">\n";
	foreach ($L_PAGE_VIEW as $key => $val){
		if ($_SESSION['sub_session']['s_page_view'] == $key) { $sel = " selected"; } else { $sel = ""; }
		$s_page_view_html .= "<option value=\"".$key."\"".$sel.">".$val."</option>\n";
	}
	$s_page_view_html .= "</select><input type=\"submit\" value=\"Set\">\n";

	// 一覧取得
	$sql  = "SELECT * FROM " . T_GAMIFICATION_AVATAR
                ." WHERE mk_flg ='0' "
                ." ORDER BY list_num";

	// イメージ件数取得
	$count = 0;
	if ($result = $cdb->query($sql)) {
		$count = $cdb->num_rows($result);
	}

	// ページビュー判断
	if ($L_PAGE_VIEW[$_SESSION['sub_session']['s_page_view']]) {
		$page_view = $L_PAGE_VIEW[$_SESSION['sub_session']['s_page_view']];
	} else {
		$page_view = $L_PAGE_VIEW[0];
	}

	// 最大ページ数算出
	$max_page = ceil($count/$page_view);

	// 現在表示ページをセションから取得する
	if ($_SESSION['sub_session']['s_page']) {
		$page = $_SESSION['sub_session']['s_page'];
	} else {
		$page = 1;
	}

	// 表示ページと前ページ・次ページの数値を算出
	$start = ($page - 1) * $page_view;
	$next = $page + 1;
	$back = $page - 1;

	// SQL文に一覧表示する条件を追加
	$sql .= " LIMIT ".$start.",".$page_view.";";

	// データ読み込み
	if ($result = $cdb->query($sql)) {

		// 取得データ件数が０の場合
		$check_count = 0;
		if ($result = $cdb->query($sql)) {
			$check_count = $cdb->num_rows($result);
		}
		if (!$check_count) {
			$html .= "現在、データは登録されていません。";
			return $html;
		}

		// 一覧表示開始
		$html .= "<br>\n";
		$html .= "修正する場合は、修正するアバターの詳細ボタンを押してください。<br>\n";

		// 登録件数表示
		$html .= "<br>\n";
		$html .= "<div style=\"float:left;\">総数(".$count."):PAGE[".$page."/".$max_page."]</div>\n";

		// 前ページボタン表示制御
		if ($back > 0) {
			$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\" style=\"float:left;\">";
			$html .= "<input type=\"submit\" value=\"前のページ\">";
			$html .= "<input type=\"hidden\" name=\"action\" value=\"sub_session\">\n";
			$html .= "<input type=\"hidden\" name=\"mode\" value=\"page\">\n";
			$html .= "<input type=\"hidden\" name=\"s_page\" value=\"".$back."\">\n";
			$html .= "</form>";
		}

		// 次ページボタン表示制御
		if ($page < $max_page) {
			$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\" style=\"float:left;\">";
			$html .= "<input type=\"hidden\" name=\"action\" value=\"sub_session\">\n";
			$html .= "<input type=\"hidden\" name=\"mode\" value=\"page\">\n";
			$html .= "<input type=\"hidden\" name=\"s_page\" value=\"".$next."\">\n";
			$html .= "<input type=\"submit\" value=\"次のページ\">";
			$html .= "</form>";
		}

		$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\" style=\"float:left;\">";
		$html .= "<input type=\"hidden\" name=\"action\" value=\"sub_session\">\n";
		$html .= "<input type=\"hidden\" name=\"s_page\" value=\"1\">\n";
		$html .= $s_page_view_html;
		$html .= "</form><br><br>\n";
		$html .= "<table class=\"secret_form\">\n";
		$html .= "<tr class=\"secret_form_menu\">\n";

		//--------------------
		// リストタイトル表示
		//--------------------
		$html .= "<th>アバターID</th>\n";
		$html .= "<th>アバター名</th>\n";
		$html .= "<th>レア度</th>\n";
		$html .= "<th>画像設定</th>\n";
		$html .= "<th>表示・非表示</th>\n";

		// 詳細
		if (!ereg("practice__view",$authority)
			&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__view",$_SESSION['authority'])===FALSE))
		) {
			$html .= "<th>詳細</th>\n";
		}

		// 削除
		if (!ereg("practice__del",$authority)
			&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__del",$_SESSION['authority'])===FALSE))
		) {
			$html .= "<th>削除</th>\n";
		}
		$html .= "</tr>\n";
                
                // イメージ格納ディレクトリ作成
                create_directory();

		//--------------
		// 明細表示
		//--------------
		$i = 1;
		while ($list = $cdb->fetch_assoc($result)) {
                         $img_setting = false;
                        foreach (glob(MATERIAL_GAM_AVATAR_DIR.'avatar_'.$list['avatar_id'].'.*') as $val ) {
                            $img_setting = true;
                        }
			// HTML作成
			$html .= "<tr class=\"secret_form_cell\">\n";
			$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
			$html .= "<input type=\"hidden\" name=\"avatar_id\" value=\"".$list['avatar_id']."\">\n";
			$html .= "<td>".$list['avatar_id']."</td>\n";
			$html .= "<td>".$list['avatar_name']."</td>\n";
			$html .= "<td>".$L_GAM_RARITY[$list['rarity']]."</td>\n";
                        if ($img_setting) {
                            $html .= "<td style=\"color:blue;text-align:center;\">設定済</td>\n";
                        } else {
                            $html .= "<td style=\"color:red;text-align:center;\">未設定</td>\n";
                        }
			$html .= "<td style=\"text-align:center;\">".$L_DISPLAY[$list['display']]."</td>\n";

			// 詳細ボタン（権限が有る場合は、ボタン表示）
			if (!ereg("practice__view",$authority)
				&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__view",$_SESSION['authority'])===FALSE))
			) {
				$html .= "<td><input type=\"submit\" name=\"mode\" value=\"詳細\"></td>\n";
			}

			// 削除ボタン（権限が有る場合は、ボタン表示）
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
        } else {
            $html .= "<p>現在、データは登録されていません。</p>";
        }
	return $html;
}


/**
 * 新規登録フォーム
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @return string HTML
 */
function addform($ERROR) {

	// グローバル変数
	global $L_DISPLAY, $L_GAM_CHARACTER_TYPE, $L_GAM_CHARACTER_OPEN,$L_GAM_RARITY;

	$html .= "<br>\n";
	$html .= "アバターマスタ 新規登録フォーム<br>\n";
	$html .= "<br>\n";


	// エラーメッセージ表示
	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}

	// 入力画面表示
	$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\" enctype=\"multipart/form-data\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"check\">\n";

	// htmlクラス生成
	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(GAMIFICATION_AVATAR);
        
	$L_GAM_RARITY[0] = '選択してください';
	ksort($L_GAM_RARITY);
	// フォーム部品生成
	$INPUTS['AVATARID']	= array('result'=>'plane','value'=>"---");
	$INPUTS['AVATARNAME']	= array('result'=>'form', 'type' => 'text', 'name' => 'avatar_name', 'size' => '50', 'value'=>$_POST['avatar_name'] ?: null);
	$INPUTS['RARITY']	= array('result'=>'form', 'type' => 'select', 'name' => 'rarity', 'array'=>$L_GAM_RARITY, 'check' => $_POST['rarity'] ?: null);
	$INPUTS['DETAIL']	= array('result'=>'form', 'type' => 'textarea', 'name' => 'detail', 'style' => 'width:400px; height:60px;', 'value'=>$_POST['detail'] ?: null);
	$INPUTS['LISTNUM']	= array('result'=>'form', 'type' => 'text', 'name' => 'list_num', 'size' => '10', 'value'=>isset($_POST['list_num']) ? $_POST['list_num']:null);

	// 表示・非表示 ラジオボタン生成
	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("display");
	$newform->set_form_id("display");
	$newform->set_form_check($_POST['display']);
	$newform->set_form_value('1');
	$radio1 = $newform->make();

	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("display");
	$newform->set_form_id("undisplay");
	$newform->set_form_check($_POST['display']);
	$newform->set_form_value('2');
	$radio2 = $newform->make();

	$display_value = $radio1 . "<label for=\"display\">".$L_DISPLAY[1]."</label> / " . $radio2 . "<label for=\"undisplay\">".$L_DISPLAY[2]."</label>";
	$INPUTS['DISPLAY'] = array('result'=>'plane','value'=>$display_value);
	$INPUTS['USERBKO']	= array('result'=>'form', 'type' => 'text', 'name' => 'usr_bko', 'size' => '50', 'value'=>$_POST['usr_bko'] ?: null);

	$make_html->set_rep_cmd($INPUTS);
	$html .= $make_html->replace();

	// 制御ボタン定義
	$html .= "<input type=\"submit\" value=\"追加確認\">\n";
	$html .= "<input type=\"reset\" value=\"クリア\">\n";
	$html .= "</form>\n";
	$html .= "<br>\n";

	$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"avatar_list\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";
	return $html;
}


/**
 * 詳細情報表示
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @return string HTML
 */
function viewform($ERROR) {

        // グローバル変数
	global $L_DISPLAY, $L_GAM_RARITY;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	// POST値取得
	if (ACTION=="check" || ACTION=="back") {
		// POST値取得
		foreach ($_POST as $key => $val) {
			$$key = $val;
		}
	} else {
		$sql = "SELECT * FROM " . T_GAMIFICATION_AVATAR .
			" WHERE avatar_id ='".$cdb->real_escape($_POST['avatar_id'])."' AND mk_flg='0' LIMIT 1;";
		$result = $cdb->query($sql);
		$list = $cdb->fetch_assoc($result);
		if (!$list) {
			$html .= "既に削除されているか、不正な情報が混ざっています。";
			return $html;
		}
		foreach ($list as $key => $val) {
			$$key = replace_decode($val);
			$$key = ereg_replace("\"","&quot;",$$key);
		}
	}

	// 画面表示
	$html .= "<br>\n";
	$html .= "詳細画面<br>\n";
	$html .= "<br>\n";
	// エラーメッセージ表示
	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}
	// 入力画面表示
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"詳細\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"check\">\n";
	$html .= "<input type=\"hidden\" name=\"avatar_id\" value=\"".$avatar_id."\">\n";
        
	// htmlクラス生成
	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(GAMIFICATION_AVATAR);
        
	$L_GAM_RARITY[0] = '選択してください';
	ksort($L_GAM_RARITY);
	// フォーム部品生成
	$INPUTS['AVATARID']	= array('result'=>'plane','value'=>$avatar_id ?: '---');
	$INPUTS['AVATARNAME']	= array('result'=>'form', 'type' => 'text', 'name' => 'avatar_name', 'size' => '50', 'value'=>$avatar_name ?: null);
	$INPUTS['RARITY']	= array('result'=>'form', 'type' => 'select', 'name' => 'rarity', 'array'=>$L_GAM_RARITY, 'check' => $rarity ?: null);
	$INPUTS['LISTNUM']	= array('result'=>'form', 'type' => 'text', 'name' => 'list_num', 'size' => '10', 'value'=>isset($list_num) ? $list_num : null);
	$INPUTS['DETAIL']	= array('result'=>'form', 'type' => 'textarea', 'name' => 'detail', 'size' => '50' ,'cols'=>'50' ,'rows'=>'5', 'value'=>$detail ?: null);

	// 表示・非表示 ラジオボタン生成
	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("display");
	$newform->set_form_id("display");
	$newform->set_form_check($display);
	$newform->set_form_value('1');
	$radio1 = $newform->make();

	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("display");
	$newform->set_form_id("undisplay");
	$newform->set_form_check($display);
	$newform->set_form_value('2');
	$radio2 = $newform->make();

	$display_value = $radio1 . "<label for=\"display\">".$L_DISPLAY[1]."</label> / " . $radio2 . "<label for=\"undisplay\">".$L_DISPLAY[2]."</label>";
	$INPUTS['DISPLAY'] = array('result'=>'plane','value'=>$display_value);
	$INPUTS['USERBKO']	= array('result'=>'form', 'type' => 'text', 'name' => 'usr_bko', 'size' => '50' ,'cols'=>'50' ,'rows'=>'5', 'value'=>$usr_bko ?: null);
        
	$make_html->set_rep_cmd($INPUTS);
	$html .= $make_html->replace();

	// 制御ボタン定義
	$html .= "<input type=\"submit\" value=\"変更確認\">";
	$html .= "<input type=\"reset\" value=\"クリア\">";
	$html .= "</form><br>\n";
	// 一覧に戻る
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"avatar_list\">\n";
	$html .= "<input type=\"submit\" value=\"一覧に戻る\">\n";
	$html .= "</form>\n";
	return $html;
}

/**
 * 入力項目チェック
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @param array $LINE (CSVエラーチェックの場合に使用します)
 * @return array エラーの場合
 */
function check($ERROR, $LINE = null) {

        // グローバル変数
	global $L_DISPLAY, $L_GAM_RARITY;
    
	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];
        
        $CHECK_LIST = array();
        if (is_array($LINE) && count($LINE) > 0) {
            $CHECK_LIST = $LINE;
        } else {
            $CHECK_LIST = $_POST;
        }

        // キャラクター名
        if (!$CHECK_LIST['avatar_name']) {
		$ERROR[] = "アバター名が未入力です。";
        } else{
			$where = "";
			if($CHECK_LIST['avatar_id'] > 0) {
				$where = " AND avatar_id <> '".$cdb->real_escape($CHECK_LIST['avatar_id'])."'";
			}
                $sql = "SELECT * FROM ".T_GAMIFICATION_AVATAR." WHERE avatar_name = '".$cdb->real_escape($CHECK_LIST['avatar_name'])."' AND mk_flg='0'".$where.";";
//				print $sql.'<br>';
                if ($result = $cdb->query($sql)) {
                        $count = $cdb->num_rows($result);
                }
                if ($count > 0) {
                    $ERROR[] = "入力されたアバター名は既に登録済です";
                }
        }
        if ($CHECK_LIST['avatar_name'] && mb_strlen($CHECK_LIST['avatar_name']) > 25) {
            $ERROR[] = "アバター名は25文字まで登録可能です。";
        }

        
        // キャラクタータイプ
        if ($CHECK_LIST['rarity'] == '' || $CHECK_LIST['rarity'] == 0) {
			$ERROR[] = "レア度が未選択です。";
        } elseif (!$L_GAM_RARITY[$CHECK_LIST['rarity']]) {
			$ERROR[] = "レア度の値が不正です";
        }

        // キャラクターの説明
        if ($CHECK_LIST['detail'] && mb_strlen($CHECK_LIST['detail']) > 255) {
		$ERROR[] = "説明文は255文字まで入力可能です。";
        }

        // キャラクター一覧での並び順
        if ($CHECK_LIST['list_num'] == '') {
			$ERROR[] = "表示順が未入力です。";
		}
		//list_numの数値チェック
		if(preg_match("/[^0-9]/",$CHECK_LIST['list_num'])){
			$ERROR[] = "表示順の数値が無効です。半角数字を入力してください";
		}
        
        // ユーザー備考
        if ($CHECK_LIST['usr_bko'] && mb_strlen($CHECK_LIST['usr_bko']) > 255) {
		$ERROR[] = "ユーザー備考は255文字まで入力可能です。";
        }
        
        // 表示・非表示
        if (!$CHECK_LIST['display']) {
		$ERROR[] = "表示・非表示が未入力です。";
        } elseif (!$L_DISPLAY[$CHECK_LIST['display']]) {
		$ERROR[] = "表示・非表示の値が不正です。";
        }

	return $ERROR;
}

/**
 * 入力確認画面表示
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function check_html() {

        global $L_DISPLAY, $L_GAM_RARITY;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	// アクション情報をhidden項目に設定
//	pre($_POST);
	if ($_POST) {
		foreach ($_POST as $key => $val) {
			if ($key == "action") {
				if (MODE == "add") {
					$val = "add";
				// 詳細から来た時はchange（更新処理）に変える
				} elseif (MODE == "詳細") {
					$val = "change";
				}
			}
			// アクション以外のデータをhidden項目に設定
			$HIDDEN .= "<input type=\"hidden\" name=\"$key\" value=\"$val\">\n";
		}
	}

	// 他の人が削除したかチェック
	if (ACTION) {
		foreach ($_POST as $key => $val) {
			 $$key = $val;
		}
	} else {
		$HIDDEN .= "<input type=\"hidden\" name=\"action\" value=\"change\">\n";
		$sql = "SELECT * FROM ".T_GAMIFICATION_AVATAR.
			" WHERE avatar_id='".$cdb->real_escape($_POST[avatar_id])."' AND mk_flg = '0' LIMIT 1;";
		$result = $cdb->query($sql);
		$list = $cdb->fetch_assoc($result);
		if (!$list) {
			$html .= "既に削除されているか、不正な情報が混ざっています。";
			return $html;
		}
		foreach ($list as $key => $val) {
			$$key = replace_decode($val);
			$$key = ereg_replace("\"","&quot;",$$key);
		}
	}

	// ボタン表示文言判定
	if (MODE == "削除") {
		$button = "削除";
	} else if (MODE == "詳細") {
		$button = "更新";
	} else {
		$button = "登録";
	}

	// 入力確認画面表示
	$html = "<br>\n";
	$html .= "確認画面：以下の内容で".$button."してもよろしければ".$button."ボタンをクリックしてください。<br>\n";
	$html .= "<br>\n";
	// htmlクラス生成
	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(GAMIFICATION_AVATAR);        
	$INPUTS['AVATARID']  	= array('result'=>'plane','value'=>$avatar_id ?:'----');
	$INPUTS['AVATARNAME']	= array('result'=>'plane','value'=>$avatar_name);
	$INPUTS['RARITY']	= array('result'=>'plane','value'=>$L_GAM_RARITY["$rarity"]);
	$INPUTS['DETAIL']	= array('result'=>'plane','value'=>$detail);
	$INPUTS['LISTNUM']      	= array('result'=>'plane','value'=>$list_num);
	$INPUTS['DISPLAY']		= array('result'=>'plane','value'=>$L_DISPLAY[$display]);
	$INPUTS['USERBKO']		= array('result'=>'plane','value'=>$usr_bko);

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();

	// 制御ボタン定義
	$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\" style=\"float:left\">\n";
	$html .= $HIDDEN;
	// 削除の場合は、１画面目に戻る
	if (MODE == "削除") {
		$html .= "<input type=\"hidden\" name=\"s_page\" value=\"1\">\n";
		$html .= "<input type=\"hidden\" name=\"avatar_id\" value=".$avatar_id.">\n";
	}
	// 登録 or 削除 ボタン
	$html .= "<input type=\"submit\" value=\"$button\">\n";
	$html .= "</form>";

	// 戻るボタン
	$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
	if (ACTION) {
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
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"avatar_list\">\n";
	}
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";

	return $html;
}

/**
 * DB新規登録
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function add() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	// 登録項目設定
        $INSERT_DATA = array();
	$INSERT_DATA['avatar_name']             = str_replace(PHP_EOL, '', $_POST['avatar_name']);
	$INSERT_DATA['rarity']                  = $_POST['rarity'];
	$INSERT_DATA['detail']                  = $_POST['detail'];
	$INSERT_DATA['list_num']                = $_POST['list_num'];
	$INSERT_DATA['usr_bko']                 = str_replace(PHP_EOL, '', $_POST['usr_bko']);
    $INSERT_DATA['display']	  		= $_POST['display'];
	$INSERT_DATA['ins_syr_id'] 		= "add";
	$INSERT_DATA['ins_date']  		= "now()";
	$INSERT_DATA['ins_tts_id'] 		= $_SESSION['myid']['id'];
	$INSERT_DATA['upd_syr_id'] 		= "add";
	$INSERT_DATA['upd_date']   		= "now()";
	$INSERT_DATA['upd_tts_id'] 		= $_SESSION['myid']['id'];

	// ＤＢ追加処理
	$ERROR = $cdb->insert(T_GAMIFICATION_AVATAR,$INSERT_DATA);
        
	if (!$ERROR) {
            $_SESSION[select_menu] = MAIN . "<>" . SUB . "<><><>";
            // ディレクトリ作成
            $id = $cdb->insert_id();
            if ($id) {
                create_directory();
            }
        }
	return $ERROR;
}

/**
 * DB更新・削除 処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function change() {

        // DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	// 更新処理
	if (MODE == "詳細") {
		// DBアップデート
        $UPDATE_DATA = array();
		$UPDATE_DATA['rarity']              = $_POST['rarity'];
		$UPDATE_DATA['avatar_name']         = str_replace(PHP_EOL, '', $_POST['avatar_name']);
		$UPDATE_DATA['detail']              = $_POST['detail'];
		$UPDATE_DATA['list_num']            = $_POST['list_num'];
		$UPDATE_DATA['usr_bko']             = str_replace(PHP_EOL, '', $_POST['usr_bko']);
		$UPDATE_DATA['display']             = $_POST['display'];
		$UPDATE_DATA['upd_syr_id']          = "update";
		$UPDATE_DATA['upd_date']            = "now()";
		$UPDATE_DATA['upd_tts_id']          = $_SESSION['myid']['id'];
		$where = " WHERE avatar_id = '".$cdb->real_escape($_POST['avatar_id'])."' LIMIT 1;";
		$ERROR = $cdb->update(T_GAMIFICATION_AVATAR,$UPDATE_DATA,$where);

	// 削除処理
	} elseif (MODE == "削除") {
                $UPDATE_DATA = array();
		$UPDATE_DATA['mk_flg']	   = "1";
		$UPDATE_DATA['mk_tts_id']  = $_SESSION['myid']['id'];
		$UPDATE_DATA['mk_date']	   = "now()";
		$UPDATE_DATA['upd_syr_id'] = "del";
		$UPDATE_DATA['upd_tts_id'] = $_SESSION['myid']['id'];
		$UPDATE_DATA['upd_date']   = "now()";
		$where = " WHERE avatar_id = '".$cdb->real_escape($_POST['avatar_id'])."' LIMIT 1;";
		$ERROR = $cdb->update(T_GAMIFICATION_AVATAR,$UPDATE_DATA,$where);

		// 画像・音声フォルダを削除する
		$sourse_file = MATERIAL_GAM_AVATAR_DIR.'avatar_'.$_POST['avatar_id'].'.*';
		system("rm -rf {$sourse_file}");
	}

	if (!$ERROR) { $_SESSION[select_menu] = MAIN . "<>" . SUB . "<><><>"; }
	return $ERROR;
}


/**
 * サブセションをPOSTから設定
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * (POST項目では、画面再描画の際、パラメータの受け渡しができない為）
 *
 * @author Azet
 */
function sub_session() {

	// ページ数をPOSTから取得し、セションに格納
	if (strlen($_POST['s_page_view'])) {
		$_SESSION['sub_session']['s_page_view'] = $_POST['s_page_view'];
	}

	// 現在ページをPOSTから取得し、セションに格納
	if (strlen($_POST['s_page'])) {
		$_SESSION['sub_session']['s_page'] = $_POST['s_page'];
	}

	return;
}

/**
 * 管理ディレクトリ作成
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param string $character_id
 */
function create_directory() {

    // 画像フォルダ
    $character_img_path = MATERIAL_GAM_AVATAR_DIR;
	if (!file_exists($character_img_path)) {
		@mkdir($character_img_path, 0777, true);    // 第三引数tureで再帰的に作成する
		@chmod($character_img_path, 0777);
	}        
}

/**
 * インポート処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function gamification_avatar_import() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$file_name = $_FILES['gamification_avatar_file']['name'];
	$file_tmp_name = $_FILES['gamification_avatar_file']['tmp_name'];
	$file_error = $_FILES['gamification_avatar_file']['error'];

	if (!$file_tmp_name) {
		$ERROR[] = "アバターtxtファイルが指定されておりません。";
	} elseif (!eregi("(.txt)$",$file_name)) {
		$ERROR[] = "ファイルの拡張子が不正です。(txt)";
	} elseif ($file_error == 1) {
		$ERROR[] = "アップロードできるファイルの容量が設定範囲を超えてます。サーバー管理者へ相談してください。";
	} elseif ($file_error == 3) {
		$ERROR[] = "ファイルの一部分のみしかアップロードされませんでした。";
	} elseif ($file_error == 4) {
		$ERROR[] = "ファイルがアップロードされませんでした。";
	}
	if ($ERROR) {
		if ($file_tmp_name && file_exists($file_tmp_name)) {
			unlink($file_tmp_name);
		}
		return $ERROR;
	}

	$LIST = file($file_tmp_name);
	if ($LIST) {
		$i = 0;
		foreach ($LIST AS $VAL) {
			if ($i == 0) {
				$i++;  continue;
			}
			unset($LINE);
			$VAL = trim($VAL);
			if (!$VAL || !ereg("\t",$VAL)) {
				continue;
			}
			$file_data = explode("\t",$VAL);
			// 項目が全て設定されている場合
			if (count($file_data) == 8) {
				$LINE['avatar_id']           = $file_data[0];
				$LINE['avatar_name'] 	= $file_data[1];
				$LINE['rarity'] 	= $file_data[2];
				$LINE['detail'] 	= $file_data[3];
				$LINE['list_num'] 		= $file_data[4];
				$LINE['usr_bko'] 		= $file_data[5];
				$LINE['display'] 		= $file_data[6];
				$LINE['mk_flg'] 		= $file_data[7];
			// 管理番号が未設定の場合は、格納配列が１つ前にずれる
			} elseif (count($file_data) == 7) {
				$LINE['avatar_id']           = '0';
				$LINE['avatar_name'] 	= $file_data[0];
				$LINE['rarity'] 	= $file_data[1];
				$LINE['detail'] 	= $file_data[2];
				$LINE['list_num']            = $file_data[3];
				$LINE['usr_bko'] 	= $file_data[4];
				$LINE['display'] 		= $file_data[5];
				$LINE['mk_flg'] 		= $file_data[6];
			} else {
				$ERROR[] = '<b>'.($i).'行目　未入力の項目があるためスキップしました。</b>';
				continue;
			}
			if($LINE['detail']){
				$LINE['detail'] = preg_replace('/<br>|<br \/>|<br\/>/', "\r\n", $LINE['detail']);
			}
			if ($LINE) {
				foreach ($LINE AS $key => $val) {
					if ($val) {
						$code = judgeCharacterCode($val);
						if ( $code != 'UTF-8' ) {
							$val = mb_convert_encoding($val,"UTF-8","sjis-win");
						}
						$LINE[$key] = replace_encode($val);
					}
				}
			}
			/* ============================== エラーチェック ============================== */
			$LINE_ERROR = array();
			$LINE_ERROR = check(null,$LINE);
			/* ============================================================================ */
			// エラーの時は強制的に非表示とする。
			if ($LINE_ERROR) {
				$LINE['display'] = "2";
			}

			// データ存在チェック
			$sql  = "SELECT * FROM " .T_GAMIFICATION_AVATAR.
					" WHERE avatar_id = '".$LINE['avatar_id']."';";
			if ($result = $cdb->query($sql)) {
				$exist_count = $cdb->num_rows($result);
			}

			// 登録処理
			if ($exist_count == 0) {
				$INSERT_DATA = array();
				// 新規登録の際は、auto_incrementのIDを使うので$LINE['game_collection_id']は登録しない。
				$INSERT_DATA['avatar_id']          		= $LINE['avatar_id'];
				$INSERT_DATA['avatar_name']				= $LINE['avatar_name'];
				$INSERT_DATA['rarity']					= $LINE['rarity'];
				$INSERT_DATA['detail']					= $LINE['detail'];
				$INSERT_DATA['list_num']                = $LINE['list_num'];
				$INSERT_DATA['usr_bko']                 = $LINE['usr_bko'];
				$INSERT_DATA['display']                 = $LINE['display'];
				$INSERT_DATA['mk_flg']					= $LINE['mk_flg'];
				$INSERT_DATA['ins_syr_id']				= "add";
				$INSERT_DATA['ins_date']				= "now()";
				$INSERT_DATA['ins_tts_id']				= $_SESSION['myid']['id'];
				$INSERT_DATA['upd_syr_id']				= "add";
				$INSERT_DATA['upd_date']				= "now()";
				$INSERT_DATA['upd_tts_id']				= $_SESSION['myid']['id'];

				$INSERT_ERROR = $cdb->insert(T_GAMIFICATION_AVATAR, $INSERT_DATA);
				// ディレクトリ作成
				$id = $cdb->insert_id();
				if ($id) {
					create_directory($id);
				}
			// 更新処理・削除処理
			} else {
				// 更新
				$INSERT_DATA = array();
				if ($LINE['mk_flg'] == '0') {
					$INSERT_DATA['avatar_id']          	= $LINE['avatar_id'];
					$INSERT_DATA['avatar_name']			= $LINE['avatar_name'];
					$INSERT_DATA['rarity']		= $LINE['rarity'];
					$INSERT_DATA['detail']			= $LINE['detail'];
					$INSERT_DATA['list_num']                        = $LINE['list_num'];
					$INSERT_DATA['usr_bko']                         = $LINE['usr_bko'];
					$INSERT_DATA['display']				= $LINE['display'];
					$INSERT_DATA['mk_flg']				= "0";
					$INSERT_DATA['mk_tts_id']			= "NULL";
					$INSERT_DATA['mk_date']				= "NULL";
					$INSERT_DATA['upd_syr_id']			= "update";
					$INSERT_DATA['upd_date']			= "now()";
					$INSERT_DATA['upd_tts_id']			= $_SESSION['myid']['id'];
				// 削除
				} else {
					$INSERT_DATA['display']				= "2";
					$INSERT_DATA['mk_flg']				= "1";
					$INSERT_DATA['mk_tts_id']			= $_SESSION['myid']['id'];
					$INSERT_DATA['mk_date']				= "now()";
					$INSERT_DATA['upd_syr_id']			 = "del";
					$INSERT_DATA['upd_tts_id']			= $_SESSION['myid']['id'];
					$INSERT_DATA['upd_date']			= "now()";
					// 画像・音声フォルダを削除する
					$sourse_file = MATERIAL_GAM_AVATAR_DIR.'avatar_'.$LINE['avatar_id'].'.*';
					system("rm -rf {$sourse_file}");
				}
				$where = " WHERE avatar_id = '".$LINE['avatar_id']."' LIMIT 1;";
				$INSERT_ERROR = $cdb->update(T_GAMIFICATION_AVATAR, $INSERT_DATA, $where);

			}                        
			if (is_array($INSERT_ERROR) && count($INSERT_ERROR) > 0) {
				$ERROR[] = '<b>'.($i)."行目　以下のエラーの為、情報の登録・更新に失敗しました。</b>";
				foreach($INSERT_ERROR as $v) {
					$ERROR[] = $v;
				}
			} elseif(is_array($LINE_ERROR) && count($LINE_ERROR) > 0) {
				$ERROR[] = '<b>'.($i)."行目　以下のエラーの為、非表示で登録しました。</b>";
				foreach($LINE_ERROR as $v) {
					$ERROR[] = $v;
				}
			}
			$i++;
		}
	}

	// アップロードファイル削除
	if ($file_tmp_name && file_exists($file_tmp_name)) {
		unlink($file_tmp_name);
	}
	return $ERROR;
}



