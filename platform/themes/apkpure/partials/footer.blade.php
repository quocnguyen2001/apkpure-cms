<footer class="footer">
    <div class="footer-content">
        <div class="footer-columns">
            {{-- Follow Us --}}
            <div class="item">
                <div class="title">{{ __('Follow Us') }}</div>
                <ul class="social-list">
                    @if ($socialLinks = Theme::getSocialLinks())
                        @foreach($socialLinks as $socialLink)
                            @php
                                $socialClass = strtolower($socialLink->social_name ?? 'link');
                                $socialUrl = $socialLink->social_url ?? '#';
                                // Validate URL protocol - only allow http/https
                                $isValidUrl = preg_match('/^https?:\/\//i', $socialUrl);
                            @endphp
                            @if($isValidUrl)
                                <li><a href="{{ $socialUrl }}" class="social-link {{ e($socialClass) }}" title="{{ $socialLink->social_name }}" target="_blank" rel="noopener noreferrer"></a></li>
                            @endif
                        @endforeach
                    @else
                        <li><a href="#" class="social-link facebook" title="Facebook"></a></li>
                        <li><a href="#" class="social-link twitter" title="Twitter"></a></li>
                        <li><a href="#" class="social-link youtube" title="YouTube"></a></li>
                        <li><a href="#" class="social-link instagram" title="Instagram"></a></li>
                    @endif
                </ul>
            </div>

            {{-- Service --}}
            <div class="item">
                <div class="title">{{ __('Service') }}</div>
                <ul>
                    <li><a href="{{ url('/apk-install') }}">{{ __('APK Install') }}</a></li>
                    <li><a href="{{ url('/signature-verification') }}">{{ __('APK Signature Verification') }}</a></li>
                    <li><a href="{{ url('/download-service') }}">{{ __('APK Download Service') }}</a></li>
                </ul>
            </div>

            {{-- Developers --}}
            <div class="item">
                <div class="title">{{ __('Developers') }}</div>
                <ul>
                    <li><a href="{{ url('/developer-console') }}">{{ __('Developer Console') }}</a></li>
                    <li><a href="{{ url('/submit-apk') }}">{{ __('Submit APK') }}</a></li>
                </ul>
            </div>

            {{-- Company --}}
            <div class="item">
                <div class="title">{{ __('Company') }}</div>
                <ul>
                    <li><a href="{{ url('/about') }}">{{ __('About Us') }}</a></li>
                    <li><a href="{{ url('/contact') }}">{{ __('Contact Us') }}</a></li>
                    <li><a href="{{ url('/support') }}">{{ __('Support Center') }}</a></li>
                </ul>
            </div>
        </div>

        <div class="other">
            <div class="info">
                @if ($copyright = Theme::getSiteCopyright())
                    {{-- Note: getSiteCopyright returns admin-controlled HTML, treated as trusted --}}
                    {!! BaseHelper::clean($copyright) !!}
                @else
                    {{ __('Copyright') }} &copy; 2014-{{ date('Y') }} {{ theme_option('site_title', 'APKPure') }} {{ __('All rights reserved.') }}
                @endif
                | <a href="{{ url('/privacy-policy') }}">{{ __('Privacy Policy') }}</a>
                | <a href="{{ url('/terms') }}">{{ __('Terms') }}</a>
            </div>
            <div class="current_box">
                <div class="current_lang">{{ strtoupper(app()->getLocale()) }}</div>
            </div>
        </div>
    </div>
</footer>
