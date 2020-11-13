<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Libraries\RequestCsvParser;
use App\FileoneRow;
use Exception;

class FileOne extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        //Log::info('POST was called on the FileOne controller '. $request->fullUrl());
        $parser = new RequestCsvParser($request);
        $csvIterator = $parser->getIterator();

        $unimported = array();

        foreach ($csvIterator as $item) {
            //Log::info('polozka "' .  implode('", "', $item) . '"');

            if ((count($item) == 2) and (ctype_alnum($item[0]))) {
                //Log::info('inserting');
                try {
                    FileoneRow::create(['record_id' => $item[0], 'record_name' => $item[1]]);
                } catch (Exception $e) {
                    if ($e->getCode() == 23000) {
                        array_push($unimported, $item);
                    } else {
                        error_log($e->getCode());
                        error_log($e->getFile());
                        error_log($e->getMessage());
                        throw($e);
                    }

                }
            } else {
                //Log::info('goes to unimported');
                array_push($unimported, $item);
            }
        }

        return response()->json(['unimported' => $unimported], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $fileOneRecord = FileoneRow::findOrFail($id);
        return response()->json(['Record ID' => $fileOneRecord->record_id, 'Name' => $fileOneRecord->record_name], 200);
    }
}
