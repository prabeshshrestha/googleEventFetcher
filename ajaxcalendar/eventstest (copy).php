<?php 
    $con = mysql_connect("localhost","root","prabesh708");
    if (!$con)
    {
       die('Could not connect: ' . mysql_error());
    }
    mysql_select_db("qcbeta", $con);

    $confirmed = 'http://schemas.google.com/g/2005#event.confirmed';

    $three_months_in_seconds = 60 * 60 * 24 * 28 * 3;
    $three_months_ago = date("Y-m-d\Th:i:sP", time() - $three_months_in_seconds);
    $three_months_from_today = date("Y-m-d\Th:i:sP", time() + $three_months_in_seconds);

    $feed = "http://www.google.com/calendar/feeds/prabesh708%40gmail.com/" . 
        "public/full?orderby=starttime&singleevents=true&" . 
        "start-min=" . $three_months_ago . "&" .
        "start-max=" . $three_months_from_today;

    $doc = new DOMDocument(); 
    $doc->load( $feed );

    $entries = $doc->getElementsByTagName( "entry" ); 

    foreach ( $entries as $entry ) { 

        $status = $entry->getElementsByTagName( "eventStatus" ); 
        $eventStatus = $status->item(0)->getAttributeNode("value")->value;

        if ($eventStatus == $confirmed) {
            $titles = $entry->getElementsByTagName( "title" ); 
            $title = $titles->item(0)->nodeValue;

            $times = $entry->getElementsByTagName( "when" ); 
            $startTime = $times->item(0)->getAttributeNode("startTime")->value;
            $when = date( "l jS \o\f F Y - h:i A", strtotime( $startTime ) );

            $places = $entry->getElementsByTagName( "where" ); 
            $where = $places->item(0)->getAttributeNode("valueString")->value;
	    $query = "INSERT INTO events (name, type, start_time, end_time, start_date, end_date, is_allday,
            creator_uid,description, google_calendar_id, hosts)
            VALUES ('$title', '0', NOW(),NOW(),CURDATE(),CURDATE(),'0','10','description here','10','host name here')";
echo $when;
	echo $query;	    
mysql_query($query) or die(mysql_error());
	    
            /*print $title . "\n"; 
            print $when . " AST\n"; 
            print $where . "\n"; 
            print "\n"; */
        }

	echo "<br/>";
    }
mysql_close($con);
?>
