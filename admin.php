<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
$append_file = '../passlist.txt';
$edit_file = '../edit.txt';
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
$valid_credentials = [
    'admin' => 'targeet' // DEV - DARK LMNx9 (t.me/x_LMNx9)
];
if (!isset($_SESSION['authenticated'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];
        
        if (isset($valid_credentials[$username])) {
            if ($password === $valid_credentials[$username]) {
                $_SESSION['authenticated'] = true;
                $_SESSION['username'] = $username;
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            }
        }
        $error_message = "WRONG - USER | PASS";
    }
    
    showLoginForm($error_message ?? '');
    exit();
}
function deletePassword($passwordToDelete, $append_file) {
    if (!file_exists($append_file)) return false;
    $passwords = file($append_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $updatedPasswords = [];
    foreach ($passwords as $line) {
        $data = json_decode($line, true);
        if ($data && isset($data['password'])) {
            if (trim($data['password']) !== trim($passwordToDelete)) {
                $updatedPasswords[] = $line;
            }
        } elseif (trim($line) !== trim($passwordToDelete)) {
            $updatedPasswords[] = $line;
        }
    }
    return file_put_contents($append_file, implode(PHP_EOL, $updatedPasswords)) !== false;
}
function deleteAllPasswords($append_file) {
    return file_exists($append_file) ? file_put_contents($append_file, '') !== false : false;
}
function addPassword($password, $expire_hours, $append_file) {
    $expire_time = time() + ($expire_hours * 3600);
    $password_data = [
        'password' => $password,
        'expire_time' => $expire_time,
        'added_at' => time(),
        'expire_hours' => $expire_hours
    ];
    return file_put_contents($append_file, json_encode($password_data).PHP_EOL, FILE_APPEND) !== false;
}
function getActivePasswords($append_file) {
    $active_passwords = [];
    if (!file_exists($append_file)) return $active_passwords;
    $password_lines = file($append_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $has_changes = false;
    foreach ($password_lines as $line) {
        $data = json_decode($line, true);
        if ($data && isset($data['expire_time'])) {
            if ($data['expire_time'] > time()) {
                $active_passwords[] = $data;
            } else {
                $has_changes = true;
            }
        } else {
            $active_passwords[] = [
                'password' => $line,
                'expire_time' => time() + 86400,
                'added_at' => time(),
                'expire_hours' => 24
            ];
            $has_changes = true;
        }
    }
    if ($has_changes) {
        file_put_contents($append_file, '');
        foreach ($active_passwords as $data) {
            file_put_contents($append_file, json_encode($data).PHP_EOL, FILE_APPEND);
        }
    }
    return $active_passwords;
}
$success_message = '';
$error_message = '';
if (isset($_GET['delete_password'])) {
    if (deletePassword($_GET['delete_password'], $append_file)) {
        $success_message = "PASSWORD DELETED SUCCESSFULLY..!";
    } else {
        $error_message = "PASSWORD DELETE FAILED..!";
    }
    header("Location: " . strtok($_SERVER['REQUEST_URI'], '?'));
    exit();
}
if (isset($_GET['delete_all_passwords'])) {
    if (deleteAllPasswords($append_file)) {
        $success_message = "ALL PASSWORDS DELETED SUCCESSFULLY..!";
    } else {
        $error_message = "DELETE ALL PASSWORDS FAILED..!";
    }
    header("Location: " . strtok($_SERVER['REQUEST_URI'], '?'));
    exit();
}
if (!empty($_POST['append_text'])) {
    $expire_hours = $_POST['expire_hours'] ?? 24;
    if (addPassword($_POST['append_text'], $expire_hours, $append_file)) {
        $success_message = "PASSWORD ADDED SUCCESSFULLY (Expires in $expire_hours hours)..!";
    } else {
        $error_message = "PASSWORD ADD FAILED..!";
    }
}
$active_passwords = getActivePasswords($append_file);
$current_content = file_exists($edit_file) ? file_get_contents($edit_file) : '';
showAdminPanel($active_passwords, $success_message, $error_message);
function showLoginForm($error_message) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Login Targeet</title>
        <style>
            :root {
                --neon-green: #39ff14;
                --neon-blue: #00f2ff;
                --neon-red: #ff073a;
                --dark-bg: #0a0a0a;
                --darker-bg: #050505;
                --glow-green: 0 0 10px rgba(57, 255, 20, 0.7);
                --glow-blue: 0 0 10px rgba(0, 242, 255, 0.7);
                --glow-red: 0 0 10px rgba(255, 7, 58, 0.7);
                --primary: #6e45e2;
                --accent: #ff7e5f;
            }

            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            }

            body {
                background: linear-gradient(135deg, var(--dark-bg), #16213e);
                color: var(--neon-green);
                min-height: 100vh;
                display: flex;
                justify-content: center;
                align-items: center;
                position: relative;
                overflow: hidden;
                padding: 20px;
            }

            .matrix {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                z-index: -1;
                opacity: 0.15;
            }

            .login-container {
                width: 100%;
                max-width: 450px;
                background: rgba(0, 0, 0, 0.7);
                border: 1px solid var(--neon-blue);
                border-radius: 10px;
                padding: 30px;
                box-shadow: var(--glow-blue);
                backdrop-filter: blur(10px);
                animation: fadeIn 0.8s ease-out;
                text-align: center;
            }

            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(20px); }
                to { opacity: 1; transform: translateY(0); }
            }

            .logo {
                width: 80px;
                height: 80px;
                border-radius: 50%;
                object-fit: cover;
                margin: 0 auto 15px;
                border: 2px solid var(--primary);
                box-shadow: 0 0 15px rgba(110, 69, 226, 0.5);
                transition: transform 0.3s ease;
            }

            h1 {
                color: var(--neon-blue);
                text-shadow: var(--glow-blue);
                margin-bottom: 20px;
                font-size: 24px;
                letter-spacing: 1px;
            }

            .input-group {
                margin-bottom: 20px;
                position: relative;
            }

            .input-group label {
                display: block;
                text-align: left;
                margin-bottom: 8px;
                color: var(--neon-blue);
                font-weight: bold;
                font-size: 14px;
            }

            .input-group input {
                width: 100%;
                padding: 12px 15px;
                background: rgba(0, 0, 0, 0.8);
                color: var(--neon-green);
                border: 1px solid var(--neon-green);
                border-radius: 25px;
                font-size: 14px;
                outline: none;
                transition: all 0.3s ease;
                box-shadow: var(--glow-green);
            }

            .input-group input:focus {
                border-color: var(--neon-blue);
                box-shadow: var(--glow-blue);
            }

            .login-btn {
                width: 100%;
                padding: 12px;
                background: linear-gradient(to right, var(--primary), var(--accent));
                color: white;
                font-weight: bold;
                border: none;
                border-radius: 25px;
                cursor: pointer;
                transition: all 0.3s ease;
                font-size: 14px;
                letter-spacing: 1px;
                box-shadow: 0 4px 10px rgba(110, 69, 226, 0.4);
                text-transform: uppercase;
                margin-top: 10px;
            }

            .login-btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 15px rgba(110, 69, 226, 0.6);
            }

            .error-message {
                color: var(--neon-red);
                text-shadow: var(--glow-red);
                margin-top: 15px;
                font-size: 13px;
                animation: shake 0.5s;
            }

            @keyframes shake {
                0%, 100% { transform: translateX(0); }
                20%, 60% { transform: translateX(-5px); }
                40%, 80% { transform: translateX(5px); }
            }

            .security-tip {
                margin-top: 20px;
                font-size: 11px;
                color: rgba(255, 255, 255, 0.5);
            }

            .pulse {
                animation: pulse 2s infinite;
            }

            @keyframes pulse {
                0% { box-shadow: 0 0 0 0 rgba(0, 242, 255, 0.7); }
                70% { box-shadow: 0 0 0 8px rgba(0, 242, 255, 0); }
                100% { box-shadow: 0 0 0 0 rgba(0, 242, 255, 0); }
            }

            @media (max-width: 480px) {
                .login-container {
                    padding: 20px;
                }
                
                h1 {
                    font-size: 20px;
                }
                
                .input-group input {
                    padding: 10px 15px;
                    font-size: 13px;
                }
                
                .login-btn {
                    padding: 10px;
                    font-size: 13px;
                }
            }
        </style>
    </head>
    <body>
        <canvas class="matrix"></canvas>
        <div class="login-container pulse">
            <img src="../lmnx9.png" alt="Admin Logo" class="logo">
            <h1>TARGEET ADMIN LOGIN</h1>
            <form method="POST">
                <div class="input-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Enter Username" required>
                </div>
                <div class="input-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter Password" required>
                </div>
                <button type="submit" class="login-btn">LOGIN</button>
                <?php if (!empty($error_message)): ?>
                    <div class="error-message"><?= htmlspecialchars($error_message) ?></div>
                <?php endif; ?>
            </form>
        </div>
        <script>
            const canvas = document.querySelector('.matrix');
            const ctx = canvas.getContext('2d');
            
            function initMatrix() {
                canvas.width = window.innerWidth;
                canvas.height = window.innerHeight;
                
                const letters = '01';
                const fontSize = Math.max(14, Math.min(18, window.innerWidth / 30));
                const columns = canvas.width / fontSize;
                const drops = Array(Math.floor(columns)).fill(1);
                
                ctx.font = `bold ${fontSize}px monospace`;
                ctx.textAlign = 'center';
                
                function drawMatrix() {
                    ctx.fillStyle = 'rgba(0, 0, 0, 0.05)';
                    ctx.fillRect(0, 0, canvas.width, canvas.height);
                    
                    drops.forEach((y, x) => {
                        const text = letters[Math.floor(Math.random() * letters.length)];
                        ctx.fillStyle = '#39ff14';
                        ctx.fillText(text, x * fontSize, y * fontSize);
                        
                        if (y * fontSize > canvas.height || Math.random() > 0.95) {
                            drops[x] = 0;
                        }
                        drops[x]++;
                    });
                }
                
                setInterval(drawMatrix, 50);
                
                window.addEventListener('resize', () => {
                    canvas.width = window.innerWidth;
                    canvas.height = window.innerHeight;
                    drops.length = Math.floor(canvas.width / fontSize);
                    drops.fill(1);
                });
            }
            
            initMatrix();
        </script>
    </body>
    </html>
    <?php
}

function showAdminPanel($active_passwords, $success_message, $error_message) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>TARGEET ADMIN PANEL</title>
        <style>
            :root {
                --neon-green: #39ff14;
                --neon-blue: #00f2ff;
                --neon-red: #ff073a;
                --dark-bg: #0a0a0a;
                --darker-bg: #050505;
                --glow-green: 0 0 8px rgba(57, 255, 20, 0.7);
                --glow-blue: 0 0 8px rgba(0, 242, 255, 0.7);
                --glow-red: 0 0 8px rgba(255, 7, 58, 0.7);
                --primary: #6e45e2;
                --accent: #ff7e5f;
            }

            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            }

            body {
                background: linear-gradient(135deg, var(--dark-bg), #16213e);
                color: var(--neon-green);
                min-height: 100vh;
                padding: 15px;
                position: relative;
                overflow-x: hidden;
            }

            .matrix {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                z-index: -1;
                opacity: 0.15;
            }

            .editor-container {
                width: 100%;
                max-width: 800px;
                margin: 0 auto;
                background: rgba(0, 0, 0, 0.7);
                border: 1px solid var(--neon-green);
                border-radius: 8px;
                padding: 20px;
                box-shadow: var(--glow-green);
                backdrop-filter: blur(5px);
                animation: fadeIn 0.5s ease-out;
            }

            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(20px); }
                to { opacity: 1; transform: translateY(0); }
            }

            .admin-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 20px;
                flex-wrap: wrap;
                gap: 10px;
            }

            h2 {
                color: var(--neon-blue);
                text-shadow: var(--glow-blue);
                font-size: 22px;
                letter-spacing: 1px;
            }

            .logout-btn {
                padding: 8px 15px;
                background: transparent;
                border: 1px solid var(--neon-red);
                color: var(--neon-red);
                border-radius: 20px;
                cursor: pointer;
                transition: all 0.3s ease;
                font-size: 13px;
                text-decoration: none;
                white-space: nowrap;
            }

            .logout-btn:hover {
                background: rgba(255, 7, 58, 0.1);
            }

            label {
                display: block;
                margin-bottom: 8px;
                color: var(--neon-blue);
                font-weight: bold;
                font-size: 14px;
            }

            input[type="text"], select {
                width: 100%;
                background: rgba(0, 0, 0, 0.8);
                color: var(--neon-green);
                border: 1px solid var(--neon-green);
                border-radius: 5px;
                margin-bottom: 15px;
                padding: 12px 15px;
                font-size: 14px;
                outline: none;
                transition: all 0.3s ease;
                box-shadow: var(--glow-green);
            }

            input[type="text"]:focus, select:focus {
                border-color: var(--neon-blue);
                box-shadow: var(--glow-blue);
            }

            button {
                padding: 10px 20px;
                background: linear-gradient(to right, var(--primary), var(--accent));
                color: white;
                font-weight: bold;
                border: none;
                border-radius: 20px;
                cursor: pointer;
                transition: all 0.3s ease;
                font-size: 14px;
                box-shadow: 0 3px 8px rgba(110, 69, 226, 0.4);
                text-transform: uppercase;
                margin-top: 8px;
                width: 100%;
            }

            button:hover {
                transform: translateY(-2px);
                box-shadow: 0 5px 12px rgba(110, 69, 226, 0.6);
            }

            hr {
                margin: 20px 0;
                border: none;
                height: 1px;
                background: linear-gradient(to right, transparent, var(--neon-green), transparent);
                box-shadow: var(--glow-green);
            }

            .success { 
                color: var(--neon-green);
                background: rgba(57, 255, 20, 0.1);
                padding: 12px;
                border-radius: 5px;
                margin-bottom: 15px;
                border-left: 3px solid var(--neon-green);
                text-shadow: var(--glow-green);
                font-size: 13px;
            }

            .error {
                color: var(--neon-red);
                background: rgba(255, 7, 58, 0.1);
                padding: 12px;
                border-radius: 5px;
                margin-bottom: 15px;
                border-left: 3px solid var(--neon-red);
                text-shadow: var(--glow-red);
                font-size: 13px;
            }

            .pulse {
                animation: pulse 2s infinite;
            }

            @keyframes pulse {
                0% { box-shadow: 0 0 0 0 rgba(57, 255, 20, 0.7); }
                70% { box-shadow: 0 0 0 8px rgba(57, 255, 20, 0); }
                100% { box-shadow: 0 0 0 0 rgba(57, 255, 20, 0); }
            }

            .password-viewer {
                background: rgba(0, 0, 0, 0.8);
                border: 1px solid var(--neon-blue);
                border-radius: 5px;
                padding: 12px;
                margin-bottom: 15px;
                max-height: 300px;
                overflow-y: auto;
                box-shadow: var(--glow-blue);
            }

            .password-viewer h3 {
                color: var(--neon-blue);
                margin-bottom: 12px;
                text-align: center;
                text-shadow: var(--glow-blue);
                font-size: 16px;
            }

            .password-list {
                display: grid;
                grid-template-columns: 1fr;
                gap: 8px;
            }

            .password-item {
                background: rgba(0, 0, 0, 0.5);
                border: 1px solid var(--neon-green);
                padding: 8px;
                border-radius: 3px;
                color: var(--neon-green);
                font-size: 13px;
                word-break: break-all;
                transition: all 0.3s ease;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .password-item:hover {
                transform: translateY(-2px);
                box-shadow: var(--glow-green);
            }

            .delete-btn {
                background: var(--neon-red);
                color: white;
                border: none;
                border-radius: 3px;
                padding: 3px 8px;
                cursor: pointer;
                font-size: 11px;
                transition: all 0.3s ease;
                white-space: nowrap;
                margin-left: 8px;
            }

            .delete-btn:hover {
                background: #d10000;
            }

            .delete-all-btn {
                background: var(--neon-red);
                color: white;
                border: none;
                border-radius: 20px;
                padding: 10px;
                cursor: pointer;
                font-size: 14px;
                transition: all 0.3s ease;
                margin-top: 8px;
            }

            .delete-all-btn:hover {
                background: #d10000;
                transform: translateY(-2px);
            }

            .expiry-info {
                font-size: 11px;
                color: var(--neon-blue);
                opacity: 0.8;
                display: block;
                margin-top: 4px;
            }

            .expiry-selector {
                display: flex;
                flex-direction: column;
                margin-bottom: 15px;
            }

            .expiry-selector label {
                margin-bottom: 5px;
            }

            @media (min-width: 768px) {
                body {
                    padding: 30px;
                }
                
                .editor-container {
                    padding: 25px;
                }
                
                h2 {
                    font-size: 24px;
                }
                
                .password-list {
                    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                }
                
                .expiry-selector {
                    flex-direction: row;
                    align-items: center;
                }
                
                .expiry-selector label {
                    margin-bottom: 0;
                    margin-right: 10px;
                    min-width: 120px;
                }
                
                button {
                    width: auto;
                    padding: 12px 25px;
                }
                
                .delete-all-btn {
                    width: auto;
                    display: inline-block;
                }
            }
        </style>
    </head>
    <body>
        <canvas class="matrix"></canvas>
        <div class="editor-container">
            <div class="admin-header">
                <h2>TARGEET ADMIN PANEL</h2>
                <a href="?logout=1" class="logout-btn">LOGOUT</a>
            </div>
            <?php if (!empty($success_message)): ?>
                <div class="success pulse"><?= htmlspecialchars($success_message) ?></div>
            <?php endif; ?>
            <?php if (!empty($error_message)): ?>
                <div class="error pulse"><?= htmlspecialchars($error_message) ?></div>
            <?php endif; ?>
            <div class="password-viewer">
                <h3>ACTIVE PASSWORDS LIST</h3>
                <div class="password-list" id="passwordList">
                    <?php
                    if (!empty($active_passwords)) {
                        foreach ($active_passwords as $data) {
                            $time_left = $data['expire_time'] - time();
                            $hours_left = floor($time_left / 3600);
                            $minutes_left = floor(($time_left % 3600) / 60);
                            echo '<div class="password-item">';
                            echo '<div>';
                            echo '<div style="font-weight: bold;">' . htmlspecialchars($data['password']) . '</div>';
                            echo '<span class="expiry-info">Expires in: ' . $hours_left . 'h ' . $minutes_left . 'm</span>';
                            echo '</div>';
                            echo '<button class="delete-btn" onclick="deletePassword(\'' . htmlspecialchars($data['password']) . '\')">Delete</button>';
                            echo '</div>';
                        }
                    } else {
                        echo '<div style="color: var(--neon-red); text-align: center;">NO ACTIVE PASSWORDS FOUND</div>';
                    }
                    ?>
                </div>
            </div>
            <form method="POST">
                <label>ADD USER ACCESS PASSWORD</label>
                <input type="text" name="append_text" placeholder="New User Access Password..." required>
                <div class="expiry-selector">
                    <label>EXPIRATION TIME:</label>
                    <select name="expire_hours" required>
                        <option value="1">1 Hour</option>
                        <option value="2">2 Hours</option>
                        <option value="6">6 Hours</option>
                        <option value="12">12 Hours</option>
                        <option value="24" selected>24 Hours</option>
                        <option value="48">48 Hours</option>
                        <option value="72">72 Hours</option>
                        <option value="168">7 Days</option>
                    </select>
                </div>
                <button type="submit">ADD PASSWORD</button>
                <button type="button" class="delete-all-btn" onclick="deleteAllPasswords()">DELETE ALL PASSWORDS</button>
            </form>
        </div>
        <script>
            const canvas = document.querySelector('.matrix');
            const ctx = canvas.getContext('2d');
            
            function initMatrix() {
                canvas.width = window.innerWidth;
                canvas.height = window.innerHeight;
                
                const letters = '01';
                const fontSize = Math.max(14, Math.min(18, window.innerWidth / 30));
                const columns = canvas.width / fontSize;
                const drops = Array(Math.floor(columns)).fill(1);
                
                ctx.font = `bold ${fontSize}px monospace`;
                ctx.textAlign = 'center';
                
                function drawMatrix() {
                    ctx.fillStyle = 'rgba(0, 0, 0, 0.05)';
                    ctx.fillRect(0, 0, canvas.width, canvas.height);
                    
                    drops.forEach((y, x) => {
                        const text = letters[Math.floor(Math.random() * letters.length)];
                        ctx.fillStyle = '#39ff14';
                        ctx.fillText(text, x * fontSize, y * fontSize);
                        
                        if (y * fontSize > canvas.height || Math.random() > 0.95) {
                            drops[x] = 0;
                        }
                        drops[x]++;
                    });
                }
                
                setInterval(drawMatrix, 50);
                
                window.addEventListener('resize', () => {
                    canvas.width = window.innerWidth;
                    canvas.height = window.innerHeight;
                    drops.length = Math.floor(canvas.width / fontSize);
                    drops.fill(1);
                });
            }
            
            initMatrix();
            
            function deletePassword(password) {
                if (confirm('Are you sure you want to delete this password?')) {
                    window.location.href = '?delete_password=' + encodeURIComponent(password);
                }
            }
            
            function deleteAllPasswords() {
                if (confirm('Are you sure you want to delete ALL passwords?')) {
                    window.location.href = '?delete_all_passwords=1';
                }
            }
            
            function updateTimers() {
                document.querySelectorAll('.password-item').forEach(item => {
                    const expiryElement = item.querySelector('.expiry-info');
                    if (expiryElement) {
                        const text = expiryElement.textContent;
                        const matches = text.match(/Expires in: (\d+)h (\d+)m/);
                        if (matches) {
                            let hours = parseInt(matches[1]);
                            let minutes = parseInt(matches[2]);
                            
                            minutes--;
                            if (minutes < 0) {
                                minutes = 59;
                                hours--;
                            }
                            
                            if (hours >= 0) {
                                expiryElement.textContent = `Expires in: ${hours}h ${minutes}m`;
                            } else {
                                window.location.reload();
                            }
                        }
                    }
                });
            }
            
            setInterval(updateTimers, 60000);
        </script>
    </body>
    </html>
    <?php
}
?>