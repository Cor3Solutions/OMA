<?php
require_once 'layout.php';
require_once 'shared.php';
$conn=thfp_db();$all_fighters=[];$bh=[];
if($conn){
    $f_sel=fighterSelect($conn);
    $res=$conn->query("SELECT $f_sel FROM thfp_fighters f ORDER BY name");
    while($f=$res->fetch_assoc())$all_fighters[$f['id']]=$f;
    $rb=$conn->query("SELECT b.*,rf.id AS red_id,rf.name AS red_name,rf.gym AS red_gym,bf.id AS blue_id,bf.name AS blue_name,bf.gym AS blue_gym,wf.name AS winner_name,e.event_number FROM thfp_bouts b LEFT JOIN thfp_fighters rf ON b.red_fighter_id=rf.id LEFT JOIN thfp_fighters bf ON b.blue_fighter_id=bf.id LEFT JOIN thfp_fighters wf ON b.winner_id=wf.id LEFT JOIN thfp_events e ON b.event_id=e.id ORDER BY e.event_date DESC");
    while($b=$rb->fetch_assoc()){$rid=(int)($b['red_id']??0);$bid2=(int)($b['blue_id']??0);$e=['event_number'=>$b['event_number'],'bout_type'=>$b['bout_type'],'discipline'=>$b['discipline']??'muay_thai','result_method'=>$b['result_method']??'','result_round'=>$b['result_round']??null,'winner_name'=>$b['winner_name']??null,'red_name'=>$b['red_name']??'','blue_name'=>$b['blue_name']??'','red_gym'=>$b['red_gym']??'','blue_gym'=>$b['blue_gym']??'','red_id'=>$rid,'blue_id'=>$bid2];if($rid)$bh[$rid][]=$e;if($bid2)$bh[$bid2][]=$e;}
}
function calcRec($id,$bh){$rec=['muay_thai'=>['w'=>0,'l'=>0,'d'=>0],'kickboxing'=>['w'=>0,'l'=>0,'d'=>0],'mma'=>['w'=>0,'l'=>0,'d'=>0]];foreach($bh[$id]??[] as $b){$disc=$b['discipline']??'muay_thai';if(!isset($rec[$disc]))$rec[$disc]=['w'=>0,'l'=>0,'d'=>0];$myN=$id==$b['red_id']?$b['red_name']:$b['blue_name'];$won=!empty($b['winner_name'])&&$b['winner_name']===$myN;$lost=!empty($b['winner_name'])&&$b['winner_name']!==$myN;if($won)$rec[$disc]['w']++;elseif($b['result_method']==='DRAW')$rec[$disc]['d']++;elseif($b['result_method']!=='no_contest'&&$lost)$rec[$disc]['l']++;}return $rec;}
thfp_head('Fighters');thfp_nav('fighters');
?>
<style>
/* Tapology-style fighter table */
.flt-bar{background:var(--white);border-bottom:1px solid var(--border);}
.flt-inner{max-width:var(--max);margin:0 auto;padding:0 16px;}
.flt-wrap{display:flex;align-items:center;gap:4px;padding:6px 0;}
.flt-cnt{font-size:11px;color:var(--dim);margin-left:auto;}
/* search bar like Tapology */
.fighter-search-wrap{background:var(--red);padding:8px 16px;}
.fighter-search-inner{max-width:var(--max);margin:0 auto;display:flex;align-items:center;gap:8px;}
.fighter-search{flex:1;max-width:320px;display:flex;}
.fighter-search input{flex:1;background:#fff;border:none;padding:5px 10px;font-size:12px;font-family:var(--font-r);outline:none;color:var(--ink);}
.fighter-search button{background:#1A1A1A;color:#fff;border:none;padding:5px 12px;font-family:var(--font-c);font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;cursor:pointer;}
/* fighter list table */
.fighter-tbl{width:100%;border-collapse:collapse;background:var(--white);border:1px solid var(--border);}
.fighter-tbl th{font-family:var(--font-c);font-size:10px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--dim);padding:7px 10px;border-bottom:2px solid var(--border);text-align:left;white-space:nowrap;background:var(--gray1);}
.fighter-tbl th.tc{text-align:center;}
.fighter-tbl td{padding:9px 10px;border-bottom:1px solid var(--gray2);font-size:12px;vertical-align:middle;}
.fighter-tbl tr:last-child td{border-bottom:none;}
.fighter-tbl tbody tr{cursor:pointer;transition:background var(--ease);}
.fighter-tbl tbody tr:hover td{background:var(--gray1);}
.fighter-tbl tbody tr:hover .ft-name{color:var(--red);}
/* fighter cell */
.ft-cell{display:flex;align-items:center;gap:8px;}
.ft-av{width:40px;height:40px;flex-shrink:0;background:var(--gray2);border:1px solid var(--border);display:grid;place-items:center;font-family:var(--font-c);font-weight:700;font-size:.9rem;color:var(--dim);overflow:hidden;}
.ft-av img{width:100%;height:100%;object-fit:cover;}
.ft-name{font-family:var(--font-c);font-size:14px;font-weight:700;text-transform:uppercase;color:var(--ink);line-height:1.2;transition:color var(--ease);}
.ft-nick{font-size:10px;color:var(--dim);font-style:italic;margin-top:1px;}
.ft-rec{font-family:var(--font-m);font-size:11px;font-weight:500;}
.ft-rec .w{color:var(--win);font-weight:600;}.ft-rec .s{color:var(--dim);}.ft-rec .l{color:var(--red);font-weight:600;}.ft-rec .d{color:var(--draw);}
</style>

<!-- Red search bar à la Tapology -->
<div class="fighter-search-wrap">
<div class="fighter-search-inner">
    <div class="fighter-search">
        <input type="text" id="fsearch" placeholder="Search fighters..." oninput="searchFighters(this.value)">
        <button>Search</button>
    </div>
    <span style="font-family:var(--font-c);font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:rgba(255,255,255,.7);" id="fcnt"><?php echo count($all_fighters);?> Fighters</span>
</div>
</div>

<!-- Filters -->
<div class="flt-bar">
<div class="flt-inner">
<div class="flt-wrap">
    <div style="display:flex;gap:2px;">
        <button class="ftab on" onclick="pf('g','all',this)">All</button>
        <button class="ftab" onclick="pf('g','male',this)">Male</button>
        <button class="ftab" onclick="pf('g','female',this)">Female</button>
    </div>
    <div style="width:1px;background:var(--border);margin:0 4px;"></div>
    <div style="display:flex;gap:2px;">
        <button class="ftab on" onclick="pf('a','all',this)">All Ages</button>
        <button class="ftab" onclick="pf('a','junior',this)">Junior</button>
        <button class="ftab" onclick="pf('a','senior',this)">Senior</button>
    </div>
</div>
</div>
</div>

<div class="wrap">
<div class="sec-hd" style="margin-bottom:0;">
    Fighter Roster
    <span style="font-size:11px;font-weight:400;letter-spacing:0;text-transform:none;color:#aaa;"><?php echo count($all_fighters);?> registered</span>
</div>
<table class="fighter-tbl" id="fTable">
    <thead>
        <tr>
            <th style="width:40%;">Fighter</th>
            <th>Gym / Team</th>
            <th class="tc">Weight</th>
            <th class="tc">Division</th>
            <th class="tc">Muay Thai</th>
            <th class="tc">Kickboxing</th>
            <th class="tc">MMA</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach($all_fighters as $f):
        $pp=photoUrl(safeGet($f,'photo_path',''));
        $g=strtolower(safeGet($f,'gender',''));
        $a=strtolower(safeGet($f,'age_category',''));
        $wc=safeGet($f,'weight_class','');
        $rec=calcRec($f['id'],$bh);
        $tw=$rec['muay_thai']['w']+$rec['kickboxing']['w']+$rec['mma']['w'];
        $tl=$rec['muay_thai']['l']+$rec['kickboxing']['l']+$rec['mma']['l'];
        $td=$rec['muay_thai']['d']+$rec['kickboxing']['d']+$rec['mma']['d'];
    ?>
    <tr data-g="<?php echo $g;?>" data-a="<?php echo $a;?>" data-name="<?php echo strtolower($f['name']);?>" onclick="of(<?php echo (int)$f['id'];?>)">
        <td>
            <div class="ft-cell">
                <div class="ft-av">
                    <?php if($pp):?><img src="<?php echo htmlspecialchars($pp);?>" alt="" onerror="this.parentNode.textContent='<?php echo initials($f['name']);?>'">
                    <?php else: echo initials($f['name']);endif;?>
                </div>
                <div>
                    <div class="ft-name"><?php echo htmlspecialchars($f['name']);?></div>
                    <?php if(!empty($f['nickname']??'')):?><div class="ft-nick">"<?php echo htmlspecialchars($f['nickname']??'');?>"</div><?php endif;?>
                </div>
            </div>
        </td>
        <td style="font-size:11px;color:var(--sub);"><?php echo htmlspecialchars(safeGet($f,'gym','—'));?></td>
        <td class="tc"><span style="font-family:var(--font-c);font-weight:700;"><?php echo $wc?$wc.' kg':'—';?></span></td>
        <td class="tc">
            <?php if($g):?><span class="tag tag-<?php echo $g==='female'?'f':'m';?>"><?php echo ucfirst($g);?></span><?php endif;?>
            <?php if($a):?><span class="tag tag-<?php echo $a==='senior'?'sr':'jr';?>"><?php echo ucfirst($a);?></span><?php endif;?>
        </td>
        <td class="tc"><div class="ft-rec"><span class="w"><?php echo $rec['muay_thai']['w'];?></span><span class="s">-</span><span class="l"><?php echo $rec['muay_thai']['l'];?></span><span class="s">-</span><span class="d"><?php echo $rec['muay_thai']['d'];?></span></div></td>
        <td class="tc"><div class="ft-rec"><span class="w"><?php echo $rec['kickboxing']['w'];?></span><span class="s">-</span><span class="l"><?php echo $rec['kickboxing']['l'];?></span><span class="s">-</span><span class="d"><?php echo $rec['kickboxing']['d'];?></span></div></td>
        <td class="tc"><div class="ft-rec"><span class="w"><?php echo $rec['mma']['w'];?></span><span class="s">-</span><span class="l"><?php echo $rec['mma']['l'];?></span><span class="s">-</span><span class="d"><?php echo $rec['mma']['d'];?></span></div></td>
    </tr>
    <?php endforeach;?>
    </tbody>
</table>
</div>

<?php
$fmap=array_values($all_fighters);
foreach($fmap as &$_f){$_f['photo_path']=photoUrl($_f['photo_path']??'');foreach(['nickname','date_of_birth','height','reach','nationality','hometown'] as $k)if(!isset($_f[$k]))$_f[$k]='';}unset($_f);
?>
<!-- Fighter Modal -->
<div class="fmo" id="fmo" onclick="if(event.target===this)cf()">
<div class="fmb">
    <div class="fm-cbar">
        <span style="font-family:var(--font-c);font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:#aaa;" id="fmPromo">THFP Fighter</span>
        <button class="fm-cbtn" onclick="cf()">&#10005; Close</button>
    </div>
    <div class="fm-top">
        <div class="fm-photo" id="fmPhoto"><div class="fm-photo-init" id="fmInit">?</div></div>
        <div class="fm-hinfo">
            <div class="fm-hnat" id="fmNat">Philippines</div>
            <div class="fm-hname" id="fmName">—</div>
            <div class="fm-hnick" id="fmNick" style="display:none"></div>
            <div class="fm-hgym" id="fmGym">—</div>
            <div class="fm-htags" id="fmTags"></div>
        </div>
    </div>
    <div class="fm-recbar">
        <div class="fm-ri"><span class="fm-rv" id="fmW">0</span><span class="fm-rl">Wins</span></div>
        <div class="fm-rsep"></div>
        <div class="fm-ri"><span class="fm-rv" id="fmL" style="color:#ffcccc;">0</span><span class="fm-rl">Losses</span></div>
        <div class="fm-rsep"></div>
        <div class="fm-ri"><span class="fm-rv" id="fmD">0</span><span class="fm-rl">Draws</span></div>
        <div class="fm-wc-right"><div class="fm-wc-lbl">Weight Class</div><div class="fm-wc-val" id="fmWC">—</div></div>
    </div>
    <div class="fm-dstrip">
        <div class="fm-dc mt"><span class="fm-dn">Muay Thai</span><div class="fm-dr"><span class="w" id="fmMTW">0</span><span class="s">-</span><span class="l" id="fmMTL">0</span><span class="s">-</span><span class="d" id="fmMTD">0</span></div></div>
        <div class="fm-dc kb"><span class="fm-dn">Kickboxing</span><div class="fm-dr"><span class="w" id="fmKBW">0</span><span class="s">-</span><span class="l" id="fmKBL">0</span><span class="s">-</span><span class="d" id="fmKBD">0</span></div></div>
        <div class="fm-dc mm"><span class="fm-dn">MMA</span><div class="fm-dr"><span class="w" id="fmMMAW">0</span><span class="s">-</span><span class="l" id="fmMMAL">0</span><span class="s">-</span><span class="d" id="fmMMAD">0</span></div></div>
    </div>
    <div class="fm-body">
        <div class="fm-shd">Fighter Details</div>
        <div class="fm-sg">
            <div class="fm-sc"><div class="fm-sl">Division</div><div class="fm-sv" id="fmDiv">—</div></div>
            <div class="fm-sc"><div class="fm-sl">Date of Birth</div><div class="fm-sv" id="fmDOB">—</div></div>
            <div class="fm-sc"><div class="fm-sl">Age</div><div class="fm-sv" id="fmAge">—</div></div>
            <div class="fm-sc"><div class="fm-sl">Nationality</div><div class="fm-sv" id="fmNat2">—</div></div>
            <div class="fm-sc"><div class="fm-sl">Height</div><div class="fm-sv" id="fmH">—</div></div>
            <div class="fm-sc"><div class="fm-sl">Reach</div><div class="fm-sv" id="fmR">—</div></div>
            <div class="fm-sc full"><div class="fm-sl">Hometown</div><div class="fm-sv" id="fmTown">—</div></div>
        </div>
        <div class="fm-shd">Fight History</div>
        <div id="fmHist"></div>
    </div>
</div>
</div>

<script>
var FM={},BH=<?php echo json_encode($bh,JSON_HEX_TAG);?>;
var FD=<?php echo json_encode($fmap,JSON_HEX_TAG);?>;
FD.forEach(function(f){FM[f.id]=f;});
var pfs={g:'all',a:'all'};
function pf(type,val,btn){pfs[type]=val;btn.closest('div').querySelectorAll('.ftab').forEach(function(p){p.classList.remove('on');});btn.classList.add('on');applyFilters();}
function searchFighters(q){applyFilters(q);}
function applyFilters(q){q=(q||document.getElementById('fsearch').value||'').toLowerCase();var v=0;document.querySelectorAll('#fTable tbody tr').forEach(function(r){var gok=pfs.g==='all'||r.dataset.g===pfs.g;var aok=pfs.a==='all'||r.dataset.a===pfs.a;var qok=!q||r.dataset.name.includes(q);var ok=gok&&aok&&qok;r.style.display=ok?'':'none';if(ok)v++;});document.getElementById('fcnt').textContent=v+' Fighter'+(v!==1?'s':'');}
function ini(n){var p=(n||'').trim().split(/\s+/).filter(Boolean);return((p[0]||'')[0]+(p.length>1?(p[p.length-1]||'')[0]:'')).toUpperCase()||'?';}
function cap(s){return s?s.charAt(0).toUpperCase()+s.slice(1):'';}
function sv(id,v){var e=document.getElementById(id);if(!e)return;e.textContent=v||'—';e.className='fm-sv'+(v?'':' empty');}
function ml(m){return{KO:'KO',TKO:'TKO',RSC:'RSC',decision:'Decision',no_contest:'No Contest',DRAW:'Draw'}[m]||(m?m.toUpperCase():'—');}
function calcRec(id){var rec={muay_thai:{w:0,l:0,d:0},kickboxing:{w:0,l:0,d:0},mma:{w:0,l:0,d:0}};(BH[id]||[]).forEach(function(b){var disc=b.discipline||'muay_thai';if(!rec[disc])rec[disc]={w:0,l:0,d:0};var myN=(id==b.red_id)?b.red_name:b.blue_name;var won=b.winner_name&&b.winner_name===myN;var lost=b.winner_name&&b.winner_name!==myN;if(won)rec[disc].w++;else if(b.result_method==='DRAW')rec[disc].d++;else if(b.result_method!=='no_contest'&&lost)rec[disc].l++;});return rec;}
function of(id){
    var f=FM[id];if(!f)return;
    var ph=document.getElementById('fmPhoto');var init=document.getElementById('fmInit');
    if(f.photo_path){ph.innerHTML='';var img=new Image();img.src=f.photo_path;img.style.cssText='width:100%;height:100%;object-fit:cover;display:block;';img.onerror=function(){ph.innerHTML='';init.textContent=ini(f.name);ph.appendChild(init);};ph.appendChild(img);}
    else{ph.innerHTML='';init.textContent=ini(f.name);ph.appendChild(init);}
    document.getElementById('fmName').textContent=f.name;document.getElementById('fmPromo').textContent=(f.gym||'THFP')+' Fighter';
    var nk=document.getElementById('fmNick');nk.style.display=f.nickname?'':'none';if(f.nickname)nk.textContent='"'+f.nickname+'"';
    document.getElementById('fmGym').textContent=f.gym||'—';document.getElementById('fmNat').textContent=f.nationality||'Philippines';
    var tags=document.getElementById('fmTags');tags.innerHTML='';
    [[f.weight_class?f.weight_class+' kg':null,'tag'],[f.gender?cap(f.gender):null,'tag tag-'+(f.gender==='female'?'f':'m')],[f.age_category?cap(f.age_category):null,'tag tag-'+(f.age_category&&f.age_category.toLowerCase()==='senior'?'sr':'jr')]].forEach(function(t){if(t[0]){var s=document.createElement('span');s.className=t[1];s.textContent=t[0];tags.appendChild(s);}});
    var rec=calcRec(id);var tw=rec.muay_thai.w+rec.kickboxing.w+rec.mma.w;var tl=rec.muay_thai.l+rec.kickboxing.l+rec.mma.l;var td=rec.muay_thai.d+rec.kickboxing.d+rec.mma.d;
    document.getElementById('fmW').textContent=tw;document.getElementById('fmL').textContent=tl;document.getElementById('fmD').textContent=td;
    document.getElementById('fmWC').textContent=f.weight_class?f.weight_class+' kg':'—';
    ['MT','KB','MMA'].forEach(function(d,i){var disc=['muay_thai','kickboxing','mma'][i];document.getElementById('fm'+d+'W').textContent=rec[disc].w;document.getElementById('fm'+d+'L').textContent=rec[disc].l;document.getElementById('fm'+d+'D').textContent=rec[disc].d;});
    sv('fmDiv',[cap(f.age_category),cap(f.gender)].filter(Boolean).join(' / ')||null);sv('fmH',f.height||null);sv('fmR',f.reach||null);sv('fmNat2',f.nationality||null);sv('fmTown',f.hometown||null);
    var dob=document.getElementById('fmDOB'),age=document.getElementById('fmAge');
    if(f.date_of_birth){var d=new Date(f.date_of_birth);dob.textContent=d.toLocaleDateString('en-US',{year:'numeric',month:'short',day:'numeric'});dob.className='fm-stat-val';age.textContent=Math.floor((new Date()-d)/31557600000)+' yrs';age.className='fm-stat-val';}
    else{dob.textContent='—';dob.className='fm-stat-val mt';age.textContent='—';age.className='fm-stat-val mt';}
    var hist=document.getElementById('fmHist');hist.innerHTML='';
    var bouts=BH[id]||[];
    if(!bouts.length){hist.innerHTML='<div style="padding:14px;color:var(--dim);font-size:14px;font-style:italic;">No recorded bouts.</div>';}
    else{bouts.forEach(function(b){var myN=(id==b.red_id)?b.red_name:b.blue_name;var won=b.winner_name&&b.winner_name===myN;var lost=b.winner_name&&!won&&b.result_method!=='no_contest';var opp=id==b.red_id?b.blue_name:b.red_name;var og=id==b.red_id?b.blue_gym:b.red_gym;var dl={'muay_thai':'Muay Thai','kickboxing':'Kickboxing','mma':'MMA'}[b.discipline||'muay_thai']||'Muay Thai';var bclass=won?'rb-w':lost?'rb-l':'rb-nc';var blabel=won?'Win':lost?'Loss':'NC';var dsc=b.discipline==='kickboxing'?'kb':b.discipline==='mma'?'mma':'mt';var row=document.createElement('div');row.className='fm-hr';row.innerHTML='<div style="text-align:center"><span class="rb '+bclass+'">'+blabel+'</span></div><div class="fm-hopp"><div class="fm-hopp-n">vs. '+(opp||'TBA')+'</div>'+(og?'<div class="fm-hopp-g">'+og+'</div>':'')+'<div class="fm-hdisc"><span class="disc disc-'+dsc+'">'+dl+'</span></div></div><div class="fm-hres"><div class="fm-hmet">'+ml(b.result_method)+(b.result_round?' · R'+b.result_round:'')+'</div><div class="fm-hev">C'+b.event_number+' · '+cap(b.bout_type)+'</div></div>';hist.appendChild(row);});}
    document.getElementById('fmo').classList.add('open');document.body.style.overflow='hidden';
}
function cf(){document.getElementById('fmo').classList.remove('open');document.body.style.overflow='';}
</script>

<style>
/* ── Fighters page mobile ──────────────────────────────────────────────── */
@media(max-width:700px){
    .fighter-search-wrap{padding:8px;}
    .fighter-search{max-width:100%;}
    .fighter-search input{font-size:16px;} /* prevent iOS zoom */
    .flt-inner{padding:0 8px;}
    /* Hide KB, MMA, Division cols — keep Name, Gym, Weight, MT */
    .fighter-tbl th:nth-child(4),.fighter-tbl td:nth-child(4),
    .fighter-tbl th:nth-child(6),.fighter-tbl td:nth-child(6),
    .fighter-tbl th:nth-child(7),.fighter-tbl td:nth-child(7){display:none;}
    /* Gym col smaller */
    .fighter-tbl td:nth-child(2){font-size:12px;}
    /* Scrollable wrapper */
    #fTable{display:block;overflow-x:auto;-webkit-overflow-scrolling:touch;}
    .ft-av{width:40px;height:40px;}
    .ft-name{font-size:14px;}
    /* Filter tabs wrap */
    .flt-wrap{flex-wrap:wrap;gap:6px;}
    #fcnt{font-size:13px;}
}
@media(max-width:480px){
    /* On very small screens also hide weight col — show only Name + MT record */
    .fighter-tbl th:nth-child(3),.fighter-tbl td:nth-child(3){display:none;}
}
</style>
<?php thfp_foot();?>