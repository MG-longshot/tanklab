<?
$curl = curl_init();

curl_setopt($curl, CURLOPT_URL, "http://worldoftanks.com/community/accounts/1001775453-azdel/");
//curl_setopt($ch, CURLOPT_HEADER, 1);
//curl_setopt($ch, CURLOPT_NOBODY, 0);
//curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//curl_setopt($ch, CURLOPT_TIMEOUT, 10);
//don't fetch the actual page, you only want headers
curl_setopt($curl, CURLOPT_NOBODY, true);

//stop it from outputting stuff to stdout
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

// attempt to retrieve the modification date
curl_setopt($curl, CURLOPT_FILETIME, true);

$result = curl_exec($curl);

if ($result === false) {
    die (curl_error($curl)); 
}

$timestamp = curl_getinfo($curl, CURLINFO_FILETIME);
if ($timestamp != -1) { //otherwise unknown
    echo date("Y-m-d H:i:s", $timestamp); //etc
}  else {
echo "Error checking last modified date\n";
}
//$result = curl_exec($ch);
?>