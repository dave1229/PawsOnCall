<?php
	include '../authenticate.php';
	session_start();
	$session = $_SESSION['session'];
	$auth = json_decode(authenticate(urldecode($session)), true);


	if ($auth['error'] == 'auth error' || !$auth['admin']) {
	    $error = array('error' => 'auth error');
	    echo json_encode($error);
	    echo "<script>window.location.replace('../login.html');</script>";
	}else{
		include '../dbconnect.php';
		if ($db->connect_error){
		    die("Can't connect");
		}else{
			$dogsFile = fopen("/tmp/dogs.csv","w") or die("Unable to open file!");
			$volunteerFile = fopen("/tmp/volunteers.csv","w") or die("Unable to open file!");
			$litterFile = fopen("/tmp/litters.csv","w") or die("Unable to open file!");
			$dogUpdatesFile = fopen("/tmp/dogUpdates.csv","w") or die("Unable to open file!");
			$litterUpdatesFile = fopen("/tmp/litterUpdates.csv","w") or die("Unable to open file!");
			$temperatureFile = fopen("/tmp/temperatures.csv","w") or die("Unable to open file!");

			//Download Dogs Table and write to CSV
			$SQL = "SELECT * FROM Dogs";
			if($dogs = $db->query($SQL)){
				fputcsv($dogsFile, array('ID', 'Name', 'Volunteer ID', 'Sex', 'Birthdate', 'Adoption Date', 'Death date', 'Breed', 'Litter ID', 'Stillborn'));
				while ($dogrow = mysqli_fetch_assoc($dogs)) fputcsv($dogsFile, $dogrow);
			}else{
				echo mysqli_error($db);
			}
			//Download Volunteer table and write to CSV
			$SQL = "SELECT ID, Name, Email, Phone, Address, City, State, ZIP, Admin FROM Volunteer";
			if($volunteers = $db->query($SQL)){
				fputcsv($volunteerFile, array('ID', 'Name', 'Email', 'Phone', 'Address', 'City', 'State', 'ZIP', 'Admin'));
				while ($volunteerrow = mysqli_fetch_assoc($volunteers)) fputcsv($volunteerFile, $volunteerrow);
			}else{
				echo mysqli_error($db);
			}
			//Download Litter table and write to CSV
			$SQL = "SELECT * FROM Litter";
			if($litters = $db->query($SQL)){
				fputcsv($litterFile, array('ID', 'Volunteer ID', 'Mother ID', 'Father ID', 'Start Whelp', 'End Whelp', 'Start Wean', 'End Wean', 'ZIP'));
				while ($litterrow = mysqli_fetch_assoc($litters)) fputcsv($litterFile, $litterrow);
			}else{
				echo mysqli_error($db);
			}
			//Download Dog Updates Table and write to CSV
			$SQL = "SELECT * FROM DogUpdates";
			if($dogUpdates = $db->query($SQL)){
				fputcsv($dogUpdatesFile, array('Dog ID', 'Time', 'Note'));
				while ($dogupdatesrow = mysqli_fetch_assoc($dogUpdates)) fputcsv($dogUpdatesFile, $dogupdatesrow);
			}else{
				echo mysqli_error($db);
			}
			//Download Litter Updates and write to CSV
			$SQL = "SELECT * FROM LitterUpdates";
			if($litterUpdates = $db->query($SQL)){
				fputcsv($litterUpdatesFile, array('Litter ID', 'Time', 'Note'));
				while ($litterupdatesrow = mysqli_fetch_assoc($litterUpdates)) fputcsv($litterUpdatesFile, $litterupdatesrow);
			}else{
				echo mysqli_error($db);
			}
			//Download Temperture Table and write to CSV
			$SQL = "SELECT * FROM Temperature";
			if($temperatures = $db->query($SQL)){
				fputcsv($temperatureFile, array('Dog ID', 'Time', 'Temp'));
				while ($temperaturerow = mysqli_fetch_assoc($temperatures)) fputcsv($temperatureFile, $temperaturerow);
			}else{
				echo mysqli_error($db);
			}
			//Write files to a Zip
			$files = array("/tmp/dogs.csv","/tmp/volunteers.csv","/tmp/litters.csv","/tmp/dogUpdates.csv","/tmp/litterUpdates.csv","/tmp/temperatures.csv");
			$zip = new ZipArchive();
			$zipname = "whelpingJournal_backup_".date("Y_m_d").".zip";
			$zip->open($zipname, ZipArchive::CREATE); //example_zip zip file created 
  
			foreach($files as $key =>$file){
				$zip->addFile($file);//add each file into example_zip zip file
			}
			$zip->close();// zip file with files created successful now close it
			fclose($dogsFile);
			fclose($volunteerFile);
			fclose($litterFile);
			fclose($dogUpdatesFile);
			fclose($litterUpdatesFile);
			fclose($temperatureFile);
			echo "<strong>Download Zip File</strong>";
				header('Content-Type: application/zip');
				header('Content-disposition: attachment; filename='.$zipname);
				header('Content-Length: ' . filesize($zipname));
				readfile($zipname);
		}
	}
?>