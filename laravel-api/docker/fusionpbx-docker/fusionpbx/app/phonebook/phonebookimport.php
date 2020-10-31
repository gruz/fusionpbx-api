<?php

$imported = simplexml_load_file($_FILES['file']['tmp_name']);
	// includes
	require_once "root.php";
	require_once "resources/require.php";
	require_once "resources/check_auth.php";

//add multi-lingual support
    $language = new text;
    $text = $language->get();
    
    $sql = " select company_name, phonebook_uuid from v_phonebook ";
    $sql .= "where domain_uuid = '".$_SESSION['domain_uuid']."' ";

    $prep_statement = $db->prepare(check_sql($sql));
    $prep_statement->execute();
    $existing = $prep_statement->fetchAll(PDO::FETCH_ASSOC);
    unset ($prep_statement, $sql);
    $numbers = array();
    $dbnames = array();
    $xmlnames = array();

    foreach($existing as $item){
        $dbnames[$item['company_name']] = $item['phonebook_uuid'];
    }

    // Authentication
    if (permission_exists('phonebook_edit') || permission_exists('phonebook_add')) {
        //access granted
    }
    else {
        echo "Access denied.";
        exit;
    }

	
/*        // Print existing array
        echo "<pre>";
            print_r($existing);
        echo "</pre>";*/

        // create phonebook object from xml
        //$phonebook = simplexml_load_file('phonebook.xml');
	$phonebook = $imported;

 	    // Loop through each entry
        foreach($phonebook->DirectoryEntry as $entry){
            if(!array_key_exists("$entry->Name", $dbnames)){
                $newuuid = uuid();
                $dbnames["$entry->Name"] = $newuuid;
                $sql = "insert into v_phonebook ";
                $sql .= "(";
                $sql .= "domain_uuid, ";
                $sql .= "phonebook_uuid, ";
                $sql .= "company_name";
                $sql .= ") ";
                $sql .= "values ";
                $sql .= "(";
                $sql .= "'".$_SESSION['domain_uuid']."', ";
                $sql .= "'$newuuid', ";
                $sql .= ":entry_name";
                $sql .= ")";
                $sth = $db->prepare($sql);
                $company_name = $entry->Name;
                $sth->bindParam(":entry_name", $company_name);
                if(!$sth->execute()){
                    print_r($sql);
                    exit('Error - Failed to execute sql(1):<br />');
                }
            }
	    }

//FIX HERE ------------------------------
        $x = 0;
        foreach($phonebook->DirectoryEntry as $entry){
            $xmlname = $entry->Name;
            $theuuid = $dbnames["$entry->Name"];
            foreach($entry->Telephone as $number){
                $number = str_replace(' ', '', $number);
                $number = str_replace('-', '', $number);
                $numbers["$x"] = array("$number" => "$theuuid");
                $x++;
            }
        }
/*        echo "<pre>";
        print_r($numbers);
        echo "</pre>";*/
//FIX HERE --------------------------------
    foreach($numbers as $number){
            foreach($number as $telephone => $uuid){
                $sql = "insert into v_phonebook_details ";
                $sql .= "(";
                    $sql .= "domain_uuid, ";
                    $sql .= "phonebook_uuid, ";
                    $sql .= "phonebook_detail_uuid, ";
                    $sql .= "phonenumber,";
                    $sql .= "contact_group";
                $sql .= ") ";
                $sql .= "values ";
                $sql .= "(";
                    $sql .= "'".$_SESSION['domain_uuid']."', ";
                    $sql .= "'".$uuid."', ";
                    $sql .= "'".uuid()."', ";
                    $sql .= ":telephone".",";
                    $sql .= "'global'";
                $sql .= ")";
                $sth = $db->prepare($sql);
                $sth->bindParam(":telephone", $telephone);
                if(!$sth->execute()){
                    print_r($sql);
                    exit('Error - Failed to execute sql(1):<br />');
                }
            }

        } 
$_SESSION["message"] = $text['label-add-complete'];
header("Location: phonebook.php");
?>
