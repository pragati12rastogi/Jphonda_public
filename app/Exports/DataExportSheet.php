<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class DataExportSheet implements  WithMultipleSheets
{
	private $data;
	private $column;
	private $sheets;
	public function __construct($data,$column=[],$sheets)
	{
		$this->data = $data;
		$this->column = $column;
		$this->sheets = $sheets;
	}

    public function sheets(): array
    {
        $sheets = [];

        for ($i = 0; $i < count($this->sheets); $i++) {
        	$sheet = $this->sheets[$i];
        	$data = $this->data[$sheet];
        	$column = $this->column[$sheet];
            $sheets[] = new DataExport($data,$column,$sheet);
        }

        return $sheets;
    }
}
