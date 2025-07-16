<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?= env('LICENCED'); ?> - <?php echo $this->renderSection('titulo'); ?> </title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="all,follow">
    <!-- Bootstrap CSS-->
    <link rel="stylesheet" href="<?php echo site_url('recursos/'); ?>vendor/bootstrap/css/bootstrap.min.css">
    <!-- Font Awesome CSS-->
    <link rel="stylesheet" href="<?php echo site_url('recursos/'); ?>vendor/font-awesome/css/font-awesome.min.css">
    <!-- Custom Font Icons CSS-->
    <link rel="stylesheet" href="<?php echo site_url('recursos/'); ?>css/font.css">
    <!-- Google fonts - Muli-->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Muli:300,400,700">
    <!-- theme stylesheet-->
    <link rel="stylesheet" href="<?php echo site_url('recursos/'); ?>css/style.default.css" id="theme-stylesheet">
    <!-- Custom stylesheet - for your changes-->
    <link rel="stylesheet" href="<?php echo site_url('recursos/'); ?>css/custom.css">
    <!-- Favicon-->
    <link rel="shortcut icon" href="<?php echo site_url('recursos/'); ?>img/favicon.ico">
    <!-- Tweaks for older IEs-->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script><![endif]-->


    <!-- Espaço reservado para renderizar os estilos de cada view  que estender esse layout -->
    <?php echo $this->renderSection('estilos'); ?>

</head>

<body>
    <header class="header">
        <nav class="navbar navbar-expand-lg">
            <div class="search-panel">
                <div class="search-inner d-flex align-items-center justify-content-center">
                    <div class="close-btn">Close <i class="fa fa-close"></i></div>
                    <form id="searchForm" action="#">
                        <div class="form-group">
                            <input type="search" name="search" placeholder="What are you searching for...">
                            <button type="submit" class="submit">Search</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="container-fluid d-flex align-items-center justify-content-between">
                <div class="navbar-header">
                    <!-- Navbar Header--><a href="#" class="navbar-brand">
                        <div class="brand-text brand-big visible text-uppercase">MUNDO <strong> DREAM</strong></div>
                        <div class="brand-text brand-sm"><strong style="color: #fff;">M</strong><strong>D</strong></div>
                    </a>
                    <!-- Sidebar Toggle Btn-->
                </div>
                <div class="right-menu list-inline no-margin-bottom">

                    <!-- Log out               -->
                    <div class="list-inline-item logout">

                        <a id="logout" href="<?php echo site_url('logout'); ?>" class="nav-link">Sair <i class="icon-logout"></i></a>
                    </div>
                </div>
            </div>
        </nav>
    </header>
    <div class="d-flex align-items-stretch">
        <!-- Sidebar Navigation-->
        <nav id="sidebar">
            <!-- Sidebar Header-->
            <div class="sidebar-header d-flex align-items-center">





            </div>
            <!-- Sidebar Navidation Menus-->
            <ul class="list-unstyled">
                <?php if (!usuario_logado()->is_cliente) : ?>
                    <li class="<?php echo (url_is('/') ? 'active' : '') ?>"><a href="<?php echo site_url('/'); ?>"> <i class="icon-home"></i>Home </a></li>
                <?php endif; ?>

                <?php if (usuario_logado()->is_cliente) : ?>
                    <li class="<?php echo (url_is('console/dashboard') ? 'active' : '') ?>"><a href="<?php echo site_url('console/dashboard'); ?>"> <i class="fas fa-th-list"></i>Minhas declarações </a></li>
                    <li class="<?php echo (url_is('conhecimentobase*') ? 'active' : '') ?>"><a href="<?php echo site_url('conhecimentobase/ajuda'); ?>"> <i class="fas fa-info-circle"></i>Central de ajuda </a></li>
                    <li class="<?php echo (url_is('declarations/criar_dici_scm') ? 'active' : '') ?>"><a href="#exampledropdownDropdown" aria-expanded="false" data-toggle="collapse"> <i class="fas fa-plus"></i>Nova declaração </a>

                        <ul id="exampledropdownDropdown" class="collapse list-unstyled ">
                            <?php if (usuario_logado()->temPermissaoPara('dici-scm-mensal')) : ?>
                                <li><a href="<?php echo site_url('declarations/criar_dici_scm'); ?>"><i class="fas fa-plus"></i>DICI-SCM | Mensal</a></li>
                            <?php endif; ?>
                            <?php if (usuario_logado()->temPermissaoPara('dici-trimestral')) : ?>
                                <li><a href="<?php echo site_url('declarations/criar_dici_scm_trimestral'); ?>"><i class="fas fa-plus"></i>DICI-SCM | Trimestral</a></li>
                            <?php endif; ?>
                            <?php if (usuario_logado()->temPermissaoPara('dici-TvPA-mensal')) : ?>
                                <li><a href="<?php echo site_url('declarations/criar_dici_tvpa'); ?>"><i class="fas fa-plus"></i>DICI-TvPA</a></li>
                            <?php endif; ?>
                            <?php if (usuario_logado()->temPermissaoPara('dici-TvPA-STFC')) : ?>
                                <li><a href="<?php echo site_url('declarations/criar_dici_stfc'); ?>"><i class="fas fa-plus"></i>DICI-STFC</a></li>
                            <?php endif; ?>
                            <?php if (usuario_logado()->temPermissaoPara('fust')) : ?>
                                <li><a href="<?php echo site_url('declarations/criar_fust'); ?>"><i class="fas fa-plus"></i>FUST</a></li>
                            <?php endif; ?>
                            <?php if (usuario_logado()->temPermissaoPara('dici-scm-mensal')) : ?>
                                <li><a href="<?php echo site_url('declarations/criar_dici_scm_anual'); ?>"><i class="fas fa-plus"></i>DICI-SCM | Anual</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>

                <?php endif; ?>


                <?php if (usuario_logado()->temPermissaoPara('listar-clientes')) : ?>

                    <li class="<?php echo (url_is('clientes*') ? 'active' : '') ?>"><a href="<?php echo site_url('clientes'); ?>"> <i class="fas fa-users"></i>Clientes </a></li>

                <?php endif; ?>

                <?php if (usuario_logado()->temPermissaoPara('listar-declaracoes')) : ?>

                    <li class="<?php echo (url_is('declarations*') ? 'active' : '') ?>"><a href="<?php echo site_url('declarations'); ?>"> <i class="fas fa-archive"></i>Declarações </a></li>

                <?php endif; ?>

                <?php if (usuario_logado()->temPermissaoPara('listar-declaracoes')) : ?>

                    <li class="<?php echo (url_is('conhecimentobase*') ? 'active' : '') ?>"><a href="<?php echo site_url('conhecimentobase'); ?>"> <i class="fas fa-info-circle"></i>Base de conhecimento </a></li>

                <?php endif; ?>


                <?php if (usuario_logado()->temPermissaoPara('listar-eventos')) : ?>

                    <li class="<?php echo (url_is('eventos*') ? 'active' : '') ?>"><a href="<?php echo site_url('eventos'); ?>"> <i class="fas fa-calendar-alt"></i>Eventos</a></li>

                <?php endif; ?>


                <?php if (usuario_logado()->temPermissaoPara('listar-usuarios')) : ?>

                    <li class="<?php echo (url_is('usuarios*') ? 'active' : '') ?>"><a href="<?php echo site_url('usuarios'); ?>"> <i class="far fa-user-circle"></i>Usuários </a></li>


                <?php endif; ?>


                <?php if (usuario_logado()->temPermissaoPara('listar-grupos')) : ?>

                    <li class="<?php echo (url_is('grupos*') ? 'active' : '') ?>"><a href="<?php echo site_url('grupos'); ?>"> <i class="icon-settings"></i>Grupos & Permissões </a>
                    </li>

                <?php endif; ?>





                <?php if (usuario_logado()->temPermissaoPara('listar-eventos')) : ?>

                    <li class="<?php echo (url_is('notifications*') ? 'active' : '') ?>"><a href="<?php echo site_url('notifications'); ?>"> <i class="fa fa-bell"></i>Notificações</a></li>

                <?php endif; ?>


                <li><a href="<?php echo site_url('usuarios/perfil'); ?>"> <i class="icon-settings"></i>Alterar
                        senha </a></li>



        </nav>
        <!-- Sidebar Navigation end-->
        <div class="page-content">
            <div class="page-header">

            </div>
            <section class="no-padding-top no-padding-bottom">


                <div class="container-fluid">

                    <?php echo $this->include('Layout/_mensagens'); ?>

                    <!-- Espaço reservado para renderizar o conteúdo de cada view  que estender esse layout -->
                    <?php echo $this->renderSection('conteudo'); ?>

                </div>


            </section>


            <footer class="footer">

                <div class="container-fluid text-center">
                    <!-- Please do not remove the backlink to us unless you support us at https://bootstrapious.com/donate. It is part of the license conditions. Thank you for understanding :)-->
                    <p class="no-margin-bottom"><?php echo date('Y'); ?> &copy; Desenvolvido por <a target="_blank" href="#">Eduardo Santos</a>.</p>
                    <p>Licenciado para <?= env('LICENCED'); ?></p>
                </div>

            </footer>
        </div>
    </div>
    <script src="https://kit.fontawesome.com/f5b74051e7.js" crossorigin="anonymous"></script>
    <!-- JavaScript files-->
    <script src="<?php echo site_url('recursos/'); ?>vendor/jquery/jquery.min.js"></script>
    <script src="<?php echo site_url('recursos/'); ?>vendor/popper.js/umd/popper.min.js"> </script>
    <script src="<?php echo site_url('recursos/'); ?>vendor/bootstrap/js/bootstrap.min.js"></script>
    <script src="<?php echo site_url('recursos/'); ?>js/front.js"></script>

    <!-- Espaço reservado para renderizar os scripts de cada view que estender esse layout -->
    <?php echo $this->renderSection('scripts'); ?>


    <?php if (url_is('eventos*')) : ?>

        <!-- Para o fullcalendar funcionar, preciso carregar os mesmos scripts que tenho na view-->
        <!-- No entanto, eles serão carregados apenas quando estiver no controller Eventos -->
        <script src="<?php echo site_url('recursos/vendor/fullcalendar/fullcalendar.min.js'); ?>"></script>
        <script src="<?php echo site_url('recursos/vendor/fullcalendar/toastr.min.js'); ?>"></script>
        <script src="<?php echo site_url('recursos/vendor/fullcalendar/moment.min.js'); ?>"></script>
    <?php endif ?>

    <script>
        $(function() {
            $('[data-toggle="popover"]').popover({
                html: true,
            });
        })
    </script>

</body>

</html>