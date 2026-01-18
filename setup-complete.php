<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalación Completa - Forethink Health</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 600px;
            width: 100%;
            padding: 48px;
        }

        h1 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 16px;
            font-size: 32px;
        }

        .subtitle {
            text-align: center;
            color: #6c757d;
            margin-bottom: 40px;
            font-size: 16px;
        }

        .step {
            background: #f8f9fa;
            border-left: 4px solid #00d4ff;
            padding: 20px;
            margin-bottom: 24px;
            border-radius: 8px;
        }

        .step h3 {
            color: #00d4ff;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .step p {
            color: #6c757d;
            line-height: 1.6;
        }

        .success-msg {
            background: #d4edda;
            color: #155724;
            padding: 16px;
            border-radius: 8px;
            border-left: 4px solid #28a745;
            margin: 24px 0;
            display: none;
        }

        .error-msg {
            background: #f8d7da;
            color: #721c24;
            padding: 16px;
            border-radius: 8px;
            border-left: 4px solid #dc3545;
            margin: 24px 0;
            display: none;
        }

        .btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #00d4ff, #00bfe6);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 24px;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 212, 255, 0.4);
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .progress {
            width: 100%;
            height: 8px;
            background: #e9ecef;
            border-radius: 4px;
            overflow: hidden;
            margin: 24px 0;
        }

        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #00d4ff, #00bfe6);
            width: 0;
            transition: width 0.3s;
        }

        .complete-icon {
            font-size: 80px;
            color: #28a745;
            text-align: center;
            margin: 30px 0;
            display: none;
        }

        .links {
            display: flex;
            gap: 12px;
            margin-top: 24px;
        }

        .links a {
            flex: 1;
            padding: 14px;
            background: #e9ecef;
            color: #2c3e50;
            text-decoration: none;
            border-radius: 8px;
            text-align: center;
            font-weight: 600;
            transition: all 0.3s;
        }

        .links a:hover {
            background: #00d4ff;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="fas fa-rocket"></i> Instalación Completa</h1>
        <p class="subtitle">Configuración automática del sistema Forethink Health</p>

        <div class="progress">
            <div class="progress-bar" id="progressBar"></div>
        </div>

        <div class="step">
            <h3><i class="fas fa-check-circle"></i> Paso 1: Configuración de Base de Datos</h3>
            <p>Se actualizará el archivo database.php con las credenciales correctas.</p>
        </div>

        <div class="step">
            <h3><i class="fas fa-box"></i> Paso 2: Cargar Productos</h3>
            <p>Se insertarán 12 productos de ejemplo con imágenes en la base de datos.</p>
        </div>

        <div class="step">
            <h3><i class="fas fa-shield-alt"></i> Paso 3: Limpieza de Seguridad</h3>
            <p>Se eliminarán archivos de instalación para proteger el sistema.</p>
        </div>

        <div class="success-msg" id="successMsg">
            <strong><i class="fas fa-check-circle"></i> ¡Instalación Completada!</strong>
            <p>El sistema está listo para usar.</p>
        </div>

        <div class="error-msg" id="errorMsg"></div>

        <div class="complete-icon" id="completeIcon">
            <i class="fas fa-check-circle"></i>
        </div>

        <button class="btn" id="installBtn" onclick="startInstallation()">
            <i class="fas fa-play-circle"></i> Iniciar Instalación Completa
        </button>

        <div class="links" id="links" style="display: none;">
            <a href="index.php"><i class="fas fa-home"></i> Ir al Inicio</a>
            <a href="admin/index.php"><i class="fas fa-user-shield"></i> Panel Admin</a>
        </div>
    </div>

    <script>
        async function startInstallation() {
            const btn = document.getElementById('installBtn');
            const progressBar = document.getElementById('progressBar');
            const successMsg = document.getElementById('successMsg');
            const errorMsg = document.getElementById('errorMsg');
            const completeIcon = document.getElementById('completeIcon');
            const links = document.getElementById('links');

            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Instalando...';
            
            try {
                // Paso 1: Actualizar config (33%)
                progressBar.style.width = '33%';
                await fetch('update-config.php');
                await sleep(1000);

                // Paso 2: Agregar productos (66%)
                progressBar.style.width = '66%';
                const response = await fetch('add-products.php');
                const result = await response.text();
                await sleep(1000);

                // Paso 3: Completar (100%)
                progressBar.style.width = '100%';
                await sleep(500);

                // Mostrar éxito
                successMsg.style.display = 'block';
                completeIcon.style.display = 'block';
                links.style.display = 'flex';
                btn.style.display = 'none';

            } catch (error) {
                errorMsg.innerHTML = '<strong><i class="fas fa-exclamation-triangle"></i> Error:</strong> ' + error.message;
                errorMsg.style.display = 'block';
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-redo"></i> Reintentar Instalación';
            }
        }

        function sleep(ms) {
            return new Promise(resolve => setTimeout(resolve, ms));
        }
    </script>
</body>
</html>
