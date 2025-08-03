<?php
    require_once(__DIR__ . "/bootstrap.php"); // Modern PHP 8.4 compatibility
	$no_login = true; // Disable automatic login requirement
	include_once("include/config.inc.php");
	include_once($_PJ_include_path . '/scripts.inc.php');

	// Set form action for login form include
	$form_action = 'inventory/customer.php';
	$form_class = 'loginForm';
	$container_style = 'margin: 0; padding: 1.5rem;';
?>
<!DOCTYPE html>
<html lang="<?= $GLOBALS['_PJ_language'] ?? 'de' ?>">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>TimeEffect - Professional Time Tracking</title>
	<meta name="description" content="TimeEffect is a professional time tracking and project management solution for teams and individuals.">
	<meta name="keywords" content="time tracking, project management, timesheet, productivity, billing">
	
	<!-- Favicon -->
	<link rel="icon" href="<?= $GLOBALS['_PJ_image_path'] ?>/favicon.ico" type="image/x-icon">
	
	<!-- CSS -->
	<link rel="stylesheet" href="<?= $GLOBALS['_PJ_css_path'] ?>/project.css" type="text/css">
	<link rel="stylesheet" href="<?= $GLOBALS['_PJ_css_path'] ?>/modern.css" type="text/css">
	<link rel="stylesheet" href="<?= $GLOBALS['_PJ_css_path'] ?>/layout.css" type="text/css">
	
	<!-- JavaScript -->
	<script src="<?= $GLOBALS['_PJ_http_root'] ?>/include/functions.js" type="text/javascript"></script>
	
	<style>
	.landing-hero {
		background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
		color: white;
		padding: 4rem 2rem;
		text-align: center;
	}
	.landing-content {
		display: grid;
		grid-template-columns: 2fr 1fr;
		gap: 3rem;
		max-width: 1200px;
		margin: 0 auto;
		padding: 3rem 2rem;
	}
	.feature-grid {
		display: grid;
		grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
		gap: 2rem;
		margin: 2rem 0;
	}
	.feature-card {
		background: var(--surface-color);
		border-radius: var(--radius-lg);
		padding: 1.5rem;
		border: 1px solid var(--border-color);
		transition: var(--transition-fast);
	}
	.feature-card:hover {
		transform: translateY(-2px);
		box-shadow: var(--shadow-lg);
	}
	.feature-icon {
		font-size: 2.5rem;
		margin-bottom: 1rem;
		display: block;
	}
	.login-sidebar {
		background: var(--surface-color);
		border-radius: var(--radius-lg);
		border: 1px solid var(--border-color);
		padding: 0;
		height: fit-content;
		sticky: true;
		top: 2rem;
	}
	@media (max-width: 768px) {
		.landing-content {
			grid-template-columns: 1fr;
			gap: 2rem;
		}
		.landing-hero {
			padding: 2rem 1rem;
		}
	}
	</style>
</head>
<body class="landing-page">
	<!-- Hero Section -->
	<div class="landing-hero">
		<div style="max-width: 800px; margin: 0 auto;">
			<img src="<?= $GLOBALS['_PJ_image_path'] ?>/logo_te_150.png" alt="TimeEffect Logo" style="height: 60px; margin-bottom: 2rem;">
			<h1 style="font-size: 3rem; margin: 0 0 1rem 0; font-weight: 700;">TimeEffect</h1>
			<p style="font-size: 1.25rem; margin: 0 0 2rem 0; opacity: 0.9;">Professional Time Tracking & Project Management</p>
			<p style="font-size: 1.1rem; margin: 0; opacity: 0.8;">Streamline your workflow, track time efficiently, and manage projects with ease.</p>
		</div>
	</div>

	<!-- Main Content -->
	<div class="landing-content">
		<!-- Features Section -->
		<div class="features-section">
			<h2 style="font-size: 2.5rem; margin: 0 0 1rem 0; color: var(--text-primary);">What is TimeEffect?</h2>
			<p style="font-size: 1.1rem; color: var(--text-secondary); margin: 0 0 2rem 0;">TimeEffect is designed for professionals who need accurate time tracking, efficient project management, and detailed reporting capabilities.</p>
			
			<div class="feature-grid">
				<div class="feature-card">
					<span class="feature-icon">⏱️</span>
					<h3 style="margin: 0 0 0.5rem 0;">Precise Time Tracking</h3>
					<p style="margin: 0; color: var(--text-secondary);">Track time with precision down to the minute. Start and stop activities with a single click.</p>
				</div>
				
				<div class="feature-card">
					<span class="feature-icon">📊</span>
					<h3 style="margin: 0 0 0.5rem 0;">Detailed Reporting</h3>
					<p style="margin: 0; color: var(--text-secondary);">Generate comprehensive reports for clients, projects, and billing with customizable date ranges.</p>
				</div>
				
				<div class="feature-card">
					<span class="feature-icon">👥</span>
					<h3 style="margin: 0 0 0.5rem 0;">Team Management</h3>
					<p style="margin: 0; color: var(--text-secondary);">Manage multiple users, assign permissions, and track team productivity across projects.</p>
				</div>
				
				<div class="feature-card">
					<span class="feature-icon">💼</span>
					<h3 style="margin: 0 0 0.5rem 0;">Project Organization</h3>
					<p style="margin: 0; color: var(--text-secondary);">Organize work by customers and projects with hierarchical structure and access controls.</p>
				</div>
				
				<div class="feature-card">
					<span class="feature-icon">💰</span>
					<h3 style="margin: 0 0 0.5rem 0;">Billing Integration</h3>
					<p style="margin: 0; color: var(--text-secondary);">Track billable hours, set hourly rates, and generate invoices with accurate time data.</p>
				</div>
				
				<div class="feature-card">
					<span class="feature-icon">🔒</span>
					<h3 style="margin: 0 0 0.5rem 0;">Secure & Private</h3>
					<p style="margin: 0; color: var(--text-secondary);">Enterprise-grade security with role-based access control and data protection.</p>
				</div>
			</div>
			
			<!-- Key Benefits -->
			<div style="margin: 3rem 0;">
				<h3 style="font-size: 1.5rem; margin: 0 0 1rem 0;">Features</h3>
				<ul style="list-style: none; padding: 0; margin: 0;">
					<li style="padding: 0.5rem 0; display: flex; align-items: center;"><span style="color: var(--success-color); margin-right: 0.5rem;">✓</span> Increase productivity with accurate time tracking</li>
					<li style="padding: 0.5rem 0; display: flex; align-items: center;"><span style="color: var(--success-color); margin-right: 0.5rem;">✓</span> Improve project profitability analysis</li>
					<li style="padding: 0.5rem 0; display: flex; align-items: center;"><span style="color: var(--success-color); margin-right: 0.5rem;">✓</span> Streamline client billing and invoicing</li>
					<li style="padding: 0.5rem 0; display: flex; align-items: center;"><span style="color: var(--success-color); margin-right: 0.5rem;">✓</span> Gain insights into team performance</li>
					<li style="padding: 0.5rem 0; display: flex; align-items: center;"><span style="color: var(--success-color); margin-right: 0.5rem;">✓</span> Ensure compliance with labor regulations</li>
				</ul>
			</div>
		</div>
		
		<!-- Login Sidebar -->
		<div class="login-sidebar">
			<div style="padding: 1.5rem 1.5rem 0.5rem 1.5rem; border-bottom: 1px solid var(--border-color);">
				<h3 style="margin: 0; text-align: center;">Access Your Account</h3>
			</div>
			<?php include('templates/shared/login-form.ihtml.php'); ?>
		</div>
	</div>
	
	<!-- Footer -->
	<footer style="background: var(--surface-color); border-top: 1px solid var(--border-color); padding: 2rem; text-align: center; margin-top: 3rem;">
		<p style="margin: 0; color: var(--text-secondary); font-size: 0.9rem;">
			TimeEffect Version: <?= $GLOBALS['_PJ_timeeffect_version'] ?? '1.0' ?> 
			(Revision: <?= $GLOBALS['_PJ_timeeffect_revision'] ?? 'dev' ?>, 
			<?= date($GLOBALS['_PJ_format_datetime'] ?? 'Y-m-d H:i', strtotime($GLOBALS['_PJ_timeeffect_date'] ?? 'now')) ?>)
		</p>
	</footer>
	
	<!-- Theme toggle script -->
	<script>
	// Initialize theme system
	document.addEventListener('DOMContentLoaded', function() {
		// Theme detection and initialization
		const savedTheme = localStorage.getItem('theme');
		const systemDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
		
		if (savedTheme) {
			document.documentElement.setAttribute('data-theme', savedTheme);
		} else if (systemDark) {
			document.documentElement.setAttribute('data-theme', 'dark');
		}
	});
	</script>
</body>
</html>
