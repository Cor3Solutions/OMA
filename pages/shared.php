<?php
function thfp_db() {
    if (file_exists('../config/database.php')) {
        try { require_once '../config/database.php'; $c = getDbConnection(); $c->set_charset('utf8mb4'); return $c; }
        catch (Exception $e) { }
    }
    $c = new mysqli('localhost','root','','oma_database');
    if ($c->connect_error) return null;
    $c->set_charset('utf8mb4');
    return $c;
}
function safeGet($a,$k,$d=''){return isset($a[$k])?$a[$k]:$d;}
function photoUrl($p){if(empty($p))return'';if(preg_match('#^https?://#',$p))return $p;if($p[0]==='/')return $p;return rtrim(dirname($_SERVER['SCRIPT_NAME']),'/').'/'.$p;}
function initials($n){$p=array_filter(explode(' ',trim($n??'')));if(empty($p))return'?';return strtoupper(substr(reset($p),0,1).substr(end($p),0,1));}
function methodLabel($m){$map=['KO'=>'KO','TKO'=>'TKO','RSC'=>'RSC','decision'=>'Decision','no_contest'=>'No Contest','DRAW'=>'Draw','SUB'=>'Sub'];return $map[$m]??strtoupper($m??'');}
function methodBadge($m){$map=['KO'=>'mb-ko','TKO'=>'mb-tko','RSC'=>'mb-rsc','decision'=>'mb-dec','no_contest'=>'mb-nc','DRAW'=>'mb-draw'];return $map[$m]??'';}
function boutTypeLabel($t){$map=['elimination'=>'Elimination','final'=>'Final','exhibition'=>'Exhibition','main_event'=>'Main Event','co_main'=>'Co-Main','prelim'=>'Prelim','amateur'=>'Amateur'];return $map[$t]??ucfirst($t??'');}
function getColumns($conn,$table){$cols=[];$r=$conn->query("SHOW COLUMNS FROM `$table`");if($r)while($row=$r->fetch_assoc())$cols[]=$row['Field'];return $cols;}
function fighterSelect($conn){$cols=getColumns($conn,'thfp_fighters');$always=['id','name','gym','weight_class','gender','age_category','status'];$optional=['nickname','photo_path','record_wins','record_losses','record_draws','hometown','nationality','date_of_birth','height','reach','last_weigh_in','current_streak','affiliation','notes','fighting_out_of'];$sel=array_intersect($always,$cols);foreach($optional as $c)if(in_array($c,$cols))$sel[]=$c;return implode(',',array_map(fn($c)=>"`$c`",$sel));}