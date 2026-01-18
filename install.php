<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalación - Forethink Health</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #00d4ff 0%, #00bfe6 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            max-width: 600px;
            width: 100%;
            padding: 40px;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo h1 {
            color: #00d4ff;
            font-size: 32px;
            margin-bottom: 10px;
        }
        
        .logo p {
            color: #666;
        }
        
        .step {
            background: #f5f5f5;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .step h3 {
            color: #333;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }
        
        .step-number {
            background: #00d4ff;
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            font-weight: bold;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-weight: 500;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .form-group small {
            color: #666;
            font-size: 12px;
            display: block;
            margin-top: 5px;
        }
        
        .btn-install {
            width: 100%;
            padding: 15px;
            background: #00d4ff;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-install:hover {
            background: #00bfe6;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,212,255,0.3);
        }
        
        .btn-install:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }
        
        .result {
            margin-top: 20px;
            padding: 20px;
            border-radius: 8px;
            display: none;
        }
        
        .result.success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        
        .result.error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        
        .result h4 {
            margin-bottom: 10px;
        }
        
        .result ul {
            margin-left: 20px;
        }
        
        .result li {
            margin-bottom: 5px;
        }
        
        .loading {
            display: none;
            text-align: center;
            padding: 20px;
        }
        
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #00d4ff;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 15px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .warning {
            background: #fff3cd;
            border: 1px solid #ffc107;
            color: #856404;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .warning strong {
            display: block;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <h1>🏥 Forethink Health</h1>
            <p>Instalación Automática</p>
        </div>
        
        <div class="warning">
            <strong>⚠️ Importante:</strong>
            Esta página creará automáticamente todas las tablas y datos iniciales. Solo úsala una vez.
        </div>
        
        <form id="installForm">
            <div class="step">
                <h3><span class="step-number">1</span> Configuración de Base de Datos</h3>
                
                <div class="form-group">
                    <label>Host de MySQL</label>
                    <input type="text" name="db_host" value="localhost" required>
                    <small>Generalmente es "localhost" en Hostinger</small>
                </div>
                
                <div class="form-group">
                    <label>Nombre de la Base de Datos</label>
                    <input type="text" name="db_name" placeholder="u851317150_forethink" required>
                    <small>Lo encuentras en: Panel Hostinger → Bases de datos MySQL</small>
                </div>
                
                <div class="form-group">
                    <label>Usuario de MySQL</label>
                    <input type="text" name="db_user" placeholder="u851317150_fh" required>
                    <small>Usuario de tu base de datos</small>
                </div>
                
                <div class="form-group">
                    <label>Contraseña de MySQL</label>
                    <input type="password" name="db_pass" required>
                    <small>Contraseña de tu base de datos</small>
                </div>
            </div>
            
            <button type="submit" class="btn-install" id="btnInstall">
                🚀 Instalar Base de Datos
            </button>
        </form>
        
        <div class="loading" id="loading">
            <div class="spinner"></div>
            <p>Instalando base de datos...</p>
        </div>
        
        <div class="result" id="result"></div>
    </div>
    
    <script>
        document.getElementById('installForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const btn = document.getElementById('btnInstall');
            const loading = document.getElementById('loading');
            const result = document.getElementById('result');
            const form = document.getElementById('installForm');
            
            // Deshabilitar botón y mostrar loading
            btn.disabled = true;
            loading.style.display = 'block';
            result.style.display = 'none';
            
            // Obtener datos del formulario
            const formData = new FormData(this);
            
            try {
                const response = await fetch('install-process.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                loading.style.display = 'none';
                result.style.display = 'block';
                
                if (data.success) {
                    result.className = 'result success';
                    result.innerHTML = `
                        <h4>✅ ¡Instalación Exitosa!</h4>
                        <ul>
                            ${data.messages.map(msg => `<li>${msg}</li>`).join('')}
                        </ul>
                        <p style="margin-top: 15px;">
                            <strong>Credenciales de Admin:</strong><br>
                            Email: admin@forethinkhealth.com<br>
                            Password: admin123
                        </p>
                        <p style="margin-top: 15px;">
                            <a href="index.php" style="color: #00d4ff; font-weight: bold;">→ Ir al Sitio</a> | 
                            <a href="admin/index.php" style="color: #00d4ff; font-weight: bold;">→ Ir al Admin</a>
                        </p>
                    `;
                    form.style.display = 'none';
                } else {
                    result.className = 'result error';
                    result.innerHTML = `
                        <h4>❌ Error en la Instalación</h4>
                        <p>${data.message}</p>
                        ${data.errors ? '<ul>' + data.errors.map(err => `<li>${err}</li>`).join('') + '</ul>' : ''}
                    `;
                    btn.disabled = false;
                }
            } catch (error) {
                loading.style.display = 'none';
                result.style.display = 'block';
                result.className = 'result error';
                result.innerHTML = `
                    <h4>❌ Error de Conexión</h4>
                    <p>No se pudo conectar con el servidor. Verifica tu configuración.</p>
                    <p><small>${error.message}</small></p>
                `;
                btn.disabled = false;
            }
        });
    </script>
</body>
</html>
