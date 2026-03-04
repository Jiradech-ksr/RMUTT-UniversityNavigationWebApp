<?php
session_start();
// ถ้าล็อกอินอยู่แล้ว ให้เด้งไปหน้า Dashboard เลย
if (isset($_SESSION['admin_logged_in'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Campus Nav</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Prompt', sans-serif;
        }

        .login-card {
            background: #fff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        .logo-icon {
            font-size: 50px;
            color: #1A237E;
            margin-bottom: 15px;
        }
    </style>
</head>

<body>

    <div class="login-card border-top border-indigo border-5" style="border-top-color: #1A237E !important;">
        <i class="fas fa-map-marked-alt logo-icon"></i>
        <h4 class="mb-1 fw-bold" style="color: #1A237E;">RMUTT Navigator</h4>
        <p class="text-muted mb-4">Admin & Staff Control Panel</p>

        <div id="errorMsg" class="alert alert-danger d-none text-start small"></div>

        <script src="https://accounts.google.com/gsi/client" async defer></script>
        <div id="g_id_onload" data-client_id="945552107938-rn7nkgiefmlt6dv1nc104tqp16g0crbt.apps.googleusercontent.com"
            data-context="signin" data-ux_mode="popup" data-callback="handleCredentialResponse"
            data-auto_prompt="false">
        </div>

        <div class="d-flex justify-content-center">
            <div class="g_id_signin" data-type="standard" data-shape="rectangular" data-theme="outline"
                data-text="signin_with" data-size="large" data-logo_alignment="left">
            </div>
        </div>

        <p class="mt-4 mb-0 text-muted small"><i class="fas fa-shield-alt"></i> เฉพาะเจ้าหน้าที่ที่ได้รับสิทธิ์เท่านั้น
        </p>
    </div>

    <script>
        // ฟังก์ชันเมื่อ Google คืนค่า Token กลับมาให้เว็บ
        function handleCredentialResponse(response) {
            // ส่ง Token ไปตรวจสอบที่ ajax_login.php
            fetch('ajax_login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'credential=' + response.credential
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        // ถ้ายืนยันสำเร็จ ให้พาไปหน้า index.php
                        window.location.href = 'index.php';
                    } else {
                        // ถ้าไม่ได้เป็น Admin หรือโดนแบน ให้แสดง Error
                        const errorDiv = document.getElementById('errorMsg');
                        errorDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + data.message;
                        errorDiv.classList.remove('d-none');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }
    </script>

</body>

</html>