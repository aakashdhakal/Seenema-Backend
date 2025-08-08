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

            .sm-px-16 {
                padding-left: 16px !important;
                padding-right: 16px !important;
            }

            .sm-py-20 {
                padding-top: 20px !important;
                padding-bottom: 20px !important;
            }

            .sm-text-base {
                font-size: 16px !important;
            }

            .sm-logo {
                width: 140px !important;
                height: 70px !important;
            }
        }

        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            .dark-bg {
                background-color: #f8fafc !important;
            }

            .dark-text {
                color: #1f2937 !important;
            }
        }
    </style>
</head>

<body
    style="margin: 0; width: 100%; padding: 0; word-break: break-word; -webkit-font-smoothing: antialiased; background-color: #f1f5f9;">

    <!-- Preheader for better inbox display -->
    <div
        style="display: none; font-size: 1px; color: transparent; line-height: 1px; max-height: 0px; max-width: 0px; opacity: 0; overflow: hidden;">
        @if($level === 'error')
            Important notification from {{ config('app.name') }}
        @else
            Welcome to {{ config('app.name') }} - Your streaming experience awaits
        @endif
        &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847;
        &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847;
        &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &zwnj; &#160;
    </div>

    <div role="article" aria-roledescription="email" aria-label="Email from {{ config('app.name') }}" lang="en">
        <table
            style="width: 100%; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;"
            cellpadding="0" cellspacing="0" role="presentation">
            <tr>
                <td align="center" style="background-color: #f1f5f9; padding: 20px 16px;">
                    <table class="sm-w-full" style="width: 600px; max-width: 100%;" cellpadding="0" cellspacing="0"
                        role="presentation">

                        <!-- Single Card Container -->
                        <tr>
                            <td class="sm-px-16"
                                style="background-color: #ffffff; border-radius: 12px; padding: 40px 32px; box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06); border: 1px solid #e5e7eb;">

                                <!-- Logo Section -->
                                <div style="text-align: center; margin-bottom: 24px;">
                                    <a href="{{ url('/') }}" style="text-decoration: none; display: inline-block;">
                                        <!--[if mso]>
                                        <table cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td style="width: 160px; height: 80px;">
                                        <![endif]-->
                                        <img src="https://res.cloudinary.com/do4gmzrn8/image/upload/v1752080576/3_dmmvsv.png"
                                            width="160" height="80" alt="{{ config('app.name') }} Logo" class="sm-logo"
                                            style="border: 0; 
                                                    width: 160px; 
                                                    height: 80px; 
                                                    max-width: 160px; 
                                                    max-height: 80px; 
                                                    display: block; 
                                                    margin: 0 auto; 
                                                    object-fit: contain;
                                                    vertical-align: middle;
                                                    background: transparent;">
                                        <!--[if mso]>
                                                </td>
                                            </tr>
                                        </table>
                                        <![endif]-->
                                    </a>
                                </div>


                                <!-- Main Heading -->
                                @if (!empty($greeting))
                                    <h2
                                        style="margin: 0 0 20px; font-size: 26px; font-weight: 700; color: #1f2937; text-align: center; line-height: 1.3;">
                                        {{ $greeting }}
                                    </h2>
                                @else
                                    @if ($level === 'error')
                                        <h2
                                            style="margin: 0 0 20px; font-size: 26px; font-weight: 700; color: #dc2626; text-align: center; line-height: 1.3;">
                                            Oops! Something went wrong
                                        </h2>
                                    @else
                                        <h2
                                            style="margin: 0 0 20px; font-size: 26px; font-weight: 700; color: #1f2937; text-align: center; line-height: 1.3;">
                                            Welcome to {{ config('app.name') }}!
                                        </h2>
                                    @endif
                                @endif

                                <!-- Content Lines -->
                                @foreach ($introLines as $line)
                                    <p
                                        style="margin: 0 0 16px; font-size: 16px; line-height: 1.6; color: #4b5563; text-align: center;">
                                        {{ $line }}
                                    </p>
                                @endforeach

                                <!-- Action Button -->
                                @isset($actionText)
                                                                <div style="margin: 32px 0; text-align: center;">
                                                                    <?php
                                    $buttonColor = match ($level) {
                                        'success' => '#00a855',
                                        'error' => '#dc2626',
                                        default => '#00a855',
                                    };
                                    $buttonHoverColor = match ($level) {
                                        'success' => '#059669',
                                        'error' => '#b91c1c',
                                        default => '#059669',
                                    };
                                                                                                        ?>
                                                                    <!--[if mso]>
                                                                                                        <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{{ $actionUrl }}" style="height:48px;v-text-anchor:middle;width:240px;" arcsize="25%" stroke="f" fillcolor="{{ $buttonColor }}">
                                                                                                            <w:anchorlock/>
                                                                                                            <center style="color:#ffffff;font-family:sans-serif;font-size:16px;font-weight:bold;">{{ $actionText }}</center>
                                                                                                        </v:roundrect>
                                                                                                        <![endif]-->
                                                                    <!--[if !mso]><!-->
                                                                    <a href="{{ $actionUrl }}" style="display: inline-block; 
                                                                                                                  background: {{ $buttonColor }}; 
                                                                                                                  border-radius: 10px; 
                                                                                                                  padding: 16px 32px; 
                                                                                                                  font-size: 16px; 
                                                                                                                  font-weight: 600; 
                                                                                                                  line-height: 1; 
                                                                                                                  color: #ffffff; 
                                                                                                                  text-decoration: none; 
                                                                                                                  box-shadow: 0 4px 12px rgba(0, 168, 85, 0.25);
                                                                                                                  transition: all 0.3s ease;
                                                                                                                  border: none;
                                                                                                                  min-width: 200px;
                                                                                                                  text-align: center;">
                                                                        {{ $actionText }}
                                                                    </a>
                                                                    <!--<![endif]-->
                                                                </div>
                                @endisset

                                <!-- Additional Content -->
                                @foreach ($outroLines as $line)
                                    <p
                                        style="margin: 0 0 16px; font-size: 16px; line-height: 1.6; color: #4b5563; text-align: center;">
                                        {{ $line }}
                                    </p>
                                @endforeach

                                <!-- Divider -->
                                <div style="margin: 28px 0; border-top: 1px solid #e5e7eb;"></div>

                                <!-- Closing -->
                                <div style="text-align: center; margin-bottom: 24px;">
                                    <p style="margin: 0 0 8px; font-size: 16px; line-height: 1.6; color: #6b7280;">
                                        @if (!empty($salutation))
                                            {{ $salutation }}
                                        @else
                                            Best regards,
                                        @endif
                                    </p>
                                    <p style="margin: 0; font-size: 16px; font-weight: 600; color: #1f2937;">
                                        The {{ config('app.name') }} Team
                                    </p>
                                </div>

                                <!-- Alternative Link -->
                                @isset($actionText)
                                    <div
                                        style="background: #f8fafc; border-radius: 8px; padding: 16px; margin: 24px 0; border: 1px solid #e5e7eb;">
                                        <p
                                            style="margin: 0 0 8px; font-size: 13px; color: #6b7280; line-height: 1.5; text-align: center;">
                                            @lang("If you're having trouble with the button above, copy and paste this URL into your browser:", ['actionText' => $actionText])
                                        </p>
                                        <p style="margin: 0; word-break: break-all; font-size: 12px; text-align: center;">
                                            <a href="{{ $actionUrl }}"
                                                style="color: #00a855; text-decoration: none; font-family: monospace; padding: 4px 8px; background: #ffffff; border-radius: 4px; border: 1px solid #d1d5db; display: inline-block;">
                                                {{ $actionUrl }}
                                            </a>
                                        </p>
                                    </div>
                                @endisset

                                <!-- Footer -->
                                <div
                                    style="text-align: center; margin-top: 24px; padding-top: 20px; border-top: 1px solid #e5e7eb;">

                                    <p style="margin: 0; font-size: 12px; color: #9ca3af; text-align: center;">
                                        &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
</body>

</html>