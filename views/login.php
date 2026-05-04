<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Altutex - Connexion</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #1a2a4c;
            --accent-color: #092b44;
            --error-red: #d93025;
            --warning-orange: #f57c00;
        }

        body {
            background-color: #f8f9fa;
            font-family: 'Inter', 'Segoe UI', sans-serif;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }

        .login-container {
            background: white;
            width: 950px;
            height: 600px;
            display: flex;
            border-radius: 30px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
        }

        .login-image {
            width: 70%;
            background: url('assets/login-b.png') center/cover no-repeat;
            position: relative;
            display: flex;
            align-items: flex-end;
            padding: 40px;
        }

        .login-image::after {
            content: "ALTUTEX your key to digital world";
            color: white;
            font-size: 0.85rem;
            letter-spacing: 1px;
            opacity: 0.8;
        }

        .login-form-section {
            width: 45%;
            padding: 60px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .logo-box {
            margin-bottom: 40px;
        }

        .logo-box img {
            height: 45px;
        }

        h2 {
            font-weight: 500;
            color: #01192d;
            margin-bottom: 30px;
            font-size: 1.8rem;
        }

        .form-control {
            background-color: #f0f4f8;
            border: none;
            padding: 14px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 1rem;
        }

        .form-control:focus {
            background-color: #e8eef3;
            box-shadow: none;
            outline: 1px solid #ddd;
        }

        .btn-login {
            background-color: var(--accent-color);
            color: white;
            border: none;
            padding: 14px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .btn-login:hover {
            background-color: #333333;
            transform: translateY(-2px);
        }

        /* Styles des Alertes */
        .alert-custom {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .alert-error {
            background-color: #ffe5e5;
            color: var(--error-red);
            border: 1px solid #f8c2c2;
        }

        .alert-locked {
            background-color: #fff3e0;
            color: var(--warning-orange);
            border: 1px solid #ffe0b2;
            font-weight: 600;
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="login-image"></div>

    <div class="login-form-section">
        <div class="logo-box">
            <img src="assets/logo.png" alt="Altutex Logo">
        </div>
        
        <h2>Sign into your account</h2>
        
        <?php if(isset($_GET['error'])): ?>
            <?php if($_GET['error'] == 'locked'): ?>
                <div class="alert-custom alert-locked">
                    ⚠️ Trop de tentatives. Wait 2 minutes.
                </div>
            <?php else: ?>
                <div class="alert-custom alert-error">
                    Login ou mot de passe incorrect.
                </div>
            <?php endif; ?>
        <?php endif; ?>
        

        <form action="index.php?action=auth_process" method="POST">
            <div class="mb-3">
                <input type="text" name="login" class="form-control" placeholder="Login" required autofocus>
            </div>
            
            <div class="mb-3">
                <input type="password" name="password" class="form-control" placeholder="Password" required>
            </div>

            <button type="submit" class="btn-login w-100">Login</button>
        </form>
    </div>
</div>

</body>
</html>