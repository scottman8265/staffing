<?php
    /**
     * Created by PhpStorm.
     * User: Scott
     * Date: 6/18/2018
     * Time: 6:41 AM
     */

    /**  Define a Read Filter class implementing \PhpOffice\PhpSpreadsheet\Reader\IReadFilter  */
    class MyReadFilter implements \PhpOffice\PhpSpreadsheet\Reader\IReadFilter
    {
        private $startRow = 0;
        private $endRow   = 0;
        private $columns  = [];

        /**  Get the list of rows and columns to read  */
        public function __construct($startRow, $endRow, $columns) {
            $this->startRow = $startRow;
            $this->endRow   = $endRow;
            $this->columns  = $columns;
        }

        public function readCell($column, $row, $worksheetName = '') {
            //  Only read the rows and columns that were configured
            if ($row >= $this->startRow && $row <= $this->endRow) {
                if (in_array($column,$this->columns)) {
                    return true;
                }
            }
            return false;
        }
    }

