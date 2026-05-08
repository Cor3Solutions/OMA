<?php
require_once 'layout.php';
require_once 'shared.php';
$conn=thfp_db();
$medals=[];$mrows=[];$top=[];$drows=[];$gym_rows=[];
$total_m=$gc=0;$gs=0.0;$gt=$st=$bt=0;$total_d=0;
$te=$tb=$tf=$fr=0;
if($conn){
    $te=(int)$conn->query("SELECT COUNT(*) c FROM thfp_events")->fetch_assoc()['c'];
    $tb=(int)$conn->query("SELECT COUNT(*) c FROM thfp_bouts")->fetch_assoc()['c'];
    $tf=(int)$conn->query("SELECT COUNT(*) c FROM thfp_fighters")->fetch_assoc()['c'];
    $fin=(int)$conn->query("SELECT COUNT(*) c FROM thfp_bouts WHERE result_method IN('KO','TKO','RSC')")->fetch_assoc()['c'];
    $tot=(int)$conn->query("SELECT COUNT(*) c FROM thfp_bouts WHERE result_method IS NOT NULL AND result_method!='no_contest'")->fetch_assoc()['c'];
    $fr=$tot>0?round(($fin/$tot)*100):0;
    $gc=(int)$conn->query("SELECT COUNT(*) c FROM thfp_bouts WHERE bout_type='final' AND winner_id IS NOT NULL AND result_method!='no_contest'")->fetch_assoc()['c'];
    $gs=$tf>0?round(($gc/$tf)*100,1):0.0;
    $mr=$conn->query("SELECT f.gym,SUM(CASE WHEN b.bout_type='final' AND b.winner_id=f.id THEN 1 ELSE 0 END) AS gold,SUM(CASE WHEN b.bout_type='final' AND b.winner_id IS NOT NULL AND b.winner_id<>f.id AND(b.red_fighter_id=f.id OR b.blue_fighter_id=f.id) THEN 1 ELSE 0 END) AS silver,SUM(CASE WHEN b.bout_type='elimination' AND b.winner_id=f.id AND EXISTS(SELECT 1 FROM thfp_bouts fin WHERE fin.event_id=b.event_id AND fin.bout_type='final' AND fin.weight_class=b.weight_class AND fin.gender=b.gender AND fin.age_category=b.age_category) AND NOT EXISTS(SELECT 1 FROM thfp_bouts fin2 WHERE fin2.event_id=b.event_id AND fin2.bout_type='final' AND fin2.weight_class=b.weight_class AND fin2.gender=b.gender AND fin2.age_category=b.age_category AND(fin2.red_fighter_id=f.id OR fin2.blue_fighter_id=f.id)) THEN 1 ELSE 0 END) AS bronze FROM thfp_fighters f JOIN thfp_bouts b ON(b.red_fighter_id=f.id OR b.blue_fighter_id=f.id) WHERE f.gym IS NOT NULL AND f.gym!='' GROUP BY f.gym HAVING(gold+silver+bronze)>0 ORDER BY gold DESC,silver DESC,bronze DESC");
    $medals=$mr->fetch_all(MYSQLI_ASSOC);
    $gt=array_sum(array_column($medals,'gold'));$st=array_sum(array_column($medals,'silver'));$bt=array_sum(array_column($medals,'bronze'));
    $mres=$conn->query("SELECT result_method,COUNT(*) cnt FROM thfp_bouts WHERE result_method IS NOT NULL GROUP BY result_method ORDER BY cnt DESC");
    $mrows=$mres->fetch_all(MYSQLI_ASSOC);$total_m=array_sum(array_column($mrows,'cnt'));
    $tres=$conn->query("SELECT f.name,f.gym,COUNT(b.id) AS wins,SUM(CASE WHEN b.result_method IN('KO','TKO','RSC') THEN 1 ELSE 0 END) AS finishes,SUM(CASE WHEN b.bout_type='final' THEN 1 ELSE 0 END) AS golds FROM thfp_fighters f JOIN thfp_bouts b ON b.winner_id=f.id GROUP BY f.id ORDER BY golds DESC,wins DESC,finishes DESC LIMIT 10");
    $top=$tres->fetch_all(MYSQLI_ASSOC);
    $gres=$conn->query("SELECT f.gym,COUNT(DISTINCT f.id) AS fighters,SUM(CASE WHEN b.winner_id=f.id THEN 1 ELSE 0 END) AS wins,SUM(CASE WHEN b.winner_id IS NOT NULL AND b.winner_id<>f.id AND(b.red_fighter_id=f.id OR b.blue_fighter_id=f.id) THEN 1 ELSE 0 END) AS losses,SUM(CASE WHEN b.winner_id=f.id AND b.result_method IN('KO','TKO','RSC') THEN 1 ELSE 0 END) AS finishes,SUM(CASE WHEN b.winner_id=f.id AND b.bout_type='final' THEN 1 ELSE 0 END) AS golds FROM thfp_fighters f JOIN thfp_bouts b ON(b.red_fighter_id=f.id OR b.blue_fighter_id=f.id) WHERE f.gym IS NOT NULL AND f.gym!='' GROUP BY f.gym HAVING(wins+losses)>0 ORDER BY golds DESC,wins DESC");
    $gym_rows=$gres->fetch_all(MYSQLI_ASSOC);
    $dres=$conn->query("SELECT discipline,COUNT(*) cnt FROM thfp_bouts WHERE discipline IS NOT NULL GROUP BY discipline ORDER BY cnt DESC");
    $drows=$dres?$dres->fetch_all(MYSQLI_ASSOC):[];$total_d=array_sum(array_column($drows,'cnt'));
}
$circ=2*M_PI*48;$dash=min($gs/100,1)*$circ;$gap=$circ-$dash;
thfp_head('Analytics');thfp_nav('analytics');
?>
<style>
.an-wrap{max-width:var(--max);margin:0 auto;padding:12px 16px;}
.an-cols{display:grid;grid-template-columns:1fr 280px;gap:12px;align-items:start;}
@media(max-width:900px){.an-cols{grid-template-columns:1fr;}}
/* stats strip */
.stats-strip{display:grid;grid-template-columns:repeat(4,1fr);border:1px solid var(--border);background:var(--white);margin-bottom:10px;}
.sc{padding:10px 12px;border-right:1px solid var(--border);text-align:center;}
.sc:last-child{border-right:none;}
.sv{font-family:var(--font-c);font-size:30px;font-weight:900;color:var(--red);line-height:1;display:block;}
.sl{font-family:var(--font-c);font-size:12px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--sub);margin-top:4px;display:block;}
/* bar chart */
.brow{display:flex;align-items:center;gap:8px;padding:5px 0;border-bottom:1px solid var(--gray2);}
.brow:last-child{border-bottom:none;}
.bnm{font-family:var(--font-c);font-size:13px;font-weight:600;text-transform:uppercase;color:var(--sub);min-width:90px;}
.btrack{flex:1;height:16px;background:var(--gray2);position:relative;border-radius:2px;overflow:hidden;}
.bfill{height:100%;position:absolute;left:0;top:0;}
.bfill-ko,.bfill-rsc{background:var(--red);}.bfill-tko{background:#cc4444;}
.bfill-dec{background:var(--blue);}.bfill-nc,.bfill-draw{background:#999;}
.bfill-mt{background:var(--red);}.bfill-kb{background:var(--draw);}.bfill-mma{background:#6B4BC8;}
.bcnt{font-family:var(--font-m);font-size:13px;font-weight:600;color:var(--ink);min-width:28px;text-align:right;}
.bpct{font-family:var(--font-c);font-size:12px;color:var(--dim);min-width:36px;text-align:right;}
/* medal table */
.mtbl{width:100%;border-collapse:collapse;}
.mtbl th{font-family:var(--font-c);font-size:12px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--dim);padding:8px 10px;border-bottom:2px solid var(--border);text-align:left;background:var(--gray1);}
.mtbl th.tc{text-align:center;}
.mtbl td{padding:10px 10px;border-bottom:1px solid var(--gray2);font-size:14px;}
.mtbl tr:last-child td{border-bottom:none;}
.mtbl tbody tr:hover td{background:var(--gray1);}
.mpos{font-family:var(--font-c);font-size:13px;font-weight:700;text-align:center;width:28px;}
.mpos.r1{color:#CC9900;}.mpos.r2{color:#888;}.mpos.r3{color:#AA6600;}
.mc{text-align:center;}
.mc span{font-family:var(--font-m);font-size:13px;font-weight:700;padding:1px 8px;}
.mc-g{color:#CC9900;}.mc-s{color:#888;}.mc-b{color:#AA6600;}.mc-z{color:var(--dim);}
/* top performers table */
.ptbl{width:100%;border-collapse:collapse;}
.ptbl th{font-family:var(--font-c);font-size:10px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--dim);padding:6px 10px;border-bottom:2px solid var(--border);text-align:left;background:var(--gray1);}
.ptbl th.tc{text-align:center;}
.ptbl td{padding:7px 10px;border-bottom:1px solid var(--gray2);font-size:12px;vertical-align:middle;}
.ptbl tr:last-child td{border-bottom:none;}
.ptbl tbody tr:hover td{background:var(--gray1);}
/* gold standard donut */
.gs-box{background:var(--white);border:1px solid var(--border);margin-bottom:10px;}
.gs-inner{padding:16px;text-align:center;}
.gs-lbl{font-family:var(--font-c);font-size:10px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--sub);margin-bottom:10px;display:block;}
.gs-donut{position:relative;width:120px;height:120px;margin:0 auto 10px;}
.gs-svg{width:120px;height:120px;transform:rotate(-90deg);}
.gs-bg{fill:none;stroke:var(--gray2);stroke-width:12;}
.gs-arc{fill:none;stroke-width:12;stroke-linecap:butt;}
.gs-center{position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;}
.gs-pct{font-family:var(--font-c);font-size:22px;font-weight:900;color:var(--red);line-height:1;}
.gs-sub{font-size:8px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--dim);margin-top:1px;}
.gs-breakdown{display:grid;grid-template-columns:1fr 1fr 1fr;gap:1px;background:var(--border);border:1px solid var(--border);margin-top:10px;}
.gs-bc{background:var(--white);padding:8px 4px;text-align:center;}
.gs-bv{font-family:var(--font-c);font-size:18px;font-weight:900;line-height:1;}
.gs-bl{font-family:var(--font-c);font-size:9px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--dim);margin-top:2px;}
.gs-bc-g .gs-bv{color:#CC9900;}.gs-bc-s .gs-bv{color:#888;}.gs-bc-b .gs-bv{color:#AA6600;}
/* gym table */
.gym-tbl{width:100%;border-collapse:collapse;}
.gym-tbl th{font-family:var(--font-c);font-size:10px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--dim);padding:6px 10px;border-bottom:2px solid var(--border);text-align:left;background:var(--gray1);}
.gym-tbl th.tc{text-align:center;}
.gym-tbl td{padding:7px 10px;border-bottom:1px solid var(--gray2);font-size:12px;}
.gym-tbl tr:last-child td{border-bottom:none;}
.gym-tbl tbody tr:hover td{background:var(--gray1);}
/* win bar inline */
.win-bar{display:flex;align-items:center;gap:4px;}
.win-bar-track{width:60px;height:6px;background:var(--gray2);border-radius:1px;overflow:hidden;}
.win-bar-fill{height:100%;background:var(--win);}
@media(max-width:640px){
    .stats-strip{grid-template-columns:1fr 1fr;}
    .sc:nth-child(2){border-right:none;}
    .sc:nth-child(3),.sc:nth-child(4){border-top:1px solid var(--border);}
    .an-wrap{padding:8px;}
    .an-cols{grid-template-columns:1fr;}
    .tgrid{grid-template-columns:1fr 1fr;}
    .ggrid{grid-template-columns:1fr;}
    .win-bar-track{width:50px;}
    .mtbl,.ptbl,.gym-tbl{font-size:13px;}
    .mtbl th,.ptbl th,.gym-tbl th{font-size:11px;padding:7px 8px;}
    .mtbl td,.ptbl td,.gym-tbl td{padding:9px 8px;}
}
</style>

<div class="an-wrap">

<div class="stats-strip">
    <div class="sc"><span class="sv"><?php echo $te;?></span><span class="sl">Events</span></div>
    <div class="sc"><span class="sv"><?php echo $tb;?></span><span class="sl">Bouts</span></div>
    <div class="sc"><span class="sv"><?php echo $tf;?></span><span class="sl">Fighters</span></div>
    <div class="sc"><span class="sv"><?php echo $fr;?>%</span><span class="sl">Finish Rate</span></div>
</div>

<div class="an-cols">
<!-- LEFT: main tables -->
<div>
    <!-- Result methods -->
    <div class="sec-hd" style="margin-bottom:0;">Result Methods</div>
    <div style="background:var(--white);border:1px solid var(--border);margin-bottom:10px;padding:10px 12px;">
    <?php if(empty($mrows)):?><div style="color:var(--dim);font-size:12px;padding:8px 0;">No results yet.</div>
    <?php else:$fm=['KO'=>'bfill-ko','TKO'=>'bfill-tko','RSC'=>'bfill-rsc','decision'=>'bfill-dec','no_contest'=>'bfill-nc','DRAW'=>'bfill-draw'];
    foreach($mrows as $r):$pct=$total_m>0?round(($r['cnt']/$total_m)*100):0;$fc=$fm[$r['result_method']]??'bfill-dec';?>
    <div class="brow">
        <span class="bnm"><?php echo methodLabel($r['result_method']);?></span>
        <div class="btrack"><div class="bfill <?php echo $fc;?>" style="width:<?php echo $pct;?>%"></div></div>
        <span class="bcnt"><?php echo $r['cnt'];?></span>
        <span class="bpct"><?php echo $pct;?>%</span>
    </div>
    <?php endforeach;endif;?>
    </div>

    <!-- Discipline breakdown -->
    <?php if(!empty($drows)):?>
    <div class="sec-hd" style="margin-bottom:0;">By Discipline</div>
    <div style="background:var(--white);border:1px solid var(--border);margin-bottom:10px;padding:10px 12px;">
    <?php $dm=['muay_thai'=>'bfill-mt','kickboxing'=>'bfill-kb','mma'=>'bfill-mma'];$dl=['muay_thai'=>'Muay Thai','kickboxing'=>'Kickboxing','mma'=>'MMA'];
    foreach($drows as $dr):$pct=$total_d>0?round(($dr['cnt']/$total_d)*100):0;$fc=$dm[$dr['discipline']]??'bfill-mt';?>
    <div class="brow">
        <span class="bnm"><?php echo $dl[$dr['discipline']]??ucfirst($dr['discipline']);?></span>
        <div class="btrack"><div class="bfill <?php echo $fc;?>" style="width:<?php echo $pct;?>%"></div></div>
        <span class="bcnt"><?php echo $dr['cnt'];?></span>
        <span class="bpct"><?php echo $pct;?>%</span>
    </div>
    <?php endforeach;?>
    </div>
    <?php endif;?>

    <!-- Medal tally -->
    <?php if(!empty($medals)):?>
    <div class="sec-hd" style="margin-bottom:0;">Medal Tally — By Gym</div>
    <div style="background:var(--white);border:1px solid var(--border);margin-bottom:10px;">
    <table class="mtbl">
        <thead><tr><th style="width:28px;">#</th><th>Gym / Team</th><th class="tc">Gold</th><th class="tc">Silver</th><th class="tc">Bronze</th></tr></thead>
        <tbody>
        <?php foreach($medals as $i=>$m):$rc=$i===0?'r1':($i===1?'r2':($i===2?'r3':''));?>
        <tr>
            <td><div class="mpos <?php echo $rc;?>"><?php echo $i+1;?></div></td>
            <td style="font-weight:600;"><?php echo htmlspecialchars($m['gym']);?></td>
            <td class="mc"><?php echo $m['gold']>0?"<span class='mc-g'>🥇 {$m['gold']}</span>":"<span class='mc-z'>—</span>";?></td>
            <td class="mc"><?php echo $m['silver']>0?"<span class='mc-s'>🥈 {$m['silver']}</span>":"<span class='mc-z'>—</span>";?></td>
            <td class="mc"><?php echo $m['bronze']>0?"<span class='mc-b'>🥉 {$m['bronze']}</span>":"<span class='mc-z'>—</span>";?></td>
        </tr>
        <?php endforeach;?>
        </tbody>
    </table>
    </div>
    <?php endif;?>

    <!-- Top performers -->
    <?php if(!empty($top)):?>
    <div class="sec-hd" style="margin-bottom:0;">Top Performers</div>
    <div style="background:var(--white);border:1px solid var(--border);margin-bottom:10px;">
    <table class="ptbl">
        <thead><tr><th>Fighter</th><th>Gym</th><th class="tc">Wins</th><th class="tc">Finishes</th><th class="tc">Golds</th></tr></thead>
        <tbody>
        <?php foreach($top as $p):?>
        <tr>
            <td style="font-family:var(--font-c);font-size:13px;font-weight:700;text-transform:uppercase;"><?php echo htmlspecialchars($p['name']);?></td>
            <td style="font-size:11px;color:var(--sub);"><?php echo htmlspecialchars($p['gym']?:'—');?></td>
            <td class="tc" style="font-family:var(--font-m);font-weight:600;color:var(--win);"><?php echo $p['wins'];?></td>
            <td class="tc" style="font-family:var(--font-m);font-weight:600;"><?php echo $p['finishes'];?></td>
            <td class="tc" style="font-family:var(--font-m);font-weight:700;color:#CC9900;"><?php echo $p['golds'];?></td>
        </tr>
        <?php endforeach;?>
        </tbody>
    </table>
    </div>
    <?php endif;?>

    <!-- Gym stats -->
    <?php if(!empty($gym_rows)):?>
    <div class="sec-hd" style="margin-bottom:0;">Gym Performance</div>
    <div style="background:var(--white);border:1px solid var(--border);margin-bottom:10px;">
    <table class="gym-tbl">
        <thead><tr><th>Gym</th><th class="tc">Fighters</th><th class="tc">W</th><th class="tc">L</th><th class="tc">Win%</th><th class="tc">Golds</th></tr></thead>
        <tbody>
        <?php foreach($gym_rows as $g):$tg=$g['wins']+$g['losses'];$wr=$tg>0?round(($g['wins']/$tg)*100):0;?>
        <tr>
            <td style="font-weight:600;"><?php echo htmlspecialchars($g['gym']);?></td>
            <td class="tc" style="color:var(--sub);"><?php echo $g['fighters'];?></td>
            <td class="tc" style="color:var(--win);font-weight:600;"><?php echo $g['wins'];?></td>
            <td class="tc" style="color:var(--red);font-weight:600;"><?php echo $g['losses'];?></td>
            <td class="tc">
                <div class="win-bar">
                    <div class="win-bar-track"><div class="win-bar-fill" style="width:<?php echo $wr;?>%"></div></div>
                    <span style="font-family:var(--font-m);font-size:11px;font-weight:600;"><?php echo $wr;?>%</span>
                </div>
            </td>
            <td class="tc" style="font-weight:700;color:#CC9900;"><?php echo $g['golds'];?></td>
        </tr>
        <?php endforeach;?>
        </tbody>
    </table>
    </div>
    <?php endif;?>
</div>

<!-- RIGHT sidebar: Gold Standard -->
<div>
    <div class="sec-hd" style="margin-bottom:0;">Gold Standard</div>
    <div class="gs-box">
        <div class="gs-inner">
            <span class="gs-lbl">Gold Standard Index</span>
            <div class="gs-donut">
                <svg class="gs-svg" viewBox="0 0 110 110">
                    <circle class="gs-bg" cx="55" cy="55" r="48"/>
                    <circle class="gs-arc" cx="55" cy="55" r="48" stroke="<?php echo '#CC0000';?>"
                        stroke-dasharray="<?php echo round($dash,2).' '.round($gap,2);?>" stroke-dashoffset="0"/>
                </svg>
                <div class="gs-center">
                    <span class="gs-pct"><?php echo number_format($gs,1);?>%</span>
                    <span class="gs-sub">Index</span>
                </div>
            </div>
            <div style="font-size:11px;color:var(--sub);line-height:1.6;">
                <strong style="color:var(--ink);"><?php echo $gc;?> gold medals</strong> &divide;
                <strong style="color:var(--ink);"><?php echo $tf;?> athletes</strong> &times; 100
            </div>
            <div class="gs-breakdown">
                <div class="gs-bc gs-bc-g"><div class="gs-bv"><?php echo $gt;?></div><div class="gs-bl">Gold</div></div>
                <div class="gs-bc gs-bc-s"><div class="gs-bv"><?php echo $st;?></div><div class="gs-bl">Silver</div></div>
                <div class="gs-bc gs-bc-b"><div class="gs-bv"><?php echo $bt;?></div><div class="gs-bl">Bronze</div></div>
            </div>
        </div>
    </div>

    <!-- Quick stats -->
    <div class="sec-hd" style="margin-bottom:0;">Summary</div>
    <div style="background:var(--white);border:1px solid var(--border);">
        <div style="display:flex;justify-content:space-between;align-items:center;padding:7px 10px;border-bottom:1px solid var(--gray2);"><span style="font-size:11px;color:var(--sub);">Total Events</span><strong style="font-family:var(--font-c);font-size:14px;font-weight:700;"><?php echo $te;?></strong></div>
        <div style="display:flex;justify-content:space-between;align-items:center;padding:7px 10px;border-bottom:1px solid var(--gray2);"><span style="font-size:11px;color:var(--sub);">Total Bouts</span><strong style="font-family:var(--font-c);font-size:14px;font-weight:700;"><?php echo $tb;?></strong></div>
        <div style="display:flex;justify-content:space-between;align-items:center;padding:7px 10px;border-bottom:1px solid var(--gray2);"><span style="font-size:11px;color:var(--sub);">Total Fighters</span><strong style="font-family:var(--font-c);font-size:14px;font-weight:700;"><?php echo $tf;?></strong></div>
        <div style="display:flex;justify-content:space-between;align-items:center;padding:7px 10px;border-bottom:1px solid var(--gray2);"><span style="font-size:11px;color:var(--sub);">Finish Rate</span><strong style="font-family:var(--font-c);font-size:14px;font-weight:700;color:var(--red);"><?php echo $fr;?>%</strong></div>
        <div style="display:flex;justify-content:space-between;align-items:center;padding:7px 10px;"><span style="font-size:11px;color:var(--sub);">Gold Medalists</span><strong style="font-family:var(--font-c);font-size:14px;font-weight:700;color:#CC9900;"><?php echo $gt;?></strong></div>
    </div>
</div>
</div><!-- /an-cols -->
</div><!-- /an-wrap -->
<?php thfp_foot();?>