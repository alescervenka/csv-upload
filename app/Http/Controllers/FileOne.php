<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Libraries\RequestCsvParser;
use App\FileoneRow;
use Exception;

/**
 * Controller for the first CSV file
 */
class FileOne extends Controller
{
    /**
     * Store the CSV file in a database
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $parser = new RequestCsvParser($request);
        $csvIterator = $parser->getIterator();

        $unimported = array();

        foreach ($csvIterator as $item) {

            if ((count($item) == 2) and (ctype_alnum($item[0]))) {

                try {
                    FileoneRow::create(['record_id' => $item[0], 'record_name' => $item[1]]);
                } catch (Exception $e) {
                    if ($e->getCode() == 23000) {
                        // duplicate entry
                        array_push($unimported, $item);
                    } else {
                        throw($e);
                    }
                }
            } else {
                array_push($unimported, $item);
            }
        }

        return response()->json(['unimported' => $unimported], 200);
    }

    /**
     * Display the specified record.
     *
     * @param  int  $id Record ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $fileOneRecord = FileoneRow::findOrFail($id);
        return response()->json(['Record ID' => $fileOneRecord->record_id, 'Name' => $fileOneRecord->record_name], 200);
    }
}
