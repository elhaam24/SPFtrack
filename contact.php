<?php include("includes/db.php"); ?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <title>Contact SPFTrack | Get in Touch</title>
    <?php include("includes/head.php"); ?>
</head>
<body class="bg-background text-on-surface font-body-md min-h-screen">

<?php include("includes/header.php"); ?>

<main class="max-w-7xl mx-auto px-margin_mobile py-xl space-y-xl">
    <!-- Hero Section -->
    <section class="text-center space-y-sm">
        <h2 class="font-h1 text-h1 text-on-background">Get in Touch</h2>
        <p class="text-on-surface-variant max-w-2xl mx-auto font-body-lg">We’d love to hear from you. Our team is dedicated to supporting your goals and ensuring you have the best possible experience with SPFTrack.</p>
    </section>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-lg">
        <!-- Contact Form Section -->
        <div class="lg:col-span-7 bg-surface-container-lowest rounded-xl p-lg shadow-sm">
            <h3 class="font-h3 text-h3 mb-lg text-on-surface">Send a Message</h3>
            <form class="space-y-md">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-md">
                    <div class="space-y-xs">
                        <label class="font-label-caps text-label-caps text-on-surface-variant px-1">Name</label>
                        <input class="w-full px-md py-sm rounded-xl border border-outline-variant bg-surface focus:outline-none focus:border-primary transition-all placeholder:text-outline" placeholder="John Doe" type="text"/>
                    </div>
                    <div class="space-y-xs">
                        <label class="font-label-caps text-label-caps text-on-surface-variant px-1">Email</label>
                        <input class="w-full px-md py-sm rounded-xl border border-outline-variant bg-surface focus:outline-none focus:border-primary transition-all placeholder:text-outline" placeholder="john@example.com" type="email"/>
                    </div>
                </div>
                <div class="space-y-xs">
                    <label class="font-label-caps text-label-caps text-on-surface-variant px-1">Subject</label>
                    <select class="w-full px-md py-sm rounded-xl border border-outline-variant bg-surface focus:outline-none focus:border-primary transition-all text-on-surface">
                        <option>Course Inquiry</option>
                        <option>Technical Support</option>
                        <option>Billing Question</option>
                        <option>Partnership Proposal</option>
                        <option>Other</option>
                    </select>
                </div>
                <div class="space-y-xs">
                    <label class="font-label-caps text-label-caps text-on-surface-variant px-1">Message</label>
                    <textarea class="w-full px-md py-sm rounded-xl border border-outline-variant bg-surface focus:outline-none focus:border-primary transition-all placeholder:text-outline" placeholder="Tell us how we can help..." rows="5"></textarea>
                </div>
                <button class="w-full md:w-auto px-xl py-md bg-primary text-on-primary font-button text-button rounded-xl hover:bg-on-primary-fixed-variant transition-colors shadow-sm" type="submit">
                    Send Inquiry
                </button>
            </form>
        </div>
    </div>
</main>

<?php include("includes/footer.php"); ?>

</body>
</html>