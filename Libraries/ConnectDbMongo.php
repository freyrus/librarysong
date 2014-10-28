<?php
class ConnectDb_Mongo {
    protected $result = NULL;
    protected $db = NULL;
    function connectDb () {
        //uses base64 to get sure the string is escaped
        $base64 = base64_encode('ifrc');
        $function = "return base64_decode('" . $base64 .  "');";
        $passhost = create_function("", $function);
        /**
         * mongodb://root:ifrc@127.0.0.1:27017
         */
        try {
            return new MongoClient('mongodb://' . MONGO_USER_HOST . ':' . call_user_func($passhost) . '@' . MONGO_HOST . ':' . MONGO_PORT . '/' . MONGO_DBNAME_USER);
        } catch (MongoException $e) {
            echo 'ERROR: ' . $e->getMessage();
            exit();
        }
    }
    function connect () {
        global $connMongo;
        if (empty($connMongo) === TRUE) {
            $connMongo = $this->connectDb();
        }
        $this->db = $connMongo->selectDB(MONGO_DBNAME);
    }
    function disconnect () {
        global $connMongo;
        if (empty($connMongo) === FALSE) {
            $connMongo->close();
        }
    }
    function reconnect () {
        $this->disconnect();
        $this->connect();
    }
    function getDb () {
        return $this->db;
    }
    function getCollection ($collectionName = null) {
        if (empty($collectionName) === TRUE) {
            $collectionName = $this->collectionName;
        }
        return $this->db->selectCollection($collectionName);
    }
}
