<?php

namespace App\Helpers;

use \App\Helpers\ValidateHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Admin;
use App\Models\AdminSettings;

class AdminHelper
{
	public static function start()
	{
		$adminSettings = DB::table('admin_settings');
		if (empty($adminSettings))
			return ['success' => false, 'msgs' => ['Не найдена таблица "admin_settings"']];

		$viewTables = DB::table('admin_settings')->where('name', 'view_tables')->first();
		if (!empty($viewTables))
			return true;

		$startData = [
			'name' => 'view_tables', 'settings' => json_encode(['users']),
			'name' => 'files', 'settings' => json_encode(['images', 'image'])
		];

		if($adminSettings->insert($startData));
			return ['success' => true];
	}

	public static function setViewTables($settings = '')
	{
		if (empty($settings))
			return ['success' => false, 'msgs' => ['Пустое поле: settings']];

		$viewTables = DB::table('admin_settings')->where('name', 'view_tables')->first();
		$viewTables->settings = json_encode($settings);

		if($viewTables->save());
			return ['success' => true];
	}

	public static function getFilesProp()
	{
		$filesProp = json_decode(DB::table('admin_settings')->where('name', 'files')->first()->settings);
		if (empty($filesProp))
			$filesProp = [];

		return ['success' => true, 'filesProp' => $filesProp];
	}

	public static function getViewTables()
	{
		$viewTables = json_decode(DB::table('admin_settings')->where('name', 'view_tables')->first()->settings);
		if (empty($viewTables))
			$viewTables = [];

		return ['success' => true, 'viewTables' => $viewTables];
	}

	public static function getViewTablesArray()
	{
		$viewTables = json_decode(DB::table('admin_settings')->where('name', 'view_tables')->first()->settings);
		if (empty($viewTables))
			return [];

		return $viewTables;
	}

	public static function getTables()
	{
		$viewTables = self::getViewTablesArray();
		$tables = [];

		$tablesName = collect(DB::connection()->select('show tables'))->map(function ($val) {
			foreach ($val as $key => $tbl) 
				return $tbl;
		});

		foreach ($tablesName as $tbName)
		{	
			if(!in_array($tbName, $viewTables))
				continue;

			$tables[$tbName]['rows'] = DB::table($tbName)->skip(0)->take(15)->get();
			$tables[$tbName]['name'] = $tbName;
			$tables[$tbName]['page'] = 1;
			$tables[$tbName]['rowsData'] = DB::select('DESCRIBE ' . $tbName);
		}

		if(!empty($tables))
			return ['success' => true, 'tables' => $tables];
		else
			return ['success' => false, 'msgs' => ['Ничего не найдено']];
	}

	public static function getTable($tableName = '', $from = '', $to = '', $page = '')
	{
		$errors = [];
		$table = [];

		$data = [
			'from' => (int) $from,
			'to' => $to,
			'page' => $page,
			'tableName' => $tableName
		];

		$errors = ValidateHelper::checkEmpty($data);
		if (!empty($errors))
			return ['success' => false, 'msgs' => $errors];

		$rowsData = json_decode(json_encode(DB::select('DESCRIBE ' . $tableName)),true);

		if (empty($rowsData))
			return ['success' => false, 'msgs' => ['Таблица не найдена']];

		foreach($rowsData as $key => $rowData)
		{
			$rowsData[$rowData['Field']] = $rowData;
			unset($rowsData[$key]);
		}

		$table['rows'] = DB::table($tableName)->skip($from)->take($to)->get();
		$table['name'] = $tableName;
		$table['page'] = $page;
		$table['rowsData'] = $rowsData;

		if(!empty($table))
			return ['success' => true, 'table' => $table];
		else
			return ['success' => false, 'msgs' => ['Ничего не найдено']];
	}

	public static function editRow($tableName = '', $rRow = '')
	{
		$errors = [];
		$bdrow = [];
		$rowsData = [];
		$row = $rRow;
		$files = [];

		$data = [
			'row' => $row,
			'tableName' => $tableName
		];

		$errors = ValidateHelper::checkEmpty($data);
		if (!empty($errors))
			return ['success' => false, 'msgs' => $errors];

		$uploads_dir = $_SERVER['DOCUMENT_ROOT'].'/uploads';

		if (!empty($_FILES["row"]))
		{
			foreach ($_FILES["row"]["error"] as $key => $error)
			{
				if ($error == UPLOAD_ERR_OK) {
					$tmp_name = $_FILES["row"]["tmp_name"][$key];
					$name = basename($_FILES["row"]["name"][$key]);
					$path = "$uploads_dir/$name";
					$row[$key] = $path;
					$files[$key] = $path;
					move_uploaded_file($tmp_name, $path);
				}
			}
		}

		$rowsData = json_decode(json_encode(DB::select('DESCRIBE ' . $tableName)),true);

		if (empty($rowsData))
			return ['success' => false, 'msgs' => ['Таблица не найдена']];

		foreach($rowsData as $key => $rowData)
		{
			$rowsData[$rowData['Field']] = $rowData;
			unset($rowsData[$key]);
		}

		if(!empty($row['id']))
			$bdrow = DB::table($tableName)->where('id','=',$row['id']);

		if (!empty($bdrow))
			return ['success' => true, 'row' => $bdrow->update($row), 'files' => $files];

		$empty = false;
		foreach ($row as $nonKey => $non)
			if(empty($non) && $rowsData[$nonKey]['Null'] == 'No' && empty($rowsData[$nonKey]['Default']) && $rowsData[$nonKey]['Extra'] != "auto_increment")
				$empty = true;

		if(!$empty)
			return ['success' => true, 'row' => DB::table($tableName)->insert($row), 'files' => $files];
		else
			return ['success' => false, 'msgs' => ['Есть пустые поля']];
	}

	public static function deleteRow($tableName = '', $row = '')
	{
		$errors = [];
		$bdrow = [];

		$data = [
			'row' => $row,
			'tableName' => $tableName
		];

		$errors = ValidateHelper::checkEmpty($data);
		if (!empty($errors))
			return ['success' => false, 'msgs' => $errors];

		if(!empty($row['id']))
			$bdrow = DB::table($tableName)->where('id','=',$row['id']);

		if (!empty($bdrow))
			if(!$bdrow->delete())
				return ['success' => false, 'msgs' => 'Не удалось изменить запись' . $deleteRow['id']];

		return ['success' => true];
	}
}