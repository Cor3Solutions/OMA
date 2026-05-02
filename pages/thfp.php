<?php
// ================================================================
// thfp.php — Tribal Hunters Fight Promotion Public Page
// Fiery dark theme — red, ember, gold palette
// ================================================================

$use_db = false;
if (file_exists('../config/database.php')) {
    try {
        require_once '../config/database.php';
        $conn = getDbConnection();
        $tcheck = @$conn->query("SHOW TABLES LIKE 'thfp_events'");
        if ($tcheck && $tcheck->num_rows > 0) $use_db = true;
    } catch (Exception $e) { $use_db = false; }
}

if ($use_db) {
    $conn->set_charset('utf8mb4');
    $res_events = $conn->query("SELECT * FROM thfp_events ORDER BY event_number DESC");
    $events = [];
    while ($e = $res_events->fetch_assoc()) {
        $e['bouts'] = [];
        $res_bouts = $conn->query("
            SELECT b.*,
                   rf.id AS red_id, rf.name AS red_name, rf.nickname AS red_nick,
                   rf.record_wins AS rw, rf.record_losses AS rl, rf.record_draws AS rd,
                   rf.hometown AS rtown, rf.gym AS red_gym, rf.photo_path AS red_photo,
                   rf.weight_class AS red_wc, rf.gender AS red_gender, rf.age_category AS red_age,
                   rf.date_of_birth AS red_dob, rf.nationality AS red_nation,
                   rf.fighting_out_of AS red_fout, rf.height AS red_height,
                   rf.reach AS red_reach, rf.last_weigh_in AS red_lw,
                   rf.current_streak AS red_streak, rf.affiliation AS red_affil,
                   rf.notes AS red_notes, rf.status AS red_status,
                   bf.id AS blue_id, bf.name AS blue_name, bf.nickname AS blue_nick,
                   bf.record_wins AS bw, bf.record_losses AS bl, bf.record_draws AS bd2,
                   bf.hometown AS btown, bf.gym AS blue_gym, bf.photo_path AS blue_photo,
                   bf.weight_class AS blue_wc, bf.gender AS blue_gender, bf.age_category AS blue_age,
                   bf.date_of_birth AS blue_dob, bf.nationality AS blue_nation,
                   bf.fighting_out_of AS blue_fout, bf.height AS blue_height,
                   bf.reach AS blue_reach, bf.last_weigh_in AS blue_lw,
                   bf.current_streak AS blue_streak, bf.affiliation AS blue_affil,
                   bf.notes AS blue_notes, bf.status AS blue_status,
                   wf.name AS winner_name
            FROM thfp_bouts b
            LEFT JOIN thfp_fighters rf ON b.red_fighter_id = rf.id
            LEFT JOIN thfp_fighters bf ON b.blue_fighter_id = bf.id
            LEFT JOIN thfp_fighters wf ON b.winner_id = wf.id
            WHERE b.event_id = {$e['id']}
            ORDER BY b.bout_order DESC
        ");
        while ($b = $res_bouts->fetch_assoc()) $e['bouts'][] = $b;
        $events[] = $e;
    }
    $ev_count  = (int)$conn->query("SELECT COUNT(*) c FROM thfp_events")->fetch_assoc()['c'];
    $bo_count  = (int)$conn->query("SELECT COUNT(*) c FROM thfp_bouts")->fetch_assoc()['c'];
    $fi_count  = (int)$conn->query("SELECT COUNT(*) c FROM thfp_fighters")->fetch_assoc()['c'];
    $fin_res   = (int)$conn->query("SELECT COUNT(*) c FROM thfp_bouts WHERE result_method IN ('KO','TKO','RSC','SUB')")->fetch_assoc()['c'];
    $res_total = (int)$conn->query("SELECT COUNT(*) c FROM thfp_bouts WHERE result_method != '' AND result_method IS NOT NULL")->fetch_assoc()['c'];
    $finish_rate = $res_total > 0 ? round(($fin_res / $res_total) * 100) : 0;
    $res_fi = $conn->query("SELECT * FROM thfp_fighters WHERE status='active' ORDER BY weight_class, record_wins DESC");
    $fighters_all = [];
    while ($f = $res_fi->fetch_assoc()) $fighters_all[$f['weight_class']][] = $f;
    $all_fighters_map = [];
    $res_all = $conn->query("SELECT * FROM thfp_fighters ORDER BY name");
    while ($f = $res_all->fetch_assoc()) $all_fighters_map[$f['id']] = $f;
    $total_events = $ev_count; $total_bouts = $bo_count; $total_fighters = $fi_count;
} else {
    // ── Demo data from Excel bout results ──
    $events = [[
        'id'=>1,'event_number'=>1,'name'=>'Combat 1',
        'event_date'=>'2025-03-15','venue'=>'Masbate Coliseum, Masbate City','status'=>'completed',
        'tournament_director'=>'','mc'=>'','sanctioned_by'=>'','officials'=>'','production_team'=>'','sponsors'=>'',
        'bouts'=>[
            ['bout_order'=>16,'bout_type'=>'main_event','weight_class'=>'105 kg','age_category'=>'Senior','gender'=>'Male',
             'red_name'=>'Buboy Palles','red_nick'=>'','rw'=>0,'rl'=>1,'rd'=>0,'rtown'=>'','red_gym'=>'Combat Hunters Tribe','red_photo'=>'','red_id'=>31,'red_notes'=>'','red_status'=>'active','red_dob'=>'','red_nation'=>'','red_fout'=>'','red_height'=>'','red_reach'=>'','red_lw'=>'','red_streak'=>'','red_affil'=>'',
             'blue_name'=>'Jonathan Israel Faustino','blue_nick'=>'','bw'=>1,'bl'=>0,'bd2'=>0,'btown'=>'','blue_gym'=>'All-In Fitness Gym','blue_photo'=>'','blue_id'=>32,'blue_notes'=>'','blue_status'=>'active','blue_dob'=>'','blue_nation'=>'','blue_fout'=>'','blue_height'=>'','blue_reach'=>'','blue_lw'=>'','blue_streak'=>'','blue_affil'=>'',
             'winner_name'=>'Jonathan Israel Faustino','result_method'=>'TKO','result_round'=>2,'result_time'=>'','decision_type'=>''],
            ['bout_order'=>20,'bout_type'=>'final','weight_class'=>'63.5 kg','age_category'=>'Senior','gender'=>'Male',
             'red_name'=>'Timothy Ike Enriquez','red_nick'=>'','rw'=>2,'rl'=>0,'rd'=>0,'rtown'=>'','red_gym'=>'Silverback MMA Tuguegarao','red_photo'=>'','red_id'=>15,'red_notes'=>'','red_status'=>'active','red_dob'=>'','red_nation'=>'','red_fout'=>'','red_height'=>'','red_reach'=>'','red_lw'=>'','red_streak'=>'','red_affil'=>'',
             'blue_name'=>'Jeremie V. Cabatan','blue_nick'=>'','bw'=>1,'bl'=>1,'bd2'=>0,'btown'=>'','blue_gym'=>'Ciudad de Cavite Combat Club','blue_photo'=>'','blue_id'=>18,'blue_notes'=>'','blue_status'=>'active','blue_dob'=>'','blue_nation'=>'','blue_fout'=>'','blue_height'=>'','blue_reach'=>'','blue_lw'=>'','blue_streak'=>'','blue_affil'=>'',
             'winner_name'=>'Timothy Ike Enriquez','result_method'=>'DEC','result_round'=>null,'result_time'=>'','decision_type'=>'Unanimous'],
            ['bout_order'=>19,'bout_type'=>'final','weight_class'=>'60 kg','age_category'=>'Senior','gender'=>'Male',
             'red_name'=>'Jessie C. Pangilinan III','red_nick'=>'','rw'=>2,'rl'=>0,'rd'=>0,'rtown'=>'','red_gym'=>'Ciudad de Cavite Combat Club','red_photo'=>'','red_id'=>14,'red_notes'=>'','red_status'=>'active','red_dob'=>'','red_nation'=>'','red_fout'=>'','red_height'=>'','red_reach'=>'','red_lw'=>'','red_streak'=>'','red_affil'=>'',
             'blue_name'=>'Francis Sumortin','blue_nick'=>'','bw'=>0,'bl'=>1,'bd2'=>0,'btown'=>'','blue_gym'=>'Team Sprawl MMA Fitness Gym','blue_photo'=>'','blue_id'=>34,'blue_notes'=>'','blue_status'=>'active','blue_dob'=>'','blue_nation'=>'','blue_fout'=>'','blue_height'=>'','blue_reach'=>'','blue_lw'=>'','blue_streak'=>'','blue_affil'=>'',
             'winner_name'=>'Jessie C. Pangilinan III','result_method'=>'TKO','result_round'=>2,'result_time'=>'','decision_type'=>''],
            ['bout_order'=>18,'bout_type'=>'final','weight_class'=>'57 kg','age_category'=>'Senior','gender'=>'Male',
             'red_name'=>'Thomas B. Cruz','red_nick'=>'','rw'=>1,'rl'=>1,'rd'=>0,'rtown'=>'','red_gym'=>'Team Sprawl MMA Fitness Gym','red_photo'=>'','red_id'=>11,'red_notes'=>'','red_status'=>'active','red_dob'=>'','red_nation'=>'','red_fout'=>'','red_height'=>'','red_reach'=>'','red_lw'=>'','red_streak'=>'','red_affil'=>'',
             'blue_name'=>'Reave Andrei Requino','blue_nick'=>'','bw'=>1,'bl'=>0,'bd2'=>0,'btown'=>'','blue_gym'=>'Silverback MMA Tuguegarao','blue_photo'=>'','blue_id'=>33,'blue_notes'=>'','blue_status'=>'active','blue_dob'=>'','blue_nation'=>'','blue_fout'=>'','blue_height'=>'','blue_reach'=>'','blue_lw'=>'','blue_streak'=>'','blue_affil'=>'',
             'winner_name'=>'Reave Andrei Requino','result_method'=>'DEC','result_round'=>null,'result_time'=>'','decision_type'=>'Unanimous'],
            ['bout_order'=>17,'bout_type'=>'final','weight_class'=>'54 kg','age_category'=>'Senior','gender'=>'Male',
             'red_name'=>'Thoei Seth D. Ablaza','red_nick'=>'','rw'=>1,'rl'=>1,'rd'=>0,'rtown'=>'','red_gym'=>'Nakmuay Camp - Benguet','red_photo'=>'','red_id'=>7,'red_notes'=>'','red_status'=>'active','red_dob'=>'','red_nation'=>'','red_fout'=>'','red_height'=>'','red_reach'=>'','red_lw'=>'','red_streak'=>'','red_affil'=>'',
             'blue_name'=>'Roland James Orengo','blue_nick'=>'','bw'=>2,'bl'=>0,'bd2'=>0,'btown'=>'','blue_gym'=>'Quezon City Rookies','blue_photo'=>'','blue_id'=>9,'blue_notes'=>'','blue_status'=>'active','blue_dob'=>'','blue_nation'=>'','blue_fout'=>'','blue_height'=>'','blue_reach'=>'','blue_lw'=>'','blue_streak'=>'','blue_affil'=>'',
             'winner_name'=>'Roland James Orengo','result_method'=>'DEC','result_round'=>null,'result_time'=>'','decision_type'=>'Split'],
            ['bout_order'=>15,'bout_type'=>'prelim','weight_class'=>'70 kg','age_category'=>'Senior','gender'=>'Male',
             'red_name'=>'Adrian Taliping','red_nick'=>'','rw'=>0,'rl'=>1,'rd'=>0,'rtown'=>'','red_gym'=>'Silverback MMA Tuguegarao','red_photo'=>'','red_id'=>29,'red_notes'=>'','red_status'=>'active','red_dob'=>'','red_nation'=>'','red_fout'=>'','red_height'=>'','red_reach'=>'','red_lw'=>'','red_streak'=>'','red_affil'=>'',
             'blue_name'=>'Joseph Mari Aranzaso','blue_nick'=>'','bw'=>1,'bl'=>0,'bd2'=>0,'btown'=>'','blue_gym'=>'Maharlika Fighters - Malabon City','blue_photo'=>'','blue_id'=>30,'blue_notes'=>'','blue_status'=>'active','blue_dob'=>'','blue_nation'=>'','blue_fout'=>'','blue_height'=>'','blue_reach'=>'','blue_lw'=>'','blue_streak'=>'','blue_affil'=>'',
             'winner_name'=>'Joseph Mari Aranzaso','result_method'=>'DEC','result_round'=>null,'result_time'=>'','decision_type'=>'Unanimous'],
            ['bout_order'=>14,'bout_type'=>'prelim','weight_class'=>'51 kg','age_category'=>'Senior','gender'=>'Male',
             'red_name'=>'Mark John Agbas','red_nick'=>'','rw'=>1,'rl'=>0,'rd'=>0,'rtown'=>'','red_gym'=>'Maharlika Fighters - Malabon City','red_photo'=>'','red_id'=>27,'red_notes'=>'','red_status'=>'active','red_dob'=>'','red_nation'=>'','red_fout'=>'','red_height'=>'','red_reach'=>'','red_lw'=>'','red_streak'=>'','red_affil'=>'',
             'blue_name'=>'Abenes Mark Gabriel','blue_nick'=>'','bw'=>0,'bl'=>1,'bd2'=>0,'btown'=>'','blue_gym'=>"Athlete's Fuel Muay Thai - Taytay, Rizal",'blue_photo'=>'','blue_id'=>28,'blue_notes'=>'','blue_status'=>'active','blue_dob'=>'','blue_nation'=>'','blue_fout'=>'','blue_height'=>'','blue_reach'=>'','blue_lw'=>'','blue_streak'=>'','blue_affil'=>'',
             'winner_name'=>'Mark John Agbas','result_method'=>'KO','result_round'=>2,'result_time'=>'','decision_type'=>''],
            ['bout_order'=>13,'bout_type'=>'prelim','weight_class'=>'54 kg','age_category'=>'Senior','gender'=>'Female',
             'red_name'=>'Marionne Rae David','red_nick'=>'','rw'=>0,'rl'=>1,'rd'=>0,'rtown'=>'','red_gym'=>'Maharlika Fighters - Malabon City','red_photo'=>'','red_id'=>25,'red_notes'=>'','red_status'=>'active','red_dob'=>'','red_nation'=>'','red_fout'=>'','red_height'=>'','red_reach'=>'','red_lw'=>'','red_streak'=>'','red_affil'=>'',
             'blue_name'=>'Stela Alagao','blue_nick'=>'','bw'=>1,'bl'=>0,'bd2'=>0,'btown'=>'','blue_gym'=>'Underground Boxing and Martial Arts Club - Batangas','blue_photo'=>'','blue_id'=>26,'blue_notes'=>'','blue_status'=>'active','blue_dob'=>'','blue_nation'=>'','blue_fout'=>'','blue_height'=>'','blue_reach'=>'','blue_lw'=>'','blue_streak'=>'','blue_affil'=>'',
             'winner_name'=>'Stela Alagao','result_method'=>'DEC','result_round'=>null,'result_time'=>'','decision_type'=>'Unanimous'],
            ['bout_order'=>12,'bout_type'=>'prelim','weight_class'=>'54 kg','age_category'=>'Junior','gender'=>'Male',
             'red_name'=>'Vinhs Castillo','red_nick'=>'','rw'=>0,'rl'=>1,'rd'=>0,'rtown'=>'','red_gym'=>'Combat Arsenal Martial Arts and Fitness','red_photo'=>'','red_id'=>23,'red_notes'=>'','red_status'=>'active','red_dob'=>'','red_nation'=>'','red_fout'=>'','red_height'=>'','red_reach'=>'','red_lw'=>'','red_streak'=>'','red_affil'=>'',
             'blue_name'=>'Mark Andrei Binwag','blue_nick'=>'','bw'=>1,'bl'=>0,'bd2'=>0,'btown'=>'','blue_gym'=>'Silverback MMA Tuguegarao','blue_photo'=>'','blue_id'=>24,'blue_notes'=>'','blue_status'=>'active','blue_dob'=>'','blue_nation'=>'','blue_fout'=>'','blue_height'=>'','blue_reach'=>'','blue_lw'=>'','blue_streak'=>'','blue_affil'=>'',
             'winner_name'=>'Mark Andrei Binwag','result_method'=>'DEC','result_round'=>null,'result_time'=>'','decision_type'=>'Unanimous'],
            ['bout_order'=>11,'bout_type'=>'prelim','weight_class'=>'48 kg','age_category'=>'Senior','gender'=>'Female',
             'red_name'=>'Kinch B. Ba-a','red_nick'=>'','rw'=>1,'rl'=>0,'rd'=>0,'rtown'=>'','red_gym'=>'Nakmuay Camp - Benguet','red_photo'=>'','red_id'=>21,'red_notes'=>'','red_status'=>'active','red_dob'=>'','red_nation'=>'','red_fout'=>'','red_height'=>'','red_reach'=>'','red_lw'=>'','red_streak'=>'','red_affil'=>'',
             'blue_name'=>'Avielle Darauay','blue_nick'=>'','bw'=>0,'bl'=>1,'bd2'=>0,'btown'=>'','blue_gym'=>'Silverback MMA Tuguegarao','blue_photo'=>'','blue_id'=>22,'blue_notes'=>'','blue_status'=>'active','blue_dob'=>'','blue_nation'=>'','blue_fout'=>'','blue_height'=>'','blue_reach'=>'','blue_lw'=>'','blue_streak'=>'','blue_affil'=>'',
             'winner_name'=>'Kinch B. Ba-a','result_method'=>'DEC','result_round'=>null,'result_time'=>'','decision_type'=>'Unanimous'],
            ['bout_order'=>10,'bout_type'=>'prelim','weight_class'=>'48 kg','age_category'=>'Junior','gender'=>'Female',
             'red_name'=>'Lorry Ann Calasiao','red_nick'=>'','rw'=>1,'rl'=>0,'rd'=>0,'rtown'=>'','red_gym'=>'Nakmuay Camp - Benguet','red_photo'=>'','red_id'=>19,'red_notes'=>'','red_status'=>'active','red_dob'=>'','red_nation'=>'','red_fout'=>'','red_height'=>'','red_reach'=>'','red_lw'=>'','red_streak'=>'','red_affil'=>'',
             'blue_name'=>'Shannaya Hope Esguerra','blue_nick'=>'','bw'=>0,'bl'=>1,'bd2'=>0,'btown'=>'','blue_gym'=>'Quezon City Rookies','blue_photo'=>'','blue_id'=>20,'blue_notes'=>'','blue_status'=>'active','blue_dob'=>'','blue_nation'=>'','blue_fout'=>'','blue_height'=>'','blue_reach'=>'','blue_lw'=>'','blue_streak'=>'','blue_affil'=>'',
             'winner_name'=>'Lorry Ann Calasiao','result_method'=>'DEC','result_round'=>null,'result_time'=>'','decision_type'=>'Unanimous'],
            ['bout_order'=>9,'bout_type'=>'prelim','weight_class'=>'63.5 kg','age_category'=>'Senior','gender'=>'Male',
             'red_name'=>'Noel Ramos','red_nick'=>'','rw'=>0,'rl'=>1,'rd'=>0,'rtown'=>'','red_gym'=>"Athlete's Fuel Muay Thai",'red_photo'=>'','red_id'=>17,'red_notes'=>'','red_status'=>'active','red_dob'=>'','red_nation'=>'','red_fout'=>'','red_height'=>'','red_reach'=>'','red_lw'=>'','red_streak'=>'','red_affil'=>'',
             'blue_name'=>'Jeremie V. Cabatan','blue_nick'=>'','bw'=>1,'bl'=>0,'bd2'=>0,'btown'=>'','blue_gym'=>'Ciudad de Cavite Combat Club','blue_photo'=>'','blue_id'=>18,'blue_notes'=>'','blue_status'=>'active','blue_dob'=>'','blue_nation'=>'','blue_fout'=>'','blue_height'=>'','blue_reach'=>'','blue_lw'=>'','blue_streak'=>'','blue_affil'=>'',
             'winner_name'=>'Jeremie V. Cabatan','result_method'=>'KO','result_round'=>2,'result_time'=>'','decision_type'=>''],
            ['bout_order'=>8,'bout_type'=>'prelim','weight_class'=>'63.5 kg','age_category'=>'Senior','gender'=>'Male',
             'red_name'=>'Timothy Ike Enriquez','red_nick'=>'','rw'=>1,'rl'=>0,'rd'=>0,'rtown'=>'','red_gym'=>'Silverback MMA Tuguegarao','red_photo'=>'','red_id'=>15,'red_notes'=>'','red_status'=>'active','red_dob'=>'','red_nation'=>'','red_fout'=>'','red_height'=>'','red_reach'=>'','red_lw'=>'','red_streak'=>'','red_affil'=>'',
             'blue_name'=>'James David Cabana','blue_nick'=>'','bw'=>0,'bl'=>1,'bd2'=>0,'btown'=>'','blue_gym'=>'MMBC Combat Hunters Tribe','blue_photo'=>'','blue_id'=>16,'blue_notes'=>'','blue_status'=>'active','blue_dob'=>'','blue_nation'=>'','blue_fout'=>'','blue_height'=>'','blue_reach'=>'','blue_lw'=>'','blue_streak'=>'','blue_affil'=>'',
             'winner_name'=>'Timothy Ike Enriquez','result_method'=>'TKO','result_round'=>1,'result_time'=>'','decision_type'=>''],
            ['bout_order'=>7,'bout_type'=>'prelim','weight_class'=>'60 kg','age_category'=>'Senior','gender'=>'Male',
             'red_name'=>'Robert Blenzon Mangulab III','red_nick'=>'','rw'=>0,'rl'=>1,'rd'=>0,'rtown'=>'','red_gym'=>'Silverback MMA Tuguegarao','red_photo'=>'','red_id'=>13,'red_notes'=>'','red_status'=>'active','red_dob'=>'','red_nation'=>'','red_fout'=>'','red_height'=>'','red_reach'=>'','red_lw'=>'','red_streak'=>'','red_affil'=>'',
             'blue_name'=>'Jessie C. Pangilinan III','blue_nick'=>'','bw'=>1,'bl'=>0,'bd2'=>0,'btown'=>'','blue_gym'=>'Ciudad de Cavite Combat Club','blue_photo'=>'','blue_id'=>14,'blue_notes'=>'','blue_status'=>'active','blue_dob'=>'','blue_nation'=>'','blue_fout'=>'','blue_height'=>'','blue_reach'=>'','blue_lw'=>'','blue_streak'=>'','blue_affil'=>'',
             'winner_name'=>'Jessie C. Pangilinan III','result_method'=>'DEC','result_round'=>null,'result_time'=>'','decision_type'=>'Split'],
            ['bout_order'=>6,'bout_type'=>'prelim','weight_class'=>'57 kg','age_category'=>'Senior','gender'=>'Male',
             'red_name'=>'Thomas B. Cruz','red_nick'=>'','rw'=>1,'rl'=>0,'rd'=>0,'rtown'=>'','red_gym'=>'Team Sprawl MMA Fitness Gym','red_photo'=>'','red_id'=>11,'red_notes'=>'','red_status'=>'active','red_dob'=>'','red_nation'=>'','red_fout'=>'','red_height'=>'','red_reach'=>'','red_lw'=>'','red_streak'=>'','red_affil'=>'',
             'blue_name'=>'Jimmel Rigua','blue_nick'=>'','bw'=>0,'bl'=>1,'bd2'=>0,'btown'=>'','blue_gym'=>'Silverback MMA Tuguegarao','blue_photo'=>'','blue_id'=>12,'blue_notes'=>'','blue_status'=>'active','blue_dob'=>'','blue_nation'=>'','blue_fout'=>'','blue_height'=>'','blue_reach'=>'','blue_lw'=>'','blue_streak'=>'','blue_affil'=>'',
             'winner_name'=>'Thomas B. Cruz','result_method'=>'DEC','result_round'=>null,'result_time'=>'','decision_type'=>'Split'],
            ['bout_order'=>5,'bout_type'=>'prelim','weight_class'=>'54 kg','age_category'=>'Senior','gender'=>'Male',
             'red_name'=>'Roland James Orengo','red_nick'=>'','rw'=>1,'rl'=>0,'rd'=>0,'rtown'=>'','red_gym'=>'Quezon City Rookies','red_photo'=>'','red_id'=>9,'red_notes'=>'','red_status'=>'active','red_dob'=>'','red_nation'=>'','red_fout'=>'','red_height'=>'','red_reach'=>'','red_lw'=>'','red_streak'=>'','red_affil'=>'',
             'blue_name'=>'Earl Benedict Bodomo','blue_nick'=>'','bw'=>0,'bl'=>1,'bd2'=>0,'btown'=>'','blue_gym'=>'MMBC Combat Hunters Tribe','blue_photo'=>'','blue_id'=>10,'blue_notes'=>'','blue_status'=>'active','blue_dob'=>'','blue_nation'=>'','blue_fout'=>'','blue_height'=>'','blue_reach'=>'','blue_lw'=>'','blue_streak'=>'','blue_affil'=>'',
             'winner_name'=>'Roland James Orengo','result_method'=>'KO','result_round'=>1,'result_time'=>'','decision_type'=>''],
            ['bout_order'=>4,'bout_type'=>'prelim','weight_class'=>'54 kg','age_category'=>'Senior','gender'=>'Male',
             'red_name'=>'Thoei Seth D. Ablaza','red_nick'=>'','rw'=>1,'rl'=>0,'rd'=>0,'rtown'=>'','red_gym'=>'Nakmuay Camp - Benguet','red_photo'=>'','red_id'=>7,'red_notes'=>'','red_status'=>'active','red_dob'=>'','red_nation'=>'','red_fout'=>'','red_height'=>'','red_reach'=>'','red_lw'=>'','red_streak'=>'','red_affil'=>'',
             'blue_name'=>'Mark Ryan Agbas','blue_nick'=>'','bw'=>0,'bl'=>1,'bd2'=>0,'btown'=>'','blue_gym'=>'Maharlika Fighters - Malabon City','blue_photo'=>'','blue_id'=>8,'blue_notes'=>'','blue_status'=>'active','blue_dob'=>'','blue_nation'=>'','blue_fout'=>'','blue_height'=>'','blue_reach'=>'','blue_lw'=>'','blue_streak'=>'','blue_affil'=>'',
             'winner_name'=>'Thoei Seth D. Ablaza','result_method'=>'DEC','result_round'=>null,'result_time'=>'','decision_type'=>'Unanimous'],
            ['bout_order'=>3,'bout_type'=>'prelim','weight_class'=>'67 kg','age_category'=>'Junior','gender'=>'Male',
             'red_name'=>'Joseph Villanueva','red_nick'=>'','rw'=>0,'rl'=>1,'rd'=>0,'rtown'=>'','red_gym'=>'Ciudad de Cavite Combat Club','red_photo'=>'','red_id'=>5,'red_notes'=>'','red_status'=>'active','red_dob'=>'','red_nation'=>'','red_fout'=>'','red_height'=>'','red_reach'=>'','red_lw'=>'','red_streak'=>'','red_affil'=>'',
             'blue_name'=>'Derex Ventura','blue_nick'=>'','bw'=>1,'bl'=>0,'bd2'=>0,'btown'=>'','blue_gym'=>'MMBC Combat Hunters Tribe','blue_photo'=>'','blue_id'=>6,'blue_notes'=>'','blue_status'=>'active','blue_dob'=>'','blue_nation'=>'','blue_fout'=>'','blue_height'=>'','blue_reach'=>'','blue_lw'=>'','blue_streak'=>'','blue_affil'=>'',
             'winner_name'=>'Derex Ventura','result_method'=>'DEC','result_round'=>null,'result_time'=>'','decision_type'=>'Unanimous'],
            ['bout_order'=>2,'bout_type'=>'prelim','weight_class'=>'54 kg','age_category'=>'Junior','gender'=>'Male',
             'red_name'=>'John Mark Gabor','red_nick'=>'','rw'=>0,'rl'=>1,'rd'=>0,'rtown'=>'','red_gym'=>'MMBC Combat Hunters Tribe','red_photo'=>'','red_id'=>3,'red_notes'=>'','red_status'=>'active','red_dob'=>'','red_nation'=>'','red_fout'=>'','red_height'=>'','red_reach'=>'','red_lw'=>'','red_streak'=>'','red_affil'=>'',
             'blue_name'=>'Kevin Chavez','blue_nick'=>'','bw'=>1,'bl'=>0,'bd2'=>0,'btown'=>'','blue_gym'=>'MMBC Combat Hunters Tribe','blue_photo'=>'','blue_id'=>4,'blue_notes'=>'','blue_status'=>'active','blue_dob'=>'','blue_nation'=>'','blue_fout'=>'','blue_height'=>'','blue_reach'=>'','blue_lw'=>'','blue_streak'=>'','blue_affil'=>'',
             'winner_name'=>'Kevin Chavez','result_method'=>'DEC','result_round'=>null,'result_time'=>'','decision_type'=>'Split'],
            ['bout_order'=>1,'bout_type'=>'prelim','weight_class'=>'51 kg','age_category'=>'Junior','gender'=>'Male',
             'red_name'=>'Lance Amaro Arcilla','red_nick'=>'','rw'=>1,'rl'=>0,'rd'=>0,'rtown'=>'','red_gym'=>'Maharlika Fighters - Malabon City','red_photo'=>'','red_id'=>1,'red_notes'=>'','red_status'=>'active','red_dob'=>'','red_nation'=>'','red_fout'=>'','red_height'=>'','red_reach'=>'','red_lw'=>'','red_streak'=>'','red_affil'=>'',
             'blue_name'=>'Jamillo Christian C. Caoile','blue_nick'=>'','bw'=>0,'bl'=>1,'bd2'=>0,'btown'=>'','blue_gym'=>'Combat Arsenal Martial Arts and Fitness','blue_photo'=>'','blue_id'=>2,'blue_notes'=>'','blue_status'=>'active','blue_dob'=>'','blue_nation'=>'','blue_fout'=>'','blue_height'=>'','blue_reach'=>'','blue_lw'=>'','blue_streak'=>'','blue_affil'=>'',
             'winner_name'=>'Lance Amaro Arcilla','result_method'=>'RSC','result_round'=>2,'result_time'=>'','decision_type'=>''],
        ],
    ]];
    $all_fighters_map = [];
    foreach ($events as $ev) foreach ($ev['bouts'] as $b) {
        if (!empty($b['red_id'])) $all_fighters_map[$b['red_id']] = ['id'=>$b['red_id'],'name'=>$b['red_name'],'nickname'=>$b['red_nick'],'gym'=>$b['red_gym'],'record_wins'=>$b['rw'],'record_losses'=>$b['rl'],'record_draws'=>$b['rd'],'hometown'=>$b['rtown'],'weight_class'=>$b['weight_class'],'gender'=>$b['gender'],'age_category'=>$b['age_category'],'photo_path'=>$b['red_photo'],'status'=>'active','notes'=>'','date_of_birth'=>'','nationality'=>'','fighting_out_of'=>'','height'=>'','reach'=>'','last_weigh_in'=>'','current_streak'=>'','affiliation'=>''];
        if (!empty($b['blue_id'])) $all_fighters_map[$b['blue_id']] = ['id'=>$b['blue_id'],'name'=>$b['blue_name'],'nickname'=>$b['blue_nick'],'gym'=>$b['blue_gym'],'record_wins'=>$b['bw'],'record_losses'=>$b['bl'],'record_draws'=>$b['bd2'],'hometown'=>$b['btown'],'weight_class'=>$b['weight_class'],'gender'=>$b['gender'],'age_category'=>$b['age_category'],'photo_path'=>$b['blue_photo'],'status'=>'active','notes'=>'','date_of_birth'=>'','nationality'=>'','fighting_out_of'=>'','height'=>'','reach'=>'','last_weigh_in'=>'','current_streak'=>'','affiliation'=>''];
    }
    $fighters_all = [];
    foreach ($all_fighters_map as $f) $fighters_all[$f['weight_class'].'|'.$f['age_category'].'|'.$f['gender']][] = $f;
    $total_events = 1; $total_bouts = 20; $total_fighters = count($all_fighters_map); $finish_rate = 45;
}

function methodBadge($m) {
    return ['KO'=>'br-ko','TKO'=>'br-tko','RSC'=>'br-rsc','SUB'=>'br-sub','DEC'=>'br-dec','DRAW'=>'br-draw','NC'=>'br-nc'][$m] ?? '';
}
function boutTypeLabel($t) {
    return ['main_event'=>'Main Event','co_main'=>'Co-Main Event','prelim'=>'Prelim','amateur'=>'Amateur','final'=>'Final'][$t] ?? ucfirst($t ?? '');
}
function initials($n) {
    $p = array_filter(explode(' ', trim($n ?? '')));
    if (empty($p)) return '?';
    return strtoupper(substr(reset($p),0,1).substr(end($p),0,1));
}

/**
 * Normalise a photo_path stored by thfpadmin.php into a browser-safe URL.
 *
 * The admin stores paths in one of three forms:
 *   a) root-relative  : /uploads/thfp_fighters/fighter_5_123.jpg   (new, correct)
 *   b) relative       : uploads/thfp_fighters/fighter_5_123.jpg    (old records)
 *   c) absolute URL   : https://…/uploads/…                         (rare, pass-through)
 *
 * This function returns a root-relative or absolute URL safe for use in <img src>.
 */
function photoUrl($path) {
    if (empty($path)) return '';
    // Already absolute URL
    if (preg_match('#^https?://#', $path)) return $path;
    // Already root-relative
    if ($path[0] === '/') return $path;
    // Relative path — make root-relative using the directory this script lives in
    $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
    return $base . '/' . $path;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tribal Hunters Fight Promotion</title>
<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;700;900&family=Rajdhani:wght@400;500;600;700&family=Oswald:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
:root{
    --fire:#E8341A;--fire-light:#FF5A3C;--ember:#FF8C00;
    --gold:#D4AF37;--gold-light:#F0D060;
    --bg:#0A0806;--surface:#141210;--card:#181512;
    --border:rgba(255,255,255,.07);--border-fire:rgba(232,52,26,.22);--border-ember:rgba(255,140,0,.18);
    --ink:#fff;--muted:rgba(255,255,255,.5);--dim:rgba(255,255,255,.22);
    --font-disp:'Cinzel',serif;--font-ui:'Rajdhani',sans-serif;--font-alt:'Oswald',sans-serif;
    --nav-h:66px;
}
html{scroll-behavior:smooth;}
body{background:var(--bg);color:var(--ink);font-family:var(--font-ui);overflow-x:hidden;-webkit-font-smoothing:antialiased;}
::-webkit-scrollbar{width:4px;}::-webkit-scrollbar-track{background:var(--bg);}::-webkit-scrollbar-thumb{background:rgba(232,52,26,.3);border-radius:2px;}
a{color:inherit;text-decoration:none;}img{display:block;max-width:100%;}

/* NAV */
nav{position:sticky;top:0;z-index:200;background:rgba(10,8,6,.96);border-bottom:1px solid var(--border-fire);backdrop-filter:blur(14px);height:var(--nav-h);}
nav::after{content:'';position:absolute;bottom:0;left:0;right:0;height:1px;background:linear-gradient(to right,transparent,var(--fire),var(--ember),transparent);}
.nav-inner{max-width:1280px;margin:0 auto;height:100%;display:flex;align-items:center;justify-content:space-between;padding:0 2rem;}
.nav-logo{display:flex;align-items:center;gap:12px;}
.logo-mark{width:36px;height:36px;border:2px solid var(--fire);transform:rotate(45deg);display:flex;align-items:center;justify-content:center;flex-shrink:0;background:rgba(232,52,26,.1);}
.logo-mark span{transform:rotate(-45deg);font-family:var(--font-disp);font-size:.58rem;font-weight:900;color:var(--fire);}
.logo-text{font-family:var(--font-disp);font-size:.9rem;font-weight:700;line-height:1.2;}
.logo-text small{display:block;font-family:var(--font-ui);font-size:.58rem;color:var(--fire);font-weight:600;letter-spacing:3px;text-transform:uppercase;margin-top:2px;}
.nav-links{display:flex;gap:2.5rem;}
.nav-links a{font-family:var(--font-ui);font-size:.85rem;font-weight:600;letter-spacing:1.5px;text-transform:uppercase;color:var(--muted);transition:color .2s;position:relative;padding:.2rem 0;}
.nav-links a::after{content:'';position:absolute;bottom:-4px;left:0;right:0;height:2px;background:linear-gradient(to right,var(--fire),var(--ember));transform:scaleX(0);transition:transform .25s;transform-origin:left;}
.nav-links a:hover,.nav-links a.active{color:var(--ink);}
.nav-links a:hover::after,.nav-links a.active::after{transform:scaleX(1);}
.hamburger{display:none;flex-direction:column;gap:5px;background:none;border:none;cursor:pointer;padding:6px;}
.hamburger span{display:block;width:22px;height:1.5px;background:var(--fire);border-radius:2px;transition:all .3s;}
.hamburger.open span:nth-child(1){transform:rotate(45deg) translate(5px,5px);}
.hamburger.open span:nth-child(2){opacity:0;}
.hamburger.open span:nth-child(3){transform:rotate(-45deg) translate(5px,-5px);}
.mobile-nav{display:none;position:fixed;top:var(--nav-h);left:0;right:0;z-index:190;background:rgba(10,8,6,.98);border-bottom:1px solid var(--border-fire);padding:1rem 1.5rem;flex-direction:column;gap:.15rem;}
.mobile-nav.open{display:flex;}
.mobile-nav a{font-family:var(--font-ui);font-size:.95rem;font-weight:600;letter-spacing:1.5px;text-transform:uppercase;color:var(--muted);padding:.75rem 1rem;border-radius:3px;transition:all .18s;}
.mobile-nav a:hover{color:var(--fire-light);background:rgba(232,52,26,.08);}

/* HERO */
.hero{background:var(--bg);padding:5rem 2rem 4rem;text-align:center;position:relative;overflow:hidden;min-height:72vh;display:flex;align-items:center;justify-content:center;}
.hero-flames{position:absolute;inset:0;pointer-events:none;background:radial-gradient(ellipse 70% 50% at 50% 100%,rgba(232,52,26,.2),transparent),radial-gradient(ellipse 40% 30% at 20% 80%,rgba(255,140,0,.08),transparent),radial-gradient(ellipse 40% 30% at 80% 80%,rgba(232,52,26,.1),transparent);}
.hero-grid{position:absolute;inset:0;pointer-events:none;background-image:linear-gradient(rgba(232,52,26,.035) 1px,transparent 1px),linear-gradient(90deg,rgba(232,52,26,.035) 1px,transparent 1px);background-size:60px 60px;mask-image:radial-gradient(ellipse 80% 80% at 50% 100%,black,transparent);}
.hero-content{position:relative;z-index:1;max-width:900px;margin:0 auto;width:100%;}
.hero-eyebrow{display:inline-flex;align-items:center;gap:.5rem;font-family:var(--font-ui);font-size:.65rem;font-weight:700;letter-spacing:4px;text-transform:uppercase;color:var(--fire);border:1px solid var(--border-fire);padding:5px 18px;border-radius:2px;margin-bottom:1.8rem;background:rgba(232,52,26,.08);}
.hero-eyebrow::before,.hero-eyebrow::after{content:'◆';font-size:.48rem;color:var(--ember);}
.hero h1{font-family:var(--font-disp);font-size:clamp(3rem,10vw,7.5rem);font-weight:900;line-height:.88;letter-spacing:-1px;margin-bottom:.5rem;}
.hero h1 .line1{display:block;color:var(--ink);}
.hero h1 .line2{display:block;background:linear-gradient(135deg,var(--fire) 0%,var(--ember) 50%,var(--gold) 100%);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;filter:drop-shadow(0 0 28px rgba(232,52,26,.45));}
.hero-sub{font-family:var(--font-disp);font-style:italic;font-size:clamp(.88rem,2vw,1.15rem);color:var(--muted);margin-bottom:2.2rem;letter-spacing:1px;}
.flame-rule{display:flex;align-items:center;justify-content:center;gap:12px;margin:0 auto 2.2rem;max-width:280px;}
.flame-rule-line{flex:1;height:1px;background:linear-gradient(to right,transparent,var(--fire));}
.flame-rule-line.rev{background:linear-gradient(to left,transparent,var(--fire));}
.hero-stats{display:inline-flex;flex-wrap:wrap;justify-content:center;border:1px solid var(--border-fire);border-radius:3px;overflow:hidden;background:rgba(232,52,26,.05);}
.hero-stat{padding:1.2rem 2rem;border-right:1px solid var(--border-fire);min-width:110px;position:relative;}
.hero-stat:last-child{border-right:none;}
.hero-stat::before{content:'';position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(to right,var(--fire),var(--ember));}
.hs-val{font-family:var(--font-disp);font-size:2.3rem;font-weight:900;color:var(--fire-light);line-height:1;display:block;filter:drop-shadow(0 0 10px rgba(232,52,26,.45));}
.hs-lbl{font-family:var(--font-ui);font-size:.58rem;font-weight:700;letter-spacing:3px;text-transform:uppercase;color:var(--muted);margin-top:4px;display:block;}

/* FILTER BAR */
.filter-bar{background:var(--surface);border-bottom:1px solid var(--border-fire);position:sticky;top:var(--nav-h);z-index:100;padding:.55rem 2rem;display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;}
.filter-nav{display:flex;gap:.2rem;background:rgba(0,0,0,.4);border:1px solid var(--border);padding:3px;border-radius:3px;}
.filter-pill{font-family:var(--font-ui);font-size:.72rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;padding:5px 14px;border-radius:2px;border:none;cursor:pointer;transition:all .2s;background:transparent;color:var(--muted);}
.filter-pill.active{background:var(--fire);color:#fff;box-shadow:0 2px 12px rgba(232,52,26,.3);}
.filter-pill:hover:not(.active){background:rgba(255,255,255,.06);color:var(--ink);}
.ev-count-label{font-size:.75rem;color:var(--dim);font-weight:500;}

/* LAYOUT */
.page-wrap{max-width:1280px;margin:0 auto;display:grid;grid-template-columns:220px 1fr;gap:1.75rem;padding:1.75rem 2rem;align-items:start;}
@media(max-width:960px){.page-wrap{grid-template-columns:1fr;}}

/* SIDEBAR */
.pub-sidebar{position:sticky;top:calc(var(--nav-h) + 52px);background:var(--surface);border:1px solid var(--border-fire);border-radius:3px;overflow:hidden;max-height:calc(100vh - var(--nav-h) - 72px);overflow-y:auto;}
@media(max-width:960px){.pub-sidebar{display:none;}}
.sb-head{background:var(--bg);border-bottom:1px solid var(--border-fire);padding:.65rem 1.1rem;font-size:.6rem;font-weight:700;letter-spacing:4px;text-transform:uppercase;color:var(--fire);}
.sb-ev{display:flex;align-items:flex-start;gap:.8rem;padding:.8rem 1.1rem;border-bottom:1px solid var(--border);transition:background .18s;cursor:pointer;}
.sb-ev:last-child{border-bottom:none;}
.sb-ev:hover{background:rgba(232,52,26,.06);}
.sb-ev.cur{background:rgba(232,52,26,.08);border-left:2px solid var(--fire);}
.sb-num{font-family:var(--font-disp);font-size:1.3rem;font-weight:900;color:var(--fire);line-height:1;flex-shrink:0;min-width:20px;}
.sb-name{font-size:.8rem;font-weight:700;color:var(--ink);letter-spacing:.5px;text-transform:uppercase;}
.sb-date{font-size:.72rem;color:var(--muted);margin-top:2px;}
.st-pill{display:inline-block;font-size:.54rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;padding:2px 7px;border-radius:2px;margin-top:4px;}
.s-completed{background:rgba(212,175,55,.1);color:var(--gold);border:1px solid rgba(212,175,55,.2);}
.s-upcoming{background:rgba(22,163,74,.08);color:#4ade80;border:1px solid rgba(22,163,74,.2);}
.s-live{background:rgba(232,52,26,.15);color:var(--fire-light);border:1px solid var(--border-fire);animation:livepulse 1.5s infinite;}
.s-cancelled{background:rgba(100,116,139,.08);color:#94a3b8;border:1px solid rgba(100,116,139,.18);}
@keyframes livepulse{0%,100%{opacity:1;}50%{opacity:.4;}}

/* EVENT CARDS */
.ev-card{background:var(--card);border:1px solid var(--border-fire);border-radius:3px;overflow:hidden;margin-bottom:1.1rem;position:relative;}
.ev-card::before{content:'';position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(to right,var(--fire),var(--ember),transparent);}
.ev-head{background:linear-gradient(135deg,#141210,#1a1714);border-bottom:1px solid var(--border-fire);padding:1.25rem 1.5rem;display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;flex-wrap:wrap;cursor:pointer;user-select:none;}
.ev-promo{font-size:.58rem;font-weight:700;letter-spacing:4px;text-transform:uppercase;color:var(--fire);display:block;margin-bottom:3px;opacity:.75;}
.ev-name{font-family:var(--font-disp);font-size:clamp(1.35rem,3.5vw,1.9rem);font-weight:900;line-height:1;}
.ev-meta{display:flex;gap:1rem;flex-wrap:wrap;margin-top:.5rem;font-size:.76rem;color:var(--muted);font-weight:500;}
.ev-meta span{display:flex;align-items:center;gap:5px;}
.ev-meta svg{opacity:.5;flex-shrink:0;}
.ev-right{display:flex;align-items:center;gap:.7rem;flex-shrink:0;margin-top:2px;}
.ev-status{font-size:.56rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;padding:4px 10px;border-radius:2px;}
.ev-toggle{font-size:1.1rem;color:rgba(255,255,255,.28);transition:transform .3s,color .2s;width:26px;height:26px;display:flex;align-items:center;justify-content:center;border:1px solid rgba(255,255,255,.1);border-radius:2px;flex-shrink:0;}
.ev-toggle.open{transform:rotate(180deg);}
.ev-head:hover .ev-toggle{color:var(--fire-light);border-color:var(--border-fire);}
.ev-body{overflow:hidden;transition:max-height .4s cubic-bezier(.4,0,.2,1);}
.ev-body.collapsed{max-height:0;}
.ev-body.expanded{max-height:9000px;}

.section-divider{padding:.4rem 1.5rem;font-size:.58rem;font-weight:700;letter-spacing:4px;text-transform:uppercase;color:var(--ember);background:rgba(255,140,0,.04);border-top:1px solid var(--border-ember);border-bottom:1px solid var(--border-ember);}

/* BOUT */
.bout{display:grid;grid-template-columns:1fr 160px 1fr;align-items:center;gap:1.25rem;padding:1.35rem 1.75rem;border-bottom:1px solid var(--border);transition:background .18s;}
.bout:last-child{border-bottom:none;}
.bout:hover{background:rgba(232,52,26,.04);}
.fighter{display:flex;align-items:center;gap:.9rem;cursor:pointer;}
.fighter.right{flex-direction:row-reverse;text-align:right;}
.fighter:hover .fa,.fighter:hover .fa-photo{transform:scale(1.05);}
.fighter:hover .fn{color:var(--fire-light);}
.fa{width:80px;height:80px;border-radius:50%;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-family:var(--font-disp);font-size:1.4rem;font-weight:900;border:2px solid;transition:all .22s;background:var(--surface);}
.fa-r{background:rgba(232,52,26,.08);color:var(--fire-light);border-color:rgba(232,52,26,.28);}
.fa-b{background:rgba(37,99,235,.07);color:#60a5fa;border-color:rgba(37,99,235,.18);}
.fa-photo{width:80px;height:80px;border-radius:50%;object-fit:cover;border:2px solid;flex-shrink:0;transition:all .22s;}
.fighter.winner .fa,.fighter.winner .fa-photo{border-color:var(--fire)!important;box-shadow:0 0 0 3px rgba(232,52,26,.14),0 0 18px rgba(232,52,26,.18);}
.fighter.winner .fn{color:var(--fire-light);}
.fn{font-size:1rem;font-weight:700;color:rgba(255,255,255,.88);line-height:1.2;transition:color .2s;}
.fk{font-family:var(--font-disp);font-style:italic;font-size:.82rem;color:var(--muted);margin-top:2px;}
.fr{font-family:var(--font-alt);font-size:.72rem;color:var(--dim);margin-top:3px;letter-spacing:1px;}
.fgym{font-size:.66rem;color:var(--fire);margin-top:2px;opacity:.65;font-weight:600;}
.fclick{font-size:.62rem;color:var(--ember);margin-top:5px;opacity:0;transition:opacity .2s;font-weight:600;}
.fighter:hover .fclick{opacity:1;}

.bc{text-align:center;}
.bt{font-size:.56rem;font-weight:700;letter-spacing:2.5px;text-transform:uppercase;color:var(--ember);display:block;margin-bottom:1px;}
.bwc{font-size:.64rem;color:var(--muted);display:block;margin-bottom:6px;font-weight:600;letter-spacing:.4px;}
.vs{font-family:var(--font-alt);font-size:.68rem;font-weight:600;letter-spacing:4px;color:var(--dim);display:block;margin-bottom:6px;}
.br{font-size:.64rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;padding:3px 11px;border-radius:2px;display:inline-block;}
.br-ko{background:rgba(232,52,26,.18);color:var(--fire-light);border:1px solid rgba(232,52,26,.28);}
.br-tko{background:rgba(232,52,26,.11);color:#fca5a5;border:1px solid rgba(232,52,26,.18);}
.br-rsc{background:rgba(255,140,0,.14);color:var(--ember);border:1px solid rgba(255,140,0,.22);}
.br-sub{background:rgba(139,92,246,.11);color:#c4b5fd;border:1px solid rgba(139,92,246,.18);}
.br-dec{background:rgba(59,130,246,.09);color:#93c5fd;border:1px solid rgba(59,130,246,.16);}
.br-draw{background:rgba(100,116,139,.1);color:#94a3b8;border:1px solid rgba(100,116,139,.14);}
.br-nc{background:rgba(100,116,139,.07);color:#64748b;border:1px solid rgba(100,116,139,.12);}
.bd{font-size:.64rem;color:var(--dim);display:block;margin-top:2px;}
.br-pend{font-size:.62rem;font-weight:600;letter-spacing:1.5px;text-transform:uppercase;color:var(--dim);border:1px solid var(--border);padding:3px 10px;border-radius:2px;display:inline-block;background:rgba(255,255,255,.02);}

/* RANKINGS */
.rank-section{border-top:1px solid var(--border-fire);padding:3.5rem 2rem;max-width:1280px;margin:0 auto;}
.sec-eyebrow{font-size:.64rem;font-weight:700;letter-spacing:5px;text-transform:uppercase;color:var(--fire);margin-bottom:.3rem;}
.sec-title{font-family:var(--font-disp);font-size:clamp(1.5rem,4vw,2.1rem);font-weight:700;letter-spacing:-.5px;margin-bottom:1.75rem;line-height:1;}
.rank-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(255px,1fr));gap:.9rem;}
.rank-card{background:var(--surface);border:1px solid var(--border-fire);border-radius:3px;overflow:hidden;position:relative;}
.rank-card::before{content:'';position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(to right,var(--fire),var(--ember),transparent);}
.rk-head{background:var(--bg);border-bottom:1px solid var(--border-fire);padding:.65rem 1.1rem;font-size:.64rem;font-weight:700;letter-spacing:3px;text-transform:uppercase;color:var(--fire);}
.rk-row{display:flex;align-items:center;gap:.9rem;padding:.9rem 1.1rem;border-bottom:1px solid var(--border);transition:background .18s;cursor:pointer;}
.rk-row:last-child{border-bottom:none;}
.rk-row:hover{background:rgba(232,52,26,.06);}
.rk-avatar{width:46px;height:46px;border-radius:50%;flex-shrink:0;object-fit:cover;border:2px solid var(--border-fire);transition:border-color .18s;}
.rk-avatar-init{width:46px;height:46px;border-radius:50%;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-family:var(--font-disp);font-size:.88rem;font-weight:900;border:2px solid var(--border-fire);background:rgba(232,52,26,.07);color:var(--fire);transition:border-color .18s;}
.rk-row:hover .rk-avatar,.rk-row:hover .rk-avatar-init{border-color:var(--fire);}
.rk-row:hover .rname{color:var(--fire-light);}
.rn{font-family:var(--font-disp);font-size:1.15rem;font-weight:900;color:var(--dim);min-width:22px;text-align:center;}
.rn.champ{color:var(--fire-light);filter:drop-shadow(0 0 8px rgba(232,52,26,.45));}
.ri{flex:1;min-width:0;}
.rname{font-size:.88rem;font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;transition:color .18s;}
.rnick{font-family:var(--font-disp);font-style:italic;font-size:.76rem;color:var(--muted);margin-top:1px;}
.rrec{font-family:var(--font-alt);font-size:.66rem;color:var(--dim);margin-top:2px;letter-spacing:1px;}
.ctag{font-size:.52rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;background:rgba(232,52,26,.14);color:var(--fire-light);border:1px solid var(--border-fire);padding:3px 8px;border-radius:2px;flex-shrink:0;}

/* ABOUT */
.about-sec{border-top:1px solid var(--border-fire);background:linear-gradient(180deg,var(--bg) 0%,#0f0d0b 100%);padding:4.5rem 2rem;}
.about-inner{max-width:1280px;margin:0 auto;}
.about-grid{display:grid;grid-template-columns:1.3fr 1fr;gap:5rem;align-items:center;}
@media(max-width:800px){.about-grid{grid-template-columns:1fr;gap:2.5rem;}}
.about-body p{font-size:1rem;color:var(--muted);line-height:1.85;margin-bottom:1.15rem;}
.about-body p strong{font-weight:700;color:rgba(255,255,255,.85);}
.mosaic{display:grid;grid-template-columns:1fr 1fr;gap:2px;}
.mo-tile{aspect-ratio:1;background:rgba(232,52,26,.04);border:1px solid var(--border-fire);display:flex;flex-direction:column;align-items:center;justify-content:center;padding:1.2rem;text-align:center;transition:background .2s;position:relative;overflow:hidden;}
.mo-tile::before{content:'';position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(to right,var(--fire),var(--ember));}
.mo-tile:hover{background:rgba(232,52,26,.09);}
.mo-val{font-family:var(--font-disp);font-size:2.4rem;font-weight:900;color:var(--fire-light);line-height:1;filter:drop-shadow(0 0 14px rgba(232,52,26,.38));}
.mo-lbl{font-size:.58rem;font-weight:700;letter-spacing:3px;text-transform:uppercase;color:var(--muted);margin-top:6px;}

footer{background:var(--bg);border-top:1px solid var(--border-fire);padding:2.5rem 2rem;text-align:center;position:relative;}
footer::before{content:'';position:absolute;top:0;left:0;right:0;height:1px;background:linear-gradient(to right,transparent,var(--fire),var(--ember),transparent);}
.fl{font-family:var(--font-disp);font-size:.92rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--fire-light);margin-bottom:.5rem;filter:drop-shadow(0 0 8px rgba(232,52,26,.35));}
footer p{font-size:.7rem;color:rgba(255,255,255,.22);letter-spacing:1px;}

/* MODAL */
.modal-overlay{display:none;position:fixed;inset:0;z-index:500;background:rgba(0,0,0,.88);backdrop-filter:blur(10px);align-items:center;justify-content:center;padding:1.5rem 1rem;overflow-y:auto;}
.modal-overlay.open{display:flex;}
.modal{background:var(--card);border:1px solid var(--border-fire);border-radius:4px;width:100%;max-width:660px;animation:mslide .22s cubic-bezier(.22,.68,0,1.1);position:relative;overflow:hidden;}
.modal::before{content:'';position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(to right,var(--fire),var(--ember),var(--gold));z-index:5;}
@keyframes mslide{from{transform:translateY(18px) scale(.97);opacity:0}to{transform:none;opacity:1}}
.modal-banner{display:grid;grid-template-columns:190px 1fr;background:var(--bg);min-height:195px;}
.modal-close{position:absolute;top:.8rem;right:.8rem;z-index:10;background:rgba(0,0,0,.7);border:1px solid var(--border-fire);color:var(--muted);cursor:pointer;width:30px;height:30px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.95rem;transition:all .18s;}
.modal-close:hover{background:var(--fire);color:#fff;border-color:var(--fire);}
.modal-photo-hero{width:190px;height:195px;position:relative;overflow:hidden;flex-shrink:0;background:rgba(232,52,26,.04);}
.modal-photo-hero img.hero-img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;object-position:top center;}
.modal-photo-hero-gradient{position:absolute;inset:0;background:linear-gradient(to right,transparent 55%,rgba(10,8,6,.85) 100%);pointer-events:none;}
.modal-photo-hero-init{width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-family:var(--font-disp);font-size:3.8rem;font-weight:900;color:rgba(232,52,26,.12);}
.modal-fighter-head{display:flex;flex-direction:column;justify-content:flex-end;padding:1.1rem 1.2rem;}
.modal-record-row{display:flex;align-items:center;gap:0;margin-bottom:.85rem;}
.modal-rec-cell{flex:1;text-align:center;padding:.4rem .3rem;border-radius:2px;}
.modal-rec-cell:nth-child(1){background:rgba(74,222,128,.07);border:1px solid rgba(74,222,128,.14);}
.modal-rec-cell:nth-child(2){background:rgba(248,113,113,.07);border:1px solid rgba(248,113,113,.14);margin:0 .3rem;}
.modal-rec-cell:nth-child(3){background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.07);}
.mrn{font-family:var(--font-disp);font-size:1.8rem;font-weight:900;line-height:1;display:block;}
.mrn.w{color:#4ADE80;}.mrn.l{color:#F87171;}.mrn.d{color:rgba(255,255,255,.22);}
.mrl{font-size:.54rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;display:block;margin-top:2px;}
.modal-rec-cell:nth-child(1) .mrl{color:rgba(74,222,128,.45);}
.modal-rec-cell:nth-child(2) .mrl{color:rgba(248,113,113,.45);}
.modal-rec-cell:nth-child(3) .mrl{color:rgba(255,255,255,.2);}
.modal-name{font-family:var(--font-disp);font-size:1.35rem;font-weight:900;line-height:1.05;margin-bottom:.18rem;}
.modal-nick{font-family:var(--font-disp);font-style:italic;font-size:.82rem;color:var(--fire-light);margin-bottom:.2rem;}
.modal-gym-row{font-size:.72rem;color:var(--muted);font-weight:600;display:flex;align-items:center;gap:.35rem;}
.modal-gym-row::before{content:'';width:8px;height:1px;background:var(--fire);display:inline-block;flex-shrink:0;}
.modal-body{padding:1.1rem 1.35rem 1.35rem;}
.modal-info{display:grid;grid-template-columns:1fr 1fr;gap:0;border:1px solid var(--border-fire);border-radius:3px;overflow:hidden;margin-bottom:.9rem;}
.mi-row{display:flex;flex-direction:column;gap:2px;padding:.6rem .85rem;border-bottom:1px solid var(--border);border-right:1px solid var(--border);background:var(--surface);transition:background .15s;}
.mi-row:hover{background:rgba(232,52,26,.06);}
.mi-row:nth-child(even){border-right:none;}
.mi-row:nth-last-child(-n+2){border-bottom:none;}
.mi-row.full{grid-column:1/-1;border-right:none;}
.mi-row.full:last-child{border-bottom:none;}
.mi-label{font-size:.56rem;font-weight:700;letter-spacing:2.5px;text-transform:uppercase;color:var(--fire);}
.mi-val{font-size:.88rem;color:rgba(255,255,255,.88);font-weight:600;line-height:1.3;}
.mi-val.empty{color:var(--dim);font-weight:400;font-style:italic;font-size:.8rem;}
.mbadge{font-size:.56rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;padding:3px 8px;border-radius:2px;display:inline-block;}
.mb-active{background:rgba(22,163,74,.08);color:#4ade80;border:1px solid rgba(22,163,74,.18);}
.mb-inactive{background:rgba(100,116,139,.08);color:#64748B;border:1px solid rgba(100,116,139,.16);}
.mb-male{background:rgba(59,130,246,.07);color:#60a5fa;border:1px solid rgba(59,130,246,.16);}
.mb-female{background:rgba(236,72,153,.07);color:#f9a8d4;border:1px solid rgba(236,72,153,.16);}
.modal-notes{background:rgba(232,52,26,.04);border:1px solid var(--border-fire);border-radius:3px;padding:.8rem 1rem;font-size:.84rem;color:var(--muted);font-style:italic;line-height:1.6;}

/* RESPONSIVE */
@media(max-width:640px){
    .nav-links{display:none;}
    .hamburger{display:flex;}
    .hero{padding:2.8rem 1rem 2.2rem;min-height:auto;}
    .hero-stats{width:100%;}
    .hero-stat{padding:.9rem;min-width:50%;}
    .filter-bar{padding:.5rem 1rem;}
    .page-wrap{padding:1rem;}
    .bout{grid-template-columns:1fr;gap:.65rem;padding:1rem 1.1rem;margin:.3rem;border:1px solid var(--border);border-radius:3px;}
    .bc{order:-1;display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:.5rem;padding:.45rem .75rem;background:rgba(232,52,26,.05);border-radius:2px;border:1px solid var(--border-fire);}
    .vs{display:none;}
    .fighter.right{flex-direction:row;text-align:left;}
    .fa,.fa-photo{width:60px;height:60px;font-size:1.1rem;}
    .ev-head{padding:.95rem 1.1rem;}
    .modal-overlay{padding:.4rem;align-items:flex-end;}
    .modal{border-radius:6px 6px 0 0;max-width:100%;}
    .modal-banner{grid-template-columns:1fr;min-height:auto;}
    .modal-photo-hero{width:100%;height:160px;}
    .modal-info{grid-template-columns:1fr;}
    .mi-row{border-right:none;}
    .mi-row:nth-last-child(-n+2){border-bottom:1px solid var(--border);}
    .mi-row:last-child{border-bottom:none;}
    .rank-section,.about-sec{padding:2.5rem 1rem;}
}
@media(max-width:960px) and (min-width:641px){
    .nav-links{display:none;}
    .hamburger{display:flex;}
}
</style>
</head>
<body>

<nav>
<div class="nav-inner">
    <a href="#" class="nav-logo">
        <div class="logo-mark"><span>TH</span></div>
        <div class="logo-text">Tribal Hunters<small>Fight Promotion</small></div>
    </a>
    <div class="nav-links">
        <a href="#events" class="active">Events</a>
        <a href="#rankings">Rankings</a>
        <a href="#about">About</a>
    </div>
    <button class="hamburger" id="hamburger"><span></span><span></span><span></span></button>
</div>
</nav>
<div class="mobile-nav" id="mobileNav">
    <a href="#events" onclick="closeMobileNav()">Events</a>
    <a href="#rankings" onclick="closeMobileNav()">Rankings</a>
    <a href="#about" onclick="closeMobileNav()">About</a>
</div>

<section class="hero">
<div class="hero-flames"></div><div class="hero-grid"></div>
<div class="hero-content">
    <div class="hero-eyebrow">Masbate, Philippines &middot; Est. 2025</div>
    <h1><span class="line1">Tribal</span><span class="line2">Hunters</span></h1>
    <p class="hero-sub">Fight Promotion &mdash; Where Warriors Are Forged</p>
    <div class="flame-rule">
        <div class="flame-rule-line"></div>
        <span>🔥</span>
        <div class="flame-rule-line rev"></div>
    </div>
    <div class="hero-stats">
        <div class="hero-stat"><span class="hs-val"><?php echo $total_events; ?></span><span class="hs-lbl">Events</span></div>
        <div class="hero-stat"><span class="hs-val"><?php echo $total_bouts; ?></span><span class="hs-lbl">Bouts</span></div>
        <div class="hero-stat"><span class="hs-val"><?php echo $total_fighters; ?></span><span class="hs-lbl">Fighters</span></div>
        <div class="hero-stat"><span class="hs-val"><?php echo $finish_rate; ?>%</span><span class="hs-lbl">Finish Rate</span></div>
    </div>
</div>
</section>

<div class="filter-bar" id="events">
    <div class="filter-nav">
        <button class="filter-pill active" onclick="filterEvents('all',this)">All</button>
        <button class="filter-pill" onclick="filterEvents('completed',this)">Completed</button>
        <button class="filter-pill" onclick="filterEvents('upcoming',this)">Upcoming</button>
    </div>
    <span class="ev-count-label" id="ev-count"><?php echo $total_events; ?> event<?php echo $total_events!==1?'s':''; ?></span>
</div>

<div class="page-wrap">
<aside class="pub-sidebar">
    <div class="sb-head">All Events</div>
    <?php foreach ($events as $ev):
        $sc=['completed'=>'s-completed','upcoming'=>'s-upcoming','cancelled'=>'s-cancelled','live'=>'s-live'][$ev['status']]??'';
    ?>
    <a href="#ev-<?php echo $ev['id']; ?>" class="sb-ev" onclick="expandCard(<?php echo $ev['id']; ?>)">
        <div class="sb-num"><?php echo $ev['event_number']; ?></div>
        <div>
            <div class="sb-name"><?php echo htmlspecialchars($ev['name']); ?></div>
            <div class="sb-date"><?php echo $ev['event_date']?date('M d, Y',strtotime($ev['event_date'])):'Date TBA'; ?></div>
            <span class="st-pill <?php echo $sc; ?>"><?php echo ucfirst($ev['status']); ?></span>
        </div>
    </a>
    <?php endforeach; ?>
</aside>

<div>
<?php if(empty($events)):?>
<div style="text-align:center;padding:4rem;font-family:var(--font-disp);font-style:italic;color:var(--dim);">No events yet. Check back soon.</div>
<?php endif;?>
<?php foreach ($events as $idx=>$ev):
    $sc=['completed'=>'s-completed','upcoming'=>'s-upcoming','cancelled'=>'s-cancelled','live'=>'s-live'][$ev['status']]??'';
    $cnt=count($ev['bouts']);
    $first=$idx===0;
    $order=['main_event'=>0,'co_main'=>1,'final'=>2,'prelim'=>3,'amateur'=>4];
    $sorted=$ev['bouts'];
    usort($sorted,fn($a,$b)=>($order[$a['bout_type']]??5)<=>($order[$b['bout_type']]??5));
    $lastType=null;
?>
<div class="ev-card" id="ev-<?php echo $ev['id']; ?>" data-status="<?php echo $ev['status']; ?>">
    <div class="ev-head" onclick="toggleCard(<?php echo $ev['id']; ?>)">
        <div>
            <span class="ev-promo">Tribal Hunters Fight Promotion</span>
            <div class="ev-name"><?php echo htmlspecialchars($ev['name']); ?></div>
            <div class="ev-meta">
                <span><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg><?php echo $ev['event_date']?date('F d, Y',strtotime($ev['event_date'])):'Date TBA'; ?></span>
                <?php if(!empty($ev['venue'])): ?><span><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg><?php echo htmlspecialchars($ev['venue']); ?></span><?php endif; ?>
                <span><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg><?php echo $cnt; ?> bout<?php echo $cnt!==1?'s':''; ?></span>
            </div>
        </div>
        <div class="ev-right">
            <span class="ev-status <?php echo $sc; ?>"><?php echo ucfirst($ev['status']); ?></span>
            <span class="ev-toggle <?php echo $first?'open':''; ?>" id="tgl-<?php echo $ev['id']; ?>">&#8964;</span>
        </div>
    </div>
    <div class="ev-body <?php echo $first?'expanded':'collapsed'; ?>" id="body-<?php echo $ev['id']; ?>">
    <?php if(empty($sorted)):?>
        <p style="font-family:var(--font-disp);font-style:italic;color:var(--dim);padding:2rem;font-size:.92rem;">Fight card to be announced.</p>
    <?php endif;?>
    <?php foreach($sorted as $b):
        $typeLabel=boutTypeLabel($b['bout_type']);
        if($b['bout_type']!==$lastType):
            $lastType=$b['bout_type'];?>
        <div class="section-divider"><?php echo $typeLabel;?>s</div>
        <?php endif;
        $w=$b['winner_name']??null;
        $m=$b['result_method']??'';
        $rw=$w&&$w===($b['red_name']??'');
        $bw=$w&&$w===($b['blue_name']??'');
        $mb=methodBadge($m);
        $rid=(int)($b['red_id']??0);
        $bid=(int)($b['blue_id']??0);
        $rp=photoUrl($b['red_photo']??'');
        $bp=photoUrl($b['blue_photo']??'');
    ?>
    <div class="bout">
        <div class="fighter <?php echo $rw?'winner':''; ?>" <?php if($rid):?>onclick="openFighter(<?php echo $rid;?>)"<?php endif;?>>
            <?php if($rp):?><img src="<?php echo htmlspecialchars($rp);?>" class="fa-photo" style="border-color:<?php echo $rw?'var(--fire)':'rgba(232,52,26,.28)';?>;" alt="" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';"><div class="fa fa-r" style="display:none;"><?php echo initials($b['red_name']??'?');?></div>
            <?php else:?><div class="fa fa-r"><?php echo initials($b['red_name']??'?');?></div><?php endif;?>
            <div>
                <div class="fn"><?php echo htmlspecialchars($b['red_name']??'TBA');?></div>
                <?php if(!empty($b['red_nick'])):?><div class="fk">&ldquo;<?php echo htmlspecialchars($b['red_nick']);?>&rdquo;</div><?php endif;?>
                <div class="fr"><?php echo (int)($b['rw']??0).'-'.(int)($b['rl']??0).'-'.(int)($b['rd']??0);?></div>
                <?php if(!empty($b['red_gym'])):?><div class="fgym"><?php echo htmlspecialchars($b['red_gym']);?></div><?php endif;?>
                <?php if($rid):?><div class="fclick">View Profile &rsaquo;</div><?php endif;?>
            </div>
        </div>
        <div class="bc">
            <span class="bt"><?php echo boutTypeLabel($b['bout_type']);?></span>
            <span class="bwc"><?php echo htmlspecialchars(trim(($b['age_category']??'').' '.($b['gender']??'').' '.($b['weight_class']??'')));?></span>
            <span class="vs">VS</span>
            <?php if($m):?>
                <span class="br <?php echo $mb;?>"><?php echo $m;?></span>
                <?php if(!empty($b['decision_type'])):?><span class="bd"><?php echo $b['decision_type'];?></span><?php endif;?>
                <span class="bd"><?php echo !empty($b['result_round'])?'Rd '.$b['result_round']:'';echo!empty($b['result_time'])?' &middot; '.$b['result_time']:'';?></span>
            <?php else:?><span class="br-pend"><?php echo ($ev['status']==='upcoming')?'Scheduled':'TBD';?></span><?php endif;?>
        </div>
        <div class="fighter right <?php echo $bw?'winner':''; ?>" <?php if($bid):?>onclick="openFighter(<?php echo $bid;?>)"<?php endif;?>>
            <?php if($bp):?><img src="<?php echo htmlspecialchars($bp);?>" class="fa-photo" style="border-color:<?php echo $bw?'var(--fire)':'rgba(37,99,235,.18)';?>;" alt="" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';"><div class="fa fa-b" style="display:none;"><?php echo initials($b['blue_name']??'?');?></div>
            <?php else:?><div class="fa fa-b"><?php echo initials($b['blue_name']??'?');?></div><?php endif;?>
            <div>
                <div class="fn"><?php echo htmlspecialchars($b['blue_name']??'TBA');?></div>
                <?php if(!empty($b['blue_nick'])):?><div class="fk">&ldquo;<?php echo htmlspecialchars($b['blue_nick']);?>&rdquo;</div><?php endif;?>
                <div class="fr"><?php echo (int)($b['bw']??0).'-'.(int)($b['bl']??0).'-'.(int)($b['bd2']??0);?></div>
                <?php if(!empty($b['blue_gym'])):?><div class="fgym"><?php echo htmlspecialchars($b['blue_gym']);?></div><?php endif;?>
                <?php if($bid):?><div class="fclick">View Profile &rsaquo;</div><?php endif;?>
            </div>
        </div>
    </div>
    <?php endforeach;?>
    <?php if(!empty($ev['tournament_director'])||!empty($ev['officials'])||!empty($ev['sponsors'])):?>
    <div style="background:rgba(232,52,26,.04);border-top:1px solid var(--border-fire);padding:.9rem 1.5rem;display:flex;flex-direction:column;gap:.3rem;">
        <?php if(!empty($ev['tournament_director'])):?><div style="font-size:.74rem;color:var(--muted);"><span style="color:var(--ember);font-weight:700;text-transform:uppercase;letter-spacing:1px;font-size:.6rem;margin-right:.4rem;">Director:</span><?php echo htmlspecialchars($ev['tournament_director']);?><?php if(!empty($ev['mc'])):?>&nbsp;&middot;&nbsp;<span style="color:var(--ember);font-weight:700;text-transform:uppercase;letter-spacing:1px;font-size:.6rem;margin-right:.4rem;">MC:</span><?php echo htmlspecialchars($ev['mc']);?><?php endif;?></div><?php endif;?>
        <?php if(!empty($ev['sanctioned_by'])):?><div style="font-size:.74rem;color:var(--muted);"><span style="color:var(--ember);font-weight:700;text-transform:uppercase;letter-spacing:1px;font-size:.6rem;margin-right:.4rem;">Sanctioned by:</span><?php echo htmlspecialchars($ev['sanctioned_by']);?></div><?php endif;?>
        <?php if(!empty($ev['officials'])):?><div style="font-size:.74rem;color:var(--muted);"><span style="color:var(--ember);font-weight:700;text-transform:uppercase;letter-spacing:1px;font-size:.6rem;margin-right:.4rem;">Officials:</span><?php echo htmlspecialchars($ev['officials']);?></div><?php endif;?>
        <?php if(!empty($ev['sponsors'])):?><div style="margin-top:.2rem;padding-top:.35rem;border-top:1px solid var(--border-fire);font-size:.74rem;color:var(--muted);"><span style="color:var(--ember);font-weight:700;text-transform:uppercase;letter-spacing:1px;font-size:.6rem;margin-right:.4rem;">Sponsors:</span><?php echo htmlspecialchars($ev['sponsors']);?></div><?php endif;?>
    </div>
    <?php endif;?>
    </div>
</div>
<?php endforeach;?>
</div>
</div>

<section class="rank-section" id="rankings">
<div class="sec-eyebrow">Pound for Pound</div>
<div class="sec-title">Fighter Rankings</div>
<div class="rank-grid">
<?php foreach($fighters_all as $wc=>$wf):
    $label=str_replace(['|','Junior','Senior','Male','Female'],['  ','Jr','Sr','M','F'],$wc);
?>
<div class="rank-card">
    <div class="rk-head"><?php echo htmlspecialchars($label);?></div>
    <?php $rk=0;foreach($wf as $f):$rk++;$is_c=$rk===1;$pp=photoUrl($f['photo_path']??'');?>
    <div class="rk-row" <?php if(!empty($f['id'])):?>onclick="openFighter(<?php echo (int)$f['id'];?>)"<?php endif;?>>
        <div class="rn <?php echo $is_c?'champ':'';?>"><?php echo $is_c?'&#9670;':$rk;?></div>
        <?php if($pp):?><img src="<?php echo htmlspecialchars($pp);?>" class="rk-avatar" alt="" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';"><div class="rk-avatar-init" style="display:none;"><?php echo strtoupper(substr(trim($f['name']),0,1));?></div>
        <?php else:?><div class="rk-avatar-init"><?php echo strtoupper(substr(trim($f['name']),0,1));?></div><?php endif;?>
        <div class="ri">
            <div class="rname"><?php echo htmlspecialchars($f['name']);?></div>
            <?php if(!empty($f['nickname'])):?><div class="rnick">&ldquo;<?php echo htmlspecialchars($f['nickname']);?>&rdquo;</div><?php endif;?>
            <div class="rrec"><?php echo($f['record_wins']??0).'-'.($f['record_losses']??0).'-'.($f['record_draws']??0);?></div>
        </div>
        <?php if($is_c):?><span class="ctag">Champ</span><?php endif;?>
    </div>
    <?php endforeach;?>
</div>
<?php endforeach;?>
</div>
</section>

<section class="about-sec" id="about">
<div class="about-inner">
<div class="about-grid">
    <div class="about-body">
        <div class="sec-eyebrow">Our Story</div>
        <div class="sec-title">Tribal Hunters<br>Fight Promotion</div>
        <p><strong>Tribal Hunters Fight Promotion</strong> is Masbate's premier combat sports organization, dedicated to elevating the art of Muay Thai and mixed martial arts across the Philippines.</p>
        <p>Founded in 2025 under the <strong>Oriental Muayboran Academy</strong>, THFP provides a world-class competitive platform for local warriors to showcase their skills, discipline, and heart.</p>
        <p>From grassroots amateur bouts to high-stakes professional main events, every THFP card is a celebration of the fighting spirit that defines the warriors of Masbate.</p>
    </div>
    <div class="mosaic">
        <div class="mo-tile"><div class="mo-val"><?php echo $total_events;?></div><div class="mo-lbl">Events</div></div>
        <div class="mo-tile"><div class="mo-val"><?php echo $total_bouts;?></div><div class="mo-lbl">Bouts</div></div>
        <div class="mo-tile"><div class="mo-val"><?php echo $total_fighters;?></div><div class="mo-lbl">Fighters</div></div>
        <div class="mo-tile"><div class="mo-val"><?php echo $finish_rate;?>%</div><div class="mo-lbl">Finish Rate</div></div>
    </div>
</div>
</div>
</section>

<footer>
    <div class="fl">Tribal Hunters Fight Promotion</div>
    <p>Oriental Muayboran Academy &middot; Masbate, Philippines &middot; &copy; <?php echo date('Y');?></p>
</footer>

<!-- FIGHTER MODAL -->
<div class="modal-overlay" id="fighterModal" onclick="handleOverlayClick(event)">
<div class="modal" id="modalBox">
    <div class="modal-banner">
        <button class="modal-close" onclick="closeModal()">&times;</button>
        <div class="modal-photo-hero" id="modalPhotoHero">
            <div class="modal-photo-hero-init" id="modalPhotoInit"></div>
            <div class="modal-photo-hero-gradient"></div>
        </div>
        <div class="modal-fighter-head">
            <div class="modal-record-row">
                <div class="modal-rec-cell"><span class="mrn w" id="modalW">0</span><span class="mrl">Wins</span></div>
                <div class="modal-rec-cell"><span class="mrn l" id="modalL">0</span><span class="mrl">Losses</span></div>
                <div class="modal-rec-cell"><span class="mrn d" id="modalD">0</span><span class="mrl">Draws</span></div>
            </div>
            <div class="modal-name" id="modalName">—</div>
            <div class="modal-nick" id="modalNick" style="display:none;"></div>
            <div class="modal-gym-row" id="modalGym" style="display:none;"></div>
        </div>
    </div>
    <div class="modal-body">
        <div class="modal-info">
            <div class="mi-row"><span class="mi-label">Weight Class</span><span class="mi-val" id="modalWC">—</span></div>
            <div class="mi-row"><span class="mi-label">Division</span><span class="mi-val" id="modalDiv">—</span></div>
            <div class="mi-row"><span class="mi-label">Date of Birth</span><span class="mi-val" id="modalDOB">—</span></div>
            <div class="mi-row"><span class="mi-label">Height</span><span class="mi-val" id="modalHeight">—</span></div>
            <div class="mi-row"><span class="mi-label">Nationality</span><span class="mi-val" id="modalNation">—</span></div>
            <div class="mi-row"><span class="mi-label">Reach</span><span class="mi-val" id="modalReach">—</span></div>
            <div class="mi-row"><span class="mi-label">Hometown</span><span class="mi-val" id="modalTown">—</span></div>
            <div class="mi-row"><span class="mi-label">Gym</span><span class="mi-val" id="modalGymRow">—</span></div>
            <div class="mi-row full"><span class="mi-label">Status</span><span class="mi-val" id="modalStatus">—</span></div>
        </div>
        <div id="modalNotes" style="display:none;" class="modal-notes"></div>
    </div>
</div>
</div>

<script>
var FM={};
<?php
// Normalise all photo_path values before JSON-encoding for the JS modal
$fmap = array_values($all_fighters_map);
foreach ($fmap as &$_f) {
    $_f['photo_path'] = photoUrl($_f['photo_path'] ?? '');
}
unset($_f);
?>
var FD=<?php echo json_encode($fmap,JSON_HEX_TAG);?>;
FD.forEach(function(f){FM[f.id]=f;});

function openFighter(id){
    var f=FM[id];if(!f)return;
    var hero=document.getElementById('modalPhotoHero');
    var init=document.getElementById('modalPhotoInit');
    var oldImg=hero.querySelector('img.hero-img');if(oldImg)oldImg.remove();
    if(f.photo_path){
        var img=document.createElement('img');img.className='hero-img';img.src=f.photo_path;img.alt=f.name;
        img.style.cssText='position:absolute;inset:0;width:100%;height:100%;object-fit:cover;object-position:top center;';
        img.onerror=function(){this.remove();init.textContent=ini(f.name);init.style.display='flex';};
        hero.insertBefore(img,hero.firstChild);init.style.display='none';
    } else {init.textContent=ini(f.name);init.style.display='flex';}
    document.getElementById('modalName').textContent=f.name;
    var nk=document.getElementById('modalNick');if(f.nickname){nk.textContent='\u201C'+f.nickname+'\u201D';nk.style.display='';}else nk.style.display='none';
    var gm=document.getElementById('modalGym');if(f.gym){gm.textContent=f.gym;gm.style.display='';}else gm.style.display='none';
    document.getElementById('modalW').textContent=f.record_wins||0;
    document.getElementById('modalL').textContent=f.record_losses||0;
    document.getElementById('modalD').textContent=f.record_draws||0;
    sv('modalWC',f.weight_class);
    var dv=[f.age_category,f.gender].filter(Boolean).join(' \u00b7 ');sv('modalDiv',dv||null);
    var dob=document.getElementById('modalDOB');
    if(f.date_of_birth){var d=new Date(f.date_of_birth);var age=Math.floor((new Date()-d)/31557600000);dob.textContent='Age '+age+' | '+d.toLocaleDateString('en-US',{year:'numeric',month:'short',day:'numeric'});dob.className='mi-val';}
    else{dob.textContent='—';dob.className='mi-val empty';}
    sv('modalHeight',f.height);sv('modalNation',f.nationality);sv('modalReach',f.reach);sv('modalTown',f.hometown);sv('modalGymRow',f.gym);
    var st=f.status||'active';
    var gs=f.gender?'&nbsp;<span class="mbadge mb-'+f.gender.toLowerCase()+'">'+f.gender+'</span>':'';
    document.getElementById('modalStatus').innerHTML='<span class="mbadge mb-'+st+'">'+cap(st)+'</span>'+gs;document.getElementById('modalStatus').className='mi-val';
    var nm=document.getElementById('modalNotes');if(f.notes&&f.notes.trim()){nm.textContent=f.notes;nm.style.display='';}else nm.style.display='none';
    document.getElementById('fighterModal').classList.add('open');document.body.style.overflow='hidden';
}
function sv(id,val){var e=document.getElementById(id);e.textContent=val||'—';e.className='mi-val'+(val?'':' empty');}
function closeModal(){document.getElementById('fighterModal').classList.remove('open');document.body.style.overflow='';}
function handleOverlayClick(e){if(e.target===document.getElementById('fighterModal'))closeModal();}
function ini(n){var p=n.trim().split(/\s+/).filter(Boolean);return((p[0]||'')[0]+(p.length>1?(p[p.length-1]||'')[0]:'')).toUpperCase();}
function cap(s){return s.charAt(0).toUpperCase()+s.slice(1);}
function toggleCard(id){var b=document.getElementById('body-'+id);var t=document.getElementById('tgl-'+id);var o=b.classList.contains('expanded');b.classList.toggle('expanded',!o);b.classList.toggle('collapsed',o);t.classList.toggle('open',!o);}
function expandCard(id){var b=document.getElementById('body-'+id);var t=document.getElementById('tgl-'+id);if(b&&b.classList.contains('collapsed')){b.classList.replace('collapsed','expanded');t.classList.add('open');}}
function filterEvents(status,btn){document.querySelectorAll('.filter-pill').forEach(function(p){p.classList.remove('active');});btn.classList.add('active');var cards=document.querySelectorAll('.ev-card');var v=0;cards.forEach(function(card){var m=status==='all'||card.dataset.status===status;card.style.display=m?'':'none';if(m)v++;});document.getElementById('ev-count').textContent=v+' event'+(v!==1?'s':'');}
var hb=document.getElementById('hamburger');var mn=document.getElementById('mobileNav');
hb.addEventListener('click',function(){hb.classList.toggle('open');mn.classList.toggle('open');});
function closeMobileNav(){hb.classList.remove('open');mn.classList.remove('open');}
document.addEventListener('click',function(e){if(!hb.contains(e.target)&&!mn.contains(e.target))closeMobileNav();});
document.addEventListener('keydown',function(e){if(e.key==='Escape')closeModal();});
var obs=new IntersectionObserver(function(entries){entries.forEach(function(e){if(e.isIntersecting){var id=e.target.id.replace('ev-','');document.querySelectorAll('.sb-ev').forEach(function(l){l.classList.remove('cur');});var lk=document.querySelector('.sb-ev[href="#ev-'+id+'"]');if(lk)lk.classList.add('cur');}});},{threshold:.15,rootMargin:'-60px 0px 0px 0px'});
document.querySelectorAll('.ev-card').forEach(function(c){obs.observe(c);});
</script>
</body>
</html>