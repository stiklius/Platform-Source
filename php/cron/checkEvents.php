<?php

// THIS FILE IS RUN BY SERVER CRON EVERY 10 MINUTES TO UPDATE THE PLATFORM DATABASE
// WITH THE DONATIONS OF TOKENS FROM THE CONTRACT 

//Database Configs
$servername = "localhost"; //server name or ip
$username = "root"; //database user
$password = "toor"; // database pass
$dbname = "tribe_token_platform"; //database name

// Create connection to database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection has been successfull
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// Get the last transaction that was stored in database from the block file + 1
$block = hexdec(file_get_contents("block"));

//set variables for the Etherscan API ( CURRENTLY TESTNET ONLY)
$APIkey = "API KEY HERE";
$CONTRACTaddress = "CONTRACT ADDRESS HERE";
$url = "https://ropsten.etherscan.io/api?module=logs&action=getLogs&fromBlock=". $block ."&toBlock=latest&address=". $CONTRACTaddress ."&apikey=" . $APIkey;
// Call the Etherscan API
$data = file_get_contents($url);
// Decode the json into an array
$json = json_decode($data, true);

//Create a loop for each of the results
foreach($json['result'] as $item){
	//Remove the first 2 characters from the data
	$solid = substr($item['data'], 2);
	//Split the data into 2 chunks
	$part = split_at($solid, 64);
	
	//store the transaction number in the block file
	file_put_contents("block", $item['blockNumber']);
	
	//Create the variable for database insertion
	$sql = "INSERT INTO donations (project, value, block) VALUES ('". hexdec($part[0]) ."', '". hexdec($part[1]) ."', '". $item['blockNumber'] ."')";
	//Check if the query was successfull and error out if not
	if ($conn->query($sql) === TRUE) {
		echo "New record created successfully";
	} else {
		echo "Error: " . $sql . "<br>" . $conn->error;
	}


}
//close database connection
$conn->close();

// split function for the string
function split_at($string, $num) {
$length = strlen($string);
$output[0] = substr($string, 0, $num);
$output[1] = substr($string, $num, $length );
return $output;
}
?>