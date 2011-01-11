<?php

// Your private feed - which you get by right-clicking the 'xml' button in the 'Private Address' section of 'Calendar Details'.
if (!isset($calendarfeed)) {$calendarfeed = "https://www.google.com/calendar/feeds/cridland.net_9nfm5orp01h05bnhtd0j0og5g0%40group.calendar.google.com/private-239a285fcf327d9ba6b1f20712498a6a/basic"; }

// Date format you want your details to appear
$dateformat="j F Y"; // 10 March 2009 - see http://www.php.net/date for details
$timeformat="g.ia"; // 12.15am

//Time offset - if times are appearing too early or too late on your website, change this.
$offset="+1 hour"; // you can use "+1 hour" here for example
// you can also use $offset="now";

// How you want each thing to display.
// By default, this contains all the bits you can grab. You can put ###DATE### in here too if
// you want to, and disable the 'group by date' below.
$event_display="<P><B>###TITLE###</b> - from ###FROM### ###DATESTART### until ###UNTIL### ###DATEEND### (<a href='###LINK###'>add this</a>)<BR>###WHERE### (<a href='###MAPLINK###'>map</a>)<br>###DESCRIPTION###</p>";

// What happens if there's nothing to display
$event_error="<P>There are no events to display.</p>";

// The separate date header is here
$event_dateheader="<P><B>###DATE###</b></P>";
$GroupByDate=true;
// Change the above to 'false' if you don't want to group this by dates,
// but remember to add ###DATE### in the event_display if you do.

// ...and how many you want to display (leave at 999 for everything)
$items_to_show=999;

// Change this to 'true' to see lots of fancy debug code
$debug_mode=false;

//
//End of configuration block
/////////

if ($debug_mode) {error_reporting (E_ALL); echo "<P>Debug mode is on.</p>";}

// Form the XML address.
$calendar_xml_address = str_replace("/basic","/full?singleevents=true&futureevents=true&orderby=starttime&sortorder=a",$calendarfeed); //This goes and gets future events in your feed.

if ($debug_mode) {
echo "<P>We're going to go and grab <a href='$calendar_xml_address'>this feed</a>.<P>";}

// Set the offset correctly
$offset=(strtotime("now")-strtotime($offset));
if ($debug_mode) {echo "Offset is ".$offset;}

$xml = simplexml_load_file($calendar_xml_address);

if ($debug_mode) {echo "<P>Successfully retrieved it.</p>";}

$items_shown=0;
$xml->asXML();

foreach ($xml->entry as $entry){
    $ns_gd = $entry->children('http://schemas.google.com/g/2005');

    //Do some niceness to the description
    //Make any URLs used in the description clickable: thanks Adam
    $description = eregi_replace('(((f|ht){1}tp://)[-a-zA-Z0-9@:%_\+.~#?,&//=]+)','<a href="\\1">\\1</a>', $entry->content);
    // Make email addresses in the description clickable: thanks, Bjorn
    $description = eregi_replace('([_.0-9a-z-]+@([0-9a-z][0-9a-z-]+\.)+[a-z]{2,3})','<a
href="mailto:\\1">\\1</a>', $description);

    // These are the dates we'll display
    $gCalDate = date($dateformat, strtotime($ns_gd->when->attributes()->startTime)-$offset);
    $gCalDateStart = date($dateformat, strtotime($ns_gd->when->attributes()->startTime)-$offset);
    $gCalDateEnd = date($dateformat, strtotime($ns_gd->when->attributes()->endTime)-$offset);
    $gCalStartTime = gmdate($timeformat, strtotime($ns_gd->when->attributes()->startTime)-$offset);
    $gCalEndTime = gmdate($timeformat,strtotime($ns_gd->when->attributes()->endTime)-$offset);
                   
    // Now, let's run it through some str_replaces, and store it with the date for easy sorting later
    $temp_event=$event_display;
    $temp_dateheader=$event_dateheader;
    $temp_event=str_replace("###TITLE###",$entry->title,$temp_event);
    $temp_event=str_replace("###DESCRIPTION###",$description,$temp_event);

    if ($gCalDateStart!=$gCalDateEnd) {
    //This starts and ends on a different date, so show the dates
    $temp_event=str_replace("###DATESTART###",$gCalDateStart,$temp_event);
    $temp_event=str_replace("###DATEEND###",$gCalDateEnd,$temp_event);
    } else {
    $temp_event=str_replace("###DATESTART###",'',$temp_event);
    $temp_event=str_replace("###DATEEND###",'',$temp_event);
    }

    $temp_event=str_replace("###DATE###",$gCalDate,$temp_event);
    $temp_dateheader=str_replace("###DATE###",$gCalDate,$temp_dateheader);
    $temp_event=str_replace("###FROM###",$gCalStartTime,$temp_event);
    $temp_event=str_replace("###UNTIL###",$gCalEndTime,$temp_event);
    $temp_event=str_replace("###WHERE###",$ns_gd->where->attributes()->valueString,$temp_event);
    $temp_event=str_replace("###LINK###",$entry->link->attributes()->href,$temp_event);
    $temp_event=str_replace("###MAPLINK###","http://maps.google.com/?q=".urlencode($ns_gd->where->attributes()->valueString),$temp_event);
    // Accept and translate HTML
    $temp_event=str_replace("&lt;","<",$temp_event);
    $temp_event=str_replace("&gt;",">",$temp_event);
    $temp_event=str_replace("&quot;","\"",$temp_event);
                   
    if (($items_to_show>0 AND $items_shown<$items_to_show)) {
                if ($GroupByDate) {if ($gCalDate!=$old_date) { echo $temp_dateheader; $old_date=$gCalDate;}}
        echo $temp_event;
        $items_shown++;
    }
}

if (!$items_shown) { echo $event_error; }
?>
