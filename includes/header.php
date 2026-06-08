<header class="bg-surface dark:bg-surface-dim shadow-sm docked full-width top-0 sticky z-50 transition-colors duration-200 ease-in-out flex justify-between items-center px-margin_mobile h-16 w-full">
    <div class="flex items-center gap-md">
        <a href="index.php" class="flex items-center gap-2">
            <img src="images/logo.png" alt="SPFTrack Logo" class="w-8 h-8">
            <h1 class="font-h2 text-h2 font-semibold text-on-surface dark:text-on-surface">SPFTrack</h1>
        </a>
    </div>
    <nav class="hidden md:flex gap-xl items-center">
        <a class="font-label-caps text-label-caps <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'text-primary dark:text-primary-fixed-dim' : 'text-on-surface-variant dark:text-on-surface-variant'; ?> hover:bg-surface-container-low transition-colors px-2 py-1 rounded" href="index.php">Home</a>
        <a class="font-label-caps text-label-caps <?php echo basename($_SERVER['PHP_SELF']) == 'courses.php' ? 'text-primary dark:text-primary-fixed-dim' : 'text-on-surface-variant dark:text-on-surface-variant'; ?> hover:bg-surface-container-low transition-colors px-2 py-1 rounded" href="courses.php">Courses</a>
        <a class="font-label-caps text-label-caps <?php echo basename($_SERVER['PHP_SELF']) == 'about.php' ? 'text-primary dark:text-primary-fixed-dim' : 'text-on-surface-variant dark:text-on-surface-variant'; ?> hover:bg-surface-container-low transition-colors px-2 py-1 rounded" href="about.php">About</a>
        <a class="font-label-caps text-label-caps <?php echo basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'text-primary dark:text-primary-fixed-dim' : 'text-on-surface-variant dark:text-on-surface-variant'; ?> hover:bg-surface-container-low transition-colors px-2 py-1 rounded" href="contact.php">Contact</a>
    </nav>
    <div class="flex items-center gap-md">
        <a href="student/index.php" class="hidden md:block font-button text-button text-primary hover:text-on-primary-container px-md py-sm">Log In</a>
        <a href="student/register.php" class="bg-primary-container text-on-primary-container font-button text-button px-lg py-sm rounded-xl hover:bg-primary transition-colors">Sign Up</a>
    </div>
</header>
