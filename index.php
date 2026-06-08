<?php
include("includes/db.php");

// Fetch counts for stats
$student_count_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM student");
$student_count = mysqli_fetch_assoc($student_count_query)['total'] + 50000; // Adding base for display

$course_count_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM courseunit");
$course_count = mysqli_fetch_assoc($course_count_query)['total'] + 450; // Adding base for display

// Fetch featured courses
$featured_courses_query = mysqli_query($conn, "SELECT cu.*, t.teacher_name FROM courseunit cu LEFT JOIN teacher t ON cu.teacher_id = t.teacher_id LIMIT 4");
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <title>SPFTrack | Track Your Progress</title>
    <?php include("includes/head.php"); ?>
</head>
<body class="bg-background text-on-surface font-body-md overflow-x-hidden">

<?php include("includes/header.php"); ?>

<main class="w-full pb-20 md:pb-0">
    <!-- Hero Section -->
    <section class="relative px-margin_mobile pt-xl pb-lg overflow-hidden bg-gradient-to-br from-surface to-surface-container-low">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center gap-lg">
            <div class="flex-1 space-y-md z-10">
                <span class="inline-block px-3 py-1 bg-primary-container text-on-primary-container rounded-full font-label-caps text-label-caps uppercase">Best</span>
                <h2 class="font-h1 text-h1 text-on-background lg:text-5xl lg:leading-tight">Track Your Progress</h2>
                <p class="text-on-surface-variant font-body-lg max-w-lg">
                    Monitor your academic performance, identify areas for improvement, and achieve your goals with our comprehensive tracking tools.
                </p>
                <div class="flex flex-wrap gap-md pt-md">
                    <a href="student/index.php" class="bg-primary hover:bg-on-primary-fixed-variant text-on-primary px-xl py-3 rounded-xl font-button transition-all transform active:scale-95 shadow-md inline-block">
                        Join for Free
                    </a>
                    <a href="courses.php" class="bg-secondary-container text-on-secondary-container px-xl py-3 rounded-xl font-button transition-all hover:bg-secondary-fixed shadow-sm inline-block">
                        Explore Courses
                    </a>
                </div>
            </div>
            <div class="flex-1 relative">
                <div class="relative w-full aspect-square md:aspect-video rounded-xl overflow-hidden shadow-xl">
                    <img class="w-full h-full object-cover" src="https://images.unsplash.com/photo-1523240795612-9a054b0db644?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80" alt="Students collaborating"/>
                    <div class="absolute inset-0 bg-gradient-to-t from-on-background/40 to-transparent"></div>
                </div>
                <!-- Decorative Elements -->
                <div class="absolute -top-4 -right-4 w-24 h-24 bg-primary-fixed-dim rounded-full blur-3xl opacity-50"></div>
                <div class="absolute -bottom-4 -left-4 w-32 h-32 bg-secondary-fixed rounded-full blur-3xl opacity-30"></div>
            </div>
        </div>
    </section>

    <!-- How it Works: Bento Layout -->
    <section class="py-xl px-margin_mobile max-w-7xl mx-auto">
        <div class="mb-lg text-center md:text-left">
            <h3 class="font-h2 text-h2 text-on-surface mb-xs">Track Your Progress</h3>
            <p class="text-on-surface-variant font-body-md max-w-md">Track your academic performance, identify areas for improvement, and achieve your goals with our comprehensive tracking tools.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-md">
            <!-- Step 1 -->
            <div class="bg-surface-container-low p-xl rounded-xl shadow-sm border border-outline-variant/10 flex flex-col gap-md">
                <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center">
                    <span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">psychology</span>
                </div>
                <div>
                    <h4 class="font-h3 text-h3 text-on-surface mb-sm">Get Started</h4>
                    <p class="text-on-surface-variant font-body-sm">Sign up for a free account and create your personalized learning profile.</p>
                </div>
            </div>
            <!-- Step 2 -->
            <div class="bg-primary p-xl rounded-xl shadow-md flex flex-col gap-md transform scale-105 z-10">
                <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                    <span class="material-symbols-outlined text-white" style="font-variation-settings: 'FILL' 1;">speed</span>
                </div>
                <div>
                    <h4 class="font-h3 text-h3 text-white mb-sm">Monitor Your Progress</h4>
                    <p class="text-primary-fixed-dim font-body-sm">Track your academic performance, identify areas for improvement, and achieve your goals with our comprehensive tracking tools.</p>
                </div>
            </div>
            <!-- Step 3 -->
            <div class="bg-surface-container-low p-xl rounded-xl shadow-sm border border-outline-variant/10 flex flex-col gap-md">
                <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center">
                    <span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">verified</span>
                </div>
                <div>
                    <h4 class="font-h3 text-h3 text-on-surface mb-sm">Achieve Your Goals</h4>
                    <p class="text-on-surface-variant font-body-sm">Reach your academic and professional goals with our comprehensive tracking tools.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Courses Carousel Teaser -->
    <section class="py-xl bg-surface-container">
        <div class="max-w-7xl mx-auto px-margin_mobile">
            <div class="flex justify-between items-end mb-lg">
                <div>
                    <h3 class="font-h2 text-h2 text-on-surface mb-xs">Featured Courses</h3>
                    <p class="text-on-surface-variant font-body-sm">Access courses taught by the best teachers in the world.</p>
                </div>
                <div class="flex gap-sm">
                    <button class="p-2 rounded-full border border-outline hover:bg-surface-container-high transition-colors">
                        <span class="material-symbols-outlined">chevron_left</span>
                    </button>
                    <button class="p-2 rounded-full border border-outline bg-white hover:bg-surface-container-high transition-colors">
                        <span class="material-symbols-outlined">chevron_right</span>
                    </button>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-md">
                <?php while ($course = mysqli_fetch_assoc($featured_courses_query)): ?>
                <!-- Course Card -->
                <div class="bg-white rounded-xl overflow-hidden shadow-sm hover:shadow-lg transition-shadow">
                    <div class="h-40 bg-on-background relative">
                        <img class="w-full h-full object-cover opacity-80" src="https://images.unsplash.com/photo-1498050108023-c5249f4df085?ixlib=rb-4.0.3&auto=format&fit=crop&w=1172&q=80" alt="<?php echo $course['courseUnit_name']; ?>"/>
                        <span class="absolute top-sm right-sm bg-primary-container text-on-primary-container px-2 py-1 rounded-lg text-[10px] font-bold">POPULAR</span>
                    </div>
                    <div class="p-md space-y-sm">
                        <div class="flex justify-between items-center">
                            <span class="font-label-caps text-[10px] text-primary"><?php echo strtoupper($course['courseUnit_code']); ?></span>
                            <div class="flex items-center text-tertiary">
                                <span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' 1;">star</span>
                                <span class="text-xs font-bold ml-1">4.9</span>
                            </div>
                        </div>
                        <h5 class="font-h3 text-body-md font-semibold text-on-surface h-12 line-clamp-2"><?php echo $course['courseUnit_name']; ?></h5>
                        <div class="flex items-center gap-xs">
                            <span class="material-symbols-outlined text-outline text-sm">person</span>
                            <span class="text-xs text-on-surface-variant font-label-caps uppercase tracking-wider"><?php echo $course['teacher_name'] ?? 'Expert Faculty'; ?></span>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
                
                <?php if (mysqli_num_rows($featured_courses_query) == 0): ?>
                <p class="col-span-full text-center text-on-surface-variant py-lg">No courses available at the moment. Check back soon!</p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <?php include("includes/footer.php"); ?>
</main>

</body>
</html>