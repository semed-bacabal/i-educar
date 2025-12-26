<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <link rel="shortcut icon" href="{{ url('favicon.ico') }}">
    <title>@if(isset($title)) {!! html_entity_decode($title) !!} - @endif {{ html_entity_decode(config('legacy.app.entity.name')) }} - i-Educar</title>

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans">
    <link rel="stylesheet" href="{{ Asset::get('intranet/styles/login.css') }}">
    <link rel="stylesheet" href="{{ Asset::get('intranet/styles/font-awesome.css') }}">

    <!-- Google Tag Manager -->
    <script>
        dataLayer = [{
            'slug': '{{$config['app']['database']['dbname']}}',
            'user_id': 0
        }];

        (function (w, d, s, l, i) {
            w[l] = w[l] || [];
            w[l].push({'gtm.start': new Date().getTime(), event: 'gtm.js'});
            var f = d.getElementsByTagName(s)[0], j = d.createElement(s), dl = l != 'dataLayer' ? '&l=' + l : '';
            j.async = true;
            j.src = 'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
            f.parentNode.insertBefore(j, f);
        })(window, document, 'script', 'dataLayer', '{{ config('legacy.gtm') }}');
    </script>
    <!-- End Google Tag Manager -->

    @if($errors->count() && str_contains($errors->first(), 'errou a senha muitas vezes' ))
    <script>
        window.onload = function() {
            document.getElementById("form-login-submit").disabled = true;
            setTimeout(function () {
                document.getElementById("form-login-submit").disabled = false;
            }, 60000);
        }
    </script>
    @endif
</head>

<body style="background-image: url('intranet/imagens/login_background.jpg'); background-size: cover; background-position: center center; background-repeat: no-repeat;">

<!-- Google Tag Manager (noscript) -->
<noscript>
    <iframe src="https://www.googletagmanager.com/ns.html?id={{ config('legacy.gtm') }}" height="0" width="0" style="display:none;visibility:hidden" title="Google Tag Manager"></iframe>
</noscript>
<!-- End Google Tag Manager (noscript) -->

<div id="main">
    <style>
        #main {
            display: flex;
            width: 100%;
            gap: 2rem;
            box-sizing: border-box;
        }
        #main .half {
            flex: 1 1 50%;
            max-width: 50%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            box-sizing: border-box;
            padding: 20px;
        }
        @media (max-width: 1200px) {
            #main { flex-direction: column; }
            #main .half { max-width: 100%; }
        }
    </style>

    <div class="half" id="extra-content">
        <?php
            $avisos = [
                [
                    'imagem' => Asset::get('intranet/imagens/rematricula.png'),
                    'titulo' => 'Rematricula Digital',
                    'descricao' => 'Inscrições abertas até 30 de janeiro',
                    'link' => 'https://ieducarbacabal.com.br/rematriculadigital/formulario.php',
                ],
                [
                    'imagem' => Asset::get('intranet/imagens/seletivo.png'),
                    'titulo' => 'Avaliação Funcional de Equipe de Trabalho',
                    'descricao' => 'Inscrições abertas até 30 de janeiro',
                    'link' => 'https://ieducarbacabal.com.br/avaliacaofuncional/equipedetrabalho/formulario.php',
                ],
                [
                    'imagem' => Asset::get('intranet/imagens/seletivo.png'),
                    'titulo' => 'Avaliação Funcional de Gestor',
                    'descricao' => 'Inscrições abertas até 30 de janeiro',
                    'link' => 'https://ieducarbacabal.com.br/avaliacaofuncional/gestor/formulario.php',
                ],
                [
                    'imagem' => Asset::get('intranet/imagens/seletivo.png'),
                    'titulo' => 'Avaliação Funcional de Supervisor SEMED',
                    'descricao' => 'Inscrições abertas até 30 de janeiro',
                    'link' => 'https://ieducarbacabal.com.br/avaliacaofuncional/supervisaosemed/formulario.php',
                ],
                [
                    'imagem' => Asset::get('intranet/imagens/seletivo.png'),
                    'titulo' => 'Avaliação Funcional de Coordenador SEMED',
                    'descricao' => 'Inscrições abertas até 30 de janeiro',
                    'link' => 'https://ieducarbacabal.com.br/avaliacaofuncional/coordenacaosemed/formulario.php',
                ],
            ];
        ?>

        <div style="background: #F2600C; border-radius: 16px; padding: 18px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); width: 100%; box-sizing: border-box;">
            <h2 style="color: white; margin: 0; padding-bottom: 16px; text-align: center; font-weight: bold;">AVISOS</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px;">
                @foreach($avisos as $aviso)
                    <div style="background: #0A7307; border-radius: 12px; padding: 16px; box-shadow: 0 1px 4px rgba(0,0,0,0.06); display: flex; flex-direction: column; align-items: center;">
                        <img alt="Imagem do aviso" src="{{ $aviso['imagem'] }}" style="width: 100%; border-radius: 8px; margin-bottom: 12px;">
                        <h4 style="color: white; margin: 0 0 8px 0; font-weight: bold;">{{ $aviso['titulo'] }}</h4>
                        <!-- <p style="font-size: 14px; color: white; margin-bottom: 12px;">{{ $aviso['descricao'] }}</p> -->
                        <button
                            type="button"
                            style="margin-top: auto; padding: 8px 12px; border-radius: 6px; background-color: #2271b6ff; color: #fff; border: 1px solid #275b89; cursor: pointer; transition: background-color .2s, border-color .2s, transform .1s;"
                            onclick='window.open(@json($aviso["link"]), "_blank")'
                            onmouseover="this.style.backgroundColor='#1e4770'; this.style.borderColor='#1e4770'; this.style.transform='translateY(-1px)';"
                            onmouseout="this.style.backgroundColor='#275b89'; this.style.borderColor='#275b89'; this.style.transform='none';"
                        >
                            Saiba mais
                        </button>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="half">
        <div>
            <img alt="Logo" style="width: 150px" src="{{ config('legacy.config.ieducar_image') ?? url('intranet/imagens/brasao-republica.png') }}" >
        </div>

        <h1>{{ config('legacy.config.ieducar_entity_name') }}</h1>

        @if (session('status'))
            <p class="success">{{ session('status') }}</p>
        @endif

        @if($errors->count())
            <p class="error">{{ $errors->first() }}</p>
        @endif

        <div id="login-form" class="box shadow">
            @yield('content')
        </div>
    </div>

</div>

<div id="footer">
    <div>
        {!! config('legacy.config.ieducar_login_footer') !!}
    </div>

    <div class="footer-social">

        {!! config('legacy.config.ieducar_external_footer') !!}

        @if(config('legacy.config.facebook_url') || config('legacy.config.linkedin_url') || config('legacy.config.twitter_url'))
            <div class="social-icons">
                <p> Siga-nos nas redes sociais&nbsp;&nbsp;</p>
                @if(config('legacy.config.facebook_url'))
                    <a target="_blank" href="{{ config('legacy.config.facebook_url')}}" rel="noopener"><img alt="Logomarca do Facebbok" src="{{ Asset::get('intranet/imagens/icon-social-facebook.png') }}"></a>
                @endif
                @if(config('legacy.config.linkedin_url'))
                    <a target="_blank" href="{{ config('legacy.config.linkedin_url')}}" rel="noopener"><img alt="Logomarca do Linkedin" src="{{ Asset::get('intranet/imagens/icon-social-linkedin.png') }}"></a>
                @endif
                @if(config('legacy.config.twitter_url'))
                    <a target="_blank" href="{{ config('legacy.config.twitter_url')}}" rel="noopener"><img alt="Logomarca do Twitter" src="{{ Asset::get('intranet/imagens/icon-social-twitter.png') }}"></a>
                @endif
            </div>
        @endif
    </div>
</div>

</body>
</html>
