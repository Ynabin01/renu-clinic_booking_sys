<?php
session_start();
require '../config/database.php';
header('Content-Type: application/json; charset=utf-8');

$doctor=(int)($_GET['doctor_id']??0);
$date=$_GET['date']??'';
$time=$_GET['time']??'';

if($doctor<1 || !preg_match('/^\d{4}-\d{2}-\d{2}$/',$date)){
    echo json_encode(['ok'=>false,'message'=>'Invalid doctor or date.']); exit;
}
$doctorCheck=$pdo->prepare('SELECT doctor_id,clinic_id FROM doctors WHERE doctor_id=?');
$doctorCheck->execute([$doctor]);
$d=$doctorCheck->fetch();
if(!$d){echo json_encode(['ok'=>false,'message'=>'Doctor not found.']);exit;}

$sql="SELECT appointment_time FROM appointments WHERE doctor_id=? AND appointment_date=? AND status IN ('Pending','Confirmed')";
$stmt=$pdo->prepare($sql);$stmt->execute([$doctor,$date]);
$occupied=[];
foreach($stmt->fetchAll() as $row)$occupied[]=substr($row['appointment_time'],0,5);

$selectedOccupied=false;
if($time!=='') $selectedOccupied=in_array(substr($time,0,5),$occupied,true);

/* Standard clinic slots used for recommendations. Existing custom appointment
   times are also respected, so an occupied slot is never recommended. */
$available=[];
for($h=9;$h<=17;$h++){
    foreach([0,30] as $m){
        if($h===17 && $m===30) continue;
        $slot=sprintf('%02d:%02d',$h,$m);
        if(!in_array($slot,$occupied,true))$available[]=$slot;
    }
}
echo json_encode([
 'ok'=>true,
 'selected_occupied'=>$selectedOccupied,
 'occupied'=>$occupied,
 'available'=>$available
]);
