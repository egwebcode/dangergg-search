<?php
/**
 * NEXUS AUTHENTICATION CORE v1.0
 * Security Level: High (Headers)
 * Storage: Hardcoded Array (As requested)
 */

// 1. SECURITY HEADERS (The "Ultra Secure" Layer)
// Prevents the site from being loaded in an iframe (Clickjacking protection)
header("X-Frame-Options: DENY");
// Enforces HTTPS (if SSL is available)
header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
// Prevents MIME-type sniffing
header("X-Content-Type-Options: nosniff");
// XSS Protection
header("X-XSS-Protection: 1; mode=block");
// Disables caching for login page to prevent back-button re-entry
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

session_start();

// 2. CONFIGURATION & LOGIC
$error_msg = '';

// DEFINED USERS (4 Users as requested - Pure PHP, not exposed in HTML)
// Format: 'username' => 'password'
$users = [
    'admin'     => 'supra123',
    'gerente'   => 'nexus2025',
    'suporte'   => 'fixnow',
    'operador'  => 'work88'
];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';

    // Verify credentials
    if (array_key_exists($user, $users) && $users[$user] === $pass) {
        // Success
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $user;
        $_SESSION['fingerprint_token'] = bin2hex(random_bytes(16)); // Session Token
        
        header("Location: ./consultar.php");
        exit;
    } else {
        $error_msg = "Acesso Negado. Credenciais inválidas.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEXUS ACCESS CONTROL</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">

    <style>
        /* CORE AESTHETICS */
        body {
            font-family: 'Rajdhani', sans-serif;
            background-color: #050505;
            background-image: 
                radial-gradient(at 0% 0%, hsla(253,16%,7%,1) 0, transparent 50%), 
                radial-gradient(at 50% 0%, hsla(225,39%,30%,1) 0, transparent 50%), 
                radial-gradient(at 100% 0%, hsla(339,49%,30%,1) 0, transparent 50%);
            height: 100vh;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* GLASS CARD */
        .glass-panel {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        /* INPUT STYLING */
        .input-group {
            position: relative;
            margin-bottom: 1.5rem;
        }
        .nexus-input {
            width: 100%;
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid #333;
            color: #00ffcc;
            padding: 12px 15px;
            border-radius: 8px;
            outline: none;
            transition: 0.3s;
            letter-spacing: 1px;
        }
        .nexus-input:focus {
            border-color: #00ffcc;
            box-shadow: 0 0 15px rgba(0, 255, 204, 0.2);
        }
        .input-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #555;
            transition: 0.3s;
        }
        .nexus-input:focus ~ .input-icon {
            color: #00ffcc;
        }

        /* HOLD BUTTON MECHANISM */
        .fingerprint-container {
            position: relative;
            width: 80px;
            height: 80px;
            margin: 0 auto;
            cursor: pointer;
            user-select: none;
            -webkit-tap-highlight-color: transparent;
        }

        .fingerprint-icon {
            font-size: 3rem;
            color: #444;
            transition: color 0.3s ease;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 10;
        }

        .fingerprint-container:active .fingerprint-icon,
        .fingerprint-container.scanning .fingerprint-icon {
            color: #00ffcc;
        }

        /* SVG CIRCLE ANIMATION */
        .progress-ring {
            transform: rotate(-90deg);
            transform-origin: 50% 50%;
        }
        .progress-ring__circle {
            stroke-dasharray: 251.2;
            stroke-dashoffset: 251.2; /* Full circumference */
            transition: stroke-dashoffset 0s; /* Instant reset */
            stroke: #00ffcc;
            filter: drop-shadow(0 0 5px #00ffcc);
        }

        /* Animation class added via JS */
        .scanning .progress-ring__circle {
            transition: stroke-dashoffset 2s linear; /* 2 seconds hold time */
            stroke-dashoffset: 0;
        }

        .scan-text {
            text-align: center;
            color: #666;
            font-size: 0.8rem;
            margin-top: 10px;
            text-transform: uppercase;
            letter-spacing: 2px;
            transition: 0.3s;
        }
        .scanning + .scan-text {
            color: #00ffcc;
            text-shadow: 0 0 10px rgba(0,255,204,0.5);
        }

        /* ERROR MESSAGE */
        .error-banner {
            background: rgba(255, 0, 50, 0.1);
            border-left: 3px solid #ff0033;
            color: #ff0033;
            padding: 10px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
    </style>
</head>
<body>

    <div class="glass-panel w-full max-w-md p-8 rounded-2xl relative overflow-hidden">
        
        <!-- Decorative Header -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-white tracking-widest">NEXUS</h1>
            <p class="text-gray-500 text-sm tracking-[0.3em] uppercase mt-1">Secure Access Node</p>
        </div>

        <?php if($error_msg): ?>
        <div class="error-banner animate-pulse">
            <i class="fas fa-exclamation-triangle"></i>
            <span><?php echo $error_msg; ?></span>
        </div>
        <?php endif; ?>

        <form id="loginForm" action="" method="POST" autocomplete="off">
            
            <!-- User Input -->
            <div class="input-group">
                <input type="text" name="username" class="nexus-input" placeholder="IDENTIFICAÇÃO" required>
                <i class="fas fa-user input-icon"></i>
            </div>

            <!-- Password Input -->
            <div class="input-group">
                <input type="password" name="password" class="nexus-input" placeholder="SENHA DE ACESSO" required>
                <i class="fas fa-lock input-icon"></i>
            </div>

            <!-- Biometric Hold Trigger -->
            <div class="mt-10 flex flex-col items-center justify-center">
                
                <div class="fingerprint-container" id="scanBtn">
                    <!-- SVG Ring -->
                    <svg class="progress-ring" width="80" height="80">
                        <circle class="progress-ring__circle" stroke-width="4" fill="transparent" r="40" cx="40" cy="40"/>
                    </svg>
                    <!-- Icon -->
                    <i class="fas fa-fingerprint fingerprint-icon"></i>
                </div>
                
                <div class="scan-text" id="scanText">Segure para Autenticar</div>
            </div>

            <!-- Hidden Submit Button (Triggered by JS) -->
            <button type="submit" id="realSubmit" style="display:none;"></button>
        </form>

        <!-- Decorative footer elements -->
        <div class="absolute bottom-2 left-0 w-full text-center opacity-20 text-[10px] text-white">
            SECURE CONNECTION // TLS 1.3 // NO LOGS
        </div>
    </div>

    <script>
        /**
         * NEXUS INTERFACE LOGIC
         * Handles the "Hold to Submit" interaction
         */
        const scanBtn = document.getElementById('scanBtn');
        const scanText = document.getElementById('scanText');
        const loginForm = document.getElementById('loginForm');
        
        let holdTimer = null;
        let isComplete = false;
        const HOLD_DURATION = 2000; // 2 seconds

        // Events for Mouse and Touch
        const startEvents = ['mousedown', 'touchstart'];
        const endEvents = ['mouseup', 'mouseleave', 'touchend'];

        startEvents.forEach(evt => scanBtn.addEventListener(evt, startScan));
        endEvents.forEach(evt => scanBtn.addEventListener(evt, stopScan));

        function startScan(e) {
            if(e.type === 'touchstart') e.preventDefault(); // Prevent scrolling on mobile
            if(isComplete) return;

            // Check if inputs are filled
            const user = document.querySelector('input[name="username"]').value;
            const pass = document.querySelector('input[name="password"]').value;

            if(!user || !pass) {
                scanText.innerText = "Preencha os dados primeiro";
                scanText.style.color = "#ff0033";
                setTimeout(() => {
                    scanText.innerText = "Segure para Autenticar";
                    scanText.style.color = "#666";
                }, 2000);
                return;
            }

            // Start Visual Animation
            scanBtn.classList.add('scanning');
            scanText.innerText = "Verificando Biometria...";

            // Start Logic Timer
            holdTimer = setTimeout(() => {
                success();
            }, HOLD_DURATION);
        }

        function stopScan() {
            if(isComplete) return;

            // Reset Visuals
            scanBtn.classList.remove('scanning');
            scanText.innerText = "Segure para Autenticar";
            
            // Clear Timer
            if(holdTimer) {
                clearTimeout(holdTimer);
                holdTimer = null;
            }
        }

        function success() {
            isComplete = true;
            scanBtn.classList.remove('scanning');
            
            // Success Visuals
            const icon = scanBtn.querySelector('.fingerprint-icon');
            icon.style.color = "#00ffcc";
            icon.className = "fas fa-check fingerprint-icon";
            scanText.innerText = "ACESSO CONCEDIDO";
            scanText.style.color = "#00ffcc";

            // Submit Form
            setTimeout(() => {
                document.getElementById('realSubmit').click();
            }, 500);
        }
    </script>
</body>
</html>