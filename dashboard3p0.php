<?php
/*
Hartselle Nailwood Dashboard
2018.10.05
Nicholas West
*/
$autoRefresh=TRUE;
include 'header2p0.php';
?>
<a style='text-decoration:none;' href='dashboard3p0.php'><div class="brand">Sonoco Reels & Plugs</div></a>
<div class="brand timeStamp">
    <?php echo date('l h:i:s A') . "<br />" . date('jS \of F Y') . "<br />"; ?>
</div>
<?php




//   ****************    GET THRESHOLDS (VISUAL INDICATOR LEVELS)   ****************
//$lines = [1,3,4,5,6,7,8,9,20,30,40,50,60];
$lines = [1,3,4,5,6,7,9,20,30,40,50,60,62];
foreach ($lines as $l) {
    $sUnitsTgt[$l] = 0.0;
    $sUnitsL1[$l] = 0.0;
    $dUnitsTgt[$l] = 0.0;
    $dUnitsL1[$l] = 0.0;
    $spdTgt[$l] = 0.0;
	$spdL1[$l] = 0.0;
	$spdL1[$l] = 0.0;
	$sSpdPer[$l] = 0.0;
	$rollAvg10Per[$l] = 0.0;
}
$q = "SELECT p.idLine, shiftTarget, shiftL1, dailyTarget, dailyL1, uptimeTarget, uptimeL1, "
        . "(SELECT targetUnitsPerHr FROM target_speed WHERE target_Speed.idLine = p.idLine), "
		. "(SELECT visL1 FROM target_speed WHERE target_Speed.idLine = p.idLine), "
		. "(SELECT visL2 FROM target_speed WHERE target_Speed.idLine = p.idLine) "
        . "AS SpdTgt FROM target_production AS p";
$result = queryMysql($q);



if($result) { $num = mysqli_num_rows($result); }
if ($num==0) { 
    $error =    "<span class='error'>ERROR Connecting to database. (Nailer Lines - 10 min rolling avg.)</span><br /><br />";
} else {
    for ($i=0; $i<$num; $i++) {
        $row = mysqli_fetch_row($result);
        $sUnitsTgt[$row[0]]=$row[1];
        $sUnitsL1[$row[0]]=$row[2];
        $dUnitsTgt[$row[0]]=$row[3];
        $dUnitsL1[$row[0]]=$row[4];
        $uptimeTgt[$row[0]]=$row[5];
        $uptimeL1[$row[0]]=$row[6];
        $spdTgt[$row[0]] = $row[7];
		$spdL1[$row[0]] = $row[8];
		$spdL2[$row[0]] = $row[9];
        
    }
    //WHY IS THE LAST INDEX ASSIGNED TO THE VARIABLE?
}
$q = "";

//   ****************    DISPLAY DASHBOARD   ****************    

//$order = [6,1,30,7,8,3,4,9,5,20,40,50,60];
$order = [6,1,30,7,3,4,9,5,20,40,50,60,62];
echo "<div class='dashboard'><table>";
echo "<tr>"
. "<td><div class='dbTitle'>Line</div></td><td><div class='dbTitle'>Shift</div></td>"
. "<td><div class='dbTitle'>Units Produced</div></td><td><div class='dbTitle'>Uptime</div></td>"
. "<td><div class='dbTitle'>Avg Speed</div></td><td><div class='dbTitle'>Speed (10min)</div></td>"
. "<td><div class='dbTitle'>Data Integrity</div><td><div class='dbTitle'>Order Info</div></td></td>"
. "<td><div class='dbTitle'>Need</div></td></tr>";




 
foreach ($order as $i) {
    
    //$sSpdPer[$i]=$sSpdAvg[$i]/$spdTgt[$i]*100;
    echo "$i =" . $oQty[$i] . ' of ' . $oNeeded[$i] . "<br />";
    // ------------ LINE NAME ------------------- 
    
    echo "<tr><td><a style='text-decoration:none;' href='DTEvents.php?c=$i'><div class='dbCol colName "; 
    if ($lDown[$i]==0) { echo "good'>".$lLabel[$i]."</div></a></td>"; } 
    elseif ($lDown[$i]==1 && $s[$i]>0){
		if ($idleTime[$i]<=999){
			echo "bad'>".$lLabel[$i]."<br/> "
                . number_format($idleTime[$i],0) . " min</div></a></td>"; 
		} else {
			echo "bad'>".$lLabel[$i]."<br/> "
                . number_format(($idleTime[$i]/60),0) . " hr</div></a></td>"; 
		}
    } else { echo "'>" . $lLabel[$i] . "</div></a></td>"; }
    // ------------ SHIFT -------------------                      
    echo "<td><a style='text-decoration:none;' href='DTEvents.php?c=$i'>"
        . "<div class='dbCol colShift "; if ($s[$i]>0) {echo "colActive";} echo "'>".$s[$i]."</div></a></td>";
    // ------------ UNITS -------------------                       
    echo "<td><a style='text-decoration:none;' href='DTEvents.php?c=$i'>"
        . "<div class='dbCol colUnits "; if ($s[$i]>0) {echo "colActive";} echo "'>". number_format($sQty[$i],0) . "</div></a></td>";
    // ------------ UPTIME -------------------
    echo "<td><a style='text-decoration:none;' href='DTEvents.php?c=$i'><div class='dbCol colUptime "; 
    if ($s[$i]>0 && $upTime[$i]>=$uptimeL1[$i]/100 && $upTime[$i]<$uptimeTgt[$i]/100) { echo " okay"; }
    if ($s[$i]>0 && $upTime[$i]>=$uptimeTgt[$i]/100) { echo " good"; }
    if ($s[$i]>0 && $upTime[$i]<$uptimeL1[$i]/100) { echo " bad"; }
    echo "'>".number_format($upTime[$i]*100,0)."</div></a></td>";
    // ------------ AVERAGE SPEED -------------------              
	$sSpdPer[$i]=$sSpdAvg[$i]/$spdTgt[$i]*100;
    echo "<td><a style='text-decoration:none;' href='DTEvents.php?c=$i'><div class='dbCol colSpd ";
    if ($s[$i]>0 && $sSpdPer[$i]<$spdL2[$i] && $sSpdPer[$i]>=$spdL1[$i]) { echo " okay"; }
    if ($s[$i]>0 && $sSpdPer[$i]>=$spdL2[$i] ) { echo " good"; }
    if ($s[$i]>0 && $sSpdPer[$i]<$spdL1[$i] ) { echo " bad"; }
    echo "'>".number_format($sSpdPer[$i],0)."</div></a></td>";
    // ------------ CURRENT SPEED -------------------
	$rollAvg10Per[$i]=$rollAvg10[$i]/$spdTgt[$i]*100;
    echo "<td><a style='text-decoration:none;' href='DTEvents.php?c=$i'><div class='dbCol colSpd";
    if ($s[$i]>0 && $rollAvg10Per[$i]<$spdL2[$i] && $rollAvg10Per[$i]>=$spdL1[$i]) { echo " okay"; }
    if ($s[$i]>0 && $rollAvg10Per[$i]>=$spdL2[$i] ) { echo " good"; }
    if ($s[$i]>0 && $rollAvg10Per[$i]<$spdL1[$i] ) { echo " bad"; }
    echo "'>".number_format($rollAvg10Per[$i],0);
    echo "</div></a></td>";
    // ------------ Data Integrity (Not Specified) -------------------
    echo "<td><a style='text-decoration:none;' href='DTEvents.php?c=$i'>"
        . "<div class='dbCol colData "; if ($s[$i]>0) {echo "colActive";}   
    if ($lossBySPS[$i][$spsNS]<=.25 && $lossBySPS[$i][$spsMR]<= $sSchedule[$i]['unpaidShiftMins']/60*1.1 ) { echo "'>OK"; } 
    if ($lossBySPS[$i][$spsNS]<=.25 && $lossBySPS[$i][$spsMR] > $sSchedule[$i]['unpaidShiftMins']/60*1.1) {
        echo " colDataIssue'><font size='6'>Market Related:<br></font><font size='8'>".number_format($lossBySPS[$i][$spsMR]*60)." min</font>";
    }if ($lossBySPS[$i][$spsNS]>.25 ) {
        echo " colDataIssue'><font size='6'>Not Specified:<br></font><font size='8'>".number_format($lossBySPS[$i][$spsNS]*60)." min</font>";
    }
    echo "</div></a></td>";
    // ------------ Order Info -------------------
    if($i!=8 && $i!=50 && $i != 9){
		echo "<td><a style='text-decoration:none;' href='viewRuns.php?c=$i'>"
			. "<div class='dbCol colOrder "; if ($s[$i]>0) {echo "colActive";} echo "'>".$oID[$i]."<br/>"
			. $oQty[$i] . ' of ' . $oNeeded[$i] ."</div></a></td>";
    } else {
		echo "<td><a style='text-decoration:none;' href='viewRuns.php?c=$i'>"
        . "<div class='dbCol colOrder "; if ($s[$i]>0) {echo "colActive";} echo "'>".$oID[$i]."<br/>"
        .............
        ................
        }
    } else {$remaining[$i]="";}
    echo "'>$remaining[$i]</div></a></td>";

    
    echo "</tr>"; 
   
}
echo "</table></div>";
/*
echo "<table>";
$order = [1,3,4,5,6,30,20];
foreach($order as $l) {
foreach ($lossBySPS[$l] as $key => $value) {
    echo "<tr><td>Hrs=$sHrsIntoShift[$l]</td><td>$idleTime[$l]</td><td>Line=$l</td><td>Key=$key</td><td>Value=$value</td></tr>";
}
}
echo "</table>";
 * 
 */

mysqli_close($myLink);
?>

</body>
</html>