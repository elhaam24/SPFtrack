<?php
include("includes/db.php");

// Fetch courses with teacher names and department names
$courses_query = mysqli_query($conn, "
    SELECT cu.*, t.teacher_name, d.department_name 
    FROM courseunit cu 
    LEFT JOIN teacher t ON cu.teacher_id = t.teacher_id 
    LEFT JOIN course c ON cu.course_id = c.course_id
    LEFT JOIN department d ON c.department_id = d.department_id
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Explore Courses - SPFTrack</title>
    <?php include("includes/head.php"); ?>
    <style>
        .tonal-card {
            box-shadow: 0 4px 12px rgba(30, 41, 59, 0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .tonal-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(30, 41, 59, 0.1);
        }
    </style>
</head>
<body class="bg-surface text-on-surface min-h-screen flex flex-col font-body-md">

<?php include("includes/header.php"); ?>

<main class="flex-grow pb-xl">
    <!-- Hero & Search Section -->
    <section class="px-margin_mobile pt-xl pb-lg max-w-7xl mx-auto">
        <div class="flex flex-col gap-sm mb-lg">
            <h2 class="font-h1 text-h1 text-on-surface">Expand your horizons</h2>
            <p class="font-body-lg text-body-lg text-on-surface-variant">Find courses offered by professionals.</p>
        </div>
        <div class="relative w-full max-w-2xl">
            <span class="material-symbols-outlined absolute left-md top-1/2 -translate-y-1/2 text-outline">search</span>
            <input class="w-full pl-[48px] pr-md py-md bg-white border border-outline-variant rounded-xl focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary-container/30 transition-all font-body-md text-on-surface shadow-sm" placeholder="Search for courses" type="text"/>
        </div>
    </section>

    <!-- Category Filters -->
    <section class="px-margin_mobile mb-xl overflow-x-auto no-scrollbar max-w-7xl mx-auto">
        <div class="flex gap-sm py-xs">
            <button class="px-lg py-sm rounded-full bg-on-background text-inverse-primary font-button text-button whitespace-nowrap">All Courses</button>
            <button class="px-lg py-sm rounded-full bg-secondary-container text-on-secondary-container font-button text-button whitespace-nowrap hover:bg-surface-container-high transition-colors">Science</button>
            <button class="px-lg py-sm rounded-full bg-secondary-container text-on-secondary-container font-button text-button whitespace-nowrap hover:bg-surface-container-high transition-colors">Arts</button>
            <button class="px-lg py-sm rounded-full bg-secondary-container text-on-secondary-container font-button text-button whitespace-nowrap hover:bg-surface-container-high transition-colors">Business</button>
            <button class="px-lg py-sm rounded-full bg-secondary-container text-on-secondary-container font-button text-button whitespace-nowrap hover:bg-surface-container-high transition-colors">Technology</button>
        </div>
    </section>

    <!-- Bento Grid Course Layout -->
    <section class="px-margin_mobile max-w-7xl mx-auto">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-lg">
            <?php 
            $first = true;
            while ($course = mysqli_fetch_assoc($courses_query)): 
                if ($first): // Make the first one large as in the original design
                    $first = false;
            ?>
            <!-- Course Card 1 (Large Feature) -->
            <div class="lg:col-span-2 tonal-card bg-white rounded-xl overflow-hidden flex flex-col md:flex-row">
                <div class="md:w-1/2 relative min-h-[240px]">
                    <img class="absolute inset-0 w-full h-full object-cover" src="https://images.unsplash.com/photo-1516321318423-f06f85e504b3?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80" alt="<?php echo $course['courseUnit_name']; ?>"/>
                    <div class="absolute top-md left-md">
                        <span class="bg-primary text-on-primary px-sm py-xs rounded-lg font-label-caps text-label-caps uppercase">Featured</span>
                    </div>
                </div>
                <div class="p-lg flex flex-col justify-between md:w-1/2">
                    <div>
                        <div class="flex items-center gap-xs text-tertiary mb-xs">
                            <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
                            <span class="font-label-caps text-label-caps">4.9 (1.2k Reviews)</span>
                        </div>
                        <h3 class="font-h2 text-h2 text-on-surface mb-sm"><?php echo $course['courseUnit_name']; ?></h3>
                        <p class="font-body-sm text-body-sm text-on-surface-variant mb-md"><?php echo $course['description'] ?? 'Master the tools of tomorrow. From foundational concepts to advanced techniques.'; ?></p>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-sm">
                            <span class="material-symbols-outlined text-outline">schedule</span>
                            <span class="font-body-sm text-body-sm text-on-surface-variant">12 Weeks</span>
                        </div>
                        <a href="student/index.php" class="bg-primary text-on-primary font-button text-button px-lg py-sm rounded-xl hover:bg-on-primary-fixed-variant transition-all">Learn More</a>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <!-- Regular Course Card -->
            <div class="tonal-card bg-white rounded-xl overflow-hidden flex flex-col">
                <div class="h-48 relative">
                    <img class="w-full h-full object-cover" src="https://images.unsplash.com/photo-1454165833767-131f369501d1?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80" alt="<?php echo $course['courseUnit_name']; ?>"/>
                </div>
                <div class="p-md flex-grow flex flex-col">
                    <span class="font-label-caps text-label-caps text-primary mb-xs"><?php echo $course['department_name'] ?? 'General'; ?></span>
                    <h3 class="font-h3 text-h3 text-on-surface mb-sm"><?php echo $course['courseUnit_name']; ?></h3>
                    <p class="font-body-sm text-body-sm text-on-surface-variant mb-md flex-grow"><?php echo $course['description'] ?? 'Unlock your potential by understanding the core principles of this field.'; ?></p>
                    <div class="pt-md border-t border-surface-container flex items-center justify-between">
                        <span class="font-body-sm text-body-sm font-semibold text-on-surface">8 Weeks</span>
                        <a href="student/index.php" class="text-primary font-button text-button hover:bg-surface-container-low px-sm py-xs rounded-lg transition-colors">Learn More</a>
                    </div>
                </div>
            </div>
            <?php endif; endwhile; ?>
            
            <?php if (mysqli_num_rows($courses_query) == 0): ?>
            <p class="col-span-full text-center text-on-surface-variant py-lg">No courses found matching your criteria.</p>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php include("includes/footer.php"); ?>

</body>
</html>