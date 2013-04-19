<?php
/*
A couple of simple examples of what you can do with the PHP Class Wrapper for Voobly Public API.

Ladders ID List: check ladders_ids.txt
*/

include('vooblyAPI.php');
$vooblyCon = new vooblyAPI(new standarParser(15)); //We create a new vooblyAPI instance with an implementation of the urlParser interface by parameter ("15" is the timeout value for the connection in seconds)

//=============================Example 1==================================
// Get the ID and Rating of one specific ladder from a list of nicks

$nicks = array('TheViper', 'dogao', 'Cyclops', '__62_Tealc_Arg', 'ManDraKE_');
$ladder_id = 8;

//Get Users ID's
$users_id = $vooblyCon->findUsers($nicks);

//Array cleanup
$clean_ids = [];
foreach($users_id as $id){
	$clean_ids[] = $id['uid'];
}

try{
	//Get Ratings data
	$ratings = $vooblyCon->getLadderInfo($clean_ids,$ladder_id);?>
	<h1>Custom Live Ratings Test</h1>
	<table>
	  <thead>
	    <tr>
	      <th><?php echo implode('</th><th>', array_keys(current($ratings))); ?></th>
	    </tr>
	  </thead>
	  <tbody>
	<?php foreach ($ratings as $row): array_map('htmlentities', $row); ?>
	    <tr>
	      <td><?php echo implode('</td><td>', $row); ?></td>
	    </tr>
	<?php endforeach; ?>
	  <tbody>
	</table>
<?php
} catch (Exception $e) {
    echo 'Exception: ',  $e->getMessage(), "\n";
}

//=============================Example 2==================================
//Display a Top 5 from RM 1vs1 Ladder
$ladder_id2 = 131;
$limit = 5; //top 5

try{
	$top = $vooblyCon->getTop($ladder_id2,$limit);?>
	<h1>Top 5 RM 1vs1 Test</h1>
	<table>
	  <thead>
	    <tr>
	      <th><?php echo implode('</th><th>', array_keys(current($top))); ?></th>
	    </tr>
	  </thead>
	  <tbody>
	<?php foreach ($top as $row): array_map('htmlentities', $row); ?>
	    <tr>
	      <td><?php echo implode('</td><td>', $row); ?></td>
	    </tr>
	<?php endforeach; ?>
	  <tbody>
	</table>
<?php
} catch (Exception $e) {
    echo 'Exception: ',  $e->getMessage(), "\n";}
?>