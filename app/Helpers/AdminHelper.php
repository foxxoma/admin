<?php

namespace App\Helpers;

use \App\Helpers\ValidateHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Admin;

class AdminHelper
{
	public static function getTables()
	{
		$tables = [];

		$tablesName = collect(DB::connection()->select('show tables'))->map(function ($val) {
			foreach ($val as $key => $tbl) {
				return $tbl;
			}
		});

		foreach ($tablesName as $tbName)
		{
			$tables[$tbName]['rows'] = DB::table($tbName)->skip(0)->take(15)->get();
			$tables[$tbName]['name'] = $tbName;
			$tables[$tbName]['page'] = 1;
			$tables[$tbName]['rowsName'] = DB::getSchemaBuilder()->getColumnListing($tbName);
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

		$table['rows'] = DB::table($tableName)->skip($from)->take($to)->get();
		$table['name'] = $tableName;
		$table['page'] = $page;
		$table['rowsName'] = DB::getSchemaBuilder()->getColumnListing($tableName);

		if(!empty($table))
			return ['success' => true, 'table' => $table];
		else
			return ['success' => false, 'msgs' => ['Ничего не найдено']];
	}

	public static function editRow($tableName = '', $row = '')
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
			return ['success' => true, 'row' => $bdrow->update($row)];

		$empty = false;
		foreach ($row as $nonKey => $non)
			if(empty($non) && $nonKey != 'id' && $nonKey != 'created_at' && $nonKey != 'updated_at'&& $nonKey != 'remember_token' && $nonKey != 'email_verified_at')
				$empty = true;

		if(!$empty)
			return ['success' => true, 'row' => $table->insert($row)];
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