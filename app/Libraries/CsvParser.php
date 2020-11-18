<?php


namespace App\Libraries;

use Iterator;

/**
 * Provide an Iterator interface to parsed lines and fields of a CSV file
 */
class CsvParser implements Iterator
{
    private $mFileHandle = null;
    private $mRowNo = 1;
    private $mParsedRow = null;

    public function __construct($fileHandle)
    {
        $this->mFileHandle = $fileHandle;
    }

    public function current() {
        return $this->mParsedRow;
    }

    public function key() {
        return $this->mRowNo - 1;
    }

    public function next() {
        $this->mRowNo++;
    }

    public function rewind() {
        $this->mRowNo = 1;
        rewind($this->mFileHandle);
    }

    public function valid() {
        if (($this->mParsedRow = fgetcsv($this->mFileHandle, 0, ",")) !== FALSE) {
            return TRUE;
        }
        return FALSE;
    }

}
