<?php
  function parse() {
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
	CModule::IncludeModule('iblock');
	$csv = $_SERVER["DOCUMENT_ROOT"]."/upload/myfile.csv";
	$lines = file($csv);
	$last_update_file = $_SERVER["DOCUMENT_ROOT"]."/upload/lastUpdate.txt";
	$last = filemtime($csv);

	$fa = explode(';', file_get_contents($last_update_file));
	if (trim($last) === trim($fa[0])) {
		return;
	}

	$el = new CIBlockElement;
	$data = array();
	foreach ($lines as $line_num => $line) {
		if($line_num == 0) continue;
		$arr_line = explode(';', $line);
		$temp = array();
		foreach ($arr_line as $line_item) {
			$temp[] = $line_item;
		}
		$data[] = $temp;
	}
	$k = 1;
	foreach($data as $line) {
		$arLoadProductArray = Array(
			"MODIFIED_BY"    => $USER->GetID(),
			"IBLOCK_SECTION_ID" => false,
			"IBLOCK_ID"      => 1,
			"NAME"           => $line[2],
			"ACTIVE"         => "Y",
			"PREVIEW_TEXT"   => $line[1],
			"DETAIL_TEXT"    => $line[3],
		);
		if (trim($fa[0]) === '') {
			if($PRODUCT_ID = $el->Add($arLoadProductArray)) {
				$last .= ';' . $PRODUCT_ID;
				echo "New ID: ".$PRODUCT_ID;
			}
			else
				echo "Error: ".$el->LAST_ERROR;
		} else {
			$PRODUCT_ID = $fa[$k++];
			$last .= ';' . $PRODUCT_ID;
			if($res = $el->Update($PRODUCT_ID, $arLoadProductArray)) {
				echo "Update ID: ".$PRODUCT_ID;
			}
			else
				echo "Error: ".$el->LAST_ERROR;
		}
	}
	$last_update = fopen($last_update_file, 'w');
	fwrite($last_update, $last);
	fclose($last_update);
}

parse();


