<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;

use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithTitle;
class DataExport implements FromCollection, WithHeadings, ShouldAutoSize, WithEvents,WithTitle
{
	private $data;
	private $column;
	private $title;
	public function __construct($data,$column=[],$title="")
	{
		$this->data = $data;
		$this->column = $column;
		$this->title = $title; 
	}
    public function collection()
    {
        return $this->data;
    }

    public function headings() : array
    {
        return $this->column;
    }
	public function title(): string
    {
        return $this->title;
    }
    public function registerEvents(): array
	{
	    return [
	        AfterSheet::class    => function(AfterSheet $event) {
	        	
	            // All headers - set font size to 14
	            $cellRange = 'A1:FF1'; 
	            $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setSize(14);

	            $styleArray = array(
				    'alignment' => array(
				        'wrapText' => true
				    )
				);
				$cellRange = 'B1:B'.$event->sheet->getDelegate()->getHighestRow();
	            $event->sheet->getDelegate()->getStyle($cellRange)->applyFromArray($styleArray);
	        },
	    ];
	}
}
