<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Sign In - Examination System</title>
	<script src="https://cdn.tailwindcss.com"></script>
	<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
	<!-- SF Pro Display font for iOS feel -->
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
	<style>
		:root {
			--ios-blue: #007AFF;
			--ios-blue-light: #5AC8FA;
			--ios-gray: #8E8E93;
			--ios-gray-light: #F2F2F7;
			--ios-gray-dark: #1C1C1E;
			--ios-green: #34C759;
			--ios-red: #FF3B30;
			--ios-orange: #FF9500;
			--ios-purple: #AF52DE;
		}

		* {
			-webkit-font-smoothing: antialiased;
			-moz-osx-font-smoothing: grayscale;
		}

		body {
			font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
			background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
			min-height: 100vh;
			position: relative;
			overflow-x: hidden;
		}

		/* Animated background */
		body::before {
			content: '';
			position: fixed;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
			background: 
				radial-gradient(circle at 20% 80%, rgba(120, 119, 198, 0.3) 0%, transparent 50%),
				radial-gradient(circle at 80% 20%, rgba(255, 119, 198, 0.3) 0%, transparent 50%),
				radial-gradient(circle at 40% 40%, rgba(120, 219, 255, 0.2) 0%, transparent 50%);
			animation: backgroundMove 20s ease-in-out infinite;
			z-index: -1;
		}

		@keyframes backgroundMove {
			0%, 100% { transform: translateX(0) translateY(0) rotate(0deg); }
			33% { transform: translateX(-30px) translateY(-50px) rotate(1deg); }
			66% { transform: translateX(20px) translateY(20px) rotate(-1deg); }
		}

		/* Glass morphism card */
		.glass-card {
			background: rgba(255, 255, 255, 0.1);
			backdrop-filter: blur(40px) saturate(180%);
			border: 1px solid rgba(255, 255, 255, 0.2);
			box-shadow: 
				0 32px 64px rgba(0, 0, 0, 0.1),
				0 0 0 1px rgba(255, 255, 255, 0.1),
				inset 0 1px 0 rgba(255, 255, 255, 0.2);
		}

		/* iOS-style input fields */
		.ios-input {
			background: rgba(255, 255, 255, 0.9);
			backdrop-filter: blur(20px);
			border: 1px solid rgba(255, 255, 255, 0.3);
			transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
			font-size: 16px;
			padding: 16px 20px;
		}

		.ios-input:focus {
			background: rgba(255, 255, 255, 0.95);
			border-color: var(--ios-blue);
			box-shadow: 0 0 0 4px rgba(0, 122, 255, 0.1);
			transform: translateY(-2px);
		}

		.ios-input::placeholder {
			color: rgba(60, 60, 67, 0.6);
		}

		/* Matching stylish button */
		.ios-button {
			background: linear-gradient(135deg, rgba(255, 255, 255, 0.2) 0%, rgba(255, 255, 255, 0.1) 100%);
			backdrop-filter: blur(20px) saturate(180%);
			border: 2px solid rgba(255, 255, 255, 0.3);
			color: white;
			font-weight: 600;
			font-size: 17px;
			padding: 16px 32px;
			border-radius: 16px;
			transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
			position: relative;
			overflow: hidden;
			box-shadow: 
				0 8px 32px rgba(255, 255, 255, 0.1),
				0 0 0 1px rgba(255, 255, 255, 0.1),
				inset 0 1px 0 rgba(255, 255, 255, 0.2);
		}

		.ios-button:hover {
			transform: translateY(-3px) scale(1.02);
			background: linear-gradient(135deg, rgba(255, 255, 255, 0.3) 0%, rgba(255, 255, 255, 0.2) 100%);
			border-color: rgba(255, 255, 255, 0.5);
			box-shadow: 
				0 16px 48px rgba(255, 255, 255, 0.2),
				0 0 0 1px rgba(255, 255, 255, 0.2),
				inset 0 1px 0 rgba(255, 255, 255, 0.3);
		}

		.ios-button:active {
			transform: translateY(-1px) scale(0.98);
		}

		.ios-button::before {
			content: '';
			position: absolute;
			top: 0;
			left: -100%;
			width: 100%;
			height: 100%;
			background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
			transition: left 0.6s ease;
		}

		.ios-button:hover::before {
			left: 100%;
		}

		/* Loading spinner */
		.loading-spinner {
			display: inline-block;
			width: 16px;
			height: 16px;
			border: 2px solid rgba(255, 255, 255, 0.3);
			border-radius: 50%;
			border-top-color: white;
			animation: spin 1s ease-in-out infinite;
			margin-right: 8px;
		}

		@keyframes spin {
			to { transform: rotate(360deg); }
		}

		/* Floating labels */
		.floating-label {
			position: relative;
		}

		.floating-label input:focus + label,
		.floating-label input:not(:placeholder-shown) + label {
			transform: translateY(-32px) scale(0.85);
			color: var(--ios-blue);
			font-weight: 500;
		}

		.floating-label label {
			position: absolute;
			left: 20px;
			top: 16px;
			transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
			pointer-events: none;
			color: rgba(60, 60, 67, 0.6);
			font-size: 16px;
			font-weight: 400;
		}

		/* Animations */
		@keyframes fadeInUp {
			from {
				opacity: 0;
				transform: translateY(30px);
			}
			to {
				opacity: 1;
				transform: translateY(0);
			}
		}

		@keyframes slideInRight {
			from {
				opacity: 0;
				transform: translateX(30px);
			}
			to {
				opacity: 1;
				transform: translateX(0);
			}
		}

		.animate-fade-in-up {
			animation: fadeInUp 0.8s ease-out;
		}

		.animate-slide-in-right {
			animation: slideInRight 0.8s ease-out;
		}

		.animate-delay-100 { animation-delay: 0.1s; animation-fill-mode: both; }
		.animate-delay-200 { animation-delay: 0.2s; animation-fill-mode: both; }
		.animate-delay-300 { animation-delay: 0.3s; animation-fill-mode: both; }
		.animate-delay-400 { animation-delay: 0.4s; animation-fill-mode: both; }

		/* Logo animation */
		.logo-container {
			position: relative;
		}

		.logo-container::before {
			content: '';
			position: absolute;
			top: -10px;
			left: -10px;
			right: -10px;
			bottom: -10px;
			background: linear-gradient(45deg, var(--ios-blue), var(--ios-purple), var(--ios-blue));
			border-radius: 50%;
			opacity: 0.1;
			animation: pulse 2s ease-in-out infinite;
		}

		@keyframes pulse {
			0%, 100% { transform: scale(1); opacity: 0.1; }
			50% { transform: scale(1.1); opacity: 0.2; }
		}

		/* Error message styling */
		.error-message {
			background: rgba(255, 59, 48, 0.1);
			backdrop-filter: blur(20px);
			border: 1px solid rgba(255, 59, 48, 0.3);
			color: var(--ios-red);
			border-radius: 12px;
			padding: 16px 20px;
			font-size: 14px;
			font-weight: 500;
		}

		/* Password toggle button */
		.password-toggle {
			position: absolute;
			right: 16px;
			top: 50%;
			transform: translateY(-50%);
			color: rgba(60, 60, 67, 0.6);
			transition: all 0.2s ease;
			cursor: pointer;
			padding: 4px;
			border-radius: 6px;
		}

		.password-toggle:hover {
			color: var(--ios-blue);
			background: rgba(0, 122, 255, 0.1);
		}

		/* Loading state */
		.loading {
			position: relative;
			pointer-events: none;
		}

		.loading::after {
			content: '';
			position: absolute;
			top: 50%;
			left: 50%;
			width: 20px;
			height: 20px;
			margin: -10px 0 0 -10px;
			border: 2px solid rgba(255, 255, 255, 0.3);
			border-top: 2px solid white;
			border-radius: 50%;
			animation: spin 1s linear infinite;
		}

		@keyframes spin {
			0% { transform: rotate(0deg); }
			100% { transform: rotate(360deg); }
		}
	</style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
	<!-- Main Container -->
	<div class="w-full max-w-md">
		<!-- Logo and Header -->
		<div class="text-center mb-8 animate-fade-in-up">
			<div class="logo-container inline-block mb-6">
				<div class="w-20 h-20 bg-gradient-to-br from-white/20 to-white/10 rounded-3xl flex items-center justify-center backdrop-blur-sm border border-white/20 shadow-2xl">
					<i class="fas fa-graduation-cap text-3xl text-white"></i>
				</div>
			</div>
			<h1 class="text-3xl font-bold text-white mb-2 tracking-tight">
				Welcome Back
			</h1>
			<p class="text-white/80 text-lg font-medium">
				Sign in to your examination portal
			</p>
		</div>

		<!-- Login Card -->
		<div class="glass-card rounded-3xl p-8 animate-fade-in-up animate-delay-200">
			<?php if (isset($error)): ?>
				<div class="error-message mb-6 animate-slide-in-right animate-delay-300">
					<div class="flex items-center">
						<i class="fas fa-exclamation-circle mr-3 text-lg"></i>
						<span><?= htmlspecialchars($error) ?></span>
					</div>
				</div>
			<?php endif; ?>

			<form action="<?= dirname($_SERVER['SCRIPT_NAME']) ?>/api/auth/login" method="POST" id="loginForm" class="space-y-6">
				<!-- School ID Field -->
				<div class="floating-label animate-fade-in-up animate-delay-300">
					<input 
						type="text" 
						id="school_id" 
						name="school_id" 
						required 
						class="ios-input w-full rounded-2xl border-0 outline-none"
						placeholder=" "
						autocomplete="username"
					>
					<label for="school_id">School ID</label>
				</div>

				<!-- Password Field -->
				<div class="floating-label animate-fade-in-up animate-delay-400">
					<div class="relative">
						<input 
							type="password" 
							id="password" 
							name="password" 
							required 
							class="ios-input w-full rounded-2xl border-0 outline-none pr-12"
							placeholder=" "
							autocomplete="current-password"
						>
						<label for="password">Password</label>
						<button 
							type="button" 
							class="password-toggle"
							onclick="togglePasswordVisibility()"
							aria-label="Toggle password visibility"
						>
							<i id="passwordEye" class="fas fa-eye"></i>
						</button>
					</div>
				</div>

				<!-- Sign In Button -->
				<button 
					type="submit" 
					class="ios-button w-full animate-fade-in-up animate-delay-500"
					id="submitButton"
				>
					<span id="buttonText">
						<i class="fas fa-sign-in-alt mr-2"></i>
						Log In
					</span>
				</button>
			</form>

		</div>

		<!-- Footer -->
		<div class="text-center mt-8 animate-fade-in-up animate-delay-700">
			<p class="text-white/50 text-sm">
				© 2024 Examination System. All rights reserved.
			</p>
		</div>
	</div>

	<script>
		// Enhanced password visibility toggle
		function togglePasswordVisibility() {
			const input = document.getElementById('password');
			const eye = document.getElementById('passwordEye');
			const toggle = eye.parentElement;
			
			if (input.type === 'password') {
				input.type = 'text';
				eye.classList.remove('fa-eye');
				eye.classList.add('fa-eye-slash');
				toggle.style.transform = 'translateY(-50%) scale(1.1)';
			} else {
				input.type = 'password';
				eye.classList.remove('fa-eye-slash');
				eye.classList.add('fa-eye');
				toggle.style.transform = 'translateY(-50%) scale(1)';
			}
			
			// Reset transform after animation
			setTimeout(() => {
				toggle.style.transform = 'translateY(-50%)';
			}, 150);
		}

		// Enhanced form submission with loading state
		document.getElementById('loginForm').addEventListener('submit', function(e) {
			const submitButton = document.getElementById('submitButton');
			const buttonText = document.getElementById('buttonText');
			
			// Add loading state
			submitButton.disabled = true;
			submitButton.style.pointerEvents = 'none';
			
			// Replace button content with loading spinner
			buttonText.innerHTML = '<span class="loading-spinner"></span>Signing in...';
			
			// Optional: Add a slight delay to show the loading animation
			setTimeout(() => {
				// The form will submit naturally after this timeout
				// This is just for UX to show the loading state briefly
			}, 300);
		});

		// Input focus animations
		document.addEventListener('DOMContentLoaded', function() {
			const inputs = document.querySelectorAll('.ios-input');
			
			inputs.forEach(input => {
				// Add focus ring animation
				input.addEventListener('focus', function() {
					this.parentElement.style.transform = 'translateY(-2px)';
				});
				
				input.addEventListener('blur', function() {
					this.parentElement.style.transform = 'translateY(0)';
				});
				
				// Add typing animation
				input.addEventListener('input', function() {
					if (this.value.length > 0) {
						this.style.background = 'rgba(255, 255, 255, 0.95)';
					} else {
						this.style.background = 'rgba(255, 255, 255, 0.9)';
					}
				});
			});

			// Add subtle parallax effect to background
			document.addEventListener('mousemove', function(e) {
				const moveX = (e.clientX * -1 / 50);
				const moveY = (e.clientY * -1 / 50);
				document.body.style.backgroundPosition = `${moveX}px ${moveY}px`;
			});

			// Add keyboard shortcuts
			document.addEventListener('keydown', function(e) {
				// Enter key on school_id field focuses password field
				if (e.key === 'Enter' && document.activeElement.id === 'school_id') {
					e.preventDefault();
					document.getElementById('password').focus();
				}
			});

			// Add smooth scroll to error if present
			const errorMessage = document.querySelector('.error-message');
			if (errorMessage) {
				setTimeout(() => {
					errorMessage.scrollIntoView({ 
						behavior: 'smooth', 
						block: 'center' 
					});
				}, 800);
			}
		});

		// Add ripple effect to button
		document.getElementById('submitButton').addEventListener('click', function(e) {
			if (this.disabled) return;
			
			const ripple = document.createElement('span');
			const rect = this.getBoundingClientRect();
			const size = Math.max(rect.width, rect.height);
			const x = e.clientX - rect.left - size / 2;
			const y = e.clientY - rect.top - size / 2;
			
			ripple.style.cssText = `
				position: absolute;
				width: ${size}px;
				height: ${size}px;
				left: ${x}px;
				top: ${y}px;
				background: rgba(255, 255, 255, 0.3);
				border-radius: 50%;
				transform: scale(0);
				animation: ripple 0.6s ease-out;
				pointer-events: none;
			`;
			
			this.appendChild(ripple);
			
			setTimeout(() => {
				ripple.remove();
			}, 600);
		});

		// Add ripple animation keyframes
		const style = document.createElement('style');
		style.textContent = `
			@keyframes ripple {
				to {
					transform: scale(2);
					opacity: 0;
				}
			}
		`;
		document.head.appendChild(style);
	</script>
</body>
</html>