<?php


namespace App\Libraries;

use Illuminate\Http\Request;
use App\Libraries\CsvParser;

class RequestCsvParser
{

    private $mFileHandle = null;

    /**
     * Create the Request CSV parser from the request
     * The request must either be a JSON with the 'data' field or a POSTed file called 'data'
     * @param Request $request The request to be searched and parsed as a CSV
     */
    public function __construct(Request $request)
    {
        $this->openHandle($request);
    }

    private function openHandle(Request $request) {
        if ($request->isJson()) {
            if ($request->has('data')) {
                $tenMBs = 10 * 1024 * 1024;
                $this->mFileHandle = fopen("php://temp/maxmemory:$tenMBs", 'r+');
                fputs($this->mFileHandle, $request->get("data"));
            } else {
                abort(400, 'The JSON request does not contain the data field');
            }
        } else {
            if ($request->hasFile('data')) {
                error_log('this one is a POST with the data file');
            } else {
                error_log('this one is a POST without the data file');
            }
            abort(400, 'stay tuned for more');
        }
    }

    public function getIterator() {
        rewind($this->mFileHandle);
        return new CsvParser($this->mFileHandle);
    }

    public function __destruct() {
        if (!is_null($this->mFileHandle)) {
            fclose($this->mFileHandle);
            $this->mFileHandle = null;
        }
    }
}
