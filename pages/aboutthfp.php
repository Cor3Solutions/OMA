<?php
require_once 'layout.php';
require_once 'shared.php';
$conn=thfp_db();
$te=$tb=$tf=$fr=0;$latest=null;
if($conn){
    $te=(int)$conn->query("SELECT COUNT(*) c FROM thfp_events")->fetch_assoc()['c'];
    $tb=(int)$conn->query("SELECT COUNT(*) c FROM thfp_bouts")->fetch_assoc()['c'];
    $tf=(int)$conn->query("SELECT COUNT(*) c FROM thfp_fighters")->fetch_assoc()['c'];
    $fin=(int)$conn->query("SELECT COUNT(*) c FROM thfp_bouts WHERE result_method IN('KO','TKO','RSC')")->fetch_assoc()['c'];
    $tot=(int)$conn->query("SELECT COUNT(*) c FROM thfp_bouts WHERE result_method IS NOT NULL AND result_method!='no_contest'")->fetch_assoc()['c'];
    $fr=$tot>0?round(($fin/$tot)*100):0;
    $latest=$conn->query("SELECT * FROM thfp_events ORDER BY event_date DESC LIMIT 1")->fetch_assoc();
}
thfp_head('About THFP');thfp_nav('aboutthfp');
?>
<style>
.ab-wrap{max-width:var(--max);margin:0 auto;padding:12px 16px;}
.ab-cols{display:grid;grid-template-columns:1fr 240px;gap:12px;align-items:start;}
@media(max-width:900px){.ab-cols{grid-template-columns:1fr;}}

/* ── Dark hero banner with logo ───── */
.ab-banner{background:#111;padding:0;margin-bottom:12px;position:relative;overflow:hidden;display:flex;align-items:stretch;flex-wrap:wrap;}
.ab-banner::before{content:'';position:absolute;inset:0;background:radial-gradient(ellipse 60% 80% at 0% 50%,rgba(204,0,0,.12),transparent);}
.ab-banner-left{padding:20px 24px;display:flex;align-items:center;gap:16px;position:relative;z-index:1;flex:1;}
.ab-banner-logo{height:52px;width:auto;object-fit:contain;filter:brightness(1.1) drop-shadow(0 0 12px rgba(204,0,0,.4));}
.ab-banner-text{}
.ab-org-title{font-family:var(--font-c);font-size:24px;font-weight:900;text-transform:uppercase;letter-spacing:1px;color:#fff;line-height:1;}
.ab-org-title span{color:var(--red);}
.ab-org-sub{font-family:var(--font-c);font-size:10px;font-weight:700;letter-spacing:3px;text-transform:uppercase;color:#777;margin-top:3px;}
.ab-banner-right{display:flex;border-left:1px solid #222;position:relative;z-index:1;}
.ab-stat{text-align:center;padding:20px 18px;border-right:1px solid #222;}
.ab-stat:last-child{border-right:none;}
.ab-sv{font-family:var(--font-c);font-size:28px;font-weight:900;color:var(--red);line-height:1;display:block;}
.ab-sl{font-family:var(--font-c);font-size:9px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:#666;display:block;margin-top:3px;}

/* ── Inaugural event callout ───────── */
.inaugural{background:var(--white);border:1px solid var(--border);border-left:3px solid var(--red);padding:12px 14px;margin-bottom:10px;display:flex;align-items:flex-start;gap:12px;}
.inaugural-icon{font-size:1.6rem;flex-shrink:0;line-height:1;margin-top:2px;}
.inaugural-label{font-family:var(--font-c);font-size:10px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--red);margin-bottom:3px;}
.inaugural-title{font-family:var(--font-c);font-size:14px;font-weight:800;text-transform:uppercase;color:var(--ink);margin-bottom:2px;}
.inaugural-meta{font-size:11px;color:var(--sub);}

/* ── About text ─────────────────────── */
.ab-text{background:var(--white);border:1px solid var(--border);padding:14px;margin-bottom:10px;font-size:13px;color:#333;line-height:1.75;}
.ab-text p{margin-bottom:10px;}.ab-text p:last-child{margin-bottom:0;}
.ab-text strong{color:var(--ink);font-weight:600;}

/* ── Leadership card ────────────────── */
.leader-card{background:var(--white);border:1px solid var(--border);display:flex;align-items:center;gap:12px;padding:12px 14px;margin-bottom:10px;}
.leader-av{width:52px;height:52px;flex-shrink:0;background:#1A1A1A;border:2px solid var(--red);display:grid;place-items:center;font-family:var(--font-c);font-size:1.1rem;font-weight:900;color:var(--red);}
.leader-name{font-family:var(--font-c);font-size:15px;font-weight:800;text-transform:uppercase;color:var(--ink);line-height:1.2;}
.leader-title{font-size:11px;color:var(--sub);margin-top:2px;}
.leader-org{font-family:var(--font-c);font-size:10px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--red);margin-top:4px;}

/* ── Disciplines table ──────────────── */
.disc-tbl{width:100%;border-collapse:collapse;background:var(--white);border:1px solid var(--border);}
.disc-tbl th{font-family:var(--font-c);font-size:10px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--dim);padding:7px 10px;border-bottom:2px solid var(--border);text-align:left;background:var(--gray1);}
.disc-tbl td{padding:12px;border-bottom:1px solid var(--gray2);font-size:14px;vertical-align:top;}
.disc-tbl tr:last-child td{border-bottom:none;}
.disc-icon{font-size:1.5rem;margin-bottom:2px;line-height:1;}
.disc-name{font-family:var(--font-c);font-size:15px;font-weight:800;text-transform:uppercase;margin-bottom:3px;}
.disc-desc{font-size:13px;color:var(--sub);line-height:1.6;}

/* ── Officials ──────────────────────── */
.off-list{background:var(--white);border:1px solid var(--border);}
.off-row{display:flex;gap:8px;padding:10px 12px;border-bottom:1px solid var(--gray2);font-size:14px;align-items:flex-start;}
.off-row:last-child{border-bottom:none;}
.off-lbl{font-family:var(--font-c);font-size:12px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--red);min-width:110px;flex-shrink:0;padding-top:1px;}
.off-val{color:var(--ink);line-height:1.5;}

/* ── Sponsors ───────────────────────── */
.sp-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:4px;}
.sp-item{background:var(--white);border:1px solid var(--border);padding:10px 12px;font-size:13px;color:var(--sub);font-style:italic;}

/* ── CTA bar ────────────────────────── */
.cta-bar{background:var(--red);padding:16px 20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-top:10px;}
.cta-title{font-family:var(--font-c);font-size:20px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#fff;}
.cta-sub{font-size:14px;color:rgba(255,255,255,.7);margin-top:4px;}
.cta-btn{background:#fff;color:var(--red);font-family:var(--font-c);font-size:14px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;padding:10px 22px;border:none;cursor:pointer;text-decoration:none;display:inline-block;transition:all var(--ease);}
.cta-btn:hover{background:#1A1A1A;color:#fff;}

@media(max-width:640px){
    .ab-wrap{padding:8px;}
    .ab-banner{flex-direction:column;}
    .ab-banner-right{border-left:none;border-top:1px solid #222;}
    .ab-stat{padding:14px 14px;}
}
</style>

<div class="ab-wrap">

<!-- Dark hero banner -->
<div class="ab-banner">
    <div class="ab-banner-left">
        <img src="img/tapology.png" alt="THFP" class="ab-banner-logo">
        <div class="ab-banner-text">
            <div class="ab-org-title">Tribal Hunters <span>Fight Promotion</span></div>
            <div class="ab-org-sub">Where Warriors Are Forged &mdash; Est. 2026</div>
        </div>
    </div>
    <div class="ab-banner-right">
        <div class="ab-stat"><span class="ab-sv"><?php echo $te;?></span><span class="ab-sl">Events</span></div>
        <div class="ab-stat"><span class="ab-sv"><?php echo $tb;?></span><span class="ab-sl">Bouts</span></div>
        <div class="ab-stat"><span class="ab-sv"><?php echo $tf;?></span><span class="ab-sl">Fighters</span></div>
        <div class="ab-stat"><span class="ab-sv"><?php echo $fr;?>%</span><span class="ab-sl">Finishes</span></div>
    </div>
</div>

<!-- Inaugural event callout -->
<div class="inaugural">
    <div class="inaugural-icon">🏆</div>
    <div>
        <div class="inaugural-label">Inaugural Event</div>
        <div class="inaugural-title">Tribal Hunters &mdash; Combat1: &ldquo;Survival of the Fittest&rdquo;</div>
        <div class="inaugural-meta">April 2026 &middot; Quezon City, Philippines &middot; The event that started it all</div>
    </div>
</div>

<div class="ab-cols">
<div>
    <!-- About -->
    <div class="sec-hd" style="margin-bottom:0;">About THFP</div>
    <div class="ab-text">
        <p><strong>Tribal Hunters Fight Promotion (THFP)</strong> is a dynamic emerging organization dedicated to elevating the world of combat sports. We specialize in organizing meaningful tournaments focused on Muay Thai, Kickboxing, MMA, and Grappling.</p>
        <p>The ultimate goal is to create a platform for fighters to shine — from novice grassroot competitors to seasoned veterans. Our mission is to provide unparalleled opportunities for athletes to showcase their talent, gain invaluable experience, and ultimately conquer the international arena.</p>
        <p>We believe in fostering a <strong>competitive yet supportive environment</strong> where every fighter has the chance to prove their mettle and reach their full potential.</p>
        <p>THFP was established in early 2026 with its inaugural event staged in April, dubbed <strong>Tribal Hunters &mdash; Combat1</strong>, themed <em>"Survival of the Fittest"</em>. The organisation is based at its main office and gym in <strong>Quezon City, Philippines</strong>.</p>
    </div>

    <!-- Leadership -->
    <div class="sec-hd" style="margin-bottom:0;">Leadership</div>
    <div class="leader-card" style="margin-bottom:10px;">
        <div class="leader-av">BT</div>
        <div>
            <div class="leader-name">Ajarn Brendaley C. Tarnate</div>
            <div class="leader-title">Head of Organization</div>
            <div class="leader-org">Tribal Hunters Fight Promotion</div>
        </div>
    </div>

    <!-- Disciplines -->
    <div class="sec-hd" style="margin-bottom:0;">Combat Disciplines</div>
    <table class="disc-tbl" style="margin-bottom:10px;">
        <thead><tr><th style="width:40px;"></th><th>Discipline</th><th>Description</th></tr></thead>
        <tbody>
            <tr>
                <td><div class="disc-icon">🥊</div></td>
                <td><div class="disc-name" style="color:var(--red);">Muay Thai</div></td>
                <td><div class="disc-desc">The Art of Eight Limbs. Our primary discipline rooted in Thai tradition, sanctioned under Sit Kru Sane Siamyout Philippines. Open to juniors and seniors across all weight classes.</div></td>
            </tr>
            <tr>
                <td><div class="disc-icon">🦵</div></td>
                <td><div class="disc-name" style="color:var(--draw);">Kickboxing</div></td>
                <td><div class="disc-desc">Dynamic stand-up striking combining punches and kicks. Open to all age and weight categories across the THFP platform.</div></td>
            </tr>
            <tr>
                <td><div class="disc-icon">🤼</div></td>
                <td><div class="disc-name" style="color:#6B4BC8;">MMA</div></td>
                <td><div class="disc-desc">Mixed Martial Arts combining striking and grappling. THFP supports the full spectrum of combat sports development in the Philippines.</div></td>
            </tr>
            <tr>
                <td><div class="disc-icon">🏅</div></td>
                <td><div class="disc-name" style="color:#1A7A3C;">Grappling</div></td>
                <td><div class="disc-desc">Submission and positional grappling for all levels. THFP is committed to growing the grappling community alongside our striking disciplines.</div></td>
            </tr>
        </tbody>
    </table>

    <!-- Officials from latest event -->
    <?php if(!empty($latest)):?>
    <div class="sec-hd" style="margin-bottom:0;">Event Officials — Combat <?php echo $latest['event_number'];?></div>
    <div class="off-list" style="margin-bottom:10px;">
        <?php if(!empty($latest['tournament_director'])):?>
        <div class="off-row"><span class="off-lbl">Director</span><span class="off-val"><?php echo htmlspecialchars($latest['tournament_director']);?></span></div>
        <?php endif;?>
        <?php if(!empty($latest['mc'])):?>
        <div class="off-row"><span class="off-lbl">MC</span><span class="off-val"><?php echo htmlspecialchars($latest['mc']);?></span></div>
        <?php endif;?>
        <?php if(!empty($latest['sanctioned_by'])):?>
        <div class="off-row"><span class="off-lbl">Sanctioned by</span><span class="off-val"><?php echo htmlspecialchars($latest['sanctioned_by']);?></span></div>
        <?php endif;?>
        <?php if(!empty($latest['officials'])):?>
        <div class="off-row"><span class="off-lbl">Officials</span><span class="off-val"><?php echo htmlspecialchars($latest['officials']);?></span></div>
        <?php endif;?>
        <?php if(!empty($latest['production_team'])):?>
        <div class="off-row"><span class="off-lbl">Production</span><span class="off-val"><?php echo htmlspecialchars($latest['production_team']);?></span></div>
        <?php endif;?>
    </div>

    <?php if(!empty($latest['sponsors'])):?>
    <div class="sec-hd" style="margin-bottom:0;">Sponsors &amp; Partners</div>
    <div class="sp-grid" style="margin-bottom:10px;">
        <?php foreach(array_filter(array_map('trim',explode(',',$latest['sponsors']))) as $sp):?>
        <div class="sp-item"><?php echo htmlspecialchars($sp);?></div>
        <?php endforeach;?>
    </div>
    <?php endif;?>
    <?php endif;?>
</div>

<!-- Right sidebar -->
<div>
    <div class="sec-hd" style="margin-bottom:0;">Organization</div>
    <div style="background:var(--white);border:1px solid var(--border);margin-bottom:10px;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;padding:7px 10px;border-bottom:1px solid var(--gray2);font-size:11px;gap:8px;"><span style="color:var(--sub);white-space:nowrap;">Head</span><strong style="text-align:right;">Ajarn Brendaley C. Tarnate</strong></div>
        <div style="display:flex;justify-content:space-between;padding:7px 10px;border-bottom:1px solid var(--gray2);font-size:11px;"><span style="color:var(--sub);">Founded</span><strong>Early 2026</strong></div>
        <div style="display:flex;justify-content:space-between;align-items:flex-start;padding:7px 10px;border-bottom:1px solid var(--gray2);font-size:11px;gap:8px;"><span style="color:var(--sub);white-space:nowrap;">Inaugural</span><strong style="text-align:right;">Combat1 — April 2026</strong></div>
        <div style="display:flex;justify-content:space-between;padding:7px 10px;border-bottom:1px solid var(--gray2);font-size:11px;"><span style="color:var(--sub);">Location</span><strong>Quezon City, PH</strong></div>
        <div style="display:flex;justify-content:space-between;align-items:flex-start;padding:7px 10px;border-bottom:1px solid var(--gray2);font-size:11px;gap:8px;"><span style="color:var(--sub);white-space:nowrap;">Academy</span><strong style="text-align:right;">Oriental Muayboran Academy</strong></div>
        <div style="display:flex;justify-content:space-between;align-items:flex-start;padding:7px 10px;font-size:11px;gap:8px;"><span style="color:var(--sub);white-space:nowrap;">Sanctioning</span><strong style="text-align:right;">Sit Kru Sane Siamyout PH</strong></div>
    </div>

    <div class="sec-hd" style="margin-bottom:0;">Disciplines</div>
    <div style="background:var(--white);border:1px solid var(--border);margin-bottom:10px;">
        <div style="display:flex;align-items:center;gap:8px;padding:8px 10px;border-bottom:1px solid var(--gray2);"><span style="font-size:1.1rem;">🥊</span><span style="font-family:var(--font-c);font-size:12px;font-weight:700;text-transform:uppercase;color:var(--red);">Muay Thai</span></div>
        <div style="display:flex;align-items:center;gap:8px;padding:8px 10px;border-bottom:1px solid var(--gray2);"><span style="font-size:1.1rem;">🦵</span><span style="font-family:var(--font-c);font-size:12px;font-weight:700;text-transform:uppercase;color:var(--draw);">Kickboxing</span></div>
        <div style="display:flex;align-items:center;gap:8px;padding:8px 10px;border-bottom:1px solid var(--gray2);"><span style="font-size:1.1rem;">🤼</span><span style="font-family:var(--font-c);font-size:12px;font-weight:700;text-transform:uppercase;color:#6B4BC8;">MMA</span></div>
        <div style="display:flex;align-items:center;gap:8px;padding:8px 10px;"><span style="font-size:1.1rem;">🏅</span><span style="font-family:var(--font-c);font-size:12px;font-weight:700;text-transform:uppercase;color:#1A7A3C;">Grappling</span></div>
    </div>

    <div class="sec-hd" style="margin-bottom:0;">Mission</div>
    <div style="background:var(--white);border:1px solid var(--border);padding:10px 12px;margin-bottom:10px;font-size:11px;color:var(--sub);line-height:1.65;font-style:italic;">
        &ldquo;To provide unparalleled opportunities for athletes to showcase their talent, gain invaluable experience, and conquer the international arena.&rdquo;
    </div>

    <a href="thfp.php" style="display:block;background:var(--red);color:#fff;font-family:var(--font-c);font-size:14px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;padding:12px;text-align:center;text-decoration:none;margin-bottom:6px;">View Events &rsaquo;</a>
    <a href="fighters.php" style="display:block;background:#1A1A1A;color:#fff;font-family:var(--font-c);font-size:14px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;padding:12px;text-align:center;text-decoration:none;">View Fighters &rsaquo;</a>
</div>
</div><!-- /ab-cols -->

<!-- CTA bar -->
<div class="cta-bar">
    <div>
        <div class="cta-title">Join the Tribe</div>
        <div class="cta-sub">Fighter, gym owner, or fan — be part of the THFP community in the Philippines</div>
    </div>
    <a href="thfp.php" class="cta-btn">View Fight Cards &rsaquo;</a>
</div>

</div><!-- /ab-wrap -->
<?php thfp_foot();?>