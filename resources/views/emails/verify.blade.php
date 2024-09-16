<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Подтверждение email-адреса - Djigit Cards</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #ff3d00;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .content {
            background-color: #fff9f5;
            border: 1px solid #ffccbc;
            border-radius: 4px;
            padding: 20px;
            margin-top: 20px;
        }
        .verification-code {
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            color: #ff3d00;
            margin: 20px 0;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 12px;
            color: #888888;
        }
    </style>
</head>
<body>
<div class="header">
    <h1>Djigit Cards</h1>
</div>
<div class="content">
    <h2>Подтверждение email-адреса</h2>
    <p>Уважаемый пользователь,</p>
    <p>Спасибо за регистрацию в Djigit Cards. Для завершения процесса регистрации, пожалуйста, введите следующий код подтверждения:</p>
    <div class="verification-code">{{ $verificationCode }}</div>
    <p>Если вы не запрашивали это подтверждение, пожалуйста, проигнорируйте данное письмо.</p>
    <p>С уважением,<br>Команда Djigit Cards</p>
</div>
<div class="footer">
    <p>Это автоматическое сообщение, пожалуйста, не отвечайте на него.</p>
    <p>&copy; 2024 Djigit Cards. Все права защищены.</p>
</div>
</body>
</html>