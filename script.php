<?php
  function parse() {
  	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
  	require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/csv_data.php");
  	CModule::IncludeModule('iblock');
  	$file_csv = new \Bitrix\Main\IO\File(\Bitrix\Main\Application::getDocumentRoot()."/upload/myfile.csv");

  	$csvFile = new CCSVData('R', true);
  	$csvFile->LoadFile($_SERVER["DOCUMENT_ROOT"]."/upload/myfile.csv");
  	$csvFile->SetDelimiter(';');

  	$file_last_update = new \Bitrix\Main\IO\File(\Bitrix\Main\Application::getDocumentRoot()."/upload/lastUpdate.txt");

  	if (!$file_csv) {
  		echo 'Файл csv не найден!';
  		return;
  	}

  	if (!$file_last_update) {
  		echo 'Файл last_update.txt не найден!';
  		return;
  	}

  	if ($file_csv->getModificationTime() == trim($file_last_update->getContents())) return;

  	$arSelect = Array("ID", "NAME", "PREVIEW_TEXT", "DETAIL_TEXT");
  	$arFilter = Array("IBLOCK_ID"=>1, "ACTIVE_DATE"=>"Y", "ACTIVE"=>"Y");
  	$el = new CIBlockElement;
  	$res = $el::GetList(Array(), $arFilter, false, false, $arSelect);
  	while ($arRes = $csvFile->Fetch()) {
  		if ($ob = $res->GetNextElement()) {
  			$arFields = $ob->GetFields();
  			if ($arRes[1] != $arFields["NAME"] || $arRes[2] != $arFields["PREVIEW_TEXT"] || $arRes[3] != $arFields["DETAIL_TEXT"]) {
  				$arLoadProductArray = Array(
  					"MODIFIED_BY"    => $USER->GetID(),
  					"IBLOCK_SECTION_ID" => false,
  					"IBLOCK_ID"      => 1,
  					"NAME"           => $arRes[1],
  				);
  				if ($arRes[2] != $arFields["PREVIEW_TEXT"])
  					$arLoadProductArray["PREVIEW_TEXT"] = $arRes[2];
  				if ($arRes[2] != $arFields["DETAIL_TEXT"])
  					$arLoadProductArray["DETAIL_TEXT"] = $arRes[3];
  				if($el->Update($arFields["ID"], $arLoadProductArray))
  					echo "Update ID: ".$arFields["ID"];
  				else
  					echo "Error: ".$el->LAST_ERROR;
  			}
  		} else {
  			$arLoadProductArray = Array(
  				"MODIFIED_BY"    => $USER->GetID(),
  				"IBLOCK_SECTION_ID" => false,
  				"IBLOCK_ID"      => 1,
  				"NAME"           => $arRes[1],
  				"ACTIVE"         => "Y",
  				"PREVIEW_TEXT"   => $arRes[2],
  				"DETAIL_TEXT"    => $arRes[3],
  			);
  			if($PRODUCT_ID = $el->Add($arLoadProductArray))
  				echo "New ID: ".$PRODUCT_ID;
  			else
  				echo "Error: ".$el->LAST_ERROR;
  		}
  	}

  	$file_last_update->putContents($file_csv->getModificationTime());
  }

  parse();
