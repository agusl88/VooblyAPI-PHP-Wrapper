<?php
/*
A couple of simple examples of what you can do with the PHP Class Wrapper for Voobly Public API.

Ladders ID List
	Beginners Overall 	: 
	Begginners RM 		: 
	Begginners DM 		: 
	Begginners CS 		: 
	RM Overall 			: 21
	RM 1vs1 			: 
	RM 1vs1 (old)		: 13
	RM General (old)    : 8
	RM Team Game 		: 
	RM Clans 			: 
	DM (DeathMatch)	    : 9
	AOFE Overall 		: 
	AOFE RM 1vs1 		: 
	AOFE RM TG 			: 
	AOFE Castle Blood 	: 
	AOFE CS 			: 
	CS Overall 			: 
	CS CBA 				: 16
	CS CBA Hero 		: 
	CS Bloods 			: 18
	CS Alternative 		: 
	CS Castle Blood 	: 
*/

//First of all we need to include the class, and create a new instance. 
include('vooblyAPI.php');
$vooblyCon = new vooblyAPI();

//=============================Example 1==================================
// Get the ID and Rating of one specific ladder from a list of nicks

$nicks = array('TheViper', 'dogao', 'Cyclops');
$ladder_id = 21;

//Get Users ID's
$users_id = $vooblyCon->findUsers($nicks);

//Array cleanup
$clean_ids = [];
foreach($users_id as $id)
{
	$clean_ids[] = $id['uid'];
}

//Get Ratings data
try{
	$ratings = $vooblyCon->getLadderInfo($clean_ids,$ladder_id);
	?>
	<h1>Custom Live Ratings Sampple</h1>
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
//Display a Top 5 from RM Overall ladder

try{
	$top = $vooblyCon->getTop(21,5);?>
	<h1>Rm Overall Ladder Top 5</h1>
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
    echo 'Exception: ',  $e->getMessage(), "\n";
}
?>