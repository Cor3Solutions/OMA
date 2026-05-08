<?php
require_once 'layout.php';
require_once 'shared.php';
$conn=thfp_db();$events=[];$all_fighters_map=[];$te=$tb=$tf=$fr=0;
if($conn){
    $res=$conn->query("SELECT * FROM thfp_events ORDER BY event_number DESC");
    while($e=$res->fetch_assoc()){$e['bouts']=[];$rb=$conn->query("SELECT b.*,rf.id AS red_id,rf.name AS red_name,rf.gym AS red_gym,bf.id AS blue_id,bf.name AS blue_name,bf.gym AS blue_gym,wf.name AS winner_name FROM thfp_bouts b LEFT JOIN thfp_fighters rf ON b.red_fighter_id=rf.id LEFT JOIN thfp_fighters bf ON b.blue_fighter_id=bf.id LEFT JOIN thfp_fighters wf ON b.winner_id=wf.id WHERE b.event_id={$e['id']} ORDER BY b.bout_order ASC");while($b=$rb->fetch_assoc())$e['bouts'][]=$b;$events[]=$e;}
    $te=(int)$conn->query("SELECT COUNT(*) c FROM thfp_events")->fetch_assoc()['c'];
    $tb=(int)$conn->query("SELECT COUNT(*) c FROM thfp_bouts")->fetch_assoc()['c'];
    $tf=(int)$conn->query("SELECT COUNT(*) c FROM thfp_fighters")->fetch_assoc()['c'];
    $fin=(int)$conn->query("SELECT COUNT(*) c FROM thfp_bouts WHERE result_method IN('KO','TKO','RSC')")->fetch_assoc()['c'];
    $tot=(int)$conn->query("SELECT COUNT(*) c FROM thfp_bouts WHERE result_method IS NOT NULL AND result_method!='no_contest'")->fetch_assoc()['c'];
    $fr=$tot>0?round(($fin/$tot)*100):0;
    $f_sel=fighterSelect($conn);$ra=$conn->query("SELECT $f_sel FROM thfp_fighters f ORDER BY name");
    while($f=$ra->fetch_assoc())$all_fighters_map[$f['id']]=$f;
}
$dlabels=['muay_thai'=>'Muay Thai','kickboxing'=>'Kickboxing','mma'=>'MMA'];
$dclass=['muay_thai'=>'disc-mt','kickboxing'=>'disc-kb','mma'=>'disc-mma'];
thfp_head('Events');thfp_nav('thfp');thfp_hero($te,$tb,$tf,$fr);
?>
<style>
.filter-bar{background:var(--white);border-bottom:1px solid var(--border);position:sticky;top:var(--nav-h);z-index:100;}
.filter-bar-inner{max-width:var(--max);margin:0 auto;padding:5px 16px;display:flex;align-items:center;gap:4px;}
.ev-sb-row{display:flex;align-items:center;gap:8px;padding:7px 10px;border-bottom:1px solid var(--gray2);transition:background var(--ease);cursor:pointer;text-decoration:none;}
.ev-sb-row:last-child{border-bottom:none;}
.ev-sb-row:hover{background:var(--gray1);}
.ev-sb-row.cur{background:rgba(204,0,0,.05);border-left:2px solid var(--red);}
.ev-sb-num{font-family:var(--font-c);font-size:16px;font-weight:900;color:var(--red);line-height:1;flex-shrink:0;min-width:22px;}
.ev-sb-info{flex:1;min-width:0;}
.ev-sb-name{font-size:11px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.ev-sb-date{font-size:10px;color:var(--dim);margin-top:1px;}
.ev-card{background:var(--white);border:1px solid var(--border);margin-bottom:10px;position:relative;}
.ev-card::before{content:'';position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(to right,var(--red),var(--ember),transparent);}
.ev-hd{padding:10px 12px;cursor:pointer;user-select:none;display:flex;align-items:flex-start;justify-content:space-between;gap:8px;border-bottom:1px solid var(--gray2);}
.ev-hd:hover{background:var(--gray1);}
.ev-num{font-family:var(--font-c);font-size:10px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--red);margin-bottom:2px;}
.ev-name{font-family:var(--font-c);font-size:clamp(16px,4vw,22px);font-weight:800;text-transform:uppercase;color:var(--ink);line-height:1.1;}
.ev-meta-row{display:flex;flex-wrap:wrap;gap:4px 10px;margin-top:5px;align-items:center;}
.ev-meta-item{font-size:13px;color:var(--sub);display:flex;align-items:center;gap:4px;}
.ev-toggle{font-family:var(--font-c);font-size:12px;font-weight:700;letter-spacing:1px;text-transform:uppercase;border:1px solid var(--border);padding:6px 12px;background:var(--gray1);color:var(--sub);white-space:nowrap;flex-shrink:0;min-height:36px;display:flex;align-items:center;}
.ev-toggle.open{background:var(--red);color:#fff;border-color:var(--red);}
.bout-sec{background:var(--gray1);border-top:1px solid var(--border);border-bottom:1px solid var(--border);padding:4px 12px;font-family:var(--font-c);font-size:10px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--sub);}
.bout-row{display:grid;grid-template-columns:1fr 155px 1fr;align-items:center;padding:10px 12px;border-bottom:1px solid var(--gray2);transition:background var(--ease);gap:6px;}
.bout-row:last-child{border-bottom:none;}
.bout-row:hover{background:var(--gray1);}
.fc{display:flex;align-items:center;gap:8px;cursor:pointer;}
.fc.right{flex-direction:row-reverse;text-align:right;}
.fc:hover .fc-name{color:var(--red);}
.fc-av{width:50px;height:50px;flex-shrink:0;background:var(--gray2);border:1px solid var(--border);display:grid;place-items:center;font-family:var(--font-c);font-size:.95rem;font-weight:700;color:var(--dim);overflow:hidden;}
.fc-av img{width:100%;height:100%;object-fit:cover;}
.fc.winner .fc-av{border:2px solid var(--red);}
.fc-name{font-family:var(--font-c);font-size:15px;font-weight:700;text-transform:uppercase;color:var(--ink);line-height:1.25;transition:color var(--ease);}
.fc.winner .fc-name{color:var(--red);}
.fc-gym{font-size:12px;color:var(--dim);margin-top:3px;}
.fc-cta{font-size:12px;color:var(--blue);margin-top:3px;}
.bc{text-align:center;}
.bc-type{font-family:var(--font-c);font-size:11px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--sub);display:block;margin-bottom:3px;}
.bc-disc{display:block;margin-bottom:3px;}
.bc-wc{font-size:12px;color:var(--dim);display:block;margin-bottom:5px;}
.bc-vs{font-family:var(--font-c);font-size:20px;font-weight:900;color:var(--border2);display:block;margin-bottom:5px;letter-spacing:2px;}
.bc-pend{font-family:var(--font-c);font-size:10px;font-weight:600;letter-spacing:1px;text-transform:uppercase;color:var(--dim);border:1px solid var(--border);padding:2px 8px;background:var(--gray1);}
.ev-body{overflow:hidden;transition:max-height .35s ease;}
.ev-body.closed{max-height:0;}
.ev-body.open{max-height:9999px;}
.ev-footer{background:var(--gray1);border-top:1px solid var(--border);padding:10px 14px;font-size:13px;color:var(--sub);display:flex;flex-direction:column;gap:5px;}
.ev-fl{font-family:var(--font-c);font-size:11px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--dim);margin-right:4px;}
.stats-strip{display:grid;grid-template-columns:repeat(4,1fr);border:1px solid var(--border);background:var(--white);margin-bottom:10px;}
.sc{padding:10px 12px;border-right:1px solid var(--border);text-align:center;}
.sc:last-child{border-right:none;}
.sv{font-family:var(--font-c);font-size:28px;font-weight:900;color:var(--red);line-height:1;display:block;}
.sl{font-family:var(--font-c);font-size:12px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--sub);margin-top:4px;display:block;}
@media(max-width:640px){
    /* bout rows stack vertically */
    .bout-row{grid-template-columns:1fr;gap:6px;padding:10px 10px;border-bottom:1px solid var(--gray2);border-radius:0;}
    .bout-row:last-child{border-bottom:none;}
    .bc{order:-1;display:flex;flex-wrap:wrap;align-items:center;gap:5px;padding:6px 8px;border-bottom:1px solid var(--gray2);margin-bottom:4px;background:var(--gray1);}
    .bc-vs{display:none;}
    .fc.right{flex-direction:row;text-align:left;}
    /* bigger avatars for touch */
    .fc-av{width:52px;height:52px;font-size:1.1rem;}
    .fc-name{font-size:15px;}
    /* 2-col stats strip on mobile */
    .stats-strip{grid-template-columns:1fr 1fr;}
    .sc:nth-child(2){border-right:none;}
    .sc:nth-child(3),.sc:nth-child(4){border-top:1px solid var(--border);}
    .sv{font-size:24px;}
    /* sidebar hidden on mobile — already handled by cols grid */
    .ev-hd{padding:12px 10px;}
    .ev-num{font-size:11px;}
}
</style>

<div class="filter-bar">
<div class="filter-bar-inner">
    <button class="ftab on" onclick="fe('all',this)">All Events</button>
    <button class="ftab" onclick="fe('completed',this)">Completed</button>
    <button class="ftab" onclick="fe('upcoming',this)">Upcoming</button>
    <span class="filter-bar-count" id="evcnt"><?php echo $te;?> event<?php echo $te!==1?'s':'';?></span>
</div>
</div>

<div class="wrap"><div class="cols">
<div>
<div class="stats-strip">
    <div class="sc"><span class="sv"><?php echo $te;?></span><span class="sl">Events</span></div>
    <div class="sc"><span class="sv"><?php echo $tb;?></span><span class="sl">Bouts</span></div>
    <div class="sc"><span class="sv"><?php echo $tf;?></span><span class="sl">Fighters</span></div>
    <div class="sc"><span class="sv"><?php echo $fr;?>%</span><span class="sl">Finish Rate</span></div>
</div>

<?php foreach($events as $idx=>$ev):
    $pc=['completed'=>'p-done','upcoming'=>'p-soon','cancelled'=>'p-gone','ongoing'=>'p-live'][$ev['status']]??'p-gone';
    $first=$idx===0;
    $ord=['final'=>0,'exhibition'=>1,'elimination'=>2];$sorted=$ev['bouts'];
    usort($sorted,fn($a,$b)=>($ord[$a['bout_type']]??9)<=>($ord[$b['bout_type']]??9));$lastType=null;
?>
<div class="ev-card" id="ev-<?php echo $ev['id'];?>" data-status="<?php echo $ev['status'];?>">
    <div class="ev-hd" onclick="toggle(<?php echo $ev['id'];?>)">
        <div>
            <div class="ev-num">Combat <?php echo $ev['event_number'];?> &middot; Tribal Hunters Fight Promotion</div>
            <div class="ev-name"><?php echo htmlspecialchars($ev['name']);?></div>
            <div class="ev-meta-row">
                <?php if($ev['event_date']):?><span class="ev-meta-item"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="3" y1="10" x2="21" y2="10"/></svg><?php echo date('F j, Y',strtotime($ev['event_date']));?></span><?php endif;?>
                <?php if(!empty($ev['venue'])):?><span class="ev-meta-item"><?php echo htmlspecialchars($ev['venue']);?></span><?php endif;?>
                <span class="ev-meta-item"><?php echo count($ev['bouts']);?> bout<?php echo count($ev['bouts'])!==1?'s':'';?></span>
                <span class="pill <?php echo $pc;?>"><?php echo ucfirst($ev['status']);?></span>
            </div>
        </div>
        <div class="ev-toggle <?php echo $first?'open':'';?>" id="tog-<?php echo $ev['id'];?>"><?php echo $first?'&#9650; Hide':'&#9660; Show';?></div>
    </div>
    <div class="ev-body <?php echo $first?'open':'closed';?>" id="evb-<?php echo $ev['id'];?>">
    <?php foreach($sorted as $b):
        $typeLabel=boutTypeLabel($b['bout_type']);
        if($b['bout_type']!==$lastType):$lastType=$b['bout_type'];?>
        <div class="bout-sec"><?php echo strtoupper($typeLabel);?>S</div>
    <?php endif;
        $w=$b['winner_name']??null;$m=$b['result_method']??'';
        $rw=$w&&$w===($b['red_name']??'');$bw=$w&&$w===($b['blue_name']??'');
        $mb=methodBadge($m);$ml=methodLabel($m);
        $rid=(int)($b['red_id']??0);$bid=(int)($b['blue_id']??0);
        $disc=$b['discipline']??'muay_thai';$dc=$dclass[$disc]??'disc-mt';
    ?>
    <div class="bout-row">
        <div class="fc <?php echo $rw?'winner':'';?>" <?php if($rid):?>onclick="of(<?php echo $rid;?>)"<?php endif;?>>
            <div class="fc-av"><?php echo initials($b['red_name']??'?');?></div>
            <div><div class="fc-name"><?php echo htmlspecialchars($b['red_name']??'TBA');?></div><?php if(!empty($b['red_gym'])):?><div class="fc-gym"><?php echo htmlspecialchars($b['red_gym']);?></div><?php endif;?><?php if($rid):?><div class="fc-cta">View profile</div><?php endif;?></div>
        </div>
        <div class="bc">
            <span class="bc-type"><?php echo $typeLabel;?></span>
            <span class="bc-disc"><span class="disc <?php echo $dc;?>"><?php echo $dlabels[$disc]??'Muay Thai';?></span></span>
            <span class="bc-wc"><?php echo htmlspecialchars(trim(ucfirst($b['age_category']??'').' '.ucfirst($b['gender']??'').' '.$b['weight_class'].'kg'));?></span>
            <span class="bc-vs">VS.</span>
            <?php if($m):?><span class="bc-result"><span class="mth"><?php echo $ml;?><?php if(!empty($b['result_round'])):?> · R<?php echo $b['result_round'];?><?php endif;?></span></span>
            <?php else:?><span class="bc-pend"><?php echo $ev['status']==='upcoming'?'Scheduled':'TBD';?></span><?php endif;?>
        </div>
        <div class="fc right <?php echo $bw?'winner':'';?>" <?php if($bid):?>onclick="of(<?php echo $bid;?>)"<?php endif;?>>
            <div class="fc-av"><?php echo initials($b['blue_name']??'?');?></div>
            <div><div class="fc-name"><?php echo htmlspecialchars($b['blue_name']??'TBA');?></div><?php if(!empty($b['blue_gym'])):?><div class="fc-gym"><?php echo htmlspecialchars($b['blue_gym']);?></div><?php endif;?><?php if($bid):?><div class="fc-cta">View profile</div><?php endif;?></div>
        </div>
    </div>
    <?php endforeach;?>
    <?php if(!empty($ev['tournament_director'])||!empty($ev['officials'])||!empty($ev['sponsors'])):?>
    <div class="ev-footer">
        <?php if(!empty($ev['tournament_director'])):?><div><span class="ev-fl">Director</span><?php echo htmlspecialchars($ev['tournament_director']);?><?php if(!empty($ev['mc'])):?> &middot; <span class="ev-fl">MC</span><?php echo htmlspecialchars($ev['mc']);?><?php endif;?></div><?php endif;?>
        <?php if(!empty($ev['sanctioned_by'])):?><div><span class="ev-fl">Sanctioned by</span><?php echo htmlspecialchars($ev['sanctioned_by']);?></div><?php endif;?>
        <?php if(!empty($ev['officials'])):?><div><span class="ev-fl">Officials</span><?php echo htmlspecialchars($ev['officials']);?></div><?php endif;?>
        <?php if(!empty($ev['sponsors'])):?><div><span class="ev-fl">Sponsors</span><?php echo htmlspecialchars($ev['sponsors']);?></div><?php endif;?>
    </div>
    <?php endif;?>
    </div>
</div>
<?php endforeach;?>
</div>

<!-- Sidebar -->
<div>
<div class="sec-hd">All Events</div>
<div class="box" style="margin-bottom:10px;">
<?php foreach($events as $ev):$pc=['completed'=>'p-done','upcoming'=>'p-soon','cancelled'=>'p-gone','ongoing'=>'p-live'][$ev['status']]??'p-gone';?>
<div class="ev-sb-row" onclick="expandAndScroll(<?php echo $ev['id'];?>)">
    <div class="ev-sb-num"><?php echo $ev['event_number'];?></div>
    <div class="ev-sb-info"><div class="ev-sb-name"><?php echo htmlspecialchars($ev['name']);?></div><div class="ev-sb-date"><?php echo $ev['event_date']?date('M j, Y',strtotime($ev['event_date'])):'TBA';?></div></div>
    <span class="pill <?php echo $pc;?>"><?php echo ucfirst($ev['status']);?></span>
</div>
<?php endforeach;?>
</div>

<div class="sec-hd">Quick Stats</div>
<div class="box">
    <div style="display:flex;justify-content:space-between;padding:7px 10px;border-bottom:1px solid var(--gray2);font-size:11px;"><span style="color:var(--sub);">Total Events</span><strong><?php echo $te;?></strong></div>
    <div style="display:flex;justify-content:space-between;padding:7px 10px;border-bottom:1px solid var(--gray2);font-size:11px;"><span style="color:var(--sub);">Total Bouts</span><strong><?php echo $tb;?></strong></div>
    <div style="display:flex;justify-content:space-between;padding:7px 10px;border-bottom:1px solid var(--gray2);font-size:11px;"><span style="color:var(--sub);">Fighters</span><strong><?php echo $tf;?></strong></div>
    <div style="display:flex;justify-content:space-between;padding:7px 10px;font-size:11px;"><span style="color:var(--sub);">Finish Rate</span><strong style="color:var(--red);"><?php echo $fr;?>%</strong></div>
</div>
</div>
</div></div>

<?php
$fmap=array_values($all_fighters_map);
foreach($fmap as &$_f){$_f['photo_path']=photoUrl($_f['photo_path']??'');foreach(['nickname','date_of_birth','height','reach','nationality','hometown'] as $k)if(!isset($_f[$k]))$_f[$k]='';}unset($_f);
$bh=[];foreach($events as $ev){foreach($ev['bouts'] as $b){$rid=(int)($b['red_id']??0);$bid2=(int)($b['blue_id']??0);$e=['event_number'=>$ev['event_number'],'bout_type'=>$b['bout_type'],'discipline'=>$b['discipline']??'muay_thai','result_method'=>$b['result_method']??'','result_round'=>$b['result_round']??null,'winner_name'=>$b['winner_name']??null,'red_name'=>$b['red_name']??'','blue_name'=>$b['blue_name']??'','red_gym'=>$b['red_gym']??'','blue_gym'=>$b['blue_gym']??'','red_id'=>$rid,'blue_id'=>$bid2];if($rid)$bh[$rid][]=$e;if($bid2)$bh[$bid2][]=$e;}}
thfp_fighter_modal();
thfp_fighter_js($fmap,$bh);
?>
<script>
function toggle(id){var b=document.getElementById('evb-'+id),t=document.getElementById('tog-'+id),o=b.classList.contains('open');b.classList.toggle('open',!o);b.classList.toggle('closed',o);t.textContent=o?'▼ Show':'▲ Hide';t.classList.toggle('open',!o);}
function expandAndScroll(id){var b=document.getElementById('evb-'+id),t=document.getElementById('tog-'+id);if(b&&b.classList.contains('closed')){b.classList.replace('closed','open');t&&(t.textContent='▲ Hide');t&&t.classList.add('open');}var c=document.getElementById('ev-'+id);if(c)c.scrollIntoView({behavior:'smooth',block:'start'});}
function fe(s,btn){document.querySelectorAll('.ftab').forEach(function(p){p.classList.remove('on');});btn.classList.add('on');var v=0;document.querySelectorAll('.ev-card').forEach(function(c){var m=s==='all'||c.dataset.status===s;c.style.display=m?'':'none';if(m)v++;});document.getElementById('evcnt').textContent=v+' event'+(v!==1?'s':'');}
var obs=new IntersectionObserver(function(en){en.forEach(function(e){if(e.isIntersecting){var id=e.target.id.replace('ev-','');document.querySelectorAll('.ev-sb-row').forEach(function(r){r.classList.remove('cur');if(r.getAttribute('onclick')&&r.getAttribute('onclick').includes('('+id+')'))r.classList.add('cur');})}});},{threshold:.15});
document.querySelectorAll('.ev-card').forEach(function(c){obs.observe(c);});
</script>
<?php thfp_foot();?>