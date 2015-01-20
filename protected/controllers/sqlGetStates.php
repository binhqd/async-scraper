<?php
$app = app();
$parser = $app->helper("Parser");

$sql = "SELECT states.stateName,states.stateID,states.countryID,countries.countryName
    from states 
    inner join countries on (countries.id = states.country_id)
    where states.stateID not in (select distinct(stateID) from cities)
    order by countries.countryName";

$records = $app->db->query($sql, array());

$states = array();

foreach ($records as $record) {
    $states[] = [
        'stateID'       => $record['stateID'],
        'stateName'     => $record['stateName'],
        'countryID'     => $record['countryID'],
        'countryName'   => $record['countryName'],
    ];
}

header("Content-type: application/json");
echo json_encode($states);