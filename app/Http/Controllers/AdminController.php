<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Admin;

use \App\Helpers\AdminHelper;

class AdminController extends Controller
{
	public function getTables(Request $request)
	{
		return AdminHelper::getTables();
	}

	public function getTable(Request $request)
	{
		return AdminHelper::getTable($request->tableName, $request->from, $request->to, $request->page);
	}

	public function editRow(Request $request)
	{
		return AdminHelper::editRow($request->tableName, $request->row);
	}

	public function deleteRow(Request $req)
	{
		return AdminHelper::deleteRow($request->tableName, $request->row);
	}
}
