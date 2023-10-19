<?php
  function parse() {
  	//Подключение модулей
  	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
  	require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/csv_data.php");
  	CModule::IncludeModule('iblock');

  	//csv файл
  	$file_csv = new \Bitrix\Main\IO\File(\Bitrix\Main\Application::getDocumentRoot()."/upload/myfile.csv");

  	//Парсинг csv
  	$csvFile = new CCSVData('R', true);
  	$csvFile->LoadFile($_SERVER["DOCUMENT_ROOT"]."/upload/myfile.csv");
  	$csvFile->SetDelimiter(';');

  	//Файл с данными о последнем изменении csv
  	$file_last_update = new \Bitrix\Main\IO\File(\Bitrix\Main\Application::getDocumentRoot()."/upload/lastUpdate.txt");

  	//Проверка наличия файла csv
  	if (!$file_csv) {
  		echo 'Файл csv не найден!';
  		return;
  	}

  	//Проверка наличия файла с данными о последнем изменении csv
  	if (!$file_last_update) {
  		echo 'Файл last_update.txt не найден!';
  		return;
  	}

  	//Если csv не менялся - ничего не делать
  	if ($file_csv->getModificationTime() == trim($file_last_update->getContents())) return;

  	$el = new CIBlockElement;
  	//Построчный обход файла csv
  	while ($csvData = $csvFile->Fetch()) {
  		//Поиск елемента инфоблока по XML_ID
  		$arSelect = Array("ID", "IBLOCK_ID", "NAME", "XML_ID", "IBLOCK_SECTION_ID", "PREVIEW_TEXT", "DETAIL_TEXT");
  		$arFilter = Array("IBLOCK_ID"=> 2, "XML_ID" => $csvData[0]);
  		$product = $el::GetList(Array(), $arFilter, false, false, $arSelect);

  		//Если элемент найден, проверяем на соответствие csv файлу
  		if ($ob = $product->GetNextElement()) {
  			$arProperties = $ob->GetProperties();
  			$arFields = $ob->GetFields();

  			$PROPS = array();
  			//Изменились ли свойства
  			if ($arProperties["PRICE"]["VALUE"] != $csvData[5]) $PROPS["PRICE"]["VALUE"] = $csvData[5];
  			if ($arProperties["MATERIAL"]["VALUE"] != $csvData[6]) $PROPS["MATERIAL"]["VALUE"] = $csvData[6];
  			//Изменились ли поля
  			if (count($PROPS) === 0 && $arFields["NAME"] === $csvData[1] && $arFields["IBLOCK_SECTION_ID"] === $csvData[2] &&
  				$arFields["PREVIEW_TEXT"] === $csvData[3] && $arFields["DETAIL_TEXT"] === $csvData[4])
            //Если поля и свойства не изминилсь - пропустить итерацию
  					continue;

  			//Массив обновленных данных
  			$arLoadProductArray = Array(
  				"MODIFIED_BY"     	=> $USER->GetID(),
  				"IBLOCK_SECTION_ID" => $csvData[2],
  				"IBLOCK_ID"       	=> 2,
  				"PROPERTY_VALUES" 	=> $PROPS,
  				"NAME"            	=> $csvData[1],
  				"PREVIEW_TEXT"	  	=> $csvData[3],
  				"DETAIL_TEXT"		=> $csvData[4],
  			);
  			if($PRODUCT_ID = $el->Update($arFields["ID"], $arLoadProductArray))
  				echo "Update ID: ".$PRODUCT_ID;
  			else
  				echo "Error: ".$el->LAST_ERROR;
  		//Если елемент не найден, то создаем его
  		} else {
  			$PROPS = array();
  			//Поля
  			$PROPS["PRICE"]["VALUE"] 	= $csvData[5];
  			$PROPS["MATERIAL"]["VALUE"] = $csvData[6];
  			//Массив данных
  			$arLoadProductArray = Array(
  				"MODIFIED_BY"     	=> $USER->GetID(),
  				"IBLOCK_SECTION_ID" => $csvData[2],
  				"IBLOCK_ID"       	=> 2,
  				"NAME"            	=> $csvData[1],
  				"PROPERTY_VALUES" 	=> $PROPS,
  				"ACTIVE"          	=> "Y",
  				"PREVIEW_TEXT"		=> $csvData[3],
  				"DETAIL_TEXT"		=> $csvData[4],
  				"XML_ID"		  	=> $csvData[0],
  			);
  			if($PRODUCT_ID = $el->Add($arLoadProductArray))
  				echo "New ID: ".$PRODUCT_ID;
  			else
  				echo "Error: ".$el->LAST_ERROR;
  		}
  	}

  	//Обновление записи о последнем изменении файла
  	$file_last_update->putContents($file_csv->getModificationTime());
  }

  parse();



