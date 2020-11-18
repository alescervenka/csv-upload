<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Libraries\RequestCsvParser;
use App\FiletwoRow;
use Exception;

/**
 * Controller for the second CSV file
 */
class FileTwo extends Controller
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
            if ((count($item) == 4) and (ctype_alnum($item[0]))) {

                try {
                    FiletwoRow::create(
                        ['record_id' => $item[0],
                         'record_date' => $item[1],
                         'event_name' => $item[2],
                         'number_of_events' => $item[3]
                        ]);
                } catch (Exception $e) {
                    if ($e->getCode() == 23000) {
                        // duplicate entry
                        array_push($unimported, $item);
                    } elseif ($e->getCode() == 22007) {
                        // invalid datetime
                        array_push($unimported, $item);
                    } elseif (get_class($e) == 'Carbon\Exceptions\InvalidFormatException') {
                        //Failed to parse time string
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
     * Display the specified record
     *
     * @param  int  $id Record ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $retVal = array();

        $f2Records = FiletwoRow::where('record_id', $id)->get()->sort();

        foreach ($f2Records as $r) {
            $record = [
                'Record ID' => $id,
                'Name' => $r->file1->record_name,
                'Date' => $r->record_date->format('n/j/Y'),
                'EventName' => $r->event_name,
                'NumberOfEvents' => $r->number_of_events
            ];
            array_push($retVal, $record);
        }

        return response()->json($retVal, 200);
    }
}
