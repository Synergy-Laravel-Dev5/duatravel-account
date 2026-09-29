<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Welcome</title>
</head>

<body style="margin:0;padding:40px 20px;background:#f4f6f9;font-family:Arial,Helvetica,sans-serif;color:#333333;">

    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%"
        style="max-width:650px;margin:0 auto;background:#ffffff;border-radius:12px;overflow:hidden;border:1px solid #e5e7eb;box-shadow:0 8px 20px rgba(0,0,0,.08);">

        <!-- Header -->
        <tr>
            <td align="center"
                style="background:linear-gradient(135deg,#171449,#171449);padding:40px 20px;color:#ffffff;">
                <h1 style="margin:0;font-size:30px;font-weight:bold;">
                    Welcome!
                </h1>
                <p style="margin:12px 0 0;font-size:16px;opacity:.9;">
                    Your company account has been created successfully.
                </p>
            </td>
        </tr>

        <!-- Body -->
        <tr>
            <td style="padding:35px;">

                <p style="margin:0 0 20px;font-size:16px;line-height:26px;">
                    Congratulations! Your company has been successfully registered in our system.
                    Below are your login credentials.
                </p>

                <table width="100%" cellpadding="0" cellspacing="0"
                    style="background:#f8f9fa;border:1px solid #e9ecef;border-radius:8px;padding:20px;">
                    <tr>
                        <td style="padding:12px 0;">
                            <strong style="display:block;color:#6c757d;font-size:13px;">LOGIN URL</strong>
                            <a href="{{ $url }}" style="color:#171449;text-decoration:none;font-size:15px;">
                                {{ $url }}
                            </a>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:12px 0;">
                            <strong style="display:block;color:#6c757d;font-size:13px;">EMAIL</strong>
                            <span style="font-size:15px;">{{ $email }}</span>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:12px 0;">
                            <strong style="display:block;color:#6c757d;font-size:13px;">PASSWORD</strong>
                            <span
                                style="display:inline-block;background:#ffffff;border:1px dashed #ced4da;padding:10px 15px;border-radius:6px;font-size:15px;font-weight:bold;letter-spacing:1px;">
                                {{ $password }}
                            </span>
                        </td>
                    </tr>
                </table>

                <div style="text-align:center;margin:35px 0;">
                    <a href="{{ $url }}"
                        style="background:#171449;color:#ffffff;text-decoration:none;padding:14px 35px;border-radius:8px;font-size:16px;font-weight:bold;display:inline-block;">
                        Login Now
                    </a>
                </div>

            </td>
        </tr>

        <!-- Footer -->
        <tr>
            <td align="center"
                style="padding:25px;background:#f8f9fa;border-top:1px solid #e9ecef;color:#6c757d;font-size:13px;line-height:22px;">

                Thank you for choosing our service.<br>
                We look forward to serving you.

                <div style="margin-top:15px;">
                    &copy; {{ date('Y') }} {{ config('app.name') }}. All Rights Reserved.
                </div>

            </td>
        </tr>

    </table>

</body>

</html>
