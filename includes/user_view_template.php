<?php
/**
 * User CV Display Template - Clean Inter Font Theme with Profile Photo Support
 */

$user_data = null;
$experiences = [];
$education = [];
$skills = [];

$script_dir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$base_url = ($script_dir === '/' || $script_dir === '\\') ? '' : $script_dir;

if (isset($db) && !empty($slug)) {
    $stmt_user = $db->prepare("SELECT * FROM users WHERE slug = ? OR username = ? LIMIT 1");
    if ($stmt_user) {
        $stmt_user->bind_param("ss", $slug, $slug);
        $stmt_user->execute();
        $user_data = $stmt_user->get_result()->fetch_assoc();
    }

    if ($user_data) {
        $user_id = $user_data['id'];

        $stmt_exp = $db->prepare("SELECT * FROM experiences WHERE user_id = ? ORDER BY start_date DESC");
        $stmt_exp->bind_param("i", $user_id);
        $stmt_exp->execute();
        $res_exp = $stmt_exp->get_result();
        while ($r = $res_exp->fetch_assoc()) {
            $experiences[] = $r;
        }

        $stmt_edu = $db->prepare("SELECT * FROM education WHERE user_id = ? ORDER BY graduation_year DESC");
        $stmt_edu->bind_param("i", $user_id);
        $stmt_edu->execute();
        $res_edu = $stmt_edu->get_result();
        while ($r = $res_edu->fetch_assoc()) {
            $education[] = $r;
        }

        $stmt_skill = $db->prepare("SELECT * FROM skills WHERE user_id = ? ORDER BY percentage DESC, id DESC");
        $stmt_skill->bind_param("i", $user_id);
        $stmt_skill->execute();
        $res_skill = $stmt_skill->get_result();
        while ($r = $res_skill->fetch_assoc()) {
            $skills[] = $r;
        }
    }
}

// Fallback seed data if DB connection isn't present or user not found
if (!$user_data) {
    if (strtolower($slug) === 'erga_refaldy' || strtolower($slug) === 'cecep_suwanda' || $slug === 'default' || empty($slug)) {
        $user_data = [
            'full_name' => 'Erga Refaldy D.G',
            'title'     => 'Senior Software Engineer & Web Developer',
            'email'     => 'erga.refaldy@example.com',
            'phone'     => '085712345678',
            'address'   => 'Bandung, Jawa Barat',
            'summary'   => 'Software Engineer dengan pengalaman lebih dari 6 tahun dalam merancang dan mengembangkan aplikasi web skala enterprise. Berfokus pada keandalan arsitektur backend PHP/MySQL, desain antarmuka pengguna yang ramah dan intuitif, serta optimasi performa tinggi.',
            'slug'      => 'erga_refaldy',
            'photo'     => null
        ];

        $experiences = [
            [
                'company_name' => 'PT Teknologi Indonesia Jaya',
                'job_title'    => 'Lead Web Developer',
                'start_date'   => '2022-01-15',
                'end_date'     => null,
                'is_current'   => 1,
                'description'  => "Memimpin tim rekayasa web dalam mengembangkan platform e-commerce dan dashboard internal berbasis PHP Native & MySQL. Berhasil memangkas waktu muat halaman hingga 45% serta mengimplementasikan pengujian otomatis."
            ],
            [
                'company_name' => 'Solusi Digital Nusantara',
                'job_title'    => 'Senior PHP & Web Engineer',
                'start_date'   => '2019-06-01',
                'end_date'     => '2021-12-31',
                'is_current'   => 0,
                'description'  => "Merancang dan mengimplementasikan RESTful API untuk sistem inventaris berbasis cloud. Mengoptimalkan kueri database relasional yang memproses lebih dari 500.000 permintaan harian."
            ],
            [
                'company_name' => 'Creative Media Works',
                'job_title'    => 'Junior Full Stack Developer',
                'start_date'   => '2017-03-01',
                'end_date'     => '2019-05-30',
                'is_current'   => 0,
                'description'  => "Mengembangkan template web responsif, membangun formulir dinamis, serta mengintegrasikan modul otentikasi pengguna yang aman."
            ]
        ];

        $education = [
            [
                'school_name'     => 'Universitas Komputer Indonesia',
                'degree_obtained' => 'Sarjana Komputer (S.Kom)',
                'major'           => 'Teknik Informatika',
                'start_year'      => 2013,
                'graduation_year' => 2017,
                'description'     => 'Lulus dengan predikat Cumlaude (IPK 3.82). Aktif dalam unit kegiatan mahasiswa bidang pemrograman dan otomasi sistem.'
            ],
            [
                'school_name'     => 'SMA Negeri 1 Bandung',
                'degree_obtained' => 'Ijazah SMA',
                'major'           => 'Ilmu Pengetahuan Alam (IPA)',
                'start_year'      => 2010,
                'graduation_year' => 2013,
                'description'     => 'Fokus pada studi Matematika dan Logika Komputer.'
            ]
        ];

        $skills = [
            ['skill_name' => 'PHP & MySQL', 'proficiency_level' => 'Expert', 'percentage' => 95, 'category' => 'Backend'],
            ['skill_name' => 'HTML5, CSS3, JavaScript', 'proficiency_level' => 'Expert', 'percentage' => 90, 'category' => 'Frontend'],
            ['skill_name' => 'Bootstrap & UI/UX Clean Design', 'proficiency_level' => 'Advanced', 'percentage' => 88, 'category' => 'Frontend'],
            ['skill_name' => 'Git & Version Control', 'proficiency_level' => 'Advanced', 'percentage' => 85, 'category' => 'Tools'],
            ['skill_name' => 'RESTful API Architecture', 'proficiency_level' => 'Expert', 'percentage' => 92, 'category' => 'Backend'],
            ['skill_name' => 'Apache & Linux Administration', 'proficiency_level' => 'Intermediate', 'percentage' => 78, 'category' => 'DevOps']
        ];
    }
}

function formatDateDisplay($start, $end, $is_current = 0) {
    $s_time = strtotime($start);
    $s_str = $s_time ? date('M Y', $s_time) : $start;
    
    if ($is_current) {
        return "{$s_str} — Sekarang";
    }
    if ($end) {
        $e_time = strtotime($end);
        $e_str = $e_time ? date('M Y', $e_time) : $end;
        return "{$s_str} — {$e_str}";
    }
    return $s_str;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($user_data['full_name'] ?? 'Curriculum Vitae') ?> — Curriculum Vitae</title>

    <!-- Google Font: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        .top-app-bar {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 12px 0;
        }

        .cv-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
            margin-top: 30px;
            margin-bottom: 40px;
            padding: 48px;
        }

        .user-avatar {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background: #0f172a;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.2rem;
            font-weight: 700;
            letter-spacing: -1px;
        }

        .user-avatar-img {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #e2e8f0;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }

        .user-name {
            font-size: 2.25rem;
            font-weight: 800;
            letter-spacing: -0.025em;
            color: #0f172a;
            margin-bottom: 4px;
        }

        .user-title {
            font-size: 1.1rem;
            font-weight: 500;
            color: #2563eb;
            margin-bottom: 16px;
        }

        .contact-item {
            font-size: 0.9rem;
            color: #475569;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-right: 18px;
            margin-bottom: 6px;
        }

        .section-heading {
            font-size: 1.15rem;
            font-weight: 700;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding-bottom: 8px;
            border-bottom: 2px solid #0f172a;
            margin-bottom: 24px;
        }

        .timeline-entry {
            position: relative;
            padding-left: 20px;
            border-left: 2px solid #e2e8f0;
            margin-bottom: 28px;
        }

        .timeline-entry::before {
            content: '';
            position: absolute;
            left: -6px;
            top: 4px;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #2563eb;
        }

        .entry-title {
            font-size: 1.05rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 2px;
        }

        .entry-subtitle {
            font-size: 0.9rem;
            font-weight: 600;
            color: #334155;
            margin-bottom: 4px;
        }

        .entry-date {
            font-size: 0.8rem;
            font-weight: 500;
            color: #64748b;
            background: #f1f5f9;
            padding: 3px 10px;
            border-radius: 4px;
            display: inline-block;
            margin-bottom: 10px;
        }

        .entry-desc {
            font-size: 0.92rem;
            color: #475569;
            line-height: 1.6;
        }

        .skill-item {
            margin-bottom: 16px;
        }

        .skill-header {
            display: flex;
            justify-content: space-between;
            font-size: 0.9rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 6px;
        }

        .skill-level-tag {
            font-size: 0.75rem;
            font-weight: 500;
            color: #2563eb;
            background: #eff6ff;
            padding: 2px 8px;
            border-radius: 4px;
        }

        .progress-custom {
            height: 6px;
            background-color: #f1f5f9;
            border-radius: 4px;
            overflow: hidden;
        }

        .progress-bar-custom {
            background-color: #0f172a;
            border-radius: 4px;
        }

        @media print {
            .no-print { display: none !important; }
            body { background: #ffffff; }
            .cv-card { box-shadow: none; border: none; padding: 0; margin-top: 0; }
        }
    </style>
</head>
<body>

<!-- Clean Navbar Header -->
<header class="top-app-bar no-print">
    <div class="container d-flex justify-content-between align-items-center">
        <a href="<?= htmlspecialchars($base_url ?: '/') ?>" class="text-decoration-none text-dark fw-bold d-flex align-items-center gap-2" style="font-size: 0.95rem;">
            <i class="bi bi-file-earmark-person text-primary fs-5"></i> Portfolio CV System
        </a>
        <div class="d-flex align-items-center gap-2">
            <button onclick="window.print()" class="btn btn-outline-dark btn-sm px-3 fw-medium">
                <i class="bi bi-printer me-1"></i> Cetak / Simpan PDF
            </button>
            <a href="<?= htmlspecialchars($base_url ?: '/') ?>/Admin" class="btn btn-dark btn-sm px-3 fw-medium">
                <i class="bi bi-shield-lock me-1"></i> Admin Panel
            </a>
        </div>
    </div>
</header>

<!-- Main CV Document Body -->
<main class="container">
    <div class="cv-card">
        
        <!-- Header Info & Profile Photo -->
        <div class="row align-items-center pb-4 mb-4 border-bottom">
            <div class="col-auto mb-3 mb-md-0">
                <?php if (!empty($user_data['photo']) && file_exists(__DIR__ . '/../' . $user_data['photo'])): ?>
                    <img src="<?= htmlspecialchars($user_data['photo']) ?>" alt="Foto Profil" class="user-avatar-img">
                <?php else: ?>
                    <div class="user-avatar">
                        <?= strtoupper(substr($user_data['full_name'] ?? 'E', 0, 1)) ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="col">
                <h1 class="user-name"><?= htmlspecialchars($user_data['full_name'] ?? 'Erga Refaldy D.G') ?></h1>
                <div class="user-title"><?= htmlspecialchars($user_data['title'] ?? 'Software Engineer') ?></div>
                
                <div class="d-flex flex-wrap">
                    <?php if (!empty($user_data['email'])): ?>
                        <div class="contact-item"><i class="bi bi-envelope text-secondary"></i> <?= htmlspecialchars($user_data['email']) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($user_data['phone'])): ?>
                        <div class="contact-item"><i class="bi bi-telephone text-secondary"></i> <?= htmlspecialchars($user_data['phone']) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($user_data['address'])): ?>
                        <div class="contact-item"><i class="bi bi-geo-alt text-secondary"></i> <?= htmlspecialchars($user_data['address']) ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Summary -->
        <?php if (!empty($user_data['summary'])): ?>
            <div class="mb-5">
                <h2 class="section-heading">Ringkasan Profil</h2>
                <p class="text-secondary" style="font-size: 0.96rem; line-height: 1.7;">
                    <?= nl2br(htmlspecialchars($user_data['summary'])) ?>
                </p>
            </div>
        <?php endif; ?>

        <div class="row g-5">
            <!-- Left Column: Experiences & Education -->
            <div class="col-lg-7">
                
                <!-- Experience Section -->
                <div class="mb-5">
                    <h2 class="section-heading">Pengalaman Kerja</h2>
                    <?php if (!empty($experiences)): ?>
                        <?php foreach ($experiences as $exp): ?>
                            <div class="timeline-entry">
                                <div class="entry-title"><?= htmlspecialchars($exp['job_title']) ?></div>
                                <div class="entry-subtitle"><?= htmlspecialchars($exp['company_name']) ?></div>
                                <div class="entry-date"><?= formatDateDisplay($exp['start_date'], $exp['end_date'], $exp['is_current'] ?? 0) ?></div>
                                <div class="entry-desc">
                                    <?= nl2br(htmlspecialchars($exp['description'] ?? '')) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted small">Belum ada riwayat pengalaman kerja.</p>
                    <?php endif; ?>
                </div>

                <!-- Education Section -->
                <div>
                    <h2 class="section-heading">Riwayat Pendidikan</h2>
                    <?php if (!empty($education)): ?>
                        <?php foreach ($education as $edu): ?>
                            <div class="timeline-entry">
                                <div class="entry-title"><?= htmlspecialchars($edu['degree_obtained']) ?> <?= !empty($edu['major']) ? '— ' . htmlspecialchars($edu['major']) : '' ?></div>
                                <div class="entry-subtitle"><?= htmlspecialchars($edu['school_name']) ?></div>
                                <div class="entry-date"><?= htmlspecialchars($edu['start_year'] ?? '') ?> — <?= htmlspecialchars($edu['graduation_year'] ?? 'Selesai') ?></div>
                                <?php if (!empty($edu['description'])): ?>
                                    <div class="entry-desc"><?= nl2br(htmlspecialchars($edu['description'])) ?></div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted small">Belum ada data pendidikan.</p>
                    <?php endif; ?>
                </div>

            </div>

            <!-- Right Column: Skills & Info -->
            <div class="col-lg-5">
                
                <!-- Skills Section -->
                <div class="mb-5">
                    <h2 class="section-heading">Keahlian Utama</h2>
                    <?php if (!empty($skills)): ?>
                        <?php foreach ($skills as $skill): ?>
                            <div class="skill-item">
                                <div class="skill-header">
                                    <span><?= htmlspecialchars($skill['skill_name']) ?></span>
                                    <span class="skill-level-tag"><?= htmlspecialchars($skill['proficiency_level']) ?></span>
                                </div>
                                <div class="progress-custom">
                                    <div class="progress-bar-custom" style="width: <?= (int)($skill['percentage'] ?? 80) ?>%; height: 100%;"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted small">Belum ada keahlian terdaftar.</p>
                    <?php endif; ?>
                </div>

                <!-- Public Link Box -->
                <div class="p-4 bg-light rounded border border-light-subtle no-print">
                    <div class="fw-semibold text-dark mb-1" style="font-size: 0.9rem;"><i class="bi bi-link-45deg me-1"></i> Tautan Publik CV:</div>
                    <code class="d-block p-2 bg-white rounded border mb-2 text-break" style="font-size: 0.8rem; color: #0f172a;">
                        http://localhost/project_cv/<?= htmlspecialchars($user_data['slug'] ?? 'erga_refaldy') ?>
                    </code>
                    <button onclick="navigator.clipboard.writeText(window.location.href); alert('Tautan CV telah disalin!');" class="btn btn-outline-dark btn-sm w-100 fw-medium" style="font-size: 0.8rem;">
                        <i class="bi bi-clipboard me-1"></i> Salin Link CV
                    </button>
                </div>

            </div>
        </div>

    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>