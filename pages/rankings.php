<?php
require_once 'layout.php';
require_once 'shared.php';
$conn=thfp_db();$divs=[];
if($conn){
    $f_sel=fighterSelect($conn);
    $res=$conn->query("SELECT $f_sel,(SELECT COUNT(*) FROM thfp_bouts WHERE winner_id=f.id) AS bout_wins FROM thfp_fighters f WHERE f.status='active' ORDER BY f.weight_class+0 ASC,bout_wins DESC,f.name ASC");
    while($f=$res->fetch_assoc()){
        $key=($f['weight_class']??'').'|'.strtolower($f['age_category']??'').'|'.strtolower($f['gender']??'');
        $divs[$key][]=$f;
    }
}
thfp_head('Rankings');thfp_nav('rankings');
?>
<style>
/* ── Rankings page ─────────────────────────────────────────────────────── */
.rk-page{max-width:var(--max);margin:0 auto;padding:12px 16px;}

/* Page header strip */
.rk-page-hd{background:#1A1A1A;color:#fff;padding:10px 14px;font-family:var(--font-c);font-size:14px;font-weight:700;letter-spacing:2px;text-transform:uppercase;display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;flex-wrap:wrap;gap:6px;}
.rk-div-count{font-family:var(--font-c);font-size:11px;font-weight:600;letter-spacing:1px;text-transform:uppercase;color:#aaa;font-weight:normal;}

/* Division grid */
.rk-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:14px;}

/* Division card */
.rk-card{background:var(--white);border:1px solid var(--border);box-shadow:0 1px 4px rgba(0,0,0,.06);}
.rk-card-hd{background:#1A1A1A;color:#fff;padding:9px 14px;display:flex;align-items:center;justify-content:space-between;gap:8px;}
.rk-card-title{font-family:var(--font-c);font-size:14px;font-weight:800;letter-spacing:1px;text-transform:uppercase;line-height:1;}
.rk-gender-tag{font-family:var(--font-c);font-size:10px;font-weight:700;letter-spacing:1px;text-transform:uppercase;padding:3px 9px;border-radius:2px;flex-shrink:0;}
.rk-gt-m{background:rgba(27,94,168,.25);color:#7eb8f7;border:1px solid rgba(27,94,168,.3);}
.rk-gt-f{background:rgba(180,30,100,.25);color:#f9a8d4;border:1px solid rgba(180,30,100,.3);}

/* Fighter row */
.rk-row{display:flex;align-items:center;gap:12px;padding:12px 14px;border-bottom:1px solid var(--gray2);transition:background var(--ease);cursor:pointer;min-height:58px;}
.rk-row:last-child{border-bottom:none;}
.rk-row:hover{background:var(--gray1);}
.rk-row:hover .rk-name{color:var(--red);}

/* Position number */
.rk-pos{font-family:var(--font-c);font-size:18px;font-weight:900;color:var(--dim);min-width:28px;text-align:center;flex-shrink:0;line-height:1;}
.rk-pos.top{color:var(--red);font-size:20px;}

/* Avatar */
.rk-av{width:46px;height:46px;flex-shrink:0;background:var(--gray2);border:1px solid var(--border);display:grid;place-items:center;font-family:var(--font-c);font-size:1rem;font-weight:700;color:var(--sub);overflow:hidden;}
.rk-av img{width:100%;height:100%;object-fit:cover;}

/* Info */
.rk-info{flex:1;min-width:0;}
.rk-name{font-family:var(--font-c);font-size:15px;font-weight:700;text-transform:uppercase;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;transition:color var(--ease);line-height:1.2;}
.rk-gym{font-size:13px;color:var(--dim);margin-top:3px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}

/* Right badges */
.rk-badges{display:flex;align-items:center;gap:6px;flex-shrink:0;}
.rk-leader{font-family:var(--font-c);font-size:10px;font-weight:700;letter-spacing:1px;text-transform:uppercase;background:var(--red);color:#fff;padding:3px 8px;white-space:nowrap;}
.rk-wins{font-family:var(--font-c);font-size:13px;font-weight:700;color:var(--win);white-space:nowrap;}

/* Empty state */
.rk-empty{background:var(--white);border:1px solid var(--border);padding:32px;text-align:center;color:var(--dim);font-size:15px;font-style:italic;}

/* ── RESPONSIVE ─────────────────────────────────────────────────────────── */
@media(max-width:960px){
    .rk-grid{grid-template-columns:repeat(2,1fr);gap:10px;}
}
@media(max-width:640px){
    .rk-page{padding:8px;}
    .rk-grid{grid-template-columns:1fr;gap:8px;}
    .rk-page-hd{font-size:13px;padding:9px 12px;}
    .rk-card-title{font-size:13px;}
    .rk-row{padding:11px 12px;gap:10px;min-height:54px;}
    .rk-av{width:42px;height:42px;}
    .rk-name{font-size:14px;}
    .rk-gym{font-size:12px;}
    .rk-pos{font-size:16px;min-width:24px;}
    .rk-pos.top{font-size:18px;}
}
@media(max-width:380px){
    .rk-av{width:38px;height:38px;font-size:.85rem;}
    .rk-name{font-size:13px;}
}
</style>

<!-- No filter bar needed — just a clean page header -->
<div class="rk-page">

<div class="rk-page-hd">
    Fighter Rankings &mdash; By Division
    <span class="rk-div-count"><?php echo count($divs);?> division<?php echo count($divs)!==1?'s':'';?></span>
</div>

<?php if(empty($divs)):?>
<div class="rk-empty">No fighters registered yet.</div>
<?php else:?>
<div class="rk-grid">
<?php foreach($divs as $key=>$fighters):
    $parts=explode('|',$key);
    $wc=$parts[0]??'';
    $age=ucfirst($parts[1]??'');
    $gender=ucfirst($parts[2]??'');
    $heading=trim($wc.'kg &nbsp;'.$age.' '.$gender);
    $gtClass=strtolower($parts[2]??'')==='female'?'rk-gt-f':'rk-gt-m';
?>
<div class="rk-card">
    <div class="rk-card-hd">
        <span class="rk-card-title"><?php echo htmlspecialchars(trim($wc.'kg '.$age.' '.$gender));?></span>
        <span class="rk-gender-tag <?php echo $gtClass;?>"><?php echo $gender;?></span>
    </div>
    <?php $rk=0; foreach($fighters as $f): $rk++;
        $top=$rk===1;
        $pp=photoUrl(safeGet($f,'photo_path',''));
        $wins=(int)($f['bout_wins']??0);
    ?>
    <div class="rk-row" onclick="window.location.href='fighters.php'">
        <div class="rk-pos <?php echo $top?'top':'';?>"><?php echo $top?'#1':$rk;?></div>
        <div class="rk-av">
            <?php if($pp):?>
                <img src="<?php echo htmlspecialchars($pp);?>" alt=""
                     onerror="this.parentNode.textContent='<?php echo strtoupper(substr(trim($f['name']),0,1));?>'">
            <?php else:
                echo strtoupper(substr(trim($f['name']),0,1));
            endif;?>
        </div>
        <div class="rk-info">
            <div class="rk-name"><?php echo htmlspecialchars($f['name']);?></div>
            <div class="rk-gym"><?php echo htmlspecialchars(safeGet($f,'gym',''));?></div>
        </div>
        <div class="rk-badges">
            <?php if($top):?><span class="rk-leader">Leader</span><?php endif;?>
            <?php if($wins>0):?><span class="rk-wins"><?php echo $wins;?>W</span><?php endif;?>
        </div>
    </div>
    <?php endforeach;?>
</div>
<?php endforeach;?>
</div>
<?php endif;?>
</div>
<?php thfp_foot();?>