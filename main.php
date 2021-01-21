<?php
    ini_set('display_errors', true);

    require_once __DIR__.'/./exceltools/SimpleXLSX.php';
    echo "Enter data source file name:\n";
    fscanf(STDIN, "%s", $filename); // 'datatable.xlsx'

    if ( $xlsx = SimpleXLSX::parse($filename) ) {
        $extractedData = $xlsx->rows();
        echo "Success : data has been extracted...\n";        
        $transformedData =  transformData($extractedData);
        echo "Success : data has been transfomed...\n"; 
        require "Db.php";
        echo "Enter database name:\n";
        fscanf(STDIN, "%s", $dbname);
        $db = new Db($dbname);
        if($db->connect()){
            echo "Connection established...\nEnter table name:\n";
            fscanf(STDIN, "%s", $tblname);
            if($db->createTable($tblname)){
                $db->loadData($transformedData, $tblname);
            }
        }
        else{
            echo "Error Database Connection.\n";
            print_r( sqlsrv_errors(), true);
        }
    } else {
        echo "Couln't read data source file ".$filename.": \n";
        echo SimpleXLSX::parseError();
    }

function transformData($data){
    $ret = [];
    $keys = $data[0];
    for($i = 1; $i<count($data); $i++){
        $temp = [];
        for($j = 0; $j < count($keys); $j++){
            $temp[$keys[$j]] = $data[$i][$j];
        }
        array_push($ret, $temp);
    }
    return $ret;
}

?>
