<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use App\Models\QueryLog;

class QueryController extends Controller
{

    public function __contstruct(){
	$this->middleware('auth');
	$this->middleware('permission:sql-query|export-excel|export-json', ['only' => ['index']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return view('query.index');
    }

    /**
     * ajax query.
     */
    public function store(Request $request)
    {
	    $sql = $request->input('sql');
	    
	    if(is_null($sql)){
	        return response()->json([
                   'status' => false,
                   'msg' => 'The Raw SQL field is requied.'
                ]);
	    }
	    
	    if(!preg_match('/^select/i', $sql)){
	        return response()->json([
                   'status' => false,
                   'msg' => 'Non query statements are not supported.'
                ]);
	    }

	    try{
	        $sql = $request->input('sql');
		$result = DB::select($sql);

		if($request->has('excel')){
			$resultToArray = json_decode(json_encode($result), true);
			return (new Collection($resultToArray))->downloadExcel('sql-query.xlsx');
		}elseif($request->has('json')){
			$jsonData = json_encode($result);
			$file = public_path(time().'-sql-query.json');
			file_put_contents($file, $jsonData);
			return response()->download($file);
		}

		$columns = array_keys(json_decode(json_encode($result[0]), true));
                
		QueryLog::create([
		    'user' => auth()->user()->name,
		    'sql' => $sql
		]);

		return response()->json([
                   'status' => true,
		   'data' => $result,
		   'columns' => $columns
                ]);
	    }catch(QueryException $ex){
		QueryLog::create([
                    'user' => auth()->user()->name,
		    'sql' => $sql,
		    'error' => $ex->getMessage()
                ]);

	        return response()->json([
                   'status' => false,
                   'msg' => $ex->getMessage()
                ]);
	    }

    }

    public function destroy(string $id)
    {
        //
    }
}
