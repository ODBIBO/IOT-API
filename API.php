<?php
try {
$pdo = new PDO('mysql:host=127.0.0.1:3307;dbname=dataBaseName', 'userName', 'password');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}

catch (PDOException $e) {
  print_r($e);
}
//$pdo->setAttribute(PDO::MYSQL_ATTR_INIT_COMMAND, "SET NAMES 'utf8'");

// Abfrage, welche Request Methode
if ($_SERVER['REQUEST_METHOD'] == "GET") {
    
    if ($_GET['url'] == "user") {
       echo "Get User"; 
        
    }
    else if ($_GET['url'] == "data") {
       echo "Get Data";
	   
		//Alles aus DB Data auslesen und im JSON Format ausgeben
	   try {
		$statement = $pdo->prepare("SELECT `chipid`,`timestmp`,`temp`,`humi`,`pres`, `co2`,`voc` FROM `Data`");
		$statement->execute();

		echo "<table>";
		echo "<tr>";
		echo "<td> CHIP-ID </td>";
		echo "<td> TIMESTAMP </td>";
		echo "<td> Temperatur (Â°C) </td>";
		echo "<td> Luftfeutigkeit (%)</td>";
		echo "<td> Luftdruck (hpa)</td>";
		echo "<td> CO-2 (ppm)</td>";
		echo "<td> VOC (ppb)</td>";
		echo "</tr>";
		
		while($row = $statement->fetch()) {
			echo "<tr>";
			echo "<td>",$row['chipid'],"</td>";
			echo "<td>",$row['timestmp'],"</td>";
			echo "<td>",$row['temp'],"</td>";
			echo "<td>",$row['humi'],"</td>";
			echo "<td>",$row['pres'],"</td>";
			echo "<td>",$row['co2'],"</td>";
			echo "<td>",$row['voc'],"</td>";
			echo "</tr>";
		}
		echo "</table>";
		echo "end of table";
		}
		
		catch (PDOException $e) {
				print_r($e);
		}
	}
}
	
else if ($_SERVER['REQUEST_METHOD'] == "POST") {

    if ($_GET['url'] == "user") {
        echo "Post Users"; 
        $postbody = file_get_contents("php://input");
        $postbody = json_decode($postbody);
        print_r ($postbody);
		
		$user = get_object_vars($postbody);
		print_r ($user);
        
    }
    else if ($_GET['url'] == "data") {
       echo "Post Data";
	   
		// Request Text im JSON-Format  in der Variablen speichern
        $postbody = file_get_contents("php://input");
        $postbody = json_decode($postbody);
        print_r ($postbody);
		
		//Aus DB User die ID auslesen und als authid laden
		
		try {
		$statement = $pdo->prepare("SELECT ID FROM User WHERE token=:token");
		$statement->bindParam(':token',$postbody->token);
		$statement->execute();
		$authid = $statement->fetchAll();
		
		}
		catch (PDOException $e) {
				print_r($e);
		}
		//Wenn ID fÃ¼r token vorhanden, dann Parameter aus Postbody zuordnen und into DB senden
		
		if (count($authid) == 1) {
		
			try {
				$statement = $pdo->prepare("INSERT INTO Data (authid, chipid, temp, humi, pres, co2, voc) VALUES (:authid, :chipid, :temp, :humi, :pres, :co2, :voc)");
				
				$statement->bindParam(':authid', $authid[0][0]);
				$statement->bindParam(':chipid', $postbody->chipid);
				$statement->bindParam(':temp',$postbody->temp);
				$statement->bindParam(':humi',$postbody->humi);
				$statement->bindParam(':pres',$postbody->pres);
				$statement->bindParam(':co2',$postbody->co2);
				$statement->bindParam(':voc',$postbody->voc);
				$statement->execute();
				http_response_code(200);
			}
				
			catch (PDOException $e) {
				print_r($e);
			}
		}
		else {
			echo "no valid user";
			http_response_code(401);
		}
			

		
    
	}	
}

else if ($_SERVER['REQUEST_METHOD'] == "DELETE") {

    if ($_GET['url'] == "user") {
		echo "Delete by User by Token"; 
        http_response_code(200);
    }
    else if ($_GET['url'] == "data") {
		echo "Post Data";
		http_response_code(200);
    }
		
}

else {
echo "Request not defined";
http_response_code(405);
}
	
	
?>
//end
