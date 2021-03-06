<?php
/*
* @author Prabesh Shrestha prabesh708@gmail.com
*/


// Calendar feed URL must be saved in the databases to fetch events form the same calendar in the future
$calendarURL = trim($_POST['calendarfeed']);
if (strpos($calendarURL, "basic")) {
    $calendarURL = ereg_replace("basic", "full", $_POST['calendarfeed']);
}

$dateformat = "j F Y"; // 10 March 2009 - see http://www.php.net/date for details
$timeformat = "g.ia"; // 12.15am
?>

<?php
$confirmed = 'http://schemas.google.com/g/2005#event.confirmed';

$three_months_in_seconds = 60 * 60 * 24 * 28 * 3;
$three_months_ago = date("Y-m-d\Th:i:sP", time() - $three_months_in_seconds);
$three_months_from_today = date("Y-m-d\Th:i:sP", time() + $three_months_in_seconds);

$feed = $calendarURL;
//Can be used later to filter out the list of events
/* "/full?orderby=starttime&singleevents=true&" .
  "start-min=" . $three_months_ago . "&" .
  "start-max=" . $three_months_from_today; */

$doc = new DOMDocument();
$doc->load($feed);

$entries = $doc->getElementsByTagName("entry");

foreach ($entries as $entry) {

    $status = $entry->getElementsByTagName("eventStatus");
    $eventStatus = $status->item(0)->getAttributeNode("value")->value;

    if ($eventStatus == $confirmed) {
        $titles = $entry->getElementsByTagName("title");
        $title = $titles->item(0)->nodeValue;

        $contents = $entry->getElementsByTagName("content");
        $content = $contents->item(0)->nodeValue;

        $times = $entry->getElementsByTagName("when");
        $startTime = $times->item(0)->getAttributeNode("startTime")->value;
        $when = date("l jS \o\f F Y - h:i A", strtotime($startTime));

        $endTime = $times->item(0)->getAttributeNode("endTime")->value;

        $gCalDate = date($dateformat, strtotime($startTime));
        $gCalDateStart = date($dateformat, strtotime($startTime));
        $gCalDateEnd = date($dateformat, strtotime($endTime));
        $gCalStartTime = gmdate($timeformat, strtotime($startTime));
        $gCalEndTime = gmdate($timeformat, strtotime($endTime));


        $places = $entry->getElementsByTagName("where");
        $where = $places->item(0)->getAttributeNode("valueString")->value;

    }

/*
  This is what is displayed right now and must be replaced by the SQL query to insert into the database
*/

    echo "When: " . $when;
    echo "<br/> gCalDate : " . $gCalDate;
    echo "<br/> Start Date :  " . $gCalDateStart;
    echo "<br/> End Date : " . $gCalDateEnd;
    echo "<br/> Start Time : " . $gCalStartTime;
    echo "<br/> End Time : " . $gCalEndTime;
    echo "<br/>  Title: " . $title;
    echo "<br/>  Where: " . $where;
    echo "<br/>  Status: " . "confirmed";
    echo " <br/> Description: " . $content;
    echo "<br/><br/> <br/> ";
}
?>

