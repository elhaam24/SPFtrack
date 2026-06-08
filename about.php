<?php include("includes/db.php"); ?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <title>About SPFTrack | Empowering Academic Excellence</title>
    <?php include("includes/head.php"); ?>
</head>
<body class="bg-surface selection:bg-primary-container selection:text-on-primary-container">

<?php include("includes/header.php"); ?>

<main class="pb-24">
    <!-- Hero Section -->
    <section class="relative h-[400px] flex items-center justify-center overflow-hidden mb-xl">
        <div class="absolute inset-0 z-0">
            <img alt="University Campus" class="w-full h-full object-cover opacity-20" src="https://images.unsplash.com/photo-1541339907198-e08756ebafe1?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80"/>
            <div class="absolute inset-0 bg-gradient-to-b from-surface via-transparent to-surface"></div>
        </div>
        <div class="relative z-10 text-center px-margin_mobile max-w-4xl">
            <span class="font-label-caps text-label-caps text-primary-container bg-on-primary-container px-3 py-1 rounded-full inline-block mb-md">Our Journey</span>
            <h2 class="font-h1 text-h1 text-on-background mb-md">About SPFTrack</h2>
            <p class="font-body-lg text-body-lg text-secondary max-w-2xl mx-auto">SPFTrack is a platform that helps you achieve your academic and professional goals.</p>
        </div>
    </section>
    
    <!-- Our Story - Bento Grid Style -->
    <section class="px-margin_mobile max-w-7xl mx-auto mb-xl">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-gutter">
            <div class="md:col-span-7 bg-surface-container-lowest p-xl rounded-xl shadow-sm border border-surface-container flex flex-col justify-center">
                <h3 class="font-h2 text-h2 text-on-background mb-md">Our Story</h3>
                <p class="font-body-md text-body-md text-secondary mb-md">SPFTrack began as a small research initiative focused on reducing cognitive load in university environments. We observed how brilliant minds were often bogged down by fragmented systems and sterile interfaces.</p>
                <p class="font-body-md text-body-md text-secondary">In 2026, SPFTrack was founded to solve this problem. We believe that everyone deserves access to quality education, and we are committed to providing our users with the best possible tools and resources to help them succeed.</p>
            </div>
            <div class="md:col-span-5 relative h-64 md:h-auto rounded-xl overflow-hidden shadow-md">
                <img alt="Team Collaboration" class="w-full h-full object-cover" src="https://images.unsplash.com/photo-1522071820081-009f0129c71c?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80"/>
            </div>
        </div>
    </section>

    <!-- Mission & Vision -->
    <section class="px-margin_mobile max-w-7xl mx-auto mb-xl">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-gutter">
            <div class="bg-primary-container/10 p-lg rounded-xl border-l-4 border-primary-container">
                <div class="flex items-center gap-sm mb-md">
                    <span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">rocket_launch</span>
                    <h3 class="font-h3 text-h3 text-on-primary-container">Our Mission</h3>
                </div>
                <p class="font-body-md text-body-md text-on-secondary-fixed-variant">To create and maintain an intuitive, unified interface that eliminates systemic barriers in learning. We empower students by prioritizing productivity and fostering genuine accomplishment.</p>
            </div>
            <div class="bg-surface-container-high p-lg rounded-xl border-l-4 border-secondary">
                <div class="flex items-center gap-sm mb-md">
                    <span class="material-symbols-outlined text-secondary" style="font-variation-settings: 'FILL' 1;">visibility</span>
                    <h3 class="font-h3 text-h3 text-secondary">Our Vision</h3>
                </div>
                <p class="font-body-md text-body-md text-on-secondary-fixed-variant">To be recognized as a premier provider of high-focus learning experiences, known for innovation, accessibility, and an unwavering commitment to student success.</p>
            </div>
        </div>
    </section>
</main>

<?php include("includes/footer.php"); ?>

</body>
</html>