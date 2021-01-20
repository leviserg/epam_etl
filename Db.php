<?php

	class Db {

        public $connectedDb;

        public function __construct($dbname) {
            $this->connectedDb = $dbname;
        }

        public function connect(){
            $serverName = "SERG-LEVITSKIY"; //serverName\instanceName
            $connectionInfo = array( "Database"=>$this->connectedDb, "UID"=>"leviserg", "PWD"=>"pohtefdg");
            sqlsrv_configure( 'LogSubsystems', SQLSRV_LOG_SYSTEM_OFF );
            $conn = sqlsrv_connect( $serverName, $connectionInfo);
            return $conn;
        }

        public function createTable($tblname){
            $dbconn = $this->connect();
            if (!$dbconn) {
                $errors = sqlsrv_errors();
                echo $errors[0]["message"];
                return false;
            }
            else{
                $sql = "DROP TABLE IF EXISTS ".$tblname;
                $stmt = sqlsrv_query( $dbconn, $sql );
                if( $stmt === false) {
                    $errors = sqlsrv_errors();
                    echo $errors[0]["message"];
                    $result = false;
                }else{
                    echo "Table ".$tblname." has been deleted...\n";
                    $sql = "CREATE TABLE ".$tblname."(
                        id int not null identity(1,1) primary key,
                        department_id tinyint not null, recdate datetime default getdate(),
                        power real null, raw real null, losses real null, products int null, defetcs int null,
                        idletime int check (idletime<86400), totaltime int check (totaltime<86400), efficiency real null
                    )";
                    $stmt = sqlsrv_query( $dbconn, $sql );
                    if( $stmt === false) {
                        $errors = sqlsrv_errors();
                        echo $errors[0]["message"];
                        $result = false;
                    }
                    else{
                        echo "Table ".$tblname." has been created...\n";
                        $result = true;
                    }
                }
                sqlsrv_free_stmt( $stmt);
                sqlsrv_close( $dbconn );
                return $result;
            }
        }

        public function loadData($data, $tblname){
            $dbconn = $this->connect();
            if (!$dbconn) {
                $errors = sqlsrv_errors();
                die("Error connection : ".$errors[0]["message"]);
            }
            else{
                for($i=0; $i < count($data); $i++){
                    $sql = "INSERT INTO ".$tblname." VALUES (".$data[$i]["dep_id"].", CONVERT(datetime2,'".$data[$i]["recdate"]."'),
                    ".$data[$i]["power"].",".$data[$i]["raw"].",".$data[$i]["losses"].",".$data[$i]["products"].",".$data[$i]["defects"].",
                    ".$data[$i]["idletime"].",".$data[$i]["totaltime"].",".$data[$i]["efficiency"]."
                    )";
                    $stmt = sqlsrv_query( $dbconn, $sql );
                    if( $stmt === false) {
                        $errors = sqlsrv_errors();
                        echo $sql."\n";
                        echo "Error inserting row at id = ".$data[$i]["id"].". Message : ".$errors[0]["message"]."\n";
                    }else{
                        echo "Success : Inserted row at id = ".$data[$i]["id"]."\n";
                    }
                }
                sqlsrv_free_stmt( $stmt);
                sqlsrv_close( $dbconn );
                echo "Success : Data  has been loaded to table ".$tblname."";
            }           
        }

        public function getSingleTable($tblname){
            $dbconn = $this->connect();
            if (!$dbconn) {
                return ( print_r( sqlsrv_errors(), true));
            }
            else{
                $sql = "SELECT * FROM ".$tblname;// TestTable
                $stmt = sqlsrv_query( $dbconn, $sql );
                if( $stmt === false) {
                    $ret = sqlsrv_errors();
                }else{
                    $ret = self::getSqlSrvAssocData($stmt);
                }
                sqlsrv_free_stmt( $stmt);
                sqlsrv_close( $dbconn );
                return $ret;
            }
        }
 
        public static function getSqlSrvAssocData($stmt){
            $ret = [];
            while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC) ) {
                foreach($row as $key => $keyval){
                    $temp[$key] = $keyval;
                    if(is_object($temp[$key])){
                        if(get_class($temp[$key]) == "DateTime"){
                            $temp[$key] = $keyval->format('Y-m-d H:i:s');
                        }
                    }
                }
                array_push($ret, $temp);
            }
            return $ret;
        }

    }
?>