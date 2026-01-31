<?php
$page_title = "Lineage";
include '../includes/header.php';

// Get active instructors from database
$conn = getDbConnection();
$instructors = $conn->query("SELECT * FROM instructors WHERE status = 'active' ORDER BY display_order ASC");
?>

<section class="section">
    <div class="container">
        <div class="section-header text-center" style="
    min-height: 320px;
    background:
      linear-gradient(rgba(0,0,0,0.35), rgba(0,0,0,0.35)),
      url('../assets/images/mt.jpg')
 center / cover no-repeat;
    padding: 4rem 2rem;
    border-radius: 12px;
    color: #fff;
">

            <p class="section-subtitle">Our Heritage</p>
            <h1 class="hero-title" style="color:#ffffff; text-shadow: 0 4px 8px rgba(202, 19, 19, 0.6);">
                Martial Lineage
            </h1>
            <p class="section-description">
                The unbroken chain of masters preserving ancient Siamese warfare traditions.
            </p>
        </div>

        <div style="max-width: 900px; margin: 3rem auto;">
            <div style="margin-bottom: 3rem; display: flex; align-items: center; gap: 2rem; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 250px;">
                    <img src="../assets/images/1.png" alt="Grandmaster Sane Tubtimtong"
                        style="width: 100%; height: 300px; object-fit: cover; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
                </div>
                <div style="flex: 1; min-width: 250px;">
                    <h2 style="color: var(--color-primary); margin-bottom: 1.5rem;">Grandmaster Sane Tubtimtong</h2>
                    <p style="font-size: 1.15rem; line-height: 1.8; color: var(--color-text-light);">
                        The foundation of our lineage rests upon Grandmaster Sane Tubtimtong, a legendary figure in the
                        world of Muay Boran.
                        His teachings form the cornerstone of our curriculum, passed down through generations of
                        dedicated practitioners.
                    </p>
                </div>
            </div>

            <div style="margin-bottom: 3rem; display: flex; align-items: center; gap: 2rem; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 250px;">
                    <h2 style="color: var(--color-primary); margin-bottom: 1.5rem;">Ajarn Brendaley Tarnate</h2>
                    <p style="font-size: 1.15rem; line-height: 1.8; color: var(--color-text-light);">
                        As the direct student of Grandmaster Sane, Ajarn Brendaley Tarnate carries forward the authentic
                        techniques and philosophy.
                        His meticulous documentation and teaching methodology ensure the preservation of traditional
                        knowledge for future generations.
                    </p>
                </div>
                <div style="flex: 1; min-width: 250px;">
                    <img src="../assets/images/2.png" alt="Ajarn Brendaley Tarnate"
                        style="width: 100%; height: 300px; object-fit: cover; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
                </div>
            </div>
        </div>

        <!-- Dynamic Instructors Section from Database -->
        <div style="margin-top: 5rem; margin-bottom: 3rem;">
            <div style="text-align: center; margin-bottom: 3rem;">
                <h2 style="color: var(--color-primary);">Our Kru (Instructors)</h2>
                <p style="font-size: 1.1rem; color: var(--color-text-light);">Dedicated masters preserving the tradition</p>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 2rem; margin-top: 2rem;">
                <?php while ($instructor = $instructors->fetch_assoc()): ?>
                <div style="text-align: center;">
                    <div style="width: 150px; height: 150px; margin: 0 auto 1rem; border-radius: 50%; overflow: hidden; background: var(--color-bg-light); border: 3px solid var(--color-primary); box-shadow: 0 4px 12px rgba(0,0,0,0.2);">
                        <?php if (!empty($instructor['photo_path'])): ?>
                            <img src="../<?php echo htmlspecialchars($instructor['photo_path']); ?>" 
                                 alt="<?php echo htmlspecialchars($instructor['name']); ?>"
                                 style="width: 100%; height: 100%; object-fit: cover;">
                        <?php else: ?>
                            <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 3rem; font-weight: bold; color: var(--color-primary);">
                                <?php echo strtoupper(substr($instructor['name'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <h3 style="color: var(--color-text); font-size: 1.2rem; margin-bottom: 0.5rem;">
                        <?php echo htmlspecialchars($instructor['name']); ?>
                    </h3>
                    <p style="color: var(--color-secondary); font-size: 1.1rem; font-weight: bold; margin-bottom: 0.2rem;">
                        <?php echo htmlspecialchars($instructor['khan_level']); ?>
                    </p>
                    <?php if (!empty($instructor['title'])): ?>
                        <p style="color: var(--color-text-muted); font-size: 0.9rem;">
                            <?php echo htmlspecialchars($instructor['title']); ?>
                        </p>
                    <?php endif; ?>
                    <?php if (!empty($instructor['location'])): ?>
                        <p style="color: var(--color-text-muted); font-size: 0.85rem;">
                            📍 <?php echo htmlspecialchars($instructor['location']); ?>
                        </p>
                    <?php endif; ?>
                </div>
                <?php endwhile; ?>
            </div>
        </div>

        <div style="text-align: center; margin-top: 4rem;">
            <p style="font-size: 1.15rem; color: var(--color-text-light); margin-bottom: 2rem;">
                Join the lineage and become part of this living tradition
            </p>
            <a href="membership-benefits.php" class="btn btn-primary">Begin Your Journey</a>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
