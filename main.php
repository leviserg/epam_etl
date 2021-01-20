<?php
    ini_set('display_errors', true);

    require_once __DIR__.'/./exceltools/SimpleXLSX.php';

    if ( $xlsx = SimpleXLSX::parse('datatable.xlsx') ) {
        $extractedData = $xlsx->rows();
    } else {
        echo SimpleXLSX::parseError();
    }

    $transformedData =  transformData($extractedData);

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