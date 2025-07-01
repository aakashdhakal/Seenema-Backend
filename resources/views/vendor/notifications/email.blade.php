<!DOCTYPE html>
<html lang="en" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">

<head>
    <meta charset="utf-8">
    <meta name="x-apple-disable-message-reformatting">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="format-detection" content="telephone=no, date=no, address=no, email=no">
    <title>{{ config('app.name') }}</title>
    <style>
        @media (max-width: 600px) {
            .sm-w-full {
                width: 100% !important;
            }

            .sm-px-24 {
                padding-left: 24px !important;
                padding-right: 24px !important;
            }

            .sm-py-32 {
                padding-top: 32px !important;
                padding-bottom: 32px !important;
            }
        }
    </style>
</head>

<body
    style="margin: 0; width: 100%; padding: 0; word-break: break-word; -webkit-font-smoothing: antialiased; background-color: #f1faf5;">
    <div style="display: none;">
        A message from {{ config('app.name') }}
        &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847;
        &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847;
        &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847;
        &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847;
        &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847;
        &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847;
        &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847;
        &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847;
        &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847;
        &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847;
        &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &zwnj;
        &#160;
    </div>
    <div role="article" aria-roledescription="email" aria-label="Email from {{ config('app.name') }}" lang="en">
        <table style="width: 100%; font-family: 'Segoe UI', sans-serif;" cellpadding="0" cellspacing="0"
            role="presentation">
            <tr>
                <td align="center" style="background-color: #f1faf5; padding-top: 24px; padding-bottom: 24px;">
                    <table class="sm-w-full" style="width: 600px;" cellpadding="0" cellspacing="0" role="presentation">
                        <tr>
                            <td class="sm-py-32 sm-px-24"
                                style="border-radius: 12px; padding: 48px; text-align: center;">
                                <a href="{{ url('/') }}">
                                    <img src="https://raw.githubusercontent.com/aakashdhakal/Seenema-Frontend/refs/heads/master/public/3.png?token=GHSAT0AAAAAACVJCPMGSVI4I64WESNONDEM2C4DDCQ"
                                        width="50" height="100" alt="{{ config('app.name') }} Logo"
                                        style="border: 0; width: 50%; line-height: 100%; vertical-align: middle; margin-bottom: 24px; background: transparent; padding: 8px; object-fit:cover">
                                </a>
                                <p style="margin: 0; font-size: 20px; color: #00a855; text-align: center">See it. Feel
                                    it. Seenema
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <td class="sm-px-24"
                                style="background-color: #fefffe; padding: 48px; border-bottom-left-radius: 12px; border-bottom-right-radius: 12px; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.05);">

                                {{-- Greeting --}}
                                @if (!empty($greeting))
                                    <h2 style="font-size: 24px; font-weight: 600; color: #0c1a14;">{{ $greeting }}</h2>
                                @else
                                    @if ($level === 'error')
                                        <h2 style="font-size: 24px; font-weight: 600; color: #dc2626;">Whoops!</h2>
                                    @else
                                        <h2 style="font-size: 24px; font-weight: 600; color: #0c1a14;">Hello!</h2>
                                    @endif
                                @endif

                                {{-- Intro Lines --}}
                                @foreach ($introLines as $line)
                                    <p
                                        style="margin: 0; margin-top: 24px; font-size: 16px; line-height: 24px; color: #47654f;">
                                        {{ $line }}
                                    </p>
                                @endforeach

                                {{-- Action Button --}}
                                @isset($actionText)
                                                                <div style="margin-top: 32px; margin-bottom: 32px; text-align: center;">
                                                                    <?php
                                    $color = match ($level) {
                                        'success' => '#00a855',
                                        'error' => '#dc2626',
                                        default => '#00a855',
                                    };
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    ?>
                                                                    <a href="{{ $actionUrl }}"
                                                                        style="display: inline-block; background-color: {{ $color }}; background-image: linear-gradient(135deg, {{ $color }} 0%, {{ substr($color, 0, 5) . '91' . substr($color, 5) }} 100%); border-radius: 8px; padding: 16px 32px; font-size: 16px; font-weight: 600; line-height: 1; color: #ffffff; text-decoration: none; box-shadow: 0 4px 6px -1px rgba(0, 168, 85, 0.1), 0 2px 4px -1px rgba(0, 168, 85, 0.06);">
                                                                        <!--[if mso]><i style="letter-spacing: 32px; mso-font-width: -100%; mso-text-raise: 30pt;">&nbsp;</i><![endif]-->
                                                                        <span style="mso-text-raise: 16pt;">{{ $actionText }} &rarr;</span>
                                                                        <!--[if mso]><i style="letter-spacing: 32px; mso-font-width: -100%;">&nbsp;</i><![endif]-->
                                                                    </a>
                                                                </div>
                                @endisset

                                {{-- Outro Lines --}}
                                @foreach ($outroLines as $line)
                                    <p
                                        style="margin: 0; margin-top: 24px; font-size: 16px; line-height: 24px; color: #47654f;">
                                        {{ $line }}
                                    </p>
                                @endforeach

                                {{-- Salutation --}}
                                <p
                                    style="margin: 0; margin-top: 32px; font-size: 16px; line-height: 24px; color: #47654f;">
                                    @if (!empty($salutation))
                                        {{ $salutation }}
                                    @else
                                        Regards,<br>
                                        <strong>The {{ config('app.name') }} Team</strong>
                                    @endif
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <td style="height: 48px;"></td>
                        </tr>
                        {{-- Subcopy --}}
                        @isset($actionText)
                            <tr>
                                <td
                                    style="padding-left: 24px; padding-right: 24px; text-align: center; font-size: 12px; color: #8fa599;">
                                    <p style="margin: 0; margin-bottom: 16px;">
                                        @lang(
                                            "If you're having trouble clicking the \":actionText\" button, copy and paste the URL below\n" .
                                            'into your web browser:',
                                            [
                                                'actionText' => $actionText,
                                            ]
                                        )
                                    </p>
                                    <p style="margin: 0; word-break: break-all;">
                                        <a href="{{ $actionUrl }}"
                                            style="color: #00a855; text-decoration: none;">{{ $actionUrl }}</a>
                                    </p>
                                </td>
                            </tr>
                        @endisset
                        <tr>
                            <td style="height: 24px;"></td>
                        </tr>
                        <tr>
                            <td style="text-align: center; font-size: 12px; color: #8fa599;">
                                <p style="margin: 0;">&copy; {{ date('Y') }} {{ config('app.name') }}. All rights
                                    reserved.</p>
                            </td>
                        </tr>
                        <tr>
                            <td style="height: 48px;"></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
</body>

</html>