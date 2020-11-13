<?php


namespace App\Libraries;


use Iterator;

class CsvParser implements Iterator
{
    private $mFileHandle = null;
    private $rowNo = 1;
    private $mParsedRow = null;


    public function __construct($fileHandler)
    {
        $this->mFileHandle = $fileHandler;
    }

    public function current() {
        return $this->mParsedRow;
    }

    public function key() {
        return $this->rowNo - 1;
    }

    public function next() {
        $this->rowNo++;
    }

    public function rewind() {
        $this->rowNo = 1;
        rewind($this->mFileHandle);
    }

    public function valid() {
        if (($this->mParsedRow = fgetcsv($this->mFileHandle, 0, ",")) !== FALSE) {
            return TRUE;
        }
        return FALSE;
    }

}
