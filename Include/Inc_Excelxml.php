<?php
/**
 * how to use:
 */
/*
$xls = new Inc_Excelxml;
$content = array();
$title = array('a', 'b');
$body = array('c', 'c');
$content[] = $title;
$content[] = $body;
$arrColumnWidthSheet1 = array(NULL);
foreach ($title as $item) {
    $arrColumnWidthSheet1[] = 150;
}
unset($arrColumnWidthSheet1[0]);
$arrColumnWidth = array();
$arrColumnWidth['Sheet1'] = $arrColumnWidthSheet1;
$content = array("Sheet1" => $content);
$xls->setWorksheetTitle($content);
$xls->generateXMLwithColumnWidth ("ExportTrackMember", $arrColumnWidth);
 */
class Inc_Excelxml {
    /**
     * Header of excel document (prepended to the rows)
     *
     * Copied from the excel xml-specs.
     *
     * @access private
     * @var string
     */
    private $header =  '<?xml version="1.0" encoding="UTF-8"?>
                        <Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
                            xmlns:x="urn:schemas-microsoft-com:office:excel"
                            xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
                            xmlns:html="http://www.w3.org/TR/REC-html40">';

    /**
     * Footer of excel document (appended to the rows)
     *
     * Copied from the excel xml-specs.
     *
     * @access private
     * @var string
     */
    private $footer = '</Workbook>';

     /**
     * Worksheet title
     *
     * Contains the title of a single worksheet
     *
     * @access private
     * @var array
     */
    public $worksheet_title = array();

     /**
     * Worksheet data
     *
     * Contains the data of a single worksheet
     *
     * @access private
     * @var array
     */
    public $worksheet_data = array();

    /**
     * Add a single row to the $document string
     *
     * @access private
     * @param array 1-dimensional array
     * @todo Row-creation should be done by $this->addArray
     */
    public function addRow ($array) {
        // initialize all cells for this row
        $cells = "";
        // foreach key -> write value into cells
        foreach ($array as $k => $v) {
            $cells .= "<Cell>";
            if (isset($v)) {
                $cells .= '<Data ss:Type="String">' . ($v) . '</Data>';
            }
            $cells .= "</Cell>\n";
        }

        // transform $cells content into one row
        return  "<Row>\n" . $cells . "</Row>\n";

    }
    /**
     * Set the worksheet title
     *
     * Checks the string for not allowed characters (:\/?*),
     * cuts it to maximum 31 characters and set the title. Damn
     * why are not-allowed chars nowhere to be found? Windows
     * help's no help...
     *
     * @access public
     * @param string $title Designed title
     */
    public function setWorksheetTitle ($title) {
        foreach ($title as $titlename => $data) {
            // strip out special chars first
            $titlename = preg_replace ("/[\\\|:|\/|\?|\*|\[|\]]/", "", $titlename);

            // now cut it to the allowed length
            $this->worksheet_title[] = substr ($titlename, 0, 31);

            $strData = array();
            foreach($data as $k => $v):
                $strData[] = $this->addRow ($v);
            endforeach;
            $this->worksheet_data[] = $strData;
        }
        // set title
    }
    /**
     * Generate the excel file
     *
     * Finally generates the excel file and uses the header() function
     * to deliver it to the browser.
     *
     * @access public
     * @param string $filename Name of excel file to generate (...xls)
     */
    function generateXML ($filename) {
        // deliver header (as recommended in php manual)
        header("Content-Type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: inline; filename=\"" . $filename . ".xls\"");
        header("Content-Transfer-Encoding: binary ");

        // print out document to the browser
        // need to use stripslashes for the damn ">"
        echo stripslashes ($this->header);
        foreach($this->worksheet_title as $key => $titleSheet){
            echo "\n<Worksheet ss:Name=\"" . $titleSheet . "\">\n<Table>\n";
            echo "<Column ss:Index=\"1\" ss:AutoFitWidth=\"0\" ss:Width=\"110\"/>\n";
            echo implode ("\n", $this->worksheet_data[$key]);
            echo "</Table>\n</Worksheet>\n";
        }
        echo $this->footer;
    }
    function generateXMLwithColumnWidth($filename, $arrColumnWidth) {
        // deliver header (as recommended in php manual)
        header('Content-Encoding: UTF-8');
        header("Content-Type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: inline; filename=\"" . $filename . ".xls\"");
        header("Content-Transfer-Encoding: binary ");
        // print out document to the browser
        // need to use stripslashes for the damn ">"
        echo stripslashes ($this->header);
        foreach($this->worksheet_title as $key => $titleSheet){
            echo "\n<Worksheet ss:Name=\"" . $titleSheet . "\">\n<Table>\n";
            foreach($arrColumnWidth[$titleSheet] as $keyindex =>$width ){
            echo "<Column ss:Index=\"".$keyindex."\" ss:AutoFitWidth=\"0\" ss:Width=\"".$width."\"/>\n";
                        }
            echo implode ("\n", $this->worksheet_data[$key]);
            echo "</Table>\n</Worksheet>\n";
        }
        echo $this->footer;
    }
}