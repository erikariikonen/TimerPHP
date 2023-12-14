<?php

// Enable CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");
    exit;
}

// Add these headers to allow cross-origin requests
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Include Azure Storage SDK
require_once __DIR__ . '/vendor/autoload.php';

use MicrosoftAzure\Storage\Table\TableRestProxy;
use MicrosoftAzure\Storage\Table\Models\Entity;

// Retrieve data from the POST request
$data = file_get_contents("php://input");
$json_data = json_decode($data, true);

// Log the received data
error_log("Received data: " . print_r($json_data, true));

// Your Azure Storage account credentials
$accountName = 'timerappsalpaus';
$accountKey = 'c0sk8pretAZQPmzbShN3GVC8dfwT5ovNXvfhQt81TY1tzGvuF1S5rjNA0gyZ4X5HYUjxdeTkCjRz+ASttqfoOg==';

// Your Azure Table Storage table name
$tableName = 'time';

// Determine the partition key and row key based on your data
$partitionKey = date('Y-m-d');
$rowKey = uniqid();

// Create Table REST proxy
$tableRestProxy = TableRestProxy::createTableService(
    "DefaultEndpointsProtocol=https;AccountName=$accountName;AccountKey=$accountKey"
);

// Create an entity object
$entity = new Entity();
$entity->setPartitionKey($partitionKey);
$entity->setRowKey($rowKey);
$entity->addProperty('Action', 'Edm.String', isset($json_data['action']) ? $json_data['action'] : null);
$entity->addProperty('TableName', 'Edm.String', isset($json_data['tableName']) ? $json_data['tableName'] : null);
$entity->addProperty('CarNumber', 'Edm.String', isset($json_data['title']) ? $json_data['title'] : null);
$entity->addProperty('Time', 'Edm.String', isset($json_data['timestamp']) ? $json_data['timestamp'] : null);
try {
    // Insert the entity into the table
    $tableRestProxy->insertEntity($tableName, $entity);
    error_log("Data inserted into Azure Table Storage successfully.");
} catch (\Exception $e) {
    error_log("Error inserting data into Azure Table Storage: " . $e->getMessage());
}

// Respond with a success message (or any response you need)
$response = array("status" => "success");
echo json_encode($response);
?>
