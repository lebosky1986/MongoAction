<?php
/**
 * 这个是用来直接读@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@Mongo统计数据的 基类@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
 * 这个是用来直接读@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@Mongo统计数据的 基类@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
 * 这个是用来直接读@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@Mongo统计数据的 基类@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
 * 这个是用来直接读@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@Mongo统计数据的 基类@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
 * 这个是用来直接读@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@Mongo统计数据的 基类@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019-12-12
 * Time: 21:09
 */

namespace Admin\Model;


use MongoDB\Client;


class BaseMongoModel
{
    protected $tableName = "";
    protected $collection;// = $client->selectCollection('test','test');




//db.collection.find({ "field" : { $gt: value } } ); // greater than : field > value
//db.collection.find({ "field" : { $lt: value } } ); // less than : field < value
//db.collection.find({ "field" : { $gte: value } } ); // greater than or equal to : field >= value
//db.collection.find({ "field" : { $lte: value } } ); // less than or equal to : field <= value

    /**
     * 大于
     * @param $field
     * @param $val
     * @return array
     */
    protected function compare_gt($val){
        return ['$gt'=>$val];
    }


    public function distinct($field,$where){
        $result = $this->collection->distinct($field,$where,$this->option);
        $this->option = $this->makeDefaultOp();
        return $result;
    }

    /**
     * 小于
     * @param $val
     * @return array
     */
    protected function compare_lt($val){
        return ['$lt'=>$val];
    }

    /**
     * 大等于
     * @param $val
     * @return array
     */
    protected function compare_gte($val){
        return ['$gte'=>$val];
    }

    /**
     * 小等于
     * @param $val
     * @return array
     */
    protected function compare_lte($val){
        return ['$lte'=>$val];
    }

    
    /**
     * 不等于
     * @param $val
     * @return array
     */
    protected function compare_neq($val){
    	return ['$ne'=>$val];
    }



    public function __construct(){


        //SELF_MONGO_USER
        //SELF_MONGO_PWD
        //mongodb://root:****@dds-uf638dc4908132e4118410.mongodb.rds.aliyuncs.com:3717/admin
//        define("SELF_MONGO_USER","root");
//        define("SELF_MONGO_PWD","L123456q1");
        if(defined("SELF_MONGO_USER")){
            $root = SELF_MONGO_USER;
            $pwd = SELF_MONGO_PWD;
            $uri = "mongodb://{$root}:{$pwd}@".SELF_MONGO_HOST.":".SELF_MONGO_PORT;
        }else{
            $uri = "mongodb://".SELF_MONGO_HOST.":".SELF_MONGO_PORT;
        }

//        define("SELF_MONGO_HOST","localhost");
//        define("SELF_MONGO_PORT",23717);

        //'mongodb+srv://<username>:<password>@<cluster-address>/test?retryWrites=true&w=majority'

//        $manager = new Manager("mongodb://".SELF_MONGO_HOST.":".SELF_MONGO_PORT);
        $client = (new Client($uri));
        $this->collection = $client->selectCollection(SELF_MONGO_DBNAME,$this->tableName);
        $this->option = $this->makeDefaultOp();
    }
    protected $option = [];

    /**
     * @param $field:字段
     * @param $sortType:desc,asc
     */
    public function sort($field,$sortType){
        $sortType = $sortType=="desc" ? -1 :1;
        $sort = [];
        $sort[$field] = $sortType;
        $this->option["sort"] = $sort;
        return $this;
    }

    /**
     * @param $index:起始位置0开始
     * @param $count:数量
     */
    public function limit($index,$count){
        $this->option["limit"] = $count;
        $this->option["skip"] = $index;
        return $this;
    }

    /**
     * @param string $fields: "field1,field2"
     */
    public function field($fields=""){
        $fields = explode(",",$fields);
        $newField = [];
        foreach ($fields as $k => $v){
            $newField[$v] = 1;
        }
        $this->option["projection"] = $newField;
        return $this;
    }

    //db.getCollection('user_create').find({create_log_date:"2019-12-19"}).sort({"_id":1}).limit(10)

//        array(
//            'projection' => array('id' => 1, 'age' => 1, 'name' => -1), // 指定返回哪些字段 1 表示返回 -1 表示不返回
//            'sort' => array('id' => -1), // 指定排序字段
//            'limit' => 10, // 指定返回的条数
//            'skip' => 0, // 指定起始位置
//        );


//    public function where(){
//
//    }

    /**
     * @param array $where
     * @param $option :$options = array(
    'projection' => array('id' => 1, 'age' => 1, 'name' => -1), // 指定返回哪些字段 1 表示返回 -1 表示不返回
    'sort' => array('id' => -1), // 指定排序字段
    'limit' => 10, // 指定返回的条数
    'skip' => 0, // 指定起始位置
    );
     * @return array
     */
    public function select($finter=[]){

        $data = $this->collection->find($finter,$this->option)->toArray();
        $this->option = $this->makeDefaultOp();
        return $data;
    }

    private function makeDefaultOp(){
        $this->option = [];
        $this->option["noCursorTimeout"] = true;
        $this->option["useCursor"] = true;
        $this->option["batchSize"] = 20000;
        return $this->option;
    }

    public function deleteWhere($where){
        $data = $this->collection->deleteMany($where);
        return $data;
    }

    /**
     * @param array $where
     * @return array|null|object
     */
    public function findOne($finter=[]){


        $data = $this->collection->findOne($finter);
        return $data;
    }

    public function count($finter=[]){

        return $this->collection->count($finter);
    }


    public function sum($fields,$finter=[]){
    	//print_r($fields);
    	//print_r($finter);
        $match = [
            '$match'=>$finter
        ];
        $group = [
            '$group'=>[
                '_id'=>null,
            ]
        ];

        $fields = explode(',',$fields);
        foreach($fields as $k => $field){
            $group['$group'][$field] = ['$sum'=>'$'.$field];
        }
        if($finter && sizeof($finter)>0){
            $result = $this->collection->aggregate([
                $match,
                $group
            ]);
        }else{
            $result = $this->collection->aggregate([
                $group
            ]);
        }
        //print_r($result);
        $result = $result->toArray();
        return $result[0];

//        /**



//        $match = [
//            '$match'=>$finter
//        ];
//
//
//        $group = [
//            '$group'=>[
//                '_id'=>null,
//                'win_gold'=>['$sum'=>'$win_gold'],
//                'spend_gold'=>['$sum'=>'$spend_gold']
//            ]
//
//        ];
//
//        $result = $this->collection->aggregate([
//            $match,
//            $group
//        ]);
//        print_r($result->toArray());exit;

    }







}