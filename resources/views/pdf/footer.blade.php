<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #6b7280;
            text-align: center;
            padding: 10px 20px;
            border-top: 1px solid #e5e7eb;
        }
        .footer-content {
            display: flex;
            justify-content: space-between;
            width: 100%;
        }
        .footer-left {
            text-align: left;
        }
        .footer-center {
            text-align: center;
        }
        .footer-right {
            text-align: right;
        }
        a {
            color: #026fe5;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="footer-content">
        <div class="footer-left">
            Ezles - {{ date('Y') }}
        </div>
        <div class="footer-center">
            <a href="mailto:contact@ezles.dev">contact@ezles.dev</a>
        </div>
        <div class="footer-right">
            Page <span class="pageNumber"></span> sur <span class="totalPages"></span>
        </div>
    </div>
</body>
</html> 