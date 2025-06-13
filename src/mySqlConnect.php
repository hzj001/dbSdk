<?php

/***
 * PDO查询
 * @param fun   $stmt           执行SQL语句
 * @param fun   $res_row        获取影响行数
 * @param fun   $data           获取数据
 * TOOO
 * $conn == null (如果数据库未连接上则终止程序继续往下执行，并由数据库异常返回错误代码) 
 */

$dataBaseMysqlExcMsg = null;

class Pdo_Database_Mysql{
    
    var $excMsg;
    
    private $dbConn;
    private $host;
    private $db_connect_fun;
    public function __construct($host,$db) {
        $this->host = $host;
        $this->db_connect_fun = $this->db_connect_fun($host,$db);
    }
    
    /**
     * @TODO 创建连接数据库[函数]
     * @param array $args
     * @return \PDO
     */
    private function db_connect_fun(...$args){

      try {
            $hotsInfo   = $args['0']; 
            $db         = $args['1'];
            $host       = $hotsInfo["host"];
            $port       = $hotsInfo["port"];            
            $user       = $hotsInfo["user"];
            $password   = $hotsInfo["password"];    
            
            $dns        = "mysql:dbname=$db;host=$host;port=$port";#
            $conn       = new \PDO($dns,$user,$password);

            //设置PDO错误模式为异常
            $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $conn->exec("set names utf8"); 
            $this->dbConn = $conn;
        } catch (Exception $exc) {
            $this->error_dispose($exc, __FUNCTION__);
            repose::return_Data('3001', ' DB Connect Failed! ');
            exit();
      }
      
    }
    
    /**
     * @TODO 执行储存过程
     * @param array $args
     * @return array
     */
    public function Pdo_sql_produ(...$args){
        
        try {
                $sql        = $args[0];
                $selectData = $args[1];
                $conn = $this->dbConn;
                if($conn === null){
                    set_error_handler("customerror");
                    exit();
                }
                    $stmt = $conn->prepare($sql);
                    $stmt->execute($selectData);
                    $data   = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if($stmt){
                    $resultArr = ["data"=>$data,"errcode"=>5000];
                }else{
                    $resultArr = ['data'=>$data,'errcode'=>5001];
                       
                }   
        } catch (PDOException $exc) {
            $this->error_dispose($exc, __FUNCTION__);
            $resultArr =  ['data'=>[],'res_row'=>'','errcode'=>2009];
        } 
        return $resultArr;
        
    }
    
    /**
     * @TODO 事务提交
     * @param array $args
     * @return array
     */    
    public function Pdo_sql_Transaction(...$args){
        
        $conn = $this->dbConn;
        try {
            $sql        = $args[0];
            $insertData = $args[1];
            //关闭自动提交
            $conn-> setAttribute(PDO::ATTR_AUTOCOMMIT, 0);
            if($conn == null){
                set_error_handler("customerror");
                exit();
            }
            $conn->beginTransaction();
            
            $insertId = [];
            $sqlCount = (count($sql)-1);
            for ($i=0;$i<= $sqlCount;$i++){
                $stmt           = $conn->prepare($sql[$i],array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));//定义批处理//
                $stmtExecute    = $stmt->execute($insertData[$i]);//调用批处理执行插入数据，Array
                $insertId[$i]   = $conn->lastInsertId();//获取插入数据所影响的ID
            }
            $commit      = $conn->commit();
            
            if($commit){
                $result_arr = ['data'=>$stmtExecute, 'res_row'=>$insertId, 'errcode'=>5000];
            }else {
                $result_arr = ['data'=>'', 'res_row'=>$insertId, 'errcode'=>5001];
            }
        } catch (PDOException $exc) {
            $this->error_dispose($exc, __FUNCTION__);
            $conn->rollBack();
            $result_arr = ['data'=>array('事务提交失败'),'res_row'=>'','errcode'=>2009];
        }
        return $result_arr;
        
    }
    
    /**
     * @TODO 执行INSERT语句函数
     * @param array $args
     * @return array
     */      
    public function Pdo_sql_Insert(...$args){
        
        try {
            $sql        = $args[0];
            $insertData = $args[1];
            $conn = $this->dbConn;
            if($conn == null){
                set_error_handler("customerror");
                exit();
            }

            $stmt       = $conn->prepare($sql,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));//定义批处理
            $stmt->execute($insertData);//调用批处理执行插入数据，Array                     
            $insertId   = $conn->lastInsertId();//获取插入数据所影响的ID
            if($insertId>=1 || $stmt){
                $result_arr = ['data'=>$insertId,'res_row'=>'','errcode'=>5000];
            }else {
                $result_arr = ['data'=>'','res_row'=>'','errcode'=>5001];
            }
            
        } catch (PDOException $exc) {
            $this->error_dispose($exc, __FUNCTION__);
            $result_arr = ['data'=>array('添加-失败'),'res_row'=>'','errcode'=>2009];
        }
        return $result_arr;
        
    }

    /**
     * @TODO 执行UPDATE语句函数
     * @param array $args
     * @return array
     */          
    public function Pdo_sql_Update(...$args){
        
        try {
            $sql        = $args[0];
            $insertData = $args[1];
            $conn = $this->dbConn;
            if($conn == null){
                set_error_handler("customerror");
                exit();
            }

            $stmt        = $conn->prepare($sql,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));//定义批处理
            $stmt->execute($insertData);//调用批处理执行插入数据，Array                     
            $updateRow   = $stmt->rowCount();//获取  修改||删除 所影响的行数
            if($updateRow>=1 && $stmt){
                $result_arr = ['data'=>[],'res_row'=>$updateRow,'errcode'=>5000];
            }else {
                $result_arr = ['data'=>[],'res_row'=>$updateRow,'errcode'=>5001];  
            }
            
        } catch (PDOException $exc) {
            $this->error_dispose($exc, __FUNCTION__);
            $result_arr =  ['data'=>array(),'res_row'=>'','errcode'=>2009];
        }
        return $result_arr;
        
    }
    
    /**
     * @TODO 执行SELECT语句函数
     * @param array $args
     * @return array
     */        
    public function Pdo_sql_Select(...$args){
        
        try {
                $sql        = $args[0];
                $selectData = $args[1];
                $selectDataCount = count($selectData);

                $conn = $this->dbConn;
                if($conn == null){
                    set_error_handler("customerror");
                    exit();
                }

                    $stmt       = $conn->prepare($sql);
                for($i=1;$i<=$selectDataCount;$i++){
                    $stmt->bindParam($i, $selectData[$i]);
                }
                    $stmt->execute();
                
                    $res_row    = $stmt->rowCount();
                    $data       = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if(count($data)<=0 && $res_row<=0){
                     $result_arr = ['data'=>array(),'res_row'=>$res_row,'errcode'=>2008];
                }else if($res_row >= 1){
                     $result_arr = ['data'=>$data,'res_row'=>$res_row,'errcode'=>2000];
                }   
        } catch (PDOException $exc) {
            $this->error_dispose($exc, __FUNCTION__);
            $result_arr = ['data'=>array(),'res_row'=>'','errcode'=>2009];
        } 
        return $result_arr;
        
    }
    
    /**
     * 错误信息处理
     * @param type $exc
     * @param type $funName
     */
    private function error_dispose($exc, $funName){
        
        $excMsg = $exc->getMessage();
        $this->excMsg = $excMsg;
        $GLOBALS["dataBaseMysqlExcMsg"] = json_encode(["fun"=> $funName, "excMsg"=>$excMsg], JSON_UNESCAPED_UNICODE);
               
    }


    public function __destruct() {
        $this->dbConn = null;
        unset($this->dbConn); #  释放数据库连接
    }
}